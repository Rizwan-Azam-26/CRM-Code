<?php
namespace App\Http\Controllers\Api\system;

use Illuminate\Http\Request;
use App\Http\Requests\RoleRequest;
use App\Http\Controllers\Api\BaseController;
use App\Repositories\RoleRepository;
use App\Models\Permission;
use Auth;


class RoleController extends BaseController
{
    protected $role;

    public function __construct(RoleRepository $role)
    {
        parent::__construct();

        $this->role = $role;
    }

    /**
     * Get all of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->role->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->role->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
    {
        $data = array_merge($request->all(), [
            'company_id' => $request->user()->company_id,
            'guard_name' => 'web',
            'created_by' => $request->user()->id
        ]);

        $role = $this->role->store($data);

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
        $role = $this->role->getById($id);

        return $this->response->item($role);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = array_merge($request->all(), [
            'company_id' => $request->user()->company_id,
            'updated_by' => $request->user()->id
        ]);
        $this->role->update($id, $data);

        return $this->response->withNoContent();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicate(Request $request, $id)
    {
        $role = $this->role->getById($id)->makeHidden(['id', 'company_id', 'sort', 'system', 'keep', 'read_only', 'created_at', 'updated_at']);
        $dup_role_data = $data = array_merge($role->toArray(), [
            'name' => $role->name . ' ' . time(),
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id
        ]);

        $dup_role = $this->role->store($dup_role_data);
        $dup_role->syncPermissions($role->permissions);

        return $this->response->withNoContent();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function permissions(Request $request, $id)
    {
        $permission_ids = $request->input('permissions');
        $permissions = Permission::whereIn('id', $permission_ids)->get();
        $role = $this->role->getById($id);
        $role->syncPermissions($permissions);

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
        $this->role->destroy($id);

        return $this->response->withNoContent();
    }
}
