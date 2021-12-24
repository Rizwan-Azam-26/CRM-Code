<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\CampaignRequest;
use App\Repositories\CampaignGroupRepository;
use Illuminate\Http\Request;

use App\Repositories\CampaignRepository;
use Illuminate\Support\Facades\Auth;
use Modules\Contact\Repositories\CaseStatusRepository;

class CampaignController extends BaseController
{
    /**
     * @var CampaignRepository
     */
    protected $campaignRepo;
    /**
     * @var CampaignGroupRepository
     */
    protected $campaignGroupRepo;
   
    protected $caseStatusRepo;

    public function __construct(
        CampaignRepository $campaignRepo,
        CampaignGroupRepository $campaignGroupRepo, 
        CaseStatusRepository $caseStatusRepo
     )
    {
        parent::__construct();

        $this->campaignRepo = $campaignRepo;
        $this->campaignGroupRepo = $campaignGroupRepo;
        $this->caseStatusRepo = $caseStatusRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->campaignRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->campaignRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CampaignRequest $request)
    {
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->campaignRepo->store($payload);
        return $this->response->withNoContent();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $campaignRepo = $this->campaignRepo->getById($id);
        return $this->response->item($campaignRepo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CampaignRequest $request, $id)
    {
        $request->request->remove('_method');
        $data['row'] = $this->campaignRepo->getById($id);
        $this->campaignRepo->update($id, $request->all());
        return $this->response->withNoContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->campaignRepo->destroy($id);
        return $this->response->withNoContent();   
    }

}
