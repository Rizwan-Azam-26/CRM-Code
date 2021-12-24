<?php


namespace App\Http\Controllers\Api\system;


use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\CommunicationRequest;
use App\Repositories\TemplateCallRepository;
use App\Repositories\TemplateCommunicationRepository;
use App\Repositories\TemplateEmailRepository;
use App\Repositories\TemplateSmsRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CommunicationController extends BaseController
{
    /**
     * @var TemplateCommunicationRepository
     */
    protected $templateCommuncationRepo;
    /**
     * @var TemplateSmsRepository
     */
    protected $templateSmsRepo;
    /**
     * @var TemplateEmailRepository
     */
    protected $templateEmailRepo;
    /**
     * @var TemplateCallRepository
     */
    protected $templateCallRepo;

    public function __construct(TemplateEmailRepository $templateEmailRepo, TemplateCallRepository $templateCallRepo, TemplateSmsRepository $templateSmsRepo, TemplateCommunicationRepository $templateCommuncationRepo)
    {
        $this->templateCommuncationRepo = $templateCommuncationRepo;
        $this->templateSmsRepo = $templateSmsRepo;
        $this->templateEmailRepo = $templateEmailRepo;
        $this->templateCallRepo = $templateCallRepo;
    }

    /**
     * Get all of the resource.
     *
     * @return Response
     */
    public function all()
    {
        return $this->response->collection($this->templateCommuncationRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $data['communications'] = $this->templateCommuncationRepo->findAll();
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
        $communication = $this->templateCommuncationRepo->getById($id);

        return $this->response->item($communication);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(CommunicationRequest $request)
    {
        try {
            $data = $request->all();
            $data['company_id'] = Auth::user()->company_id;

            $this->templateCommuncationRepo->store($data);
//                \Notification\Notify::success($_POST['name'].' Added');

            $this->response->setStatusCode('200');
            return $this->response->json('Success');
        } catch (Exception $e) {
            $this->response->setStatusCode('403');
            return $this->response->json('Error');
        }
        $data['sms_messages'] = $this->templateSmsRepo->findAll();
        $data['emails'] = $this->templateEmailRepo->findAll();
        $data['calls'] = $this->templateCallRepo->findAll();
        return $this->response->json($data);

    }
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(CommunicationRequest $request, $id)
    {
        try {
            $this->templateCommuncationRepo->update($id, $request->all());
//                \Notification\Notify::success($_POST['name'].' Updated');
            $this->response->setStatusCode('200');
            return $this->response->json('Success');
        } catch (Exception $e) {
            $this->response->setStatusCode('403');
            return $this->response->json('Error');
        }
        $data['row'] = $this->templateCommuncationRepo->getById($id);
        $data['sms_messages'] = $this->templateSmsRepo->findAll();
        $data['emails'] = $this->templateEmailRepo->findAll();
        $data['calls'] = $this->templateCallRepo->findAll();
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
            $this->templateCommuncationRepo->destroy($id);
            $this->response->setStatusCode('200');
            return $this->response->json('Success');
        } catch (Exception $e) {
            $this->response->setStatusCode('403');
            return $this->response->json('Error');
        }
    }

}