<?php

namespace App\Repositories;

use App\Repositories\UserRepository;
use App\Repositories\NetworkRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Contact\Models\Campaign;

class CampaignRepository
{
    use BaseRepository;

    /**
     * Campaign Model
     *
     * @var Campaign
     */
    protected $model;
    /**
     * @var UserRepository
     */
    protected $userRepo;
    /**
     * @var NetworkRepository
     */
    protected $networkRepo;

    /**
     * Constructor
     *
     * @param Campaign $campaign
     */
    public function __construct(Campaign $campaign, UserRepository $userRepo, NetworkRepository $networkRepo)
    {
        $this->model = $campaign;
        $this->userRepo = $userRepo;
        $this->networkRepo = $networkRepo;
    }

    /**
     * Get the list of all the campaign without myself.
     *
     * @return mixed
     */
    public function getList()
    {
        return $this->model
                    ->orderBy('id', 'desc')
                    ->get();
    }

    /**
     * Get the campaign by name.
     *
     * @param  string $name
     * @return mixed
     */
    public function getByName($name)
    {
        return $this->model
                    ->where('name', $name)
                    ->first();
    }

    /**
     * Get number of the records
     *
     * @param  Request $request
     * @param  int $number
     * @param  string $sort
     * @param  string $sortColumn
     * @return Paginate
     */
    public function pageWithRequest($request, $number = 10, $sort = 'desc', $sortColumn = 'created_at')
    {
        $keyword = $request->get('keyword');

        if ($request->has('limit')) {
            $number = $request->get('limit');
        }

        return $this->model->when($keyword, function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                          ->orWhere('description', 'like', "%{$keyword}%");
                })
                ->where('active', 1)
                ->orderBy($sortColumn, $sort)
                ->paginate($number);
    }
    public function findAll()
    {

        $result = DB::table('campaigns');

        if($this->userRepo->getSessionMeta('type') == 'Campaign'){
            $result->where('company_id','=', $this->userRepo->getSessionMeta('company_id'))
                ->where('group_id','=', $this->userRepo->getSessionMeta('campaign_group_id'))
                ->orderBy('name', 'ASC');
        }else{
            $result->where('company_id','=', $this->userRepo->getSessionMeta('company_id'))->orderBy('name', 'ASC');
        }

        $result->where('company_id','=', $this->userRepo->getSessionMeta('company_id'))->orderBy('name', 'ASC');
        return $result->get()->toArray();

    }

    public function findByCompany($company_id)
    {
        $query = $this->model->where('company_id','=',$company_id)->orderBy('name', 'ASC');
        return optional($query->get())->toArray();

    }

    public function findByCompanyCaseSum($company_id){

        $query = DB::table('campaigns')->select(DB::raw('COUNT(case_statuses.case_id) as cases'), 'campaigns.*');
        $query->leftJoin('case_statuses','case_statuses.campaign_id','=','campaigns.id');
        $query->where('campaigns.company_id','=',$company_id);
        $query->orderBy('campaigns.name', 'ASC');
        $query->groupBy('campaigns.id');
        return $query->get()->toArray();

    }


    public function findAllInNetwork(){
        $query = DB::table('campaigns');
        $query = $this->networkRepo->queryNetwork($query,Auth::user()->networks_companies);

        if(Auth::user()->type == 'Campaign'){
            $query->where('group_id','=', Auth::user()->campaign_group_id);
        }

        $query->orderBy('name', 'ASC');
        return $query->get()->toArray();
    }

    public function findByName($name){
        $result = DB::table('campaigns')->where('name', '=', $name)->get();
        if(count($result)){
            return $result->toArray();
        }else{
            return array();
        }
    }

    public function findByGroup($id){
        $result = DB::table('campaigns')->where('group_id', '=', $id)->get();
        return $result->toArray();

    }
}

