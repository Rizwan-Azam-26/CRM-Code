<?php


namespace App\Http\Controllers\Api\system;


use App\Http\Controllers\Api\BaseController;
use App\Repositories\UserPermissionRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserPermissionController extends BaseController
{
    protected $userPermission;

    public function __construct(UserPermissionRepository $userPermission)
    {
        parent::__construct();

        $this->userPermission = $userPermission;
    }

    /**
     * Get all of the resource.
     *
     * @return Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->userPermission->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {

        $data['user_id'] = $request->user_id;
        return $this->response->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        return $this->response->json([]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $userPermission = $this->userPermission->getById($id);

        return $this->response->item($userPermission);
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
        $data = array_merge($request->all(), [
            'company_id' => Auth::user()->company_id,
            'updated_by' => Auth::user()->id
        ]);
        $this->userPermission->update($id, $data);

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
        $this->userPermission->destroy($id);

        return $this->response->withNoContent();
    }

}