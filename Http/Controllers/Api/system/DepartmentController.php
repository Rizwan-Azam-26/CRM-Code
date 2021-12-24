<?php
namespace App\Http\Controllers\Api\system;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\DepartmentRequest;
use App\Http\Controllers\Api\BaseController;
use App\Repositories\DepartmentRepository;

class DepartmentController extends BaseController
{

    /**
     * @var DepartmentRepository
     */
    protected $department;

    public function __construct(DepartmentRepository $department)
    {
        parent::__construct();

        $this->department = $department;
    }
    /**
     * Get all of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->department->all());
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->department->pageWithRequest($request));
    }

    /**De
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DepartmentRequest $request)
    {
        $data = array_merge(
            $request->all(), 
            ['company_id' => Auth::user()->company_id]
        );
        $this->department->store($data);
  
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
        $department = $this->department->getById($id);

        return $this->response->item($department);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DepartmentRequest $request, $id)
    {
        $data = array_merge(
            $request->all(), 
            ['company_id' => Auth::user()->company_id]
        );
        $this->department->update($id, $data);
  
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
        $this->department->destroy($id);
        
        return $this->response->withNoContent();
    }
}
