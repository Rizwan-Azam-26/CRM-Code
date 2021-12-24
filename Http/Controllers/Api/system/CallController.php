<?php


namespace App\Http\Controllers\Api\system;


use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\CallRequest;
use App\Repositories\TemplateCallRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Contact\Repositories\FormFieldRepository;

class CallController extends BaseController
{
    protected $templateCallRepo;
    /**
     * @var FormFieldRepository
     */
    protected $formFieldRepo;

    public function __construct(TemplateCallRepository $templateCallRepo, FormFieldRepository $formFieldRepo)
    {
        $this->templateCallRepo = $templateCallRepo;
        $this->formFieldRepo = $formFieldRepo;
    }


    /**
     * Get all of the resource.
     *
     * @return Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->templateCallRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $data['messages'] = $this->templateCallRepo->findAll();
        return $this->response->json($data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(CallRequest $request)
    {
        try {
            $request->company_id = Auth::user()->company_id;

            $this->templateCallRepo->store($request->all());
//                \Notification\Notify::success($_POST['name']. ' Added');
            $this->response->setStatusCode('200');
            return $this->response->json('Success');
        } catch (Exception $e) {
            $this->response->setStatusCode('403');
            return $this->response->json('Error');
        }
        Fuel::add_module('calltracking');

        $data['fields'] = $this->formFieldRepo->findAll(1);
        return $this->response->json($data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $templateCall = $this->templateCallRepo->getById($id);
        return $this->response->item($templateCall);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(CallRequest $request, $id)
    {
        $data['row'] = $this->templateCallRepo->find($id);
        $this->has_company_access($data['row']['company_id'], Auth::user()->company_id);

        try {
            $this->templateCallRepo->update($id, $request->all());
            $this->response->setStatusCode('200');
            return $this->response->json('Success');
        } catch (Exception $e) {
            $this->response->setStatusCode('403');
            return $this->response->json('Error');
        }
        Fuel::add_module('calltracking');

        $data['row'] = $this->templateCallRepo->find($id);
        $data['fields'] = $this->formFieldRepo->findAll(1);
        return $this->response->json($data);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destory($id)
    {
        try {

            $data['row'] = $this->templateCallRepo->find($id);
            $this->has_company_access($data['row']['company_id'], Auth::user()->company_id);

            $this->templateCallRepo->destroy($id);
            $this->response->setStatusCode('200');
            return $this->response->json('Success');
        } catch (Exception $e) {
            $this->response->setStatusCode('403');
            return $this->response->json('Error');
        }
    }
}