<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController as ApiController;
use App\Repositories\UserRepository;
use App\Http\Resources\User\User as UserResource;
use Validator;
use App\Models\User;
use Session;

class AuthController extends ApiController {

    /**
     * 
     * Select or create user and send token
     *
     */

    function validation(Request $request)
    {
        $input = $request->all();
        $phoneNum = $input['phone_num'];
        $user = User::firstOrCreate(['phone_number' => $phoneNum]);
        if($user)
        {
            Session::put('phoneNum', $phoneNum);
            $user->sendToken();
            $webToken = Session::get('token');
            return $this->apiResponse200($webToken);
        } else
        {
            return $this->apiResponse404('User not found');
        }

    }

    /**
     * 
     * Login user.
     *
     */

    function login(Request $request)
    {
        $input = $request->all();
        $token = $input['token'];
        $phoneNum = Session::get('phoneNum');
        $user = User::where('phone_number', '=', $phoneNum)->firstOrFail();
        if($user && $user->validateToken($token)) {
            $id = $user['id'];
            $directory = "/user_files_" . $id;
            Storage::disk('public')->makeDirectory($directory);
            $success['token'] =  $user->createToken('BobApp')->accessToken;
            $success['user'] =  new UserResource($user);
            return $this->apiResponse200($success);
        } else {
            return $this->apiResponse404('Wrong token');
        }
    }

}