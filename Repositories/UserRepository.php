<?php

namespace App\Repositories;

use App\Libraries\Account;
use App\Libraries\Network;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    use BaseRepository;

    /**
     * User Model
     *
     * @var User
     */
    protected $model;
    /**
     * @var NetworkRepository
     */
    protected $networkRepo;
    /**
     * @var AccountRepository
     */
    protected $accountRepo;

    /**
     * Constructor
     *
     * @param User $user
     */
    public function __construct(User $user, AccountRepository $accountRepo, NetworkRepository $networkRepo)
    {
        $this->model = $user;
        $this->accountRepo = $accountRepo;
        $this->networkRepo = $networkRepo;
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

        return $this->model
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($query_keyword) use ($keyword) {
                    $query_keyword->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            // ->where('active', 1)
            // ->whereNotNull('email_verified_at')
            ->orderBy($sortColumn, $sort)
            ->paginate($number);
    }

    /**
     * Change the user password.
     *
     * @param App\User $user
     * @param string $password
     * @return boolean
     */
    public function changePassword($user, $password)
    {
        return $user->update(['password' => bcrypt($password)]);
    }

    public function getRegion()
    {

        if (Auth::user()->region_id) {
            return Auth::user()->region_id;
        } else {
            $user = $this->getById(Auth::user()->id);
            return $user->region_id ?? 0;
        }

    }

    public function getSessionMeta($col)
    {
        if (isset(Auth::user()->$col) && !empty(Auth::user()->$col)) {
            return Auth::user()->$col;
        }
        return false;
    }

    public function findCompanyByUserMobile($mobile_phone)
    {
        $result = DB::table('users')
            ->select('company_id')
            ->where('active', 1)
            ->where('mobile', '=', $mobile_phone)
            ->first()->toArray();

        if ($result) {
            return $result['company_id'];
        }
        return false;
    }

    public function findCompanyByRole($company_id, $role_id)
    {

        $users = array();
        $result = DB::table('users')
            ->select('id', 'first_name', 'last_name')
            ->join('rbac_')
            ->where('active', 1)
            ->orderBy('last_name')
            ->get();

        if (count($result)) {
            $users = $result->toArray();
        }
        return $users;

    }

    public function findCompanyByUserId($user_id)
    {
        $result = DB::table('users')
            ->select('company_id')
            ->where('active', 1)
            ->where('id', '=', $user_id)
            ->first()->toArray();

        if ($result) {
            return $result['company_id'];
        }
        return false;
    }

    public function findAll()
    {

        $users = array();
        $result = DB::table('users')
            ->select('id', 'first_name', 'last_name')
            ->where('active', 1)
            ->orderBy('last_name')
            ->get();

        if (count($result)) {
            $users = $result->toArray();
        }
        return $users;
    }

    public function is_active($id)
    {
        $result = DB::table('users')->where('id', '=', $id)->get()->toArray();
        if ($result && $result['active'] == 1) {
            return true;
        }
        return false;
    }

    public function findAllActive()
    {
        $query = DB::table('users')
            ->select('users.*', 'companies.name as company_name', DB::raw('CONCAT(users.first_name, " ", users.last_name) as name'))
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id');

        $query = Network::queryNetwork($query, Account::getNetworkIds(), 'users');

        $result = $query->where('active', '=', 1)->orderBy('first_name', 'asc')->get()->toArray();
        return $result;

    }

    public function findAllActiveByCompany()
    {
        $query = DB::table('users')
            ->select('users.*', 'companies.long_name as company_name', DB::raw('CONCAT(users.first_name, " ", users.last_name) as name'))
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id')
            ->where('active', '=', 1)
            ->orderBy('first_name', 'asc');

        $query = Network::queryNetwork($query, Account::getNetworkIds(), 'users');
        $result = $query->get()->toArray();

        //\Model_Log::append('access',\DB::last_query());

        if ($result) {
            $users = array();
            foreach ($result as $u) {
                $users[$u['company_name']][] = $u;
            }
            return $users;
        }
        return false;
    }

    public function findActiveListByCompany()
    {

        $query = DB::table('users')
            ->select('users.*', 'companies.long_name as company_name', DB::raw('CONCAT(users.first_name, " ", users.last_name) as name'))
            ->join('companies', 'left')->on('companies.id', '=', 'users.company_id')
            ->where('active', '=', 1)
            ->orderBy('first_name', 'asc');
        $query = Network::queryNetwork($query, Account::getNetworkIds(), 'users');
        $result = $query->get()->toArray();

        //\Model_Log::append('access',\DB::last_query());

        if ($result) {
            return $result;
        }
        return false;
    }

    public function findAllActiveWithRoles()
    {

        $query = DB::table('users')
            ->select('users.*', 'companies.name', 'rbac_roles.description')
            ->leftJoin('rbac_userroles', 'rbac_userroles.user_id', '=', 'users.id')
            ->leftJoin('rbac_roles', 'rbac_roles.id', '=', 'rbac_userroles.role_id')
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id');

        $query = Network::queryNetwork($query, Account::getNetworkIds(), 'users');

        $result = $query->where('active', '=', 1)->orderBy('first_name', 'asc')->get()->toArray();

        if ($result) {
            $users = array();
            foreach ($result as $r) {
                $users[$r['id']] = $r;
            }
            foreach ($result as $rr) {
                $users[$rr['id']]['roles'][] = $rr['description'];
            }
            return $users;
        }
        return false;
    }

    public function findAllActiveInNetwork()
    {

        $query = DB::table('users')
            ->select('users.*', 'companies.name')
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id');

        $query = Network::queryNetwork($query, Account::getNetworkIds(), 'users');
        $result = $query->where('active', '=', 1)->orderBy('first_name', 'asc')->get()->toArray();
        return $result;
    }

    public function findAllActiveInCaseNetwork($company_id)
    {

        $query = DB::table('users')
            ->select('users.*', 'companies.name')
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id');

        $query = Network::queryNetwork($query, Account::getNetworkIds(), 'users');
        $result = $query->where('active', '=', 1)->orderBy('first_name', 'asc')->get()->toArray();
        return $result;

    }

    public function findAllSortLastName($account_id = null)
    {

        $query = DB::table('users')->where('active', '=', 1);
        if ($account_id != null) {
            $query->where('account_id', '=', $account_id);
        }
        $query->orderBy('last_name', 'asc');
        $result = $query->get();
        return $result->toArray();
    }

    /* public function findAll(){

        $query = \DB::select('users.*','companies.name',array('companies.long_name','company_name'),'user_profile.picture_filename')
            ->table('users')
            ->join('companies','left')->on('companies.id','=','users.company_id')
            ->join('user_profile','left')->on('user_profile.user_id','=','users.id');

        $query = Network::queryNetwork($query, Account::getNetworkIds(),'users');

        $result = $query->orderBy('users.first_name', 'asc')->get()->toArray();
        return $result;

    } */

    public function getName($id)
    {
        $user = $this->find($id);
        if (isset($user) && !empty($user)) {
            return $user['last_name'] . ', ' . $user['first_name'];
        } else {
            return 'No user found.';
        }
    }

    public function find($id)
    {
        $result = DB::table('users')->where('id', '=', $id)->first();
      
        return $result;
    }

    public function getTemplateList()
    {
        return array();
    }

    public function updateUser($id, $data)
    {

        if (!empty($data['passwd'])) {
            $data['passwd'] = sha1($data['passwd']);
        } else {
            unset($data['passwd']);
        }

        $result = DB::table('users')->where('id', '=', $id)->update($data);
    }

    public function delete($id)
    {
        $this->destroy($id);
    }

    public function findByType($type)
    {
        $result = $this->model->where('type', '=', $type)->where('active', '=', 1)->orderBy('first_name')->get();
        return $result->toArray();

    }

    public function findByDepartment($dept)
    {

        if (!is_array($dept)) {
            $dept = array($dept);
        }

        $result = $this->model->where('department', 'in', $dept)->where('active', '=', 1)->orderBy('first_name')->get();
        return $result->toArray();

    }

    public function findByEmail($email)
    {

        $result = $this->model->where('email', '=', $email)->get();
        return $result->toArray();

    }

    public function findByToken($token)
    {

        $result = $this->model->where('reset_token', '=', $token)->get();
        return $result->toArray();

    }

    public function findByExtension($extension)
    {

        $result = $this->model->where('extension', '=', $extension)->get();
        return $result->toArray();

    }

    public function findByExtensionAndCompany($extension, $company_id)
    {

        $result = $this->model->where('extension', '=', $extension)->where('company_id', '=', $company_id)->get();
        return $result->toArray();
    }

    public function findByExtensions($extensions)
    {

        $result = $this->model->where('extension', 'IN', $extensions)->orderBy('first_name', 'ASC')->get();
        return $result->toArray();

    }

    public function findByField($field, $value, $company_id = null)
    {

        $query = $this->model->where($field, '=', $value);
        if (!$company_id) {
            $query = $query->where('company_id', '=', $company_id);
        }
        return optional($query->get())->toArray();

    }

    public function add($data)
    {

        $data['passwd'] = sha1($data['passwd']);

        return $this->store($data);
    }

    public function softdelete($id)
    {

        $data = array(
            'active' => 0,
            'extension' => NULL
        );

        $result = $this->update($id, $data);
    }

    public function update($id, $data)
    {

        if (!empty($data['passwd'])) {
            $data['passwd'] = sha1($data['passwd']);
        } else {
            unset($data['passwd']);
        }

        return $this->model->where('id', '=', $id)->update($data);
    }

    public function getFilter()
    {
        $query = DB::table('users', 'u')
            ->select('u.id', DB::raw('CONCAT(u.first_name, " ", u.last_name) as name'))
            ->where('active', '=', 1)
            ->orderBy('first_name', 'asc');

        $query = $this->networkRepo->queryNetwork($query, $this->accountRepo->getNetworkIds(), 'u');

        return $query->get()->toArray();
    }

    public function getSignatureHTML()
    {

    }

    public function reSortArrayByIndex($index_name, $data_array)
    {
        //
    }

    public function getUserByRole($role)
    {
        $query = DB::table('users', 'u')->select(
            'u.id', DB::raw('CONCAT(u.first_name, " ", u.last_name)') . ' as name')
            ->where('active', '=', 1)
            ->leftJoin('rbac_userroles as roles', 'roles.user_id', '=', 'u.id')
            ->where('roles.role_id', '=', $role)
            ->groupBy('u.id')
            ->orderBy('first_name', 'asc');

        $query = $this->networkRepo->queryNetwork($query, $this->networkRepo->getNetworkIds(), 'u');

        return $query->get()->toArray();

    }

    public function findByCompany($company_id)
    {
        return optional($this->model->where('active', '=', 1)->where('company_id', '=', $company_id)->orderBy('first_name', 'asc')->get())->toArray();
    }

    public function findByCompanySort($company_id, $sort)
    {
        return optional($this->model->where('active', '=', 1)->where('company_id', '=', $company_id)->orderBy($sort['order_by'], $sort['direction'])->first())->toArray();
    }

    public function findByIds($user_ids)
    {
        return DB::table('users')->select('users.id', 'user_call_status.open', 'users.extension')
            ->join('user_call_status', 'user_call_status.user_id', '=', 'users.id')
            ->where('users.active', '=', 1)
            ->where('users.id', 'IN', $user_ids)
            ->where('user_call_status.open', '=', 1)
            ->get()->toArray();
    }

    public function findByUserId($user_id)
    {
        return DB::table('users')->select('users.id', 'user_call_status.open')
            ->join('user_call_status', 'user_call_status.user_id', '=', 'users.id')
            //->where('users.active', '=', 1)
            ->where('users.id', '=', $user_id)
            //->where('user_call_status.open', '=', 1)
            ->get()->toArray();
    }

    public function findUserIdsByTeamIds($teams)
    {
        $result = DB::table('users')->select('users.id')
            ->join('users_teams', 'users_teams.team_id', 'IN', $teams)
            ->where('users.active', '=', 1)
            ->groupBy('users.id')
            ->get()
            ->toArray();

        if ($result) {
            foreach ($result as $user) {
                $ids[] = $user['id'];
            }
            return $ids;
        }

        return false;
    }

    public function getUsersByRoleId($role_id)
    {
        $query =$this->model
       // ->select('id', 'first_name', 'last_name')
        ->with(array('company' => function($query) {
            $query->select('long_name as company_name');
        }))
        ->with(array('userProfile' => function($query) {
            $query->select('picture_filename');
        }));
        
        
            // ->leftJoin('rbac_userroles as uroles', 'uroles.user_id', '=', 'u.id')
            // ->leftJoin('rbac_roles as rroles', 'rroles.id', '=', 'uroles.role_id')
            // ->leftJoin('companies as co', 'co.id', '=', 'u.company_id')
            // ->leftJoin('user_profile as up', 'up.user_id', '=', 'u.id');

       // $query->where('rroles.id', '=', $role_id)
       $query->where('active', '=', 1)
            //->where('u.company_id','=', \Model_Account::getCompanyId()) // PAG
            ->orderBy('first_name', 'asc');
            // ->groupBy('id');

        $query = $this->networkRepo->queryNetwork($query,Auth::user()->company_id, 'u');

        return $query->get()->toArray();

    }

    public function listUsersAndRoles()
    {
        $query = \DB::table(array('users', 'u'))->select('u.id', 'u.first_name', 'u.last_name', DB::raw('GROUP_CONCAT(rroles.description SEPARATOR ", ") as roles'), 'co.long_name as company_name')
            ->leftJoin('rbac_userroles as uroles', 'uroles.user_id', '=', 'u.id')
            ->leftJoin('rbac_roles as rroles', 'rroles.id', '=', 'uroles.role_id')
            ->leftJoin('companies asco', 'co.id', '=', 'u.company_id');

        $query->whereIn('u.company_id', Auth::user()->company_id);


        $query->where('active', '=', 1)->orderBy('first_name', 'asc')->groupBy('u.id');

        return $query->get()->toArray();

    }
}
