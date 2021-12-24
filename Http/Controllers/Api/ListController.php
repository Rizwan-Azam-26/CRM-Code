<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as Controller;
use Illuminate\Http\Request;

use App\Http\Requests\ListRequest;
use App\Repositories\ListRepository;
use Modules\Contact\Transformers\ListsTransformer;

class ListController extends Controller
{
    protected $list;

    public function __construct(ListRepository $list)
    {
        parent::__construct();

        $this->list = $list;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        return $this->response->collection($this->list->all(), new ListsTransformer);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->list->pageWithRequest($request), new ListsTransformer);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getByType($type)
    {

        return $this->response->collection($this->list->getByType($type), new ListsTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\ListsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(ListRequest $request)
    {
        $user = auth()->user();

        $data = array_merge($request->all(), [
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'company_id' => $user->company_id,
            'active'     => true
        ]);

        $list = $this->list->store($data);

        return $this->response->item($list, new ListsTransformer);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $list = $this->list->getById($id);

        return $this->response->item($list);
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
        $user = auth()->user();

        $data = array_merge($request->all(), [
            'updated_by' => $user->id
        ]);

        $list = $this->list->update($id, $data);

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
        $this->list->destroy($id);

        return $this->response->withNoContent();
    }
}
