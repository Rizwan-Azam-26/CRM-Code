<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\WebhookRequest;
use App\Repositories\TemplateWebhookRepository;
use Exception;

class WebhookTemplateController extends BaseController
{
    protected $webhookTemplate;

    public function __construct( TemplateWebhookRepository $webhookTemplate )
    {
        parent::__construct();

        $this->webhookTemplate = $webhookTemplate;
    }

    /**
     * Get all of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->webhookTemplate->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->webhookTemplate->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WebhookRequest $request)
    {
        if ($request->get('inbound_data')) {
            $inbound_data = array_merge(
                $request->get('inbound_data'),
                [ 'hash' => sha1(md5(microtime()).microtime()) ]
            );
        };

        $data = array_merge(
            $request->only(['name', 'type', 'active']), 
            [
                'company_id' => Auth::user()->company_id,
                'inbound_data' => (($request->get('inbound_data')) ? json_encode($inbound_data) : ''),
                'outbound_data' => ($request->has('outbound_data')) ? json_encode($request->get('outbound_data')) : '',
            ]
        );
        $this->webhookTemplate->store($data);
  
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
        $webhook = $this->webhookTemplate->getById($id);
        return $this->response->item($webhook);
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
        if ($request->get('inbound_data')) {
            $inbound_data = array_merge(
                $request->get('inbound_data'),
                [ 'hash' => sha1(md5(microtime()).microtime()) ]
            );
        };

        $data = array_merge(
            $request->only(['name', 'type', 'active']), 
            [
                'company_id' => Auth::user()->company_id,
                'inbound_data' => (($request->get('inbound_data')) ? json_encode($inbound_data) : ''),
                'outbound_data' => ($request->has('outbound_data')) ? json_encode($request->get('outbound_data')) : '',
            ]
        );
        $this->webhookTemplate->update($id, $data);
  
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
        $this->webhookTemplate->destroy($id);
        return $this->response->withNoContent();
    }
}
