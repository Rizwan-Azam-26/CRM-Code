<?php

namespace App\Services;

class RepaymentService
{
    const REPAY_TYPE_GRADUATED = 0;
    const REPAY_TYPE_GRADUATED_EXTENDED = 1;
    const REPAY_TYPE_IBR = 2;
    const REPAY_TYPE_ICR = 3;
    const REPAY_TYPE_ISR = 4;
    const REPAY_TYPE_PAYE = 5;
    const REPAY_TYPE_REPAYE = 9;
    const REPAY_TYPE_REPAYE_GRAD = 9;
    const REPAY_TYPE_STD = 6;
    const REPAY_TYPE_STD_EXTENDED = 7;

    public $graduated_repayment_term = 24;
    public $loan_type = 0;
    public $standard_payment = 0;
    public $monthly_interest = 0;
    public $monthly_payment = 0;
    public $last_payment = 0;
    public $paid_principal = 0;
    public $weighted_average;
    public $agi = 0;
    public $principal = 0;
    public $balance = 0;
    public $repay_type = 0;
    public $annual_growth_rate = 5.00;
    public $schedules;
    public $inflation_rate = 2.390;
    public $term = 12;
    public $term_years = 0;
    public $total_interest;
    public $total_paid;
    public $state;
    public $family_size;
    public $filing_status = 'single';
    public $service_type = 'program_switch';
    public $poverty_first_person;
    public $poverty_additional;
    private $loans_ineligible = array();
    public $loans = array();


    public function calcLoanBalance() {

        $this->principal = 0;
        $ctr = 0;
        foreach($this->loans as $loan) {
            $this->principal += (int)$loan['principal_balance'] + (int)$loan['interest_balance'];
            $ctr++;
        }

    }

    public function getPlans()
    {

        // Set Plans and their methods for calculation
        $plans = array(
            'STANDARD' => array('method'=>'calcStandardPayment', 'plan_id' => 5, 'plan_name' => 'Standard Repayment'),
            'EXSTANDARD' => array('method'=>'calcExStandardPayment', 'plan_id' => 7, 'plan_name' => 'Extended Standard Repayment'),
            'IBR' => array('method'=>'calcIBRPayment', 'plan_id' => 3, 'plan_name' => 'Income-Based Repayment'),
            'PAYE' => array('method'=>'calcPAYEPayment', 'plan_id' => 1, 'plan_name' => 'Pay As You Earn'),
            'REPAYE' => array('method'=>'calcREPAYEPayment', 'plan_id' => 2, 'plan_name' => 'Revised Pay As You Earn'),
            'ICR' => array('method'=>'calcICRPayment', 'plan_id' => 4, 'plan_name' => 'Income-Contingent Repayment')
        );

        $income_calc = $this->povertyLine('2020');
        $discretionary = $this->calcDiscretionary($this->agi, $income_calc['starting'], $income_calc['additional']);

        $plan_set = array(
            'input' => array(
                'agi' => $this->agi,
                'family_size' => $this->family_size,
                'income_calc' => $income_calc,
                'discretionary' => $discretionary
            )
        );

        // Loop through plans and get payment details
        foreach($plans as $key => $plan){

            $plan_set[$key] = $this->{$plan['method']}();
            $plan_set[$key]['monthly_payment'] = $this->monthly_payment;
            $plan_set[$key]['last_payment'] = $this->last_payment;
            $plan_set[$key]['total_interest'] = 0; //$this->calcTotalInt();
            $plan_set[$key]['total_paid'] = 0; //$this->calcTotalPaid();
            $plan_set[$key]['term'] = $this->term;
            $plan_set[$key]['term_years'] = $this->term_years;
            $plan_set[$key]['schedules'] = $this->schedules;
            $plan_set[$key]['plan_id'] = $plan['plan_id'];
            $plan_set[$key]['plan_name'] = $plan['plan_name'];
            $plan_set[$key]['weight_avg'] = $this->weighted_average;

        }

        $plan_set['input']['principal'] = $this->principal;

        return $plan_set;

    }

    // Standard
    public function calcStandardPayment(){

        $this->repay_type = self::REPAY_TYPE_STD;
        try {
            $this->validateAndInitCalc();
        }catch(\Exception $e){

        }

        $this->getPaymentSchedule();
    }

    public function calcExStandardPayment(){

        $this->repay_type = self::REPAY_TYPE_STD_EXTENDED;
        try {
            $this->validateAndInitCalc();
        }catch(\Exception $e){

        }

        $this->getPaymentSchedule();
    }


    public function calcIBRPayment() {

        $this->repay_type = self::REPAY_TYPE_IBR;

        $this->validateAndInitCalc();

        if($this->non_eligible == 1) return -1;

        $this->getPaymentSchedule();
    }

    public function calcPAYEPayment() {

        $this->repay_type = self::REPAY_TYPE_PAYE;

        $this->validateAndInitCalc();

        if($this->non_eligible == 1) return -1;

        $this->getPaymentSchedule();

    }

    public function calcREPAYEPayment() {

        // Undergraduated Loans

        $this->repay_type = self::REPAY_TYPE_REPAYE;

        $this->validateAndInitCalc();

        if($this->non_eligible == 1) return -1;


        $this->getPaymentSchedule();

    }

    public function calcREPAYEGRADPayment() {

        // Graduation Loans

        $this->repay_type = self::REPAY_TYPE_REPAYE_GRAD;

        $this->validateAndInitCalc();

        if($this->non_eligible == 1) return -1;


        $this->getPaymentSchedule();

    }


    public function calcICRPayment() {

        $this->repay_type = self::REPAY_TYPE_ICR;
        $this->validateAndInitCalc();

        if($this->non_eligible == 1) return -1;

        $this->getPaymentSchedule();
    }



    private function getPaymentSchedule(){

       $year = 1;
       $agi = $this->agi;
       // Start at Month 3 for Forgiveness payments
       $month = 1;
       //$poverty_year = date("Y");
       $poverty_year = '2020';
       $income_calc = $this->povertyLine($poverty_year);
       $discretionary = $this->calcDiscretionary($agi, $income_calc['starting'], $income_calc['additional']);
       $monthly_payment =  $this->calcMonthlyPayment($discretionary);

       // Always go with $0 payment if $5 or less
       if($monthly_payment < 5){
           $this->monthly_payment = 0;
       }else{
           $this->monthly_payment = $monthly_payment;
       }

        $years[1]['months'] = 1;

        $this->balance = $this->principal;

        $i = 0;
        while($i<$this->term){

            $i++;

            $years[$year]['months'] = $month;

           if($i != 1) {
               $years[$year]['months'] += 1;
           }

           if($month == 1 && $i != 1){

               $year++;

               if ($this->repay_type != self::REPAY_TYPE_STD && $this->repay_type != self::REPAY_TYPE_GRADUATED && $this->repay_type != self::REPAY_TYPE_STD_EXTENDED) {

                   $agi = round($agi * ((1) + ($this->annual_growth_rate * .01))); // increases 1% every year
                   // Count for Inflation
                   $income_calc = $this->povertyLine($poverty_year);
                   $discretionary = $this->calcDiscretionary($agi, $income_calc['starting'], $income_calc['additional']);
                   $monthly_payment = $this->calcMonthlyPayment($discretionary);


               }

           }

            if($monthly_payment < 5){
                $monthly_payment = 0;
            }

            if($i == 1) {
                $this->balance = $this->principal;
            }

            $weight_cal = $this->weighted_average / 100;

            $interest_payment = sprintf("%.2f", ($this->balance * ( $weight_cal / 12)));
            $principal_payment = $monthly_payment - $interest_payment;

            $schedules[$i] = array(

                'income_calc' => $income_calc,
                'balance' => $this->balance,
                'balance_ending' => $this->balance - $principal_payment,
                'interest_payment' => $interest_payment,
                'principal_payment' =>$principal_payment,
                'monthly_payment' => $monthly_payment,
                'poverty_year' => $poverty_year,
                'discretionary' => $discretionary,
                'income' => $agi,
                'payment' => $monthly_payment,
                'year' => $year,
                'month' => $month,
                'payment_count' => $i

            );

            // Update Balance
            $this->balance = $this->balance - $principal_payment;

           if($month == 12) {
               $poverty_year++;
               $month = 1;
           }else{
               $month++;
           }




       }

       $last_schedule = end($schedules);
       $this->last_payment = $last_schedule['payment'];
       $this->schedules = $schedules;


      //  print_r($schedules);

    }

    public function calcDiscretionary($agi, $starting, $additional){

        if($this->repay_type == self::REPAY_TYPE_IBR || $this->repay_type == self::REPAY_TYPE_PAYE || $this->repay_type == self::REPAY_TYPE_REPAYE || $this->repay_type == self::REPAY_TYPE_REPAYE_GRAD) {
            // Discretionary 150%
            return round($agi - (($additional * $this->family_size) + $starting) * 1.5);

            // 55000 - ((4320 * 2) + 7820) * 1.5 = 7820

        }elseif($this->repay_type == self::REPAY_TYPE_ICR){
            // Discretionary normal
            return round($agi - (($additional * $this->family_size) + $starting));
        }

    }

    public function calcMonthlyPayment($discretionary){

        if($this->repay_type == self::REPAY_TYPE_IBR) {

            return sprintf("%.2f", $discretionary * 0.15 / 12);

        }elseif($this->repay_type == self::REPAY_TYPE_PAYE || $this->repay_type == self::REPAY_TYPE_REPAYE || $this->repay_type == self::REPAY_TYPE_REPAYE_GRAD){

            return sprintf("%.2f", $discretionary * 0.1 / 12);

        }elseif($this->repay_type == self::REPAY_TYPE_ICR){


            // Calculate Both, take whichever is lower
            $icr_income_factor_payment = $this->getIncomeFactorPayment();
            $icr_discetionary_payment = round($discretionary * 0.2 / 12);

            if($icr_income_factor_payment < $icr_discetionary_payment){
                return $icr_income_factor_payment;
            }

            return $icr_discetionary_payment;

        }elseif($this->repay_type == self::REPAY_TYPE_STD || $this->repay_type == self::REPAY_TYPE_GRADUATED || $this->repay_type == self::REPAY_TYPE_STD_EXTENDED){

            $weighted_average = $this->weighted_average = $this->calcWeightedAverage();

            $repay_rate_over_time = $weighted_average / 100 /  12;

            $this->standard_payment =  sprintf("%.2f", $this->principal * ($repay_rate_over_time/(1 - pow(1 + $repay_rate_over_time, -$this->term))), 2);

            return $this->standard_payment;

        }

    }

    private function getIncomeFactorPayment(){

       $income = $this->agi;
       $status = $this->filing_status;

       if($status != 'single'){
           $status = 'married';
       }

       // Calculate 12 Year Standard
       $standard_payment = $this->calPMT($this->calcWeightedAverage(), 12, $this->principal);

       $result = DB::table('student_quotes_factor')->where('amount', '<', $income)->where('status','=',$status)->orderBy('amount','desc')->first()->toArray();

       //throw new \Exception(round($result['factor'] * $standard_payment));

       return round($result['factor'] * $standard_payment);

    }


    private function calPMT($apr, $term_years, $loan)
    {
        // This does the same things as PMT in excel, using same verb
        $term = $term_years * 12;
        $apr = $apr / 1200; // ?? 1200

        // COVID REMOVED $amount = $apr * -$loan * pow((1 + $apr), $term) / (1 - pow((1 + $apr), $term));
        // COVID ADDED

        $amount = $apr * -$loan * pow((1 + $apr), $term) / (1 - pow((1 + $apr), $term));

        return round($amount);
    }

    private function povertyLine($poverty_year) {

        $income_calc = array();

        // 2020
        $year_poverty_lines = array(

                'first_person' => 12760,
                'additional' => 4480,
                'AK_first_person' => 10634,
                'AK_additional' => 5600,
                'HI_first_person' => 9786,
                'HI_additional' => 5150
        );

        if($poverty_year > 2020){

            $difference_years = $poverty_year - 2020;
            $multiplier = $difference_years * 0.01;
        }

        if ($this->state != 'AK' && $this->state != 'HI') {
            $first_person = $year_poverty_lines['first_person'];
            $additional = $year_poverty_lines['additional'];
        } elseif ($this->state == 'AK') {
            $first_person = $year_poverty_lines['AK_first_person'];
            $additional = $year_poverty_lines['AK_additional'];
        } elseif ($this->state == 'HI') {
            $first_person = $year_poverty_lines['HI_first_person'];
            $additional = $year_poverty_lines['HI_additional'];
        }

        if($poverty_year == '2020'){
            $income_calc['first_person'] = $year_poverty_lines['first_person'];
            $income_calc['additional'] = $year_poverty_lines['additional'];

        }else {
            $income_calc['first_person'] = round($first_person * (1 + ($this->inflation_rate * $multiplier)));
            $income_calc['additional'] = round($additional * (1 + ($this->inflation_rate * $multiplier)));
        }

        $income_calc['starting'] = $income_calc['first_person'] - $income_calc['additional'];

        return $income_calc;

    }


    private function calcWeightedAverage($round=false) {

        $total_loan_amount = 0;
        $total_weight_factor = 0;
        foreach($this->loans as $loan){

            if($loan['rate_type'] == 'VARIABLE') {
                $loan['rate'] = 0.068;
            }

            if (isset($loan['principal_balance']) && $loan['principal_balance'] > 0) {
                $total_weight_factor += ($loan['principal_balance'] * $loan['rate']);
                $total_loan_amount += $loan['principal_balance'];
            }
        }

        $weight_percent = ($total_weight_factor / $total_loan_amount) * 100;
        if($round) {
            $weight_percent = round($weight_percent, 2, PHP_ROUND_HALF_UP);
        }

        $weight_percent = 6.8; // COVID UPDATE

        return $weight_percent;
    }

    public function calcTotalInt() {
        if($this->repay_type != self::REPAY_TYPE_GRADUATED  && $this->repay_type != self::REPAY_TYPE_GRADUATED_EXTENDED) {

            $this->total_interest = ($this->monthly_payment * $this->term) - $this->principal;
            if($this->total_interest < 0) $this->total_interest = 0;  // DEV - Several types might have negative interest
        }

        return round($this->total_interest, 2);
    }

    public function calcTotalPaid() {
        $this->total_paid = $this->paid_principal == 0 ? $this->total_interest + $this->principal : $this->total_interest + $this->paid_principal;

        return round($this->total_paid, 2);
    }

    public function calcLastPayment() {
        $this->total_paid = $this->paid_principal == 0 ? $this->total_interest + $this->principal : $this->total_interest + $this->paid_principal;

        return round($this->total_paid, 2);
    }


    protected function validateAndInitCalc() {

        if(empty($this->loans)){  // Ensure there are loans to process
            throw new \Exception('Need loans set to calculate');
        }

        $this->paid_principal = 0;
        $this->graduated_repayment_term = 24;
        $this->monthly_interest = 0;
        $this->monthly_payment = 0;

        if($this->repay_type != self::REPAY_TYPE_STD) {
            unset ($this->loans_ineligible);
            $this->loans_ineligible = array();
            $this->non_eligible = 0;
        }

        $this->total_interest = 0;
        $this->total_paid = 0;
        $this->principal = 0;

        // Check the types where there are restrictions, e.g. - IBR, ICR, PAYE
        if($this->repay_type == self::REPAY_TYPE_IBR) {
            $ctr = 0;
            $ineg = 0;
            foreach($this->loans as $loan) {
                if(!$this->validateIBREligible($loan)) {
                    $this->loans_ineligible[$ctr] = 1;
                    ++$ineg;
                }
                ++$ctr;
            }

            if($ineg == $ctr) $this->non_eligible = 1;
        } else if($this->repay_type == self::REPAY_TYPE_ICR) {
            $ctr = 0;
            $ineg = 0;
            foreach($this->loans as $loan) {
                if(!$this->validateICREligible($loan)) {
                    $this->loans_ineligible[$ctr] = 1;
                    ++$ineg;
                }
                ++$ctr;
            }

            if($ineg == $ctr) $this->non_eligible = 1;
        } else if($this->repay_type == self::REPAY_TYPE_ISR) {
            $ctr = 0;
            $ineg = 0;
            foreach($this->loans as $loan) {
                if(!$this->validateISREligible($loan)) {
                    $this->loans_ineligible[$ctr] = 1;
                    ++$ineg;
                }
                ++$ctr;
            }

            if($ineg == $ctr) $this->non_eligible = 1;
        } else if($this->repay_type == self::REPAY_TYPE_PAYE) {
            $ctr = 0;
            $ineg = 0;
            foreach($this->loans as $loan) {
                if(!$this->validatePAYEEligible($loan)) {
                    $this->loans_ineligible[$ctr] = 1;
                    ++$ineg;
                }
                ++$ctr;
            }

            if($ineg == $ctr) $this->non_eligible = 1;
        }

        $this->calcLoanBalance();
        $this->calcRepaymentPeriod();

    }



    private function calcRepaymentPeriod() {

        if($this->repay_type == self::REPAY_TYPE_PAYE)
            $this->term = 20 * 12;
            $this->term_years = 20;

        if($this->repay_type == self::REPAY_TYPE_REPAYE)
            $this->term = 20 * 12;
            $this->term_years = 20;

        if($this->repay_type == self::REPAY_TYPE_REPAYE_GRAD)
            $this->term = 25 * 12;
            $this->term_years = 25;

        if($this->repay_type == self::REPAY_TYPE_IBR)
            $this->term = 25 * 12;
            $this->term_years = 25;


         // If consolidation, uses principal amount for TERM
        if($this->repay_type == self::REPAY_TYPE_GRADUATED_EXTENDED ||
            $this->repay_type == self::REPAY_TYPE_STD_EXTENDED ||
            $this->repay_type == self::REPAY_TYPE_GRADUATED) {

            if ($this->principal < 7500) {
                $this->term = 10 * 12;
                $this->term_years = 10;
            } else if ($this->principal < 10000) {
                $this->term = 12 * 12;
                $this->term_years = 12;
            } else if ($this->principal < 20000) {
                $this->term = 15 * 12;
                $this->term_years = 15;
            } else if ($this->principal < 40000) {
                $this->term = 20 * 12;
                $this->term_years = 20;
            } else if ($this->principal < 60000) {
                $this->term = 25 * 12;
                $this->term_years = 25;
            } else {
                $this->term = 30 * 12;
                $this->term_years = 30;
            }



        }

        // 10 yr STANDARD or defaults to 10 if Standard Extended and loans less than 30,000
        if($this->repay_type == self::REPAY_TYPE_STD ||  $this->repay_type == self::REPAY_TYPE_STD_EXTENDED && $this->principal < 30000){
            $this->term = 10 * 12;
            $this->term_years = 10;
        }



    }

    // If have any loans before 2007, NO PAYE
    // If have any loans with PARENT PLUS(not dispersed or paid in full through consolidation), NO PAYE, NO REPAYE, NO IBR

    // if have FFEL consolidation only,

    private function validateIBREligible($loan) {
        if($loan['type'] != 5 // Direct PLUS Graduate/Professional (by Parent) [i]
            && $loan['type'] != 20 // Federal GRAD PLUS Loans (by Parent) [s]
            && $loan['type'] != 21 // Direct Subsidized Consolidation Loans (with PLUS loan by Parent) [e]
            && $loan['type'] != 22 // Unsubsidized Federal Consolidation Loans (with PLUS loan by Parent) [j]
            && $loan['type'] != 23 // Direct Unsubsidized Consolidation Loans (with PLUS loan by Parent) [k]
            && $loan['type'] != 24 // Subsidized Federal Consolidation Loans (with PLUS loan by Parent) [o]
        ) {
            return true;
        }

        return false;
    }

    private function validateICREligible($loan) {
        if($loan['type'] != 5 // Direct PLUS Graduate/Professional (by Parent)
            && $loan['type'] != 20 // Federal GRAD PLUS Loans (by Parent) [s]
            && $loan['type'] != 21 // Direct Subsidized Consolidation Loans (with PLUS loan by Parent) [e]
            && $loan['type'] != 22 // Unsubsidized Federal Consolidation Loans (with PLUS loan by Parent) [j]
            && $loan['type'] != 23 // Direct Unsubsidized Consolidation Loans (with PLUS loan by Parent) [k]
            && $loan['type'] != 24 // Subsidized Federal Consolidation Loans (with PLUS loan by Parent) [o]
        ) {
            return true;
        }

        return false;
    }

    private function validateISREligible($loan) {
        if($loan['type'] == 5) { // [F]
            return true;
        }

        return false;
    }

    private function validatePAYEEligible($loan) {
        if($loan['type'] != 5 // Direct PLUS Graduate/Professional (by Parent)
            && $loan['type'] != 20 // Federal GRAD PLUS Loans (by Parent)
            && $loan['type'] != 21 // Direct Subsidized Consolidation Loans (with PLUS loan by Parent)
            && $loan['type'] != 22 // Unsubsidized Federal Consolidation Loans (with PLUS loan by Parent)
            && $loan['type'] != 23 // Direct Unsubsidized Consolidation Loans (with PLUS loan by Parent)
            && $loan['type'] != 24
        ) { // Subsidized Federal Consolidation Loans (with PLUS loan by Parent)
            return true;
        }

        return false;
    }
}
