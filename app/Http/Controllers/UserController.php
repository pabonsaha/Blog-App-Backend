<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //login method

    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
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
                'status'  => true,
                'message' => 'Logged In Successfully',
                'user'    => $user,
                'token'   => $user->createToken("blog")->plainTextToken
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // registration method

    public function registration(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'userName' => 'required|unique:users,user_name',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:8'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'user_name' => $request->userName,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Registered Successfully',
                'user' => $user,
                'token' => $user->createToken("blog")->plainTextToken
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function signOut(): \Illuminate\Http\JsonResponse
    {
        Auth::user()->tokens()->delete();


        return response()->json([
            'status'  => true,
            'message' => 'Logged Out Successfully',
            'user'    => null,
            'token'   => null,
        ], 200);
    }
}
