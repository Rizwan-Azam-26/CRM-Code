<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\TriggerRequest;
use Modules\Automation\Repositories\TriggerRepository;
use Modules\Automation\Repositories\TriggerTypeRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Automation\Repositories\ActionRepository;

class TriggerController extends BaseController
{
    protected $trigger;
    /**
     * @var TriggerTypeRepository
     */
    protected $triggerTypeRepo;
    /**
     * @var ActionRepository
     */
    protected $actionRepo;

    public function __construct(TriggerRepository $trigger, TriggerTypeRepository $triggerTypeRepo, ActionRepository $actionRepo)
    {
        parent::__construct();

        $this->trigger = $trigger;
        $this->triggerTypeRepo = $triggerTypeRepo;
        $this->actionRepo = $actionRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->trigger->all());
    }

    /**
     * Display a listing of the resource.
     * Params1: limit=-1:not paging, limit!=-1: paging
     * Params2: company_id
     * @return Response
     */
    public function index(Request $request)
    {
        $request->company_id = Auth::user()->company_id;
        return $this->response->collection($this->trigger->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(TriggerRequest $request)
    {
        $data = $request->all();
        $data['company_id'] = 1;
        $data['trigger_id'] = $request->trigger_id;
        $data['created_by'] = Auth::user()->id;

        $this->trigger->store($data);
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
        $trigger = $this->trigger->getById($id);
        return $this->response->item($trigger);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(TriggerRequest $request, $id)
    {
        $data['row'] = $this->trigger->getById($id);
        $this->trigger->update($id, $request->all());
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
        $data['row'] = $this->trigger->getById($id);
        $this->trigger->destroy($id);
        return $this->response->withNoContent();
    }
}
