<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController as Controller;
use Illuminate\Http\Request;

use App\Http\Requests\TriggerTypeRequest;

use Modules\Automation\Repositories\TriggerTypeRepository;
// use App\Repositories\TriggerTypeRepository;

class TriggerTypeController extends Controller
{
    protected $triggerType;

    public function __construct(TriggerTypeRepository $triggerType)
    {
        parent::__construct();

        $this->triggerType = $triggerType;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->triggerType->all($request));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->triggerType->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TriggerTypeRequest $request)
    {
        $data = $request->all();

        $triggerType = $this->triggerType->store($data);

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
        $triggerType = $this->triggerType->getById($id);

        return $this->response->item($triggerType);
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
        $data = $request->all();
        $this->triggerType->update($id, $data);

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
        $this->triggerType->destroy($id);

        return $this->response->withNoContent();
    }
}
