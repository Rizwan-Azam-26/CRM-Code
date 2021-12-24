<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\MilestoneRequest;
use App\Repositories\MilestoneRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;


class MilestoneController extends BaseController
{
    protected $milestone;

    public function __construct(MilestoneRepository $milestone)
    {
        parent::__construct();

        $this->milestone = $milestone;
    }

    /**
     * Get all of the resource.
     *
     * @return Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->milestone->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->milestone->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(MilestoneRequest $request)
    {
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->milestone->store($payload);
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
        $milestone = $this->milestone->getById($id);
        return $this->response->item($milestone);
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
        $this->milestone->update($id, $request->all());
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
        $this->milestone->destroy($id);
        return $this->response->withNoContent();
    }
}
