<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserAccessController extends BaseController
{

    /**
     * @var UserRepository
     */
    protected $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        parent::__construct();
        $this->userRepo = $userRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function all()
    {
        return $this->response->collection($this->userRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->userRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
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
        $userAccess = $this->userRepo->getById($id);

        return $this->response->item($userAccess);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $userAccess = $this->userRepo->getById($id);
        $userAccess->roles()->sync($request->role_ids);
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
        $userAccess = $this->userRepo->getById($id);
        $userAccess->roles()->detach();
        return $this->response->withNoContent();
    }
}