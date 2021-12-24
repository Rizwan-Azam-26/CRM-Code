<?php

namespace App\Repositories;

use App\Models\Network;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NetworkRepository
{
    use BaseRepository;

    /**
     * Network Model
     *
     * @var Network
     */
    protected $model;
    /**
     * @var NetworkCompanyRepository
     */
    protected $networkCompanyRepo;

    /**
     * Constructor
     *
     * @param Network $network
     */
    public function __construct(Network $network, NetworkCompanyRepository $networkCompanyRepo)
    {
        $this->model = $network;
        $this->networkCompanyRepo = $networkCompanyRepo;
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

    public function queryNetwork($query, $company_ids, $alias = null, $column = null)
    {

        if (Auth::user()->id == 1 || Auth::user()->company_id == 1 || !Auth::check()) {
            return $query;
        }

        if (!$column) {
            $column = 'company_id';
        }

        if (is_array($company_ids)) {
            if ($alias) {
                $query->where($alias . '.' . $column, 'IN', $company_ids);
            } else {
                $query->where($column, 'IN', $company_ids);
            }
        } else {
            if ($alias) {
                $query->where($alias . '.' . $column, '=', $company_ids);
            } else {
                $query->where($column, '=', $company_ids);
            }
        }
        return $query;
    }

    public function getNetworkIds()
    {
        return $this->findCompanyNetworkIds(Auth::user()->company_id);
    }

    public function findCompanyNetworkIds($company_id)
    {
        if ($company_id != 1) {
            $query = DB::table('networks as n')->select(['nc.company_id'])->leftJoin('networks_companies as nc', 'nc.network_id', '=', 'n.id')
                ->where('n.company_id', '=', $company_id)->groupBy('nc.company_id');
        } else {
            // System Access
            $query = DB::table('companies')->select(['id', 'company_id']);
        }

        $result = $query->get()->toArray();

        if ($result) {
            $ids = array();
            foreach ($result as $cid) {
                if(isset($cid->company_id)){
                
                    $ids[] = $cid->company_id;
                        
                   }else{
                       
                    $ids[] = $cid->id;
                       
                   } 
            }
            return $ids;
        }

        return array($company_id);
    }

    public function hasAccess($user_company, $network_company)
    {
        if ($user_company === $network_company) {
            return true;
        }

        if ($this->inNetwork($user_company, $network_company)) {
            return true;
        }

        return false;
    }

    public function inNetwork($user_company, $network_company)
    {

        $query = DB::table('networks')
            ->select('networks.id')
            ->leftJoin('networks_companies')->on('networks_companies.network_id', '=', 'networks.id')
            ->where('networks.company_id', '=', $user_company)
            ->where('networks_companies.company_id', '=', $network_company);

        return $query->get()->toArray();

    }

    public function getNetworksByCompany($company_id)
    {
        $network_ids = $this->networkCompanyRepo->findByCompany($company_id);
        if ($network_ids) {
            $networks_companies = $this->networkCompanyRepo->findCompaniesByNetworks($network_ids);
            return $networks_companies;
        }
        return false;
    }

    public function findAllByCompanyNetworks($company_id)
    {

        $query = DB::table('networks_companies');
        $query->leftJoin('networks_companies as nc', 'nc.network_id', '=', 'networks_companies.network_id');
        $query->where('company_id', '=', $company_id);
        $query->groupBy('nc.company_id');
        $result = $query->get()->toArray();
        if ($result) {
            $ids = array();
            foreach ($result as $co) {
                $ids[] = $co['company_id'];
            }
            return $ids;
        }

        return false;
    }


    public function queryNetworkCases($queryObject, $company_ids)
    {
        //var_dump($company_ids); exit;
        if (is_array($company_ids)) {
            $queryObject->whereIn('c.company_id', $company_ids);
        } else {
            $queryObject->where('c.company_id', '=', $company_ids);
        }
        return $queryObject;
    }
}
