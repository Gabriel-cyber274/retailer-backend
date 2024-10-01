<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class UserController extends Controller
{
    public function Login(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'incorrect credentials',
                'success' => false
            ]);
        }
        // else if(is_null($user->email_verified_at)) {
        //     return response([
        //         'message' => 'email not verified',
        //         'success' => false
        //     ], 401);
        // }
        $token = $user->createToken('Personal Access Token', [])->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
            'message' => 'logged in',
            'success' => true
        ];

        return response($response, 201);
    }



    public function Register(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:8',
            'gender' => 'nullable',
            'address' => 'nullable',
            'acc_balance' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
        ]);



        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        $verificationCode = mt_rand(1000, 9999);
        $user = User::create([
            'name' => Str::title($request['name']),
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
            'admin' => false,
            'gender' => Str::title($request->gender),
            'address' => $request->address,
            'acc_balance' => 0,
            'city' => $request->city,
            'state' => $request->state,
            'verification_code' => $verificationCode
        ]);


        // Mail::send([], [], function ($message) use ($user, $verificationCode) {
        //     $message->to($user->email)
        //         ->subject('Your Verification Code (Subscurb)')
        //         ->html('<h1>Your Verification Code</h1><p>Your verification code is: ' . $verificationCode . '</p>');
        // });


        $response = [
            'user' => $user,
            'message' => 'successful signup',
            'success' => true
        ];

        return response($response);
    }



    public function resendVerificationCode(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        try {
            $verificationCode = mt_rand(1000, 9999);
            $user = User::findorfail($request->user_id);

            $user->update([
                'verification_code' => $verificationCode
            ]);


            // Mail::send([], [], function ($message) use ($user, $verificationCode) {
            //     $message->to($user->email)
            //         ->subject('Your Verification Code (Subscurb)')
            //         ->html('<h1>Your Verification Code</h1><p>Your verification code is: ' . $verificationCode . '</p>');
            // });


            $response = [
                'user' => $user,
                'message' => 'email resent',
                'success' => true
            ];

            return response($response);
        } catch (\Throwable $th) {
            $response = [
                'message' => $th->getMessage(),
                'success' => false
            ];

            return response($response);
        }
    }


    public function verifyUser(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'user_id' => 'required',
            'code' => 'required'

        ]);

        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        try {
            $user = User::findorfail($request->user_id);

            if ($user->verification_code == $request->code) {
                $user->email_verified_at = Carbon::now();
                $user->save();


                $token = $user->createToken('Personal Access Token', [])->plainTextToken;

                $response = [
                    'user' => $user,
                    'token' => $token,
                    'message' => 'email verified',
                    'success' => true,
                ];

                return response($response);
            } else {
                $response = [
                    'message' => 'pin incorrect',
                    'success' => false
                ];

                return response($response);
            }
        } catch (\Throwable $th) {
            $response = [
                'message' => $th->getMessage(),
                'success' => false
            ];

            return response($response);
        }
    }


    public function Logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return [
            'message' => 'logged out',
            'success' => true
        ];
    }


    public function checkEmail(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);


        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        $user = User::where('email', $request->email)->get()->first();

        if ($user) {
            try {
                $verificationCode = mt_rand(1000, 9999);

                $user->update([
                    'verification_code' => $verificationCode
                ]);

                // Mail::send([], [], function ($message) use ($user, $verificationCode) {
                //     $message->to($user->email)
                //         ->subject('Your Reset Code (Subscurb)')
                //         ->html('<h1>Your Reset Code</h1><p>Your Reset code is: ' . $verificationCode . '</p>');
                // });


                $response = [
                    'user' => $user,
                    'code' => $verificationCode,
                    'message' => 'user retrieved successfully',
                    'success' => true
                ];
            } catch (\Throwable $th) {
                $response = [
                    'message' => $th->getMessage(),
                    'success' => false
                ];

                return response($response);
            }

            return response($response);
        } else {
            $response = [
                'message' => "user doesn't exist",
                'success' => false
            ];

            return response($response);
        }
    }


    public function resendResetCode(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        try {
            $verificationCode = mt_rand(1000, 9999);
            $user = User::findorfail($request->user_id);

            $user->update([
                'verification_code' => $verificationCode
            ]);


            // Mail::send([], [], function ($message) use ($user, $verificationCode) {
            //     $message->to($user->email)
            //         ->subject('Your Reset Code (Subscurb)')
            //         ->html('<h1>Your Reset Code</h1><p>Your Reset code is: ' . $verificationCode . '</p>');
            // });


            $response = [
                'user' => $user,
                'message' => 'email resent',
                'success' => true
            ];

            return response($response);
        } catch (\Throwable $th) {
            $response = [
                'message' => $th->getMessage(),
                'success' => false
            ];

            return response($response);
        }
    }


    public function changePassword(Request $request)
    {
        // Validate input fields
        $fields = Validator::make($request->all(), [
            'current' => 'required|string|min:8',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|min:8|same:password',
        ]);

        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response, 422);
        }

        $id = auth()->id();

        try {
            // Find the authenticated user
            $user = User::findOrFail($id);

            // Check if the current password matches
            if (!Hash::check($request->current, $user->password)) {
                return response([
                    'message' => 'Current password is incorrect',
                    'success' => false
                ], 400);
            }

            // Update the user's password
            $user->password = Hash::make($request->password);
            $user->save();

            // Return success response
            return response([
                'user' => $user,
                'message' => 'Password changed successfully',
                'success' => true
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => 'An error occurred while changing the password',
                'success' => false
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $mainUser = User::find($user->id);

        $mainUser->update($request->all());

        if ($mainUser) {
            $response = [
                'user' => $mainUser,
                'message' => 'User info updated successfully',
                'success' => true
            ];
            return response($response);
        } else {
            $response = [
                'message' => "User doesn't exist",
                'success' => false
            ];
            return response($response, 404);
        }
    }



    public function userInfo()
    {
        $id = auth()->id();

        try {
            $user = User::findorfail($id);

            $response = [
                'user' => $user,
                'message' => 'user details retrieved',
                'success' => true
            ];

            return response($response);
        } catch (\Throwable $th) {
            $response = [
                'message' => $th->getMessage(),
                'success' => false
            ];

            return response($response);
        }
    }
}
