<?php
namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SignatureRequest;
use App\Repositories\CompanyRepository;
use App\Repositories\TemplateSignatureRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SignatureController extends BaseController
{
    /**
     * @var TemplateSignatureRepository
     */
    protected $templateSignatureRepo;

    public function __construct( TemplateSignatureRepository $templateSignatureRepo )
    {
        parent::__construct();
        $this->templateSignatureRepo = $templateSignatureRepo;
    }

    /**
     * Get all of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        return $this->response->collection($this->templateSignatureRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->templateSignatureRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(SignatureRequest $request)
    {
        $data = array_merge(
            $request->all(), 
            ['company_id' => Auth::user()->company_id]
        );
        $this->templateSignatureRepo->store($data);
  
        return $this->response->withNoContent();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $signature = $this->templateSignatureRepo->getById($id);

        return $this->response->item($signature);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request, $id)
    {
        $data = array_merge($request->all(), [
            'company_id' => $request->user()->company_id,
        ]);
        $this->templateSignatureRepo->update($id, $data);

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
        $this->templateSignatureRepo->destroy($id);

        return $this->response->withNoContent();
    }

}