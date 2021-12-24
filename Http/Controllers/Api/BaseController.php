<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use App\Support\Response;
use App\Support\Transform;
use League\Fractal\Manager;

class BaseController extends Controller
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    protected $response;

    /**
     * ApiController constructor.
     */
    public function __construct()
    {
        $manager = new Manager();

        $this->response = new Response(response(), new Transform($manager));
    }
}
