<?php

namespace App\Http\Controllers\Api\system;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\UserGroupRequest;
use App\Repositories\UserGroupRepository;


class UserGroupController extends BaseController
{
    /**
     * @var UserGroupRepository 
     */
    protected $userGroupRepo;

    public function __construct( UserGroupRepository $userGroupRepo )
    {
        parent::__construct();

        $this->userGroupRepo = $userGroupRepo;
    }

    /**
     * Get all of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->userGroupRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->userGroupRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserGroupRequest $request)
    {
        $data = array_merge(
            $request->all(), 
            ['company_id' => Auth::user()->company_id]
        );
        $userGroup = $this->userGroupRepo->store($data);
        $userGroup->users()->sync($request->get('user_ids'));
  
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
        $userGroupRepo = $this->userGroupRepo->getById($id);

        return $this->response->item($userGroupRepo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserGroupRequest $request, $id)
    {
        $data = array_merge(
            $request->all(), 
            ['company_id' => Auth::user()->company_id]
        );
        $userGroup = $this->userGroupRepo->update($id, $data);
        $userGroup->users()->sync($request->get('user_ids'));
  
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
        $this->userGroupRepo->destroy($id);

        return $this->response->withNoContent();
    }
}
