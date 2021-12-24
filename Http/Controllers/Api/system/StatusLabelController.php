<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController as Controller;
use Illuminate\Http\Request;

use App\Http\Requests\StatusLabelRequest;
use App\Repositories\StatusLabelRepository;

use Auth;

class StatusLabelController extends Controller
{
    protected $statusLabel;

    public function __construct(StatusLabelRepository $statusLabel)
    {
        parent::__construct();

        $this->statusLabel = $statusLabel;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->statusLabel->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        try{
            return $this->response->collection($this->statusLabel->pageWithRequest($request));
        }catch(\Exception $e){
                   $this->response->setStatusCode('200');
                 return $this->response->json([]);
        }
      
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = array_merge($request->all(), [
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id
        ]);

        $this->statusLabel->store($data);

        return $this->response->withNoContent();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      
        $statusLabel = $this->statusLabel->getById($id);

        return $this->response->item($statusLabel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = array_merge($request->all(), [
            'updated_by' => $request->user()->id
        ]);
        $this->statusLabel->update($id, $data);

        return $this->response->withNoContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->statusLabel->destroy($id);

        return $this->response->withNoContent();
    }
}
