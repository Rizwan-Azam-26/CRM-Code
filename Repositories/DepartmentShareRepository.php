<?php

namespace App\Repositories;

use App\Models\DepartmentShare;
use App\Repositories\UserRepository;

class DepartmentShareRepository
{
    use BaseRepository;

    /**
     * DepartmentShare Model
     *
     * @var DepartmentShare
     */
    protected $model;
    /**
     * @var UserRepository
     */
    protected $userRepo;

    /**
     * Constructor
     *
     * @param DepartmentShare $departmentShare
     */
    public function __construct(DepartmentShare $departmentShare, UserRepository $userRepo)
    {
        $this->model = $departmentShare;
        $this->userRepo = $userRepo;
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
            $query->where('title', 'like', "%{$keyword}%");
        })
            ->orderBy($sortColumn, $sort)
            ->paginate($number);
    }
    public function find($id)
    {
        $result = DB::table('departments_shared')->where('id', '=', $id)->get();
        return $result->toArray();
    }

    public function findAll()
    {

        $result = DB::table('departments_shared')
            ->where('company_id','=', $this->userRepo->getSessionMeta('company_id'))
            ->get();
        return $result->toArray();

    }

    public function findAllByCompany($company_id, $format='form')
    {

        $query = DB::table('departments_shared')->where('company_id','=',$company_id)
            ->get();
        $result = $query->toArray();

        if($result){
            if($format == 'form') {
                foreach ($result as $item) {
                    $ids[] = $item['department_id'];
                }
                return $ids;
            }else{
                return $result;
            }
        }

        return false;

    }


    public function upsert($company_id, $department_ids)
    {

        $this->model->where('company_id', '=', $company_id)->delete();



        // Exclude Dupes
        $ids = array_unique($department_ids);

        foreach ($ids as $id) {
            $query = $this->store(array('department_id' => $id, 'company_id' => $company_id));

        }

    }

    public function findAllByFilter($filter)
    {

        $query = DB::table('departments_shared');

        if(isset($filter['company_id'])){
            $query->where('company_id','=', $filter['company_id']);
        }

        if(isset($filter['type'])){
            $query->where('type','=', $filter['type']);
        }

        if(isset($filter['name'])){
            $query->where('name','=', $filter['name']);
        }

        return $query->get()->toArray();
    }

}
