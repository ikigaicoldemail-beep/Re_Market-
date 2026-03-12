<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function register(Request $request)
    {

    $data = $request->all();

    $user = User::create([
    'name' => $data['name'],
    'email' => $data['email'],
    'password' => bcrypt($data['password']),
    'phone' => $data['phone']
    ]);

    return response()->json([
    'status'=>'success',
    'user'=>$user
    ]);

    }
    public function login(Request $request)
    {

        $credentials = $request->only('email','password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {

            return response()->json([
                "status"=>"error",
                "message"=>"Invalid login"
            ],401);
        }

        return response()->json([
            "status"=>"success",
            "token"=>$token,
            "user"=>auth()->user()
        ]);

    }

}