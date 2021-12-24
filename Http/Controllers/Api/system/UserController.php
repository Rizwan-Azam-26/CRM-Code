<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserRequest;

use App\Repositories\UserRepository;
use App\Repositories\StatusUserRepository;

use App\Events\UserCreated;

use Carbon\Carbon;

use App\Libraries\Access;
use Modules\Event\Repositories\CalendarOptionRepository;

class UserController extends BaseController
{
    protected $user;
    protected $statusesUser;
    protected $calendarOption;

    public function __construct(UserRepository $user, StatusUserRepository $statusesUser, CalendarOptionRepository $calendarOption)
    {
        parent::__construct();

        $this->user = $user;
        $this->statusesUser = $statusesUser;
        $this->calendarOption = $calendarOption;
    }

    /**
     * Display all of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->user->all());
    }
    /**
     * Display all active users of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allactive(Request $request)
    {
        return $this->response->json($this->user->findAllActive());
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->user->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $data = array_merge($request->all(), [
            'account_id' => $request->user()->account_id,
            'company_id' => $request->user()->company_id,
            'password' => bcrypt($request->input('password'))
        ]);

        $role_ids = $request->role_ids;
        unset($data['role_ids']);
        
        $status_ids = $request->status_ids;
        unset($data['status_ids']);
        
        $schedule = $request->schedule;
        unset($data['schedule']);
        
        // save user data
        $user = $this->user->store($data);
        $user->email_verified_at = now();
        $user->save();

        $user->roles()->sync($role_ids);
        $user->statuses()->sync($status_ids);

        $schedule['user_id'] = $user->id;
        $schedule['company_id'] = null;
        $this->calendarOption->store($schedule);

        return $this->response->withNoContent();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $user = $this->user->getById($id);

        return $this->response->item($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id)
    {
        $role_ids = $request->role_ids;
        $data = $request->all();
        unset($data['role_ids']);
        
        $status_ids = $request->status_user_ids;
        unset($data['status_ids']);
        
        $schedule = $request->schedule;
        unset($data['user_schedule']);
        unset($data['company_schedule']);
        unset($data['schedule']);
        
        unset($data['full_name']);
        unset($data['region']);
        unset($data['dashboard']);
        unset($data['roles']);
        unset($data['user_roles']);
        unset($data['permissions']);
        unset($data['company']);
        unset($data['campaign_group']);
        unset($data['signature']);
        unset($data['statuses']);
        unset($data['created_at']);
        unset($data['updated_at']);
        
        // update user main table (users table)
        $this->user->update($id, $data);
        $user = $this->user->getById($id);
        
        // update user role (roles_users table)
        $user->roles()->sync($role_ids);

        // update user statuses (statuses_users table)
        $user->statuses()->sync($status_ids);
        
        // update user calendar option (calendar_option table)
        if ($user->userSchedule()->get()->first() === null) {
            $schedule['user_id'] = $id;
            $schedule['company_id'] = null;
            $this->calendarOption->store($schedule);
        } else {
            $schedule_id = $user->userSchedule()->get()->first()->id;
            $schedule['user_id'] = $id;
            $schedule['company_id'] = null;
            $this->calendarOption->update($schedule_id, $schedule);
        }
        
        return $this->response->withNoContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = $this->user->getById($id);

        // delete user role
        $user->roles()->detach();

        // delete user statuses
        $user->statuses()->detach();

        // delete calendar option
        if ($user->userSchedule()->get()->first() !== null) {
            $schedule_id = $user->userSchedule()->get()->first()->id;
            $this->calendarOption->destroy($schedule_id);
        }

        // delete user data
        $this->user->destroy($id);
        return $this->response->withNoContent();
    }

    public function setRole(Request $request, $id)
    {
        $user = $this->user->getById($id);
        if (isset($user)) {
            $role = $request->input('role');

            if (isset($role)) {

            }
        }
    }

    /**
     * Update user password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request, $id)
    {
        $user = $this->user->getById($id);

        $this->user->changePassword($user, $request->input('new_password'));

        return $this->response->withNoContent();
    }

    public function getUserRoles(Request $request, $id)
    {
        $userRoles = Access::listUserRoles($id);
        return $this->response->json($userRoles);
    }
}
