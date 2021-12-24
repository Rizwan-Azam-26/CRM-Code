<?php

namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;

use App\Http\Requests\AnnouncementRequest;
use App\Repositories\AnnouncementRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends BaseController
{
    protected $announcementRepo;

    public function __construct(AnnouncementRepository $announcementRepo)
    {
        parent::__construct();
        $this->announcementRepo= $announcementRepo;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $this->response->collection($this->announcementRepo->all());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->announcementRepo->pageWithRequest($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AnnouncementRequest $request)
    {
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->announcementRepo->store($payload);

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
        $announcementRepo = $this->announcementRepo->getById($id);

        return $this->response->item($announcementRepo);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AnnouncementRequest $request, $id)
    {
        $payload = $request->all();
        $payload['company_id'] = Auth::user()->company_id;
        $this->announcementRepo->update($id, $payload);

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
        $this->announcementRepo->destroy($id);

        return $this->response->withNoContent();
    }
}
