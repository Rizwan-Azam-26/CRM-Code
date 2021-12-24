<?php

namespace App\Services;
use Modules\Contact\Services\CaseService;
use Modules\Student\Repositories\StudentLoanRepository;

class ApplicationService
{

    protected $StudentLoanRepository;
    protected $CaseService;

    public function __construct(CaseService $CaseService, StudentLoanRepository $StudentLoanRepository)
    {
        $this->CaseService = $CaseService;
        $this->StudentLoanRepository = $StudentLoanRepository;
    }


    public function generate_idr_app($data)
    {
        // Get Document
        // Validate Fields
        // Merge Fields with Document
        $contact = array(
            'full_name'=> $data['first_name'] . ' ' . $data['last_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'ssn' => $data['ssn'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zipcode' => $data['zipcode'],
            'email' => $data['email'],
            'primary_phone' => $data['primary_phone'],
            'alt_phone' => $data['alt_phone'],
            'order_date' =>  date('m/d/Y')
        );

        $options = array();
        $pages = array();
        // Q1
        $options['1-2'] = 1;
        // Q5
        $options['5-1'] = $data['children_support'];
        // Q6
        $options['6-1'] = $data['other_support'];
        // Q7
        $options['7-'.$data['marital_status']] = 1;

        if(($data['children_support'] + $data['other_support']) > 0){
            // Has Family Part
            $pages['2-2'] = 'no_family_size';
        }else{
            // No Family Part
            $pages['2-2'] = 'family_size';
        }

        if($data['servicer_id'] == 1){
            // Dont know
            $pages['4'] = 'no_servicer';
        }else{
            // Selected Servicer
            $pages['4'] = 'servicer';

        }

        // Q3
        if($data['marital_status'] == 2) {
            $options['8-'.$data['spouse_loans']] = 1;
            $pages['3'] = 'joint';
        }

        // Q9
        if(isset($data['spouse_loans']) && $data['spouse_loans'] == 1){
            $options['spouse_ssn'] = $data['spouse_ssn'];
            $options['spouse_name'] = $data['spouse_name'];
            $options['spouse_dob'] = $data['spouse_dob'];
        }
        // Q10
        if(isset($data['file_jointly'])) {
            $options['10-' . $data['file_jointly']] = 1;
        }

        if(isset($data['poi_documents'])){ // Q4
            switch($data['poi_documents']){
                case 1:
                    $options['11-2'] = 1;
                    $pages['2-1'] = 'tax_returns';
                    break;
                case 2:
                    $options['11-1'] = 1;
                    $options['12-1'] = 1;
                    $pages['2-1'] = 'paystubs';
                    break;
                case 3:
                    $options['11-1'] = 1;
                    $options['12-2'] = 1;
                    $pages['2-1'] = 'taxable_income';
                    break;
            }
        }

        // Q13 - Q20
        if(isset($data['poi_documents_spouse'])){ // Q5
            switch($data['poi_documents_spouse']){

                case 1:
                    $options['13-2'] = 1;
                    $options['14-2'] = 1;
                    $pages['2-1'] = 'taxable_income';
                    break;
                case 2:
                    $options['13-1'] = 1;
                    $options['15-1'] = 1;
                    $options['16-1'] = 1;
                    $pages['2-1'] = 'paystubs_spouse';
                    break;
                case 3:
                    $options['13-1'] = 1;
                    $options['15-1'] = 1;
                    $options['16-2'] = 1;
                    $pages['2-1'] = 'paystubs_spouse';
                    break;
                case 4:
                    $options['13-1'] = 1;
                    $options['15-2'] = 1;
                    $options['16-1'] = 1;
                    $pages['2-1'] = 'paystubs_spouse';
                    break;
                case 5:
                    $options['13-1'] = 1;
                    $options['15-2'] = 1;
                    $options['16-2'] = 1;
                    $pages['2-1'] = 'taxable_income';
                    break;
            }
        }

        // Q13 - Q20
        if(isset($data['poi_documents_spouse_2'])){ // Q6
            switch($data['poi_documents_spouse_2']){

                case 1:
                    $options['17-1'] = 1;
                    $options['18-1'] = 1;
                    $options['19-1'] = 1;
                    $options['20-1'] = 1;
                    $pages['2-1'] = 'paystubs_spouse';
                    break;
                case 2:

                    $options['17-1'] = 1;
                    $options['18-1'] = 1;
                    $options['19-1'] = 1;
                    $options['20-2'] = 1;
                    $pages['2-1'] = 'paystubs_spouse';
                    break;
                case 3:

                    $options['17-1'] = 1;
                    $options['18-2'] = 1;
                    $options['19-1'] = 1;
                    $options['20-1'] = 1;
                    $pages['2-1'] = 'paystubs_spouse';
                    break;
                case 4:

                    $options['17-1'] = 1;
                    $options['18-2'] = 1;
                    $options['19-1'] = 1;
                    $options['20-2'] = 1;
                    $pages['2-1'] = 'taxable_income';
                    break;

            }
        }

        if(in_array($data['marital_status'], array(1,3,4))){ // Q3

            $options['13-1'] = $options['13-2'] = 0;
            $options['14-1'] = $options['14-2'] = 0;
            $options['15-1'] = 0;
            $options['16-1'] = 0;
            $options['17-1'] = 0;
            $options['18-1'] = $options['18-2'] = 0;
            $options['19-1'] = 0;
            $options['20-1'] = $options['20-2'] = 0;
            $pages['3'] = 'single';

        }
        return array(array_merge($contact, $options), $pages);
        // Return Document Location
    }

    public function generate_consolidation_app($case_id)
    {

        // Validate Fields!
        // Merge Fields with Document
        // Generate Doc Meta
        $data['case_id'] = $case_id;
        $data['order_date'] = date('m/d/Y');

        // Temporary Pdf
        $tmp_pdf = APPPATH.'modules'.DS.'student'.DS.'forms'.DS.'tmp'.DS.uniqid().'.pdf';


        // Prepare Array with App Variables
        $app_data = $this->CaseService->getCaseObjects($case_id);
        $loans = $this->StudentLoanRepository->findByCaseID($case_id, 1, 1);
        $ex_loans = $this->StudentLoanRepository->findByCaseID($case_id, 1, 'ISNULL');

        // Sort Excluded Loans
        $inc = 1;
        $x_loans = array();
        foreach($ex_loans as $xloan){
            if($xloan['principal_balance'] > 0){

                $x_loans['ex_balance'.$inc] = '$'.$xloan['principal_balance'];
                $x_loans['ex_loan_code'.$inc] = $xloan['student_loan_letter'];
                $x_loans['ex_acct_num'.$inc] = $xloan['loan_award_id'];
                $x_loans['ex_loan_holder'.$inc] = $xloan['loan_contact_street_1'] . ' ' . $xloan['loan_contact_street_2'] ."\r\n". $xloan['loan_contact_city'].', '.$xloan['loan_contact_state']. ' '.$xloan['loan_contact_zip'];

                $inc++;
            }
        }

        // Sort Included Loans
        $inc = 1;
        $inc_loans = array();
        foreach($loans as $loan){
            if($loan['principal_balance'] > 0) {

                $inc_loans['payoff'.$inc] = '$'.$loan['principal_balance'];
                $inc_loans['loan_code'.$inc] = $loan['student_loan_letter'];
                $inc_loans['acct_num'.$inc] = $loan['loan_award_id'];
                $inc_loans['loan_holder'.$inc] = $loan['loan_contact_street_1'] . ' ' . $loan['loan_contact_street_2'] ."\r\n". $loan['loan_contact_city'].', '.$loan['loan_contact_state']. ' '.$loan['loan_contact_zip'];

                $inc++;
            }
        }

        // Merge all data into one array for form
        $app_data = array_merge($app_data, $inc_loans, $x_loans);

        foreach($app_data as $k => $v){
            $app_data[$k] = (is_array($v)?'':$v);
        }

        $ssn_x = str_split($app_data['ssn']);

        $field_map = array(
            'first_name' => $app_data['first_name'],
            'last_name' => $app_data['last_name'],
            'full_name' => $app_data['first_name'] .' '.$app_data['last_name'],
            'middle_initial' => $app_data['middle_name'],
            'ssn1' => $ssn_x[0],
            'ssn2' => $ssn_x[1],
            'ssn3' => $ssn_x[2],
            'ssn4' => $ssn_x[3],
            'ssn5' => $ssn_x[4],
            'ssn6' => $ssn_x[5],
            'ssn7' => $ssn_x[6],
            'ssn8' => $ssn_x[7],
            'ssn9' => $ssn_x[8],
            'dob' => date("m/d/Y", strtotime($app_data['dob'])),
            'address' => $app_data['address'] . ' ' . $app_data['address_2'] . ' ' .$app_data['city'].','.$app_data['state'].' '.$app_data['zip'],
            'phone_number' => $app_data['primary_phone'],
            'email' => $app_data['email'],
            'dl_number' => $app_data['dl_state'] .' '.$app_data['dl_number'],
            'employer_address' => $app_data['employer_address'] . ' ' . $app_data['employer_city'] . ' ' .$app_data['employer_state'].','.$app_data['employer_zip'],
            'work_number' => $app_data['work_phone'],
            'ref1_address' =>$app_data['ref1_address'] . ' ' . $app_data['ref1_city'] . ' ' .$app_data['ref1_state'].','.$app_data['ref1_zip'],
            'ref2_address' => $app_data['ref2_address'] . ' ' . $app_data['ref2_city'] . ' ' .$app_data['ref2_state'].','.$app_data['ref2_zip']
        );

        $app_data = array_merge($app_data, $field_map);

        //echo '<pre>';
        // print_r($app_data);
        // exit();

        // Need discussion with Brian to install package for PDF

        // Source Consolidation Application
        $pdf = new Pdf(APPPATH.'modules'.DS.'student'.DS.'forms'.DS.'consolidation_application.pdf');
        $pdf->fillForm($app_data);
        $pdf->flatten();
        $pdf->saveAs($tmp_pdf);

        if($pdf->getError()){
            throw new \Exception($pdf->getError());
        }

        // Return Document Location
        if (is_file($tmp_pdf)) {
            return $tmp_pdf;
        }

        return false;
    }

}
