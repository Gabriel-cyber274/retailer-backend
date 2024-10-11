<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function all()
    {
        $deposit = Deposit::with(['resell.product', 'customer', 'user'])->orderBy('id', 'desc')->get();
        return response([
            'deposits' => $deposit,
            'message' => 'deposits retrieved successfully',
            'success' => true,
        ]);
    }

    public function allThisYear()
    {
        $currentYear = Carbon::now()->year;

        $deposits = Deposit::with(['resell.product', 'customer', 'user'])
            ->whereYear('created_at', $currentYear) // Filter by the current year
            ->orderBy('id', 'desc')
            ->get();

        return response([
            'deposits' => $deposits,
            'message' => 'Deposits retrieved successfully for the current year',
            'success' => true,
        ]);
    }

    public function allThisMonth()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $deposits = Deposit::with(['resell.product', 'customer', 'user'])
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->orderBy('id', 'desc')
            ->get();

        return response([
            'deposits' => $deposits,
            'message' => 'Deposits retrieved successfully for the current month',
            'success' => true,
        ]);
    }

    public function allThisWeek()
    {
        // Retrieve deposits for the current week
        $deposits = Deposit::with(['resell.product', 'customer', 'user'])
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->orderBy('id', 'desc')
            ->get();

        return response([
            'deposits' => $deposits,
            'message' => 'Deposits retrieved successfully for the current week',
            'success' => true,
        ]);
    }

    public function allToday()
    {
        // Retrieve deposits for today
        $deposits = Deposit::with(['resell.product', 'customer', 'user'])
            ->whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->get();

        return response([
            'deposits' => $deposits,
            'message' => 'Deposits retrieved successfully for today',
            'success' => true,
        ]);
    }

    public function depositsCreatedMonthly()
    {
        $currentYear = Carbon::now()->year;

        $monthlyDeposits = [];

        // Loop through all months (from January to December)
        for ($month = 1; $month <= 12; $month++) {
            // Count deposits created in the current year and specific month
            $depositCount = Deposit::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->count();

            // Add the result to the array with the month name
            $monthlyDeposits[] = [
                'month' => Carbon::createFromDate($currentYear, $month, 1)->format('F'), // Get full month name
                'deposit_count' => $depositCount
            ];
        }

        // Prepare the response
        $response = [
            'deposits' => $monthlyDeposits,
            'message' => 'Monthly deposit data retrieved successfully',
            'success' => true
        ];

        // Return the response as JSON
        return response()->json($response);
    }





    public function getAllPending()
    {
        $deposit = Deposit::with(['resell.product', 'customer', 'user'])->where('status', 'pending')->orderBy('id', 'desc')->get();
        return response([
            'deposits' => $deposit,
            'message' => 'deposits retrieved successfully',
            'success' => true,
        ]);
    }


    public function getAllCompleted()
    {
        $deposit = Deposit::with(['resell.product', 'customer', 'user'])->where('status', 'completed')->orderBy('id', 'desc')->get();
        return response([
            'deposits' => $deposit,
            'message' => 'deposits retrieved successfully',
            'success' => true,
        ]);
    }

    public function getAllCancelled()
    {
        $deposit = Deposit::with(['resell.product', 'customer', 'user'])->where('status', 'cancelled')->orderBy('id', 'desc')->get();
        return response([
            'deposits' => $deposit,
            'message' => 'deposits retrieved successfully',
            'success' => true,
        ]);
    }





























    public function index()
    {
        $userId = auth()->id();

        $deposit = Deposit::with(['resell.product', 'customer', 'user'])->where('user_id', $userId)->orderBy('id', 'desc')->get();
        return response([
            'deposit' => $deposit,
            'message' => 'deposits retrieved successfully',
            'success' => true,
        ]);
    }


    public function store(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'retail_id' => 'required|exists:retail_products,id',
            'quantity' => 'required',
            'amount' => 'required',
            'customer_id' => 'required|exists:customers,id'
        ]);

        if ($fields->fails()) {
            return response([
                'errors' => $fields->errors(),
                'success' => false
            ]);
        }

        $userId = auth()->id();

        DB::beginTransaction();

        try {
            // Retrieve the authenticated user
            $user = User::find($userId);

            // Create a deposit record
            $deposit = Deposit::create([
                'user_id' => $userId,
                'retail_id' => $request->retail_id,
                'quantity' => $request->quantity,
                'amount' => $request->amount,
                'customer_id' => $request->customer_id
            ]);

            $user->increment('acc_balance', $request->amount);

            DB::commit();

            return response([
                'deposit' => $deposit,
                'message' => 'Deposit added successfully',
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response([
                'message' => 'Failed to create deposit',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        try {
            $deposit = Deposit::with(['resell.product', 'customer', 'user'])->findOrFail($id);

            return response([
                'deposit' => $deposit,
                'message' => 'deposit retrieved successfully',
                'success' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 200);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $deposit = Deposit::with(['resell.product', 'customer', 'user'])->findOrFail($id);

            $deposit->update($request->all());

            return response([
                'deposit' => $deposit,
                'message' => 'deposit updated successfully',
                'success' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 200);
        }
    }

    public function destroy($id)
    {
        try {
            $deposit = Deposit::with(['resell.product', 'customer', 'user'])->findOrFail($id);

            $deposit->delete();

            return response([
                'message' => 'deposit deleted successfully',
                'success' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 200);
        }
    }
}
