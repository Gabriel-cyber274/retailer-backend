<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        } else if (is_null($user->email_verified_at)) {
            $verificationCode = mt_rand(1000, 9999);
            $user->update([
                'verification_code' => $verificationCode,
                'verification_expires_at' => now()->addMinutes(10),
            ]);

            return response([
                'data' => $user,
                'message' => 'email not verified',
                'success' => false
            ]);
        }
        $token = $user->createToken('Personal Access Token', [])->plainTextToken;

        $response = [
            'data' => $user,
            'token' => $token,
            'message' => 'Login successful',
            'success' => true
        ];

        return response($response, 201);
    }

    public function Register(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'nullable',
            'address' => 'nullable',
            'acc_balance' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'admin' => 'nullable',
            'phone_number' => 'required|string|max:15',
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
            'admin' => is_null($request->admin) ? false : $request->admin,
            'gender' => Str::title($request->gender),
            'address' => $request->address,
            'acc_balance' => 0,
            'city' => $request->city,
            'state' => $request->state,
            'phone_number' => $request->phone_number,
            'verification_code' => $verificationCode,
            'verification_expires_at' => now()->addMinutes(10),
        ]);


        // Mail::send([], [], function ($message) use ($user, $verificationCode) {
        //     $message->to($user->email)
        //         ->subject('Your Verification Code (Subscurb)')
        //         ->html('<h1>Your Verification Code</h1><p>Your verification code is: ' . $verificationCode . '</p>');
        // });


        $response = [
            'data' => $user,
            'message' => 'Registration successful!',
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
                'verification_code' => $verificationCode,
                'verification_expires_at' => now()->addMinutes(10),
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

            if ($user->verification_expires_at && Carbon::now()->greaterThan($user->verification_expires_at)) {
                return response([
                    'message' => 'Verification code has expired',
                    'success' => false,
                ]);
            }


            // if ($user->verification_code == $request->code) {
            if (
                $user->verification_code == $request->code &&
                $user->verification_expires_at &&
                Carbon::now()->lessThanOrEqualTo($user->verification_expires_at)
            ) {
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
                    'verification_code' => $verificationCode,
                    'verification_expires_at' => now()->addMinutes(10),
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
                'verification_code' => $verificationCode,
                'verification_expires_at' => now()->addMinutes(10),
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



    public function resetPassword(Request $request)
    {
        // Validate the input fields
        $fields = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($fields->fails()) {
            return response([
                'errors' => $fields->errors(),
                'success' => false
            ], 422);
        }

        try {
            // Find the user
            $user = User::where('email', $request->email)->firstOrFail();

            // Check if verification code matches
            if ($user->verification_code !== $request->code) {
                return response([
                    'message' => 'Invalid verification code',
                    'success' => false
                ], 400);
            }

            $user->password = Hash::make($request->password);
            $user->verification_code = null;
            $user->verification_expires_at = null;
            $user->save();

            return response([
                'message' => 'Password reset successfully',
                'success' => true
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => 'An error occurred during password reset',
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

    public function activateShop(Request $request)
    {

        $fields = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        $id = auth()->id();

        try {
            $verificationCode = mt_rand(10000, 99999);
            $user = User::findorfail($id);

            $user->update([
                'shop_name' => $request->name,
                'shop_id' => $verificationCode . $id
            ]);

            $response = [
                'user' => $user,
                'message' => 'shop activated successfully',
                'success' => true
            ];

            return response($response);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 200);
        }
    }


    public function deactivateShop(Request $request)
    {
        $id = auth()->id();

        try {
            $user = User::findorfail($id);

            $user->update([
                'shop_name' => null,
                'shop_id' => null
            ]);

            $response = [
                'user' => $user,
                'message' => 'user deactivated successfully',
                'success' => true
            ];

            return response($response);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 200);
        }
    }


    public function topResellers()
    {
        $users = User::withSum('deposits', 'amount')
            ->withCount('deposits')
            ->with(['deposits' => function ($query) {
                $query->select('user_id', \DB::raw('MAX(amount) as highest_deposit'))
                    ->groupBy('user_id');
            }])
            ->orderBy('deposits_sum_amount', 'desc')
            ->take(5)
            ->get();

        $chartData = $users->map(function ($user) {
            return [
                'name' => $user->name,
                'total_amount' => $user->deposits_sum_amount,
                'highest_deposit' => $user->deposits->first()->highest_deposit ?? 0,
                'deposit_count' => $user->deposits_count,
                'user_details' => $user
            ];
        });

        $response = [
            'chartData' => $chartData,
            'message' => 'Chart data retrieved successfully',
            'success' => true
        ];

        return response()->json($response);
    }



    public function allTimeUsers()
    {
        $users = User::where('admin', false)->get();

        $response = [
            'users' => $users,
            'message' => 'users retrieved successfully',
            'success' => true
        ];

        return response($response);
    }

    public function usersThisYear()
    {
        $currentYear = Carbon::now()->year;

        $users = User::whereYear('created_at', $currentYear)->where('admin', false)->get();

        $response = [
            'users' => $users,
            'message' => 'Users from this year retrieved successfully',
            'success' => true
        ];

        return response()->json($response);
    }
    public function usersThisMonth()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $users = User::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->where('admin', false)
            ->get();

        $response = [
            'users' => $users,
            'message' => 'Users from this month retrieved successfully',
            'success' => true
        ];

        return response()->json($response);
    }

    public function usersThisWeek()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $users = User::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('admin', false)
            ->get();

        $response = [
            'users' => $users,
            'message' => 'Users from this week retrieved successfully',
            'success' => true
        ];

        return response()->json($response);
    }

    public function usersToday()
    {
        $today = Carbon::now()->toDateString();

        $users = User::whereDate('created_at', $today)
            ->where('admin', false)
            ->get();

        $response = [
            'users' => $users,
            'message' => 'Users from today retrieved successfully',
            'success' => true
        ];

        return response()->json($response);
    }

    public function usersOnboardedMonthly()
    {
        $currentYear = Carbon::now()->year;

        $monthlyOnboarding = [];

        // Loop through all months (from January to December)
        for ($month = 1; $month <= 12; $month++) {
            // Count users created in the current year and specific month
            $userCount = User::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->where('admin', false)
                ->count();

            // Add the result to the array with the month name
            $monthlyOnboarding[] = [
                'month' => Carbon::createFromDate($currentYear, $month, 1)->format('F'), // Get full month name
                'user_count' => $userCount
            ];
        }

        // Prepare the response
        $response = [
            'onboarding' => $monthlyOnboarding,
            'message' => 'Monthly user onboarding data retrieved successfully',
            'success' => true
        ];

        // Return the response as JSON
        return response()->json($response);
    }
}
