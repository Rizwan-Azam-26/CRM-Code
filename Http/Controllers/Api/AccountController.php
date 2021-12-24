<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;

use App\Repositories\UserRepository;
use App\Transformers\ProfileTransformer;

class AccountController extends BaseController
{
    protected $user;

    public function __construct(UserRepository $user)
    {
        parent::__construct();

        $this->user = $user;
    }

    public function profile(Request $request)
    {
        return $this->response->item($request->user(), new ProfileTransformer);
    }
}
