<?php

namespace App\Services;

use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use Modules\Contact\Repositories\CaseRepository;
use Modules\Contact\Repositories\CaseAssignmentRepository;
use Modules\Log\Repositories\LogActivityRepository;
use Modules\Log\Services\LogService;

class UsersService
{
    /**
     * @var UserRepository
     */
    protected $userRepo;
    /**
     * @var CompanyRepository
     */
    protected $companyRepo;
    /**
     * @var LogActivityRepository
     */
    protected $logActivityRepo;

    /**
     * @var CaseAssignmentRepository
     */
    protected $caseAssignmentRepo;
    /**
     * @var CaseRepository
     */
    protected $caseRepo;

    private $departmentRepo;

    private $logService;

    public function __construct(
        UserRepository $userRepo,
        CaseRepository $caseRepo,
        CaseAssignmentRepository $caseAssignmentRepo,
        CompanyRepository $companyRepository,
        LogActivityRepository $logActivityRepo,
        DepartmentRepository $departmentRepo,
        LogService $logService
    )
    {
        $this->userRepo = $userRepo;
        $this->caseRepo = $caseRepo;
        $this->caseAssignmentRepo = $caseAssignmentRepo;
        $this->companyRepo = $companyRepository;
        $this->logActivityRepo = $logActivityRepo;
        $this->departmentRepo = $departmentRepo;
        $this->logService = $logService;
    }

    public function answered($data)
    {
        // Lookup User
        if(isset($data['interface'])) {
            $channel_parts = explode("@", $data['interface']);
            $sip_parts = explode("/", $channel_parts[0]);
            $extension = $sip_parts[1];

            if (!$extension) {
                throw new \InvalidArgumentException('No Extension');
            }

            $ext_user = $this->userRepo->findByField('extension', $extension);

            if(!$ext_user) {
                throw new \InvalidArgumentException('No User Found');
            }

            $user_id = $ext_user['id'];
        }


        if(strlen($data['client']) < 10){
            throw new \InvalidArgumentException('Not a phone number');
        }

        // Lookup File
        $case = $this->caseRepo->findByPhone($data['client']);
        // Belongs to Case file
        if (!$case) {
            throw new \Exception('No Case Found with '.$data['client']);
        }

        $case_id = $case['id'];

        // Get User Role
        /**
        $primary_role = \Model_System_Roles::getUserPrimaryRole($user_id);//This is from role model
        **/
        if(isset($primary_role) && $primary_role['id'] == 8){
            // Lookup Case Assignemtn of Sales Rep on File
            $department_assignment =  $this->caseAssignmentRepo->findByCaseAndDept($case_id, $primary_role['id']);
            if(!$department_assignment){
                // No Assignment! Assign...
                $this->caseAssignmentRepo->store((array(
                    'department_id' => $primary_role['id'],
                    'user_id' => $user_id,
                    'case_id' => $case_id,
                    'created' => date("Y-m-d H:i:s"),
                    'created_by' => 1
                )));


                $this->caseRepo->update($case_id, array('company_id' => $ext_user['company_id']));
                if($case['company_id'] != $ext_user['company_id']){
                    $company = $this->companyRepo->find($ext_user['company_id']);
                    $this->logService->addActivity($case_id,'Assignment','COMPANY Assignment changed from '.$case['company'].' to '.$company['long_name'].' ('.$company['name'].')','',1,$case['company_id']);
                }

                // Notify Agent
//                $notify = new \Notification\Model_Notification();
//                $notify->writeMessage($user_id,'Case Assignment','You have been automatically assigned to CASE '.$case_id,'General',$case_id);
                // Update History
                $message = '';
                $message .= $ext_user['first_name']. ' '.$ext_user['last_name'].' assigned as ';
                if(isset($primary_role['id'])){
                    $department = $this->departmentRepo->getById($primary_role['id']);
                    $message .= $department['name'];
                }
                $this->logService->addActivity($case_id,'Assignment',$message);


            }else{
                // Already has assignment, skip

                throw new \InvalidArgumentException('Case Already has assignment');
            }
        }else{
            throw new \InvalidArgumentException('Primary Role not Sales. '.$primary_role['id']);
        }


        // Assign Person to File

        // Notify Person

    }

}
