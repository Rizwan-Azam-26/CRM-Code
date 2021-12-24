<?php


namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use App\Repositories\DepartmentRepository;
use App\Repositories\TemplateNotificationRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Contact\Repositories\FormFieldRepository;
use Modules\Log\Repositories\ActivityTypeRepository;
use Modules\Notification\Http\Requests\NotificationRequest;
use Modules\Notification\Repositories\NotificationRepository;

class NotificationController extends BaseController
{
    protected $notification;
    /**
     * @var TemplateNotificationRepository
     */
    protected $templateNotificationRepo;
    /**
     * @var FormFieldRepository
     */
    protected $formFieldRepo;
    /**
     * @var DepartmentRepository
     */
    protected $departmentRepo;
    /**
     * @var ActivityTypeRepository
     */
    protected $activityTypeRepo;

    public function __construct(NotificationRepository $notification, DepartmentRepository $departmentRepo, ActivityTypeRepository $activityTypeRepo, TemplateNotificationRepository $templateNotificationRepo, FormFieldRepository $formFieldRepo)
    {
        parent::__construct();

        $this->notification = $notification;
        $this->templateNotificationRepo = $templateNotificationRepo;
        $this->formFieldRepo = $formFieldRepo;
        $this->departmentRepo = $departmentRepo;
        $this->activityTypeRepo = $activityTypeRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->templateNotificationRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->templateNotificationRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(NotificationRequest $request)
    {
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->templateNotificationRepo->store($payload);

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
        $notification = $this->templateNotificationRepo->getById($id);

        return $this->response->item($notification);
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
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->templateNotificationRepo->update($id, $payload);

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
        $this->templateNotificationRepo->destroy($id);

        return $this->response->withNoContent();
    }
}
