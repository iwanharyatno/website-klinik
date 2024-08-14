<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\CommonResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'string|required',
            'password' => 'string|required',
        ]);

        if ($validator->fails()) {
            return CommonResponse::unprocessableEntity($validator->errors()->all());
        }

        if (Auth::attempt($validator->validated())) {
            $username = $validator->validated()['username'];
            $user = User::where('username', $username)->firstOrFail();
            $token = $user->createToken($username[0]);
            
            return CommonResponse::ok([
                'user' => $user,
                'token' => $token->plainTextToken
            ]);
        }
    }
}
