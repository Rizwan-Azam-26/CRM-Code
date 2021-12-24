<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingRepository
{
    use BaseRepository;

    /**
     * Setting Model
     *
     * @var Setting
     */
    protected $model;

    /**
     * Constructor
     *
     * @param Setting $setting
     */
    public function __construct(Setting $setting)
    {
        $this->model = $setting;
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

    public function get($type, $company_id = null, $name = null, $active=null){
        $result = array();

        $query = $this->model->where('type','=',$type);

        if($company_id){
            $query->where('company_id','=', $company_id);
        }
        if($name){
            $query->where('name','=', $name);
        }

        if($active){
            $query->where('active','=', 1);
        }

       return optional($query->get())->toArray();
        /*
        if(!isset($result) || empty($result)){
            $default = self::getByType($type);
            if($default){
                $result = &$default;
            }else {
                return false;
            }
        }*/

        //$setting = array();
        //foreach($result as $item){
        //    $setting[$item['name']] = $item['value'];
        //}
        return current($result);
    }

    public function getAll($type, $company_id = null, $name = null){

        $query = DB::table('settings')->where('type','=',$type);

        if($company_id){
            $query->where('company_id','=', $company_id);
        }
        if($name){
            $query->where('name','=', $name);
        }
        $result = $query->get()->toArray();
        return $result;
    }

    public function getAllByCompanyIDS($type, $company_ids){
        $query = DB::table('settings')->where('type','=',$type);
        $query->whereIn('company_id', $company_ids);
        $result = $query->get()->toArray();
        return $result;
    }

    public function getByType($type){
        $result = DB::table('settings')->where('type','=',$type)->get();
        return $result->toArray();
    }

    public function getById($setting_id){
        return $this->model->where('id','=',$setting_id)->first();

    }

    public function getByName($name){
        return DB::table('settings')->where('name','=',$name)->first();

    }

    public function findAllActiveCompany($company_id){
        $result = DB::table('settings')
            ->where('company_id','=', $company_id)
            ->orderBy('id','DESC')->get();
        return $result->toArray();
    }

    public function findSystem(){
        $result = DB::table('settings')
            ->where('company_id','=', 1)
            ->where('active','=', 1)->order_by('id','DESC')->get();
        return $result->toArray();
    }

    public function findAllByCompany($company_id){
        $result = DB::table('settings')
            ->where('company_id','=', $company_id)->get();
        return $result->toArray();
    }

    public function findAllByFilter($filter){

        $query = DB::table('settings');

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

    public function find($id){
        $result = DB::table('settings')->where('id','=', $id)->get();
        return $result->toArray();
    }

    public function add($data){
        $result = $this->store($data);
        return $result;
    }

    public function delete($id){
        $this->destroy($id);
    }
}
