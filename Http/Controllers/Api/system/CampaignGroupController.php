<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

use App\Http\Requests\CampaignGroupRequest;
use App\Repositories\CampaignRepository;
use App\Repositories\CampaignGroupRepository;
use Illuminate\Support\Facades\Auth;

class CampaignGroupController extends BaseController
{
    /**
     * @var CampaignGroupRepository
     */
    protected $campaignGroupRepo;

    public function __construct(CampaignGroupRepository $campaignGroupRepo)
    {
        parent::__construct();
        $this->campaignGroupRepo = $campaignGroupRepo;
    }
    
    /**
     * Get all of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->campaignGroupRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->campaignGroupRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CampaignGroupRequest $request)
    {
        $payload = array_merge($request->all(), [
            'company_id' => Auth::user()->company_id
        ]);
        $this->campaignGroupRepo->store($payload);
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
        $campaignGroupRepo = $this->campaignGroupRepo->getById($id);
        return $this->response->item($campaignGroupRepo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CampaignGroupRequest $request, $id)
    {
        $this->campaignGroupRepo->update($id, $request->all());
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
        $this->campaignGroupRepo->destroy($id);
        return $this->response->withNoContent();
    }
}
