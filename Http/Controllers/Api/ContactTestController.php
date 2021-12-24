<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use App\Models\User;

final class ContactTestController extends BaseController
{
    public function __invoke(Request $request)
    {

        return $this->sendResponse('test', [
            'user_id' => $request->user()->id,
            'company_id' => $request->user()->company_id
        ]);
    }
}
