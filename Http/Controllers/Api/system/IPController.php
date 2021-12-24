<?php
namespace App\Http\Controllers\Api\system;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\IPRequest;
use App\Repositories\IPRepository;
use Exception;
use Illuminate\Support\Facades\Auth;


class IPController extends BaseController
{
    protected $ip;

    public function __construct(IPRepository $ip)
    {
        parent::__construct();

        $this->ip = $ip;
    }

    /**
     * Get all of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        return $this->response->collection($this->ip->all());
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(IPRequest $request)
    {
        $data = array_merge(
            $request->all(), 
            [
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::user()->id
            ]
        );
        $this->ip->store($data);
  
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
        $ip = $this->ip->getById($id);

        return $this->response->item($ip);
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
        $data = array_merge(
            $request->all(), 
            [
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::user()->id
            ]
        );
        $this->ip->update($id, $data);
  
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
        $this->ip->destroy($id);
        return $this->response->withNoContent();
    }
}
