<?php


namespace App\Http\Controllers\Api\system;


use App\Http\Controllers\Api\BaseController;
use App\Repositories\SettingRepository;
use App\Transformers\SettingTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SettingController extends BaseController
{
    /**
     * @var SettingRepository
     */
    protected $settingRepo;

    public function __construct(SettingRepository $settingRepo)
    {
        parent::__construct();
        $this->settingRepo = $settingRepo;
    }

    /**
     * Get all of the resource.
     *
     * @return Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->settingRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->settingRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['company_id'] = Auth::user()->company_id;
        $this->settingRepo->store($data);
        return $this->response->withNoContent();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $setting = $this->settingRepo->getById($id);
        return $this->response->item($setting, new SettingTransformer());
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
        $data['row'] = $this->settingRepo->find($id);
        $this->settingRepo->update($id, $request->all());
        return $this->response->withNoContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $data['row'] = $this->settingRepo->destroy($id);
        return $this->response->withNoContent();
    }
}