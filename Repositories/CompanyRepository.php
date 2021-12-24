<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Repositories\NetworkRepository;

class CompanyRepository
{
    use BaseRepository;

    /**
     * Company Model
     *
     * @var Company
     */
    protected $model;
    /**
     * @var NetworkRepository
     */
    protected $networkRepo;

    /**
     * Constructor
     *
     * @param Company $company
     */
    public function __construct(Company $company, NetworkRepository $networkRepo)
    {
        $this->model = $company;
        $this->networkRepo = $networkRepo;

    }

    /**
     * Get the list of all the company without myself.
     *
     * @return mixed
     */
    public function getCalendarOption()
    {
        return $this->model
            ->orderBy('id', 'desc')
            ->get();
    }
    /**
     * Get the list of the company by keyword.
     *
     * @return mixed
     */
    public function getCompaniesPaginate($request)
    {
        $user_company = $request->user()->company;
        $network_company_ids = [$user_company->id];
        $user_networks = $user_company->networks;
        foreach ($user_networks as $network) {
            if (isset($network->companies)) {
                foreach($network->companies as $company) {
                    $network_company_ids[] = $company->id;
                }
            }
        }

        $keyword = $request->get('keyword');

        if ($request->has('limit')) {
            $limit = $request->get('limit');
        }
        else {
            $limit = 10;
        }

        $sortColumn = 'created_at';
        $sort = 'desc';
        if ($request->has('sort')) {
            $sortColumn = $request->get('sort');
        }

        $companies = Company::whereIn('id', $network_company_ids)
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function($query1) use ($keyword) {
                    $query1->where('name', 'like', "%{$keyword}%")
                            ->orWhere('long_name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('case_email_domain', 'like', "%{$keyword}%")
                            ->orWhere('office_address', 'like', "%{$keyword}%")
                            ->orWhere('office_address_2', 'like', "%{$keyword}%")
                            ->orWhere('office_city', 'like', "%{$keyword}%")
                            ->orWhere('office_state', 'like', "%{$keyword}%")
                            ->orWhere('office_zip', 'like', "%{$keyword}%")
                            ->orWhere('office_fax', 'like', "%{$keyword}%")
                            ->orWhere('office_phone', 'like', "%{$keyword}%")
                            ->orWhere('legal_name', 'like', "%{$keyword}%")
                            ->orWhere('cs_email', 'like', "%{$keyword}%")
                            ->orWhere('cs_phone', 'like', "%{$keyword}%")
                            ->orWhere('website', 'like', "%{$keyword}%")
                            ->orWhere('social_facebook', 'like', "%{$keyword}%")
                            ->orWhere('social_instagram', 'like', "%{$keyword}%")
                            ->orWhere('social_twitter', 'like', "%{$keyword}%")
                            ->orWhere('social_googleplus', 'like', "%{$keyword}%")
                            ->orWhere('social_linkedin', 'like', "%{$keyword}%");
                });
            })
            ->orderBy($sortColumn, $sort)
            ->paginate($limit);
        return $companies;
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
    public function pageWithRequest($request, $pageLimit = 10, $sortColumn = 'created_at', $sortOrder = 'desc')
    {
        $search = $request->get('search');

        if ($request->has('pageLimit')) {
            $pageLimit = $request->get('pageLimit');
        }

        if ($request->has('sortColumn')) {
            $sortColumn = $request->get('sortColumn');
        }

        if ($request->has('sortOrder')) {
            $sortOrder = $request->get('sortOrder');
        }

        if (is_numeric($search)) {
            return $this->model->when($search, function ($query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('case_id', $search)
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
                ->orderBy($sortColumn, $sortOrder)
                ->paginate($pageLimit);
        } else {
            return $this->model->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
                ->orderBy($sortColumn, $sortOrder)
                ->paginate($pageLimit);
        }
    }

    public function find($id)
    {

        $query = DB::table('companies', 'co')->select(
            'co.id', 'co.account_id', 'co.name', 'co.long_name', 'co.case_email_domain', 'co.parent_id',
            'co.office_address', 'co.office_address_2', 'co.office_city', 'co.office_state', 'co.office_zip', 'co.office_fax', 'co.cs_phone',
            'co.legal_name', 'co.cs_email', 'co.email as company_email', 'co.logo', 'co.office_phone', 'co.website',
            'co.social_facebook', 'co.social_instagram', 'co.social_twitter', 'co.social_googleplus', 'co.social_linkedin',
            'co.broker_user_id', 'co.client_portal_url', 'v.name as vertical',
            'co.share_session', 'co.inbox_prefix'
        )
            ->leftJoin('verticals as v', 'v.id', '=', 'co.vertical_id')
            ->where('co.id', '=', $id)->first();

        return $query;//$query->toArray();
    }

    public function findByCaseId($case_id)
    {
        $result = DB::table('companies')
        ->select('companies.*')
            ->leftJoin('cases','cases.company_id','=','companies.id')
            ->where('cases.id','=',$case_id)
            ->get()
            ->toArray();
        return $result;
    }

    public function findParentId($company_id)
    {
        $parent_id = DB::table('companies')->select('parent_id')->where('id','=',$company_id)->get()->toArray();
        if (isset($parent_id[0])){
            return $parent_id[0]->parent_id;
        }else{
            return 0;
        }
        
    }


    public function findByCompany()
    {
        $query = DB::table('companies');
        $query = $this->networkRepo->queryNetwork($query, Auth::user()->id,null,'id');
        return $query->get()->toArray();
    }

    public function isSuspended()
    {
        $query = DB::table('companies')->select('id')->where('id','=', Auth::user()->id)->where('susp','=', 1);
        return $query->get()->toArray();
    }

    public function findByNetwork()
    {
        $query = DB::table('companies');
        $query = $this->networkRepo->queryNetwork($query, $this->networkRepo->getNetworkIds(),null,'id');
        return $query->get()->toArray();
    }

    public function getList()
    {
        $query = DB::table('companies')->select('id','name');
        if(Auth::user()->id != 1) {
            $query->whereIn('companies.id', $this->networkRepo->getNetworkIds());
        }
        $result = $query->get();

        return $result->toArray();
    }

    public function findNetworkCompaniesBySession()
    {
        $query = DB::table('companies')->select('id','name');
        if(Auth::user()->id != 1) {
            $query->where('companies.id', 'IN', $this->networkRepo->getNetworkIds());
        }
        $result = $query->get();

        return $result->toArray();
    }

    public function getIndexedList()
    {
        $query = DB::table('companies')->select('id','name');
        if(Auth::user()->id != 1) {
            $query->where('companies.id', 'IN', $this->networkRepo->getNetworkIds());
        }
        $result = $query->get()->toArray();//cached(3600, "company.list", false)->get()->toArray();

        if($result){
            foreach($result as $company){
                $payload[$company->id] = $company->name;
            }

            return $payload;
        }

        return false;
    }

    public function findAll()
    {
        $result = $this->model->get();
        return $result->toArray();
    }

}
