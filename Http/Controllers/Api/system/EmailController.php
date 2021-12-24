<?php


namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use App\Repositories\CompanyRepository;
use App\Repositories\TemplateEmailRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Contact\Repositories\FormFieldRepository;
use Modules\Document\Repositories\DocumentRepository;
use Modules\EmailManager\Http\Requests\EmailRequest;
use Modules\EmailManager\Repositories\EmailRepository;

class EmailController extends BaseController
{
    protected $email;
    /**
     * @var TemplateEmailRepository
     */
    protected $templateEmailRepo;
    /**
     * @var CompanyRepository
     */
    protected $companyRepo;
    /**
     * @var FormFieldRepository
     */
    protected $formFieldRepo;
    /**
     * @var DocumentRepository
     */
    protected $documentRepo;

    public function __construct(EmailRepository $email, TemplateEmailRepository $templateEmailRepo, DocumentRepository $documentRepo, CompanyRepository $companyRepo, FormFieldRepository $formFieldRepo)
    {
        parent::__construct();
         
        $this->email = $email;
        $this->templateEmailRepo = $templateEmailRepo;
        $this->companyRepo = $companyRepo;
        $this->formFieldRepo = $formFieldRepo;
        $this->documentRepo = $documentRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->templateEmailRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->templateEmailRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(EmailRequest $request)
    {
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->templateEmailRepo->store($payload);

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
        $email = $this->templateEmailRepo->getById($id);

        return $this->response->item($email);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(EmailRequest $request, $id)
    {
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->templateEmailRepo->update($id, $payload);

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
        $this->templateEmailRepo->destroy($id);

        return $this->response->withNoContent();
    }

    public function duplicate(Request $request)
    {
        $payload = $this->templateEmailRepo->getById($request->id)->toArray();
        $payload['company_id'] = Auth::user()->company_id;
        $this->templateEmailRepo->store($payload);

        return $this->response->withNoContent();
    }

    public function duplicateEdit(Request $request)
    {
        $payload = $this->templateEmailRepo->getById($request->email_id)->toArray();
        $payload['company_id'] = Auth::user()->company_id;
        $email_dupe_id = $this->templateEmailRepo->store($payload);

        return $this->response->withNoContent();
    }


}
