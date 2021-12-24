<?php


namespace App\Http\Controllers\Api\system;


use App\Http\Controllers\Api\BaseController;
use App\Libraries\Handler;
use App\Repositories\CompanyRepository;
use App\Repositories\SettingRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Str;

class LogoController extends BaseController
{

    /**
     * @var CompanyRepository
     */
    protected $companyRepo;
    /**
     * @var SettingRepository
     */
    protected $settingRepo;

    public function __construct(CompanyRepository $companyRepo, SettingRepository $settingRepo)
    {
        parent::__construct();

        $this->companyRepo = $companyRepo;
        $this->settingRepo = $settingRepo;
    }

    /**
     * Get all of the resource.
     *
     * @return Response
     */
    public function all()
    {
        return $this->response->collection($this->companyRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return $this->response->json([]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        return $this->response->json([]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $logo = $this->companyRepo->getById($id);

        return $this->response->item($logo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $data['row'] = $this->companyRepo->find($id);

        //$this->has_company_access($data['row']['id'], Auth::user()->company_id);

        try {
            $file_name = Str::random('uuid') . '.png';
            $logo_file_path = '/tmp/' . $file_name;

            list($type, $data) = explode(';', $request->image);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);

            file_put_contents($logo_file_path, $data);


            $json_settings = $this->settingRepo->get('s3_skywave');
            $configs = json_decode($json_settings['value'], true);
            $upload = new Handler($configs);
            $upload->setAcl('public-read');
            $upload->setFolder('cdn/images/company_logos');
            $upload->setTempFileLocation($logo_file_path);
            $upload->setNewFilename($file_name);
            $result = $upload->upload();


            $payload = array(
                'logo' => $file_name
            );


            $this->companyRepo->update($id, $payload);
            $company = session()->get('company');
            if (Auth::user()->company_id == $id) {
                $company['logo'] = $file_name; // Update Session to show new logo
            }

            $this->response->setStatusCode('200');
            return $this->response->json('Success');
        } catch (\Exception $e) {
            $this->response->setStatusCode('403');
            return $this->response->json('Error');
        }
        return $this->response->json($data);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        return $this->response->withNoContent();
    }
}