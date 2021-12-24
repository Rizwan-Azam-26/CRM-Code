<?php

namespace App\Http\Controllers;

use App\Libraries\Account;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Report\Repositories\ReportRepository;

class BaseReportController extends Controller
{
    protected $report_type;
    protected $layout = 'reporting.results';
    protected $views;
    protected $model;
    protected $menu;
    protected $ReportRepository;

    public function __construct(ReportRepository $ReportRepository)
    {
        $this->ReportRepository = $ReportRepository;
    }

    protected function export($type, $data, Excel $excel)
    {
        $filename =  $type . '_report_' . count($data) . '_records_' . date("Y-m-d_H:i:s"). '.csv';
        $excel->store($data, $filename, 'public');
        return \redirect(asset('storage/'.$filename));
    }

    protected function render($type){

        $data['table_id'] = $this->report_type;
        $type = (isset($type)?$type:'default');
        if($_POST){

            $this->model->setDateField($_POST['filter']['date_field']);
            $this->model->setDateType($_POST['filter']);
            $this->model->setDateRange(true);
            $this->model->setQuery($type);

            foreach($_POST['filter'] as $k => $v){
                $this->model->setCustomFilter($k, $v);
            }

            /* Set Filters into Sessions */
            $_SESSION['filter_'.$this->report_type] = $_POST;
            $_SESSION['filter_'.$this->report_type]['type'] = $type;
            // $obj->setlimit(1000);

        }elseif(isset($_SESSION['filter_'.$this->report_type]) && $this->report_type == \Uri::segment(2)){

            $this->model->setDateField($_SESSION['filter_'.$this->report_type]['filter']['date_field']);
            $this->model->setDateType($_SESSION['filter_'.$this->report_type]['filter']);
            $this->model->setDateRange(true);
            $this->model->setQuery($type); // Default Query

            foreach($_SESSION['filter_'.$this->report_type]['filter'] as $k => $v){
                $this->model->setCustomFilter($k, $v);
            }

        }else{
            $this->model->setDateType(array('dates'=>'today'));
            $this->model->setDateRange(true);
            $this->model->setQuery($type); // Default Query
            //$this->model->setlimit(100);
        }

        // START EMPTY
        $data['dates'] =  $this->model->getDates();
        $data['results'] = $this->model->getQuery();
        /* EXPORT */
        if(Uri::segment(5) == 'export'){
            $this->export($data['table_id'], $data['results']);
            die();
        }

        $data['view_model'] =  $this->model->hasViewModel();
        $data['filter'] = View::forge('reporting/filters',
            array(
                'filters' => get_object_vars($this->model->filters->getFilters()),
                'action' => '/reporting/'.$this->report_type.'/listing/'.$type,
                'table' => $this->report_type,
                'reports' => $this->ReportRepository->findByUserId(Account::getUserId()),
            ), true);

        /* Rendering View */
        return \Response::forge(\View::forge('layout', array('l' => $this->layout, 'c' => $data)));
    }
}
