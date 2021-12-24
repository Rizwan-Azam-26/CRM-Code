<?php


namespace App\Http\Controllers\Api\system;


use App\Http\Controllers\Api\BaseController;
use App\Models\Log;
use App\Repositories\CampaignRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\StatusRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Modules\Automation\Repositories\ActionRepository;
use Modules\Contact\Repositories\FormFieldRepository;
use Modules\Contact\Repositories\FormSectionRepository;
use Modules\Contact\Services\CaseService;
use Modules\Log\Repositories\LogFileImportRepository;

class CaseController extends BaseController
{
    /**
     * @var StatusRepository
     */
    protected $statusRepo;
    /**
     * @var CampaignRepository
     */
    protected $campaignRepo;
    /**
     * @var FormSectionRepository
     */
    protected $formSectionRepo;
    /**
     * @var FormFieldRepository
     */
    protected $formFieldRepo;
    /**
     * @var LogFileImportRepository
     */
    protected $logFileImportRepo;
    /**
     * @var CaseService
     */
    protected $caseService;
    /**
     * @var ActionRepository
     */
    protected $actionRepo;
    /**
     * @var CompanyRepository
     */
    protected $companyRepo;

    public function __construct(CompanyRepository $companyRepo, ActionRepository $actionRepo, CaseService $caseService, LogFileImportRepository $logFileImportRepository, FormFieldRepository $formFieldRepo, FormSectionRepository $formSectionRepo, CampaignRepository $campaignRepo, StatusRepository $statusRepo)
    {
        parent::__construct();
        $this->statusRepo = $statusRepo;
        $this->actionRepo = $actionRepo;
        $this->campaignRepo = $campaignRepo;
        $this->formSectionRepo = $formSectionRepo;
        $this->formFieldRepo = $formFieldRepo;
        $this->companyRepo = $companyRepo;
        $this->logFileImportRepo = $logFileImportRepository;
        $this->caseService = $caseService;
    }
    /**
     * Get all of the resource.
     *
     * @return Response
     */
    public function all(Request $request)
    {
        return $this->response->json([]);
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
        return $this->response->json([]);
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
        return $this->response->json([]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        return $this->response->json([]);
    }
    public function export(Request $request)
    {

        if(count($request->all()) > 0){
            ini_set('memory_limit', '256M');
           // Model_Case::export($request->all());
        }

        $data['statuses'] = $this->statusRepo->findAll(1);
        $data['campaigns'] = $this->campaignRepo->findAll();
        $data['fgroups'] = $this->formSectionRepo->findAllGroups();
        $data['fields'] = $this->formFieldRepo->findAllGrouped(1);
        return $this->response->json($data);

    }

    public function import(Request $request)
    {

        ini_set('auto_detect_line_endings',TRUE);

        if(count($request->all()) > 0){

            try {

                if($request->has('file') && $request->has('file')){


                    if(substr($request->file('import_file')->getClientOriginalName(),-4) != ".csv"){

                        $this->response->setStatusCode('403');
                        return $this->response->json('Error');
                    }else{

                        session()->put('import',$request->all());
                       //ask following code
                       $import_file_path = Config::get('import_folder');
                        $import_file = date('YmdHis').'/import.csv';

                        mkdir($import_file_path.str_replace('/import.csv', '', $import_file), 0777, true);
                        move_uploaded_file($request->file('import_file')->getPathName(), $import_file_path.$import_file);

                        $handle = fopen($import_file_path.$import_file, 'r');
                        $line = fgetcsv($handle);

                        fclose($handle);

                        $data['cols'] = $line;
                        $data['import_file'] = $import_file;
                        $data['fields'] = $request->ds;

                        $file_id = $this->logFileImportRepo->store(
                            array(
                                'filename' => $import_file,
                                'company_id' => $request->company_id,
                                'campaign_id' => $request->campaign_id??null,
                                'action_id' => $request->action_id??null,
                                'import_type' => $request->action,
                                'created' => date("Y-m-d H:i:s"),
                                'created_by' => Auth::user()->id
                            )
                        );
                        $import = session()->get('import');
                        $import['file_id'] = $file_id;

                        return $this->response->json($data);

                    }
                }elseif($request->has('import_file')){

                    $import['import_file'] = $request->import_file;
                    $import['cols'] = $request->cols;

                    $this->logFileImportRepo->update($import['file_id'], array(
                        'import_filename' => $request->import_file,
                        'mapped' => date('Y-m-d H:i:s')
                    ));

                    $this->caseService->fileImport($import);


                    session()->remove('import');

                }
                die('Import file not found');

            }catch (\Exception $e){
             //   var_dump($e);
                Log::error($e);
            }

        }
        // exit();

        $data['actions'] = $this->actionRepo->findByCompany(Auth::user()->company_id);
        $data['campaigns'] = $this->campaignRepo->findByCompany(Auth::user()->company_id);
        $data['companies'] = $this->companyRepo->getList();
        $data['fgroups'] = $this->formSectionRepo->findAllGroups();
        $data['fields'] = $this->formFieldRepo->findAllByCompanies(array(1,Auth::user()->company_id));

        return $this->response->json($data);

    }

}