<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController as Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\MilestoneRepository;
use App\Repositories\StatusEmailRepositories;
use App\Repositories\StatusExpirationRepository;
use App\Repositories\StatusIssueRepository;
use App\Repositories\StatusLabelRepository;
use App\Repositories\StatusRepository;
use App\Repositories\StatusStatusRepository;
use App\Repositories\StatusStepRepository;
use App\Repositories\StatusTypeRepository;
use App\Repositories\StatusWorkFlowItemRepository;
use App\Repositories\TemplateEmailRepository;
use App\Repositories\TemplateTaskRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Accounting\Repositories\AccountingTypeRepository;
use Modules\Automation\Repositories\ActionRepository;
use Modules\Portal\Repositories\PortalMilestonesRepository;
use Modules\Label\Repositories\LabelRepository;

class StatusController extends Controller
{
    protected $status;
    /**
     * @var LabelRepository
     */
    protected $labelRepo;
    /**
     * @var StatusIssueRepository
     */
    protected $statusIssueRepo;
    /**
     * @var StatusStatusRepository
     */
    protected $statusStatusRepo;
    /**
     * @var StatusStepRepository
     */
    protected $statusStepRepo;
    /**
     * @var StatusWorkFlowItemRepository
     */
    protected $statusWorkFlowItemRepo;
    /**
     * @var StatusEmailRepositories
     */
    protected $statusEmailRepo;
    /**
     * @var StatusExpirationRepository
     */
    protected $statusExpirationRepo;
    /**
     * @var CompanyRepository
     */
    protected $companyRepo;
    /**
     * @var StatusTypeRepository
     */
    protected $statusTypeRepo;
    /**
     * @var MilestoneRepository
     */
    protected $mileStoneRepo;
    /**
     * @var PortalMilestonesRepository
     */
    protected $portalMileStoneRepo;
    /**
     * @var ActionRepository
     */
    protected $actionRepo;
    /**
     * @var AccountingTypeRepository
     */
    protected $accountingTypeRepo;
    /**
     * @var DepartmentRepository
     */
    protected $departmentRepo;
    /**
     * @var TemplateEmailRepository
     */
    protected $templateEmailRepo;
    /**
     * @var TemplateTaskRepository
     */
    protected $templateTaskRepo;

    public function __construct(TemplateTaskRepository $templateTaskRepo, TemplateEmailRepository $templateEmailRepo, DepartmentRepository $departmentRepo, AccountingTypeRepository $accountingTypeRepo, ActionRepository $actionRepo, StatusTypeRepository $statusTypeRepo, MilestoneRepository $mileStoneRepo, CompanyRepository $companyRepo, StatusEmailRepositories $statusEmailRepo, StatusExpirationRepository $statusExpirationRepo, StatusStatusRepository $statusStatusRepo, StatusWorkFlowItemRepository $statusWorkFlowItemRepo, StatusStepRepository $statusStepRepo, StatusIssueRepository $statusIssueRepo, StatusRepository $status, LabelRepository $labelRepo, PortalMilestonesRepository $portalMileStoneRepo)

    {
        parent::__construct();

        $this->status = $status;
        $this->labelRepo = $labelRepo;
        $this->statusWorkFlowItemRepo = $statusWorkFlowItemRepo;
        $this->statusStepRepo = $statusStepRepo;
        $this->statusIssueRepo = $statusIssueRepo;
        $this->statusEmailRepo = $statusEmailRepo;
        $this->statusExpirationRepo = $statusExpirationRepo;
        $this->companyRepo = $companyRepo;
        $this->statusTypeRepo = $statusTypeRepo;
        $this->mileStoneRepo = $mileStoneRepo;
        $this->departmentRepo = $departmentRepo;
        $this->accountingTypeRepo = $accountingTypeRepo;
        $this->templateEmailRepo = $templateEmailRepo;
        $this->templateTaskRepo = $templateTaskRepo;
        $this->statusStatusRepo = $statusStatusRepo;
        $this->portalMileStoneRepo = $portalMileStoneRepo;
        $this->actionRepo = $actionRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->status->all());
    }

    /**
     * Display a listing of the resource.
     * Params1: limit=-1:not paging, limit!=-1: paging
     * Params2: loan_id
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->status->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //$val = Model_System_Status::validate('add_status');

        if ($request->all()) {

            if ($request->has('label_ids') && $request->has('label_ids')) {
                $label_ids = $request->label_ids;
                $request->request->remove('label_ids');
            }

            if ($request->has('issue_ids') && $request->has('issue_ids')) {
                $issue_ids = $request->issue_ids;
                $request->request->remove('issue_ids');
            }

            if ($request->has('more_statuses') && $request->has('more_statuses')) {
                $more_statuses = $request->more_statuses;
                $request->request->remove('more_statuses');
            }


            if ($request->has('next_steps') && $request->has('next_steps')) {
                $next_steps = $request->next_steps;
                $request->request->remove('next_steps');
            }

            if ($request->has('workflow_tasks') && $request->has('workflow_tasks')) {
                $workflow_tasks = $request->workflow_tasks;
                $request->request->remove('workflow_tasks');
            }

            if ($request->request->has('email_templates') && $request->has('email_templates')) {
                $email_templates = $request->email_templates;
                $request->request->remove('email_templates');
            }

            if ($request->request->has('expire_day') && $request->has('expire_day')) {
                for ($l = 0; $l < count($request->expire_day); $l++) {
                    if ($request->has('expire_action'[$l]) && $request->has('expire_day'[$l]) && $request->has('expire_action'[$l]) && $request->has('expire_day'[$l])) {
                        $expirations[] = array(
                            'company_id' => Auth::user()->company_id,
                            'action_id' => $request->expire_action[$l],
                            'expr_days' => $request->expire_day[$l]
                        );
                    }
                }
                $request->request->remove('expire_day');
                $request->request->remove('expire_action');
            }


            $request->company_id = Auth::user()->company_id;
            $status_id = $this->status->store($request->all());

            if (isset($label_ids)) {
                $this->labelRepo->updateLabels($status_id, $label_ids);
            }

            if (isset($issue_ids)) {
                $this->statusIssueRepo->updateIssues($status_id, $issue_ids);
            }

            if (isset($more_statuses)) {
                $this->statusStatusRepo->updateStatuses($status_id, $more_statuses, Auth::user()->company_id);
            }

            if (isset($next_steps)) {
                $this->statusStepRepo->updateStatuses($status_id, $next_steps, Auth::user()->company_id);
            }

            if (isset($workflow_tasks)) {
                $this->statusWorkFlowItemRepo->updateItems($status_id, $workflow_tasks, Auth::user()->company_id);
            }


            if (isset($email_templates)) {
                $this->statusEmailRepo->updateTemplates($status_id, $email_templates, Auth::user()->company_id);
            }

            if (isset($expirations)) {
                $this->statusExpirationRepo->addMany($expirations, $status_id);
            }
        }
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
        $status = $this->status->getById($id);
        return $this->response->item($status);
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
        if ($request->all()) {

            if ($request->has('label_ids') && $request->has('label_ids')) {
                $label_ids = $request->label_ids;
                $request->request->remove('label_ids');
            }

            if ($request->has('issue_ids') && $request->has('issue_ids')) {
                $issue_ids = $request->issue_ids;
                $request->request->remove('issue_ids');
            }

            if ($request->has('more_statuses') && $request->has('more_statuses')) {
                $more_statuses = $request->more_statuses;
                $request->request->remove('more_statuses');
            }


            if ($request->has('next_steps') && $request->has('next_steps')) {
                $next_steps = $request->next_steps;
                $request->request->remove('next_steps');
            }

            if ($request->has('workflow_tasks') && $request->has('workflow_tasks')) {
                $workflow_tasks = $request->workflow_tasks;
                $request->request->remove('workflow_tasks');
            }

            if ($request->has('email_templates') && $request->has('email_templates')) {
                $email_templates = $request->email_templates;
                $request->request->remove('email_templates');
            }

            if ($request->has('sms_templates') && $request->has('sms_templates')) {
                $sms_templates = $request->sms_templates;
                $request->request->remove('sms_templates');
            }

            if ($request->has('scripts_templates') && $request->has('scripts_templates')) {
                $scripts_templates = $request->scripts_templates;
                $request->request->remove('scripts_templates');
            }

            if ($request->has('expire_day') && $request->has('expire_day')) {
                for ($l = 0; $l < count($request->expire_day); $l++) {
                    if ($request->has('expire_action'[$l]) && $request->has('expire_day'[$l]) && $request->has('expire_action'[$l]) && $request->has('expire_day'[$l])) {
                        $expirations[] = array(
                            'company_id' => Auth::user()->company_id,
                            'action_id' => $request->expire_action[$l],
                            'expr_days' => $request->expire_day[$l],
                            'status_id' => $id
                        );
                    }

                }

                $request->request->remove('expire_day');
                $request->request->remove('expire_action');
            }
            
            if ($request->all()) {
                $this->status->update($id, $request->all());
            }

            if (isset($label_ids)) {
                $this->labelRepo->updateLabels($id, $label_ids);
            }

            if (isset($issue_ids)) {
                $this->statusIssueRepo->updateIssues($id, $issue_ids);
            }

            if (isset($more_statuses)) {
                $this->statusStatusRepo->updateStatuses($id, $more_statuses, Auth::user()->company_id);
            }

            if (isset($next_steps)) {
                $this->statusStepRepo->updateStatuses($id, $next_steps, Auth::user()->company_id);
            }

            if (isset($workflow_tasks)) {
                $this->statusWorkFlowItemRepo->updateItems($id, $workflow_tasks, Auth::user()->company_id);
            }

            if (isset($email_templates)) {
                $this->statusEmailRepo->updateTemplates($id, $email_templates, Auth::user()->company_id);
            }

            if (isset($sms_templates)) {
                //   Model_System_Status_SMS::updateTemplates($id, $sms_templates,\Model_Account::getCompanyId());
            }

            if (isset($scripts_templates)) {
                //  Model_System_Status_Scripts::updateTemplates($id, $scripts_templates,\Model_Account::getCompanyId());
            }

            if (isset($expirations)) {
                $this->statusExpirationRepo->upsert($expirations, $id, Auth::user()->company_id);
            } else {
                // Delete Status Expirations regardless of update
                $this->statusExpirationRepo->deleteByCompany($id, Auth::user()->company_id);
            }

        }
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
        $this->status->destroy($id);
        return $this->response->withNoContent();
    }

    public function group()
    {
        return $this->response->collection($this->status->findAllGroups());
    }
}
