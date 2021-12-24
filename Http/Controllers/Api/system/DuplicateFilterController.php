<?php


namespace App\Http\Controllers\Api\system;


use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\DuplicateFilterRuleRequest;
use App\Repositories\StatusRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Automation\Repositories\ActionRepository;
use Modules\Contact\Repositories\DuplicateFilterRuleRepository;

class DuplicateFilterController extends BaseController
{
    /**
     * @var DuplicateFilterRuleRepository
     */
    protected $duplicateFilterRuleRepo;
    /**
     * @var StatusRepository
     */
    protected $statusRepo;
    /**
     * @var ActionRepository
     */
    protected $actionRepo;

    public function __construct(DuplicateFilterRuleRepository $duplicateFilterRuleRepo, StatusRepository $statusRepo, ActionRepository $actionRepo)
    {
        parent::__construct();

        $this->duplicateFilterRuleRepo = $duplicateFilterRuleRepo;
        $this->statusRepo = $statusRepo;
        $this->actionRepo = $actionRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        return $this->response->collection($this->duplicateFilterRuleRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['rules'] = $this->duplicateFilterRuleRepo->findAll();
        return $this->response->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(DuplicateFilterRuleRequest $request)
    {

            try{
                $data = $request->all();
                $data['company_id'] = Auth::user()->company_id;

                $this->duplicateFilterRuleRepo->store($data);

                $this->response->setStatusCode('200');
                return $this->response->json('Success');
            }catch(\Exception $e){
                $this->response->setStatusCode('403');
                return $this->response->json('Error');
//                \Notification\Notify::error($e);
            }



        $data['statuses'] = $this->statusRepo->findAll(1);
        $data['actions'] = $this->actionRepo->findAll();
        return $this->response->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $duplicateFilterRuleRepo = $this->duplicateFilterRuleRepo->getById($id);

        return $this->response->item($duplicateFilterRuleRepo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {


        $data['row'] = $this->duplicateFilterRuleRepo->find($id);
        $this->has_company_access($data['row']['company_id'], Auth::user()->company_id);


            try{
                $this->duplicateFilterRuleRepo->update($id, $request->all());

                $this->response->setStatusCode('200');
                return $this->response->json('Success');
            }catch(\Exception $e){
                $this->response->setStatusCode('403');
                return $this->response->json('Error');
//                \Notification\Notify::error($e);
            }

        $data['statuses'] = $this->statusRepo->findAll(1);
        $data['actions'] = $this->actionRepo->findAll();
        $data['row'] = $this->duplicateFilterRuleRepo->find($id);
        return $this->response->json($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            $data['row'] = $this->duplicateFilterRuleRepo->find($id);
            $this->has_company_access($data['row']['company_id'], Auth::user()->company_id);

            $this->duplicateFilterRuleRepo->delete($id);

            $this->response->setStatusCode('200');
            return $this->response->json('Success');
        } catch (\Exception $e) {
            $this->response->setStatusCode('403');
            return $this->response->json('Error');
//                \Notification\Notify::error($e);
        }
    }
}