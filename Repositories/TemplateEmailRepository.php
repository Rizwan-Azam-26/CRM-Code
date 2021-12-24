<?php

namespace App\Repositories;

use App\Models\TemplateEmail;
use App\Services\MailService;
use Illuminate\Support\Facades\DB;
use Modules\EmailManager\Services\EmailService;

class TemplateEmailRepository
{
    use BaseRepository;

    /**
     * Accounting Model
     *
     * @var TemplateEmail
     */
    protected $model;

    /**
     * Constructor
     *
     * @param TemplateEmail $TemplateEmail
     */
    public function __construct(TemplateEmail $TemplateEmail)
    {
        $this->model = $TemplateEmail;
    }

    public function getByName($name)
    {
        return $this->model->where('name', 'like', '%'.$name.'%')->first();
    }

    /**
     * Get number of the records
     *
     * @param Request $request
     * @param int $number
     * @param string $sort
     * @param string $sortColumn
     * @return Paginate
     */
    public function pageWithRequest($request, $number = 10, $sort = 'asc', $sortColumn = 'name')
    {
        $keyword = $request->get('keyword');

        if ($request->has('limit')) {
            $number = $request->get('limit');
        }
        return $this->model->when($keyword, function ($query) use ($keyword) {
            $query->where('name', 'like', "%{$keyword}%")
                ->orWhere('from', 'like', "%{$keyword}%")
                ->orWhere('subject', 'like', "%{$keyword}%");
        })
            ->orderBy($sortColumn, $sort)
            ->paginate($number);
    }

    public function exists($id)
    {
        $result = $this->getById($id);
        if ($result) {
            return true;
        }
        return false;
    }

    public function getName($id)
    {
        return optional($this->getById($id))->name;
    }

    public function getFrom($id)
    {
        return optional($this->getById($id))->form;
    }

    public function existsByName($name)
    {
        $template = $this->getByName($name);
        if ($template) {
            return true;
        }
        return false;
    }

    public function getSubject($id)
    {
        return optional($this->getById($id))->subject;
    }

    /* Get Template CC */
    public function getCc($id)
    {
        return optional($this->getById($id))->cc;
    }

    /* Get Template Bcc */
    public function getBcc($id)
    {
        return optional($this->getById($id))->bcc;
    }

    /* Get Template To */
    public function getTo($id)
    {
        return optional($this->getById($id))->to;
    }

    /* Get Template Message */
    public function getMessage($id)
    {
        return optional($this->getById($id))->message;
    }

    public function findAll(){
        return $this->model->all();
    }

    public function findAllCompanyAndIndustry($company_id){
        return $this->model
            ->where('company_id','in', array(1, $company_id))
            ->orderBy('name','ASC')
            ->get();
    }

    public function findAllByCompany($company_id){
        return $this->model
            ->where('company_id', $company_id)
            ->orderBy('name','ASC')
            ->get();
    }

    public function findStatusTemplates($company_id,$status_id){
        return $this->model
            ->select('template_emails.*')
            ->join('statuses_emails', 'statuses_emails.email_template_id','=','template_emails.id')
            ->where('statuses_emails.company_id', $company_id)
            ->where('statuses_emails.status_id', $status_id)
            ->orderBy('template_emails.name','ASC')
            ->get();
    }

    public function add($data){
        return $this->store($data);
    }

    public function delete($id){
        $this->destroy($id);
    }

    public function sendEmail($to, $from, $subject, $message, $company_id){
        $headers['name'] = 'System Administrator';
        $headers['from'] = $from;
        $emailService = new MailService();
        $emailService->send($to, $subject, $message, $headers, $company_id);
    }
    public function findTemplatesByStatus($status_id)
    {
        return $this->model
            ->select('template_emails.*')
            ->join('statuses_emails as se', 'se.email_template_id', '=', 'template_emails.id')
            ->leftJoin('statuses as s', 's.id', '=', 'se.status_id')
            ->where('se.status_id', '=', $status_id);
    }

}
