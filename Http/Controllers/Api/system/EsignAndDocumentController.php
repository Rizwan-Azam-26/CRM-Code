<?php


namespace App\Http\Controllers\Api\system;


use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

class EsignAndDocumentController extends BaseController
{
    protected $esignAndDocument;

    public function __construct(EsignAndDocumentRepository $esignAndDocument)
    {
        parent::__construct();

        $this->esignAndDocument = $esignAndDocument;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->esignAndDocument->all($request));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->esignAndDocument->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(EsignAndDocumentRequest $request)
    {
        $data = array_merge($request->all(), [
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id
        ]);

        $esignAndDocument = $this->esignAndDocument->store($data);

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
        $esignAndDocument = $this->esignAndDocument->getById($id);

        return $this->response->item($esignAndDocument);
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
        $data = array_merge($request->all(), [
            'updated_by' => $request->user()->id
        ]);
        $this->esignAndDocument->update($id, $data);

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
        $this->esignAndDocument->destroy($id);

        return $this->response->withNoContent();
    }
}