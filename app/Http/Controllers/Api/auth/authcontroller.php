<?php

namespace App\Http\Controllers\Api\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\Rules\Role;

class authcontroller extends Controller
{
    public function register(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [

                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'user_name' => 'required|string',
                    'email' => 'required|email|unique:users,email',
                    'mobile' => 'required|unique:users,mobile',
                    'password' => 'required|min:6',
                    'user_type' => 'required|string',
                ]
            );
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validation->errors()
                ], 401);
            }

            $user =   User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_name' => $request->user_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->user_type);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required|min:6',
                ]
            );
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validation->errors()
                ], 401);
            }
            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken,
                'ueer' => $request->user()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    // public function checkrole()
    // {
    //     Role::where('')
    // }

    public function logout()
    {
       
        try {
            $data=Auth::guard('api')->logout();
            if ($data) {
            return response()->json([
                    'status' => true,
                    'message' => "log out successfully"
                ], 200); }   else {
                    return response()->json([
                        'status' => false,
                        'message' => "log out failed"
                    ], 500);
                } 
        
            //  Auth::guard('api')->logout();
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    protected function guard()
{
    return Auth::guard('api');
    
}
}
