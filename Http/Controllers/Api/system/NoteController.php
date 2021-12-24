<?php


namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\NoteRequest;
use App\Repositories\TemplateNoteRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Contact\Repositories\FormFieldRepository;

class NoteController extends BaseController
{
    protected $notes;
    /**
     * @var FormFieldRepository
     */
    protected $formFieldRepo;

    public function __construct(TemplateNoteRepository $notes, FormFieldRepository $formFieldRepo)
    {
        parent::__construct();

        $this->notes = $notes;
        $this->formFieldRepo = $formFieldRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->notes->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->notes->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(NoteRequest $request)
    {
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->notes->store($payload);

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
        $notes = $this->notes->getById($id);

        return $this->response->item($notes);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->notes->update($id, $payload);

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
        $this->notes->destroy($id);

        return $this->response->withNoContent();
    }
}
