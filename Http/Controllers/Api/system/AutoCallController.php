<?php


namespace App\Http\Controllers\Api\system;

use App\Http\Requests\AutoCallRequest;
use App\Repositories\AnnouncementRepository;
use App\Repositories\TemplateAutocallRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Auth;
use Modules\Contact\Repositories\FormFieldRepository;

class AutoCallController extends BaseController
{
    /**
     * @var TemplateAutocallRepository
     */
    protected $autoCallRepo;
    /**
     * @var FormFieldRepository
     */
    protected $formFieldRepo;


    public function __construct(TemplateAutocallRepository $autoCallRepo, FormFieldRepository $formFieldRepo)
    {
        parent::__construct();

        $this->autoCallRepo = $autoCallRepo;
        $this->formFieldRepo = $formFieldRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->autoCallRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->autoCallRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(AutoCallRequest $request)
    {
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->autoCallRepo->store($payload);

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
        $autoCallRepo = $this->autoCallRepo->getById($id);

        return $this->response->item($autoCallRepo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(AutoCallRequest $request, $id)
    {
        $data = array_merge(
            $request->all(),
            ['company_id' => Auth::user()->company_id]
        );
        $this->autoCallRepo->update($id, $data);

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
        $this->autoCallRepo->destroy($id);

        return $this->response->withNoContent();

    }
}
