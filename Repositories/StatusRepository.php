<?php

namespace App\Repositories;

use App\Libraries\Network;
use App\Models\Status;
use App\Repositories\UserRepository;
use App\Repositories\StatusGroupRepository;
use Illuminate\Support\Facades\DB;

class StatusRepository
{
    use BaseRepository;

    /**
     * Status Model
     *
     * @var Status
     */
    protected $model;
    protected $UserRepository;
    protected $StatusGroupRepository;
    /**
     * Constructor
     *
     * @param Status $status
     */
    public function __construct(Status $status, UserRepository $UserRepository, StatusGroupRepository $StatusGroupRepository)
    {
        $this->model = $status;
        $this->UserRepository = $UserRepository;
        $this->StatusGroupRepository = $StatusGroupRepository;
    }

    /**
     * Get all the records
     *
     * @return array User
     */
    public function allOfType($type)
    {
        return $this->model->ofType($type)->orderBy('name', 'asc')->get();
    }

    /**
     * Get number of the records
     *
     * @param Request $request
     * @param int $number
     * @param string $sort
     * @param string $sortColumn
     * @return Paginate
     */
    public function pageWithRequest($request, $number = 10, $sort = 'desc', $sortColumn = 'created_at')
    {
        $keyword = $request->get('keyword');

        if ($request->has('limit')) {
            if ($request->limit == -1){
                if ($request->has("type")){
                    return $this->allOfType($request->type);
                }
                if ($request->has("company_id")){
                    return $this->findByCompany($request->company_id);
                }
                if ($request->has("group_id")){
                    return $this->findAll($request->group_id);
                }
                return $this->model->when($keyword, function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                })
                    ->where('milestone_id', '<>', 0)
                    ->whereNotNull('milestone_id')
                    ->orderBy('name', 'asc')
                    ->orderBy($sortColumn, $sort)
                    ->get();
            }
            $number = $request->get('limit');
        }

        return $this->model->when($keyword, function ($query) use ($keyword) {
            $query->where('name', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        })
            ->where('milestone_id', '<>', 0)
            ->whereNotNull('milestone_id')
            ->orderBy('name', 'asc')
            ->orderBy($sortColumn, $sort)
            ->paginate($number);
    }

    public function findById($target_id)
    {
        return $this->model->with('status_types')->where('target_id', '=', $target_id)->first();
    }

    public function findByName($name)
    {
        return $this->model->where('name', 'like', '%' . $name . '%')->first();
    }

    public function getStatusObjects()
    {

        $query = DB::table('statuses', 's')
            ->select('s.*', 'm.name as milestone', 'st.name as type_name')
            ->leftJoin('milestones as m', 's.milestone_id', '=', 'm.id')
            ->leftJoin('status_types as st', 'st.id', '=', 's.type')
            ->leftJoin('statuses_users as su', 'su.status_id', '=', 's.id')
            ->where('s.active', '=', 1)
            ->orderBy('s.name', 'ASC');

        $result = $query->get()->toArray();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id){
        $result = DB::table('statuses','s')
            ->select('s.*', 'st.name as status_type_name','st.status_field')
            ->leftJoin('status_types as st', 'st.id','=','s.type')
            ->where('s.id', '=', $id)->first();
        return $result;

    }

    /**
     * @param $group_id
     * @return mixed
     */
    public function findAll($type=null){

        $query = DB::table('statuses','s')
                ->select('s.*', 'm.name as milestone', 'st.name as type_name')
                ->leftJoin('milestones as m', 's.milestone_id','=','m.id')
                ->leftJoin('status_types as st', 'st.id','=','s.type')
            ->where('s.active', '=', 1)
            ->orderBy('s.name', 'ASC');
        if($type){
            $query->where('type','=', $type);
        }
        return $query->get();
    }

    public function findByCompany($company_id, $active=1){

        $query = $this->model->where('active', $active)->where("company_id", $company_id)->orWhere("system", 1)->orderBy("name", "ASC")->get();
        return $query;

    }

    public function findCaseCountByCompany($company_id, $active=1){

        $query = DB::table('statuses','s')
            ->select('s.*', 'm.name as milestone', 'st.name as type_name', DB::raw('COUNT(case_statuses.case_id) as case_count'))
            ->leftJoin('milestones as m', 's.milestone_id','=','m.id')
            ->leftJoin('status_types as st', 'st.id','=','s.type')
            ->leftJoin('case_statuses', 'case_statuses.status_id','=','s.id')
            ->join('cases', 'cases.id','=','case_statuses.case_id')
            ->where('s.active', '=', $active)
            ->where('s.company_id','=', $company_id)
            ->where('cases.company_id','=', $company_id)
            ->orWhere('s.system','=', 1)
            ->orderBy('s.name', 'ASC')
            ->groupBy('s.id');

        return $query->get()->toArray();
    }

    public function findAnytimeByCompany($company_id){

        $query = DB::table('statuses','s')
            ->select('s.*', 'm.name as milestone', 'st.name as type_name')
            ->leftJoin('milestones as m', 's.milestone_id','=','m.id')
            ->leftJoin('status_types as st', 'st.id','=','s.type')
            ->where('s.active', '=', 1)
            ->where('s.company_id','=', $company_id)
            ->where('s.anytime','=', 1)
            ->orderBy('s.name', 'ASC');

        return $query->get()->toArray();
    }

    public function findByCaseAndParentCompanyIds($company_id, $parent_id, $active=1, $format_list=null){

        $query = DB::table('statuses', 's')
            ->select('s.*', 'm.name as milestone', 'st.name as type_name')
            ->leftJoin('milestones as m', 's.milestone_id','=','m.id')
            ->leftJoin('status_types as st', 'st.id','=','s.type')
            ->where('s.active', '=', $active)
            ->where('s.company_id','=', $parent_id)
            ->where('s.shared','=', 1)
            ->where('s.company_id','=', $company_id)
            ->orWhere('s.system','=', 1)
            ->orderBy('s.name', 'ASC');

        $result = $query->get();

        if($result){
            $payload = [];
            if($format_list) {

                foreach ($result as $status) {
                    $payload[] = $status;
                }
            }else{
                foreach ($result as $status) {
                    $payload[$status->company_id][] = $status;
                }
            }
            return $payload;
        }
        return false;
    }

    public function findByStatusUser($user_id){

        $query = DB::table('statuses','s')
            ->select('s.*', 'm.name as milestone', 'st.name as type_name')
            ->leftJoin('milestones as m', 's.milestone_id','=','m.id')
            ->leftJoin('status_types as st', 'st.id','=','s.type')
            ->leftJoin('statuses_users as su', 'su.status_id','=', 's.id')
            ->where('s.active', '=', 1)
            //->where('su.company_id','=', $company_id)
            ->where('su.user_id','=', $user_id)
            ->orderBy('s.name', 'ASC');
        $result = $query->get()->toArray();

        if($result){
            foreach ($result as $status) {
                $payload[$status['company_id']][] = $status;
            }
            return $payload;
        }
        return false;
    }

    public function findByCompanies($company_ids){

        $query = DB::table('statuses','s')
            ->select('s.*', 'm.name as milestone', 'st.name as type_name')
            ->leftJoin('milestones as m', 's.milestone_id','=','m.id')
            ->leftJoin('status_types as st', 'st.id','=','s.type')
            ->where('s.active', '=', 1)
            ->where('s.company_id','IN', $company_ids)
            ->orWhere('s.system','=', 1)
            ->orderBy('s.name', 'ASC');
        $result = $query->get()->toArray();
        if($result){
            foreach($result as $status){
                $payload[$status['company_id']][] = $status;
            }
            return $payload;
        }
        return false;
    }

    public function findAllByCompanies($company_ids){

        $query = DB::table('statuses','s')
            ->select('s.*', 'm.name as milestone', 'st.name as type_name')
            ->leftJoin('milestones as m', 's.milestone_id','=','m.id')
            ->leftJoin('status_types as st', 'st.id','=','s.type')
            ->where('s.active', '=', 1)
            ->whereIn('s.company_id', $company_ids)
            ->orWhere('s.system','=', 1)
            ->orderBy('s.name', 'ASC');
        $result = $query->get()->toArray();
        return $result;
    }

    public function findAllInNetwork(){
        $query = DB::select('s.*', array('m.name', 'milestone'))
            ->table('statuses','s')
            ->leftJoin('milestones as m', 's.milestone_id','=','m.id');
        $query = Network::queryNetwork($query, $this->UserRepository->getSessionMeta('networks_companies'));
        $query->where('s.active', '=', 1)
            ->orderBy('s.name', 'ASC');
        return $query->get()->toArray();
    }

    /**
     * @param $id
     * @param $new_position
     */
    public function resort($id, $new_position){
        $status = $this->find($id);
        if($status['sort'] > $new_position){
            DB::dable('statuses')->where('group_id', '=', $status['group_id'])->where('active', '=', 1)->where('sort', '<', $status['sort'])->update(array('sort' => DB::raw('sort+1')));
        }else{
            DB::table('statuses')->where('group_id', '=', $status['group_id'])->where('active', '=', 1)->where('sort', '<=', $new_position)->update(array('sort' => DB::raw('sort-1')));
        }
        // print DB::last_query();
        DB::table('statuses')->where('id', '=', $id)->update(array('sort' => $new_position));
        // print DB::last_query();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function add($data){

        if(isset($data['action_ids'])) {
            $action_ids = $data['action_ids'];
            unset($data['action_ids']);
        }

        /*$result = DB::select(array(DB::raw('MAX(sort)+1'), 'sort'))->table('statuses')->where('group_id','=',$data['group_id'])->get();
        $sort = current($result->toArray());

        if(empty($sort['sort'])){
            $sort['sort'] = 1;
        }

        $data['sort'] = $sort['sort'];*/
        $result = $this->store($data);
        $status_id = current($result);

        if(isset($action_ids)) {
            $this->manageActions($status_id, $action_ids);
        }
        return $status_id;
    }

    /**
     * @param $id
     * @param $data
     */
    public function updateStatus($id, $data){

        if(isset($data['action_ids'])) {
            $this->manageActions($id, $data['action_ids']);
            unset($data['action_ids']);
        }

        $this->update($id, $data);
    }

    /**
     * @param $id
     */
    public function delete($id){
        $status = $this->find($id);
        DB::table('statuses')->where('sort','>',$status['sort'])->update(array('sort' => DB::raw('sort-1')));
        $result = DB::table('statuses')->where('id','=',$id)->update(array('active' => 0, 'sort' => 0));
    }

    /**
     * @param $status_id
     * @return array
     */
    public function getActions($status_id){

        $result = DB::table('actions_statuses')->select('action_id')->where('status_id', '=', $status_id)->get();

        $ids = array();
        foreach($result->toArray() as $row){
            $ids[] = $row['action_id'];
        }
        return $ids;
    }

    /**
     * @param $status_id
     * @param $action_ids
     */
    public function manageActions($status_id, $action_ids){

        DB::table('actions_statuses')->where('status_id', '=', $status_id)->delete();

        $all_inserts = [];
        foreach($action_ids as $id){
            $all_inserts[] = ['status_id' => $status_id, 'action_id' => $id];
        }

        DB::table('actions_statuses')->insert($all_inserts);
    }

    /**
     * @return mixed
     */
    public function findAllGroups(){
        $result = $this->StatusGroupRepository->all();
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findGroup($id){
        $result = DB::table('status_groups')->where('id', '=', $id)->get();
        return current($result->toArray());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findGroupByStatus($id){
        $result = DB::select()
            ->table('statuses')
            //->leftJoin('status_groups as stg', 'statuses.group_id','=','stg.id')
            ->where('statuses.id', '=', $id)
            ->first();
        return $result->toArray();
    }

    /**
     * @return array
     */
    public function findExpiryRules(){
        $result = DB::table('statuses')->select('id','name','expiry_days','expiry_action_id')->where('active','=',1)->where('expiry_days', '>', 1)->where('expiry_action_id', '>', 1)->get();

        $rules = array();
        foreach($result->toArray() as $row){
            $rules[$row['id']] = $row;
        }
        return $rules;
    }

}
