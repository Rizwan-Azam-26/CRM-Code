<?php


namespace App\Http\Controllers\Api\system;

use App\Repositories\TemplateSmsRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Exception;
use Illuminate\Support\Facades\Auth;
use Modules\CallCenter\Http\Requests\SmsRequest;
use Modules\CallCenter\Repositories\CtNumberRepository;
use Modules\CallCenter\Repositories\SmsRepository;
use Modules\CallCenter\Transformers\SmsTransformer;
use Modules\Contact\Repositories\FormFieldRepository;

class SmsController extends BaseController
{
    protected $sms;
    /**
     * @var TemplateSmsRepository
     */
    protected $templateSmsRepo;
    /**
     * @var FormFieldRepository
     */
    protected $formFiledRepo;
    /**
     * @var CtNumberRepository
     */
    protected $ctNumberRepo;

    public function __construct(SmsRepository $sms, TemplateSmsRepository $templateSmsRepo, FormFieldRepository $formFieldRepository, CtNumberRepository $ctNumberRepo)
    {
        parent::__construct();

        $this->sms = $sms;
        $this->templateSmsRepo = $templateSmsRepo;
        $this->formFiledRepo = $formFieldRepository;
        $this->ctNumberRepo = $ctNumberRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {    
        return $this->response->collection($this->templateSmsRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->templateSmsRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(SmsRequest $request)
    {
        $data = array_merge(
            $request->all(),
            [
                'company_id' => Auth::user()->company_id,
                'created' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]
        );
        $this->templateSmsRepo->store($data);

        return $this->response->withNoContent();
    }

    
    public function duplicateEdit(Request $request)
    {
          
        if(count($request->all()) > 0){

            $sms_id =$request->sms_id;
            $sms_template =    $this->templateSmsRepo->getById($sms_id);
            
            $payload = $sms_template->toArray();
            $payload['company_id'] = Auth::user()->company_id;
            $payload['id'] = null;
               
            $sms_dupe_id =     $this->templateSmsRepo->store($payload);

            $sms_dupe = $this->templateSmsRepo->getById($sms_dupe_id);

            return $this->response->item($sms_dupe);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $sms = $this->templateSmsRepo->getById($id);

        return $this->response->item($sms);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(SmsRequest $request, $id)
    {

        $data = array_merge(
            $request->all(),
            [
                'company_id' => Auth::user()->company_id,
                'created' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id
            ]
        );
        $this->templateSmsRepo->update($id, $data);

        return $this->response->withNoContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->templateSmsRepo->destroy($id);

        return $this->response->withNoContent();
    }
}
