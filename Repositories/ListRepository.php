<?php

namespace App\Repositories;

use App\Models\Lists;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Repositories\NetworkRepository;
use App\Repositories\UserRepository;

class ListRepository
{
    use BaseRepository;

    /**
     * List Model
     *
     * @var Lists
     */
    protected $model;
    /**
     * @var NetworkRepository
     */
    protected $networkRepo;
    /**
     * @var UserRepository
     */
    protected $userRepo;

    /**
     * Constructor
     *
     * @param List $list
     */
    public function __construct(Lists $list, NetworkRepository $networkRepo, UserRepository $userRepo)
    {
        $this->model = $list;
        $this->networkRepo =$networkRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Get the list by type.
     *
     * @param string $type
     * @return mixed
     */
    public function getByType($type)
    {
        return $this->model
            ->ofType($type)
            ->get();
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
            $number = $request->get('limit');
        }

        return $this->model->when($keyword, function ($query) use ($keyword) {
            $query->where('name', 'like', "%{$keyword}%")
                ->orWhere('type', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        })
            ->orderBy($sortColumn, $sort)
            ->paginate($number);
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
    public function pageWithRequestByType($request, $type, $number = 10, $sort = 'desc', $sortColumn = 'created_at')
    {
        $keyword = $request->get('keyword');

        if ($request->has('limit')) {
            $number = $request->get('limit');
        }

        return $this->model->ofType($type)
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('type', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->orderBy($sortColumn, $sort)
            ->paginate($number);
    }

    public function find($id)
    {

        $result = DB::table('lists', 'l')
            ->where('l.id', '=', $id)
            ->get();

        return $result->toArray();
    }

    public function findAll()
    {

        $result = DB::table('lists')
            ->get();
        return $result->toArray();
    }

    public function findByUser($user_id)
    {

        $result = DB::table('lists')
            ->where('created_by', '=', $user_id)
            ->get();
        return $result->toArray();

    }

    public function findByFilter($filter, $columns)
    {

        $query = DB::table('lists', 'l')
        ->select($columns);


        if(isset($filter['list_id'])){
            $query->where('list_id','=',$filter['list_id']);
        }

        if(isset($filter['created_by'])){
            $query->where('created_by','=',$filter['created_by']);
        }

        $result = $query->get()->toArray();
        return $result;

    }

    public function findAllInNetwork()
    {

        $query = DB::table('lists', 'l');
        $query = $this->networkRepo->queryNetwork($query, $this->userRepo->getSessionMeta('networks_companies'));
        $query->where('l.company_id','!=',$this->userRepo->getSessionMeta('company_id'));
        $result = $query->get()->toArray();
        return $result;

    }

    public function findByCompany($company_id)
    {

        $query = DB::table('lists', 'l');
        $query->where('l.company_id','=',$company_id)->where('company_shared', '=', 1);
        return $query->get()->toArray();

    }

    public function findByType($type, $user_id = null, $company_id = null){

        $query = DB::table('lists', 'l');

        $query->where('type','=',$type);

        if($user_id){
            $query->where('created_by','=',$user_id);
        }

        if($company_id){
            $query->where('company_id','=',$company_id);
        }

        $result = $query->get()->toArray();
        return $result;

    }

    public function findAllShared($user_id){

        $query = DB::table('lists', 'l')
            ->select('l.*')
            ->Join('list_users')->on('list_users.list_id','=','l.id');
        $query->where('list_users.user_id', '=', $user_id);
        $result = $query->get()->toArray();
        return $result;

    }

}
