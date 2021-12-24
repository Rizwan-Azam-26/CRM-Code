<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\CdrRequest;
use Illuminate\Http\Request;

use \App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use App\Models\User;

final class CdrController extends BaseController
{

    public function __invoke(Request $request)
    {

        $request->validate([
            'name' => 'required'
        ]);

        return response()->json(['cdr'], 200);

    }
}
