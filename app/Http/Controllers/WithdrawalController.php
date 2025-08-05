<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::where('user_id', Auth::id())->latest()->get();
        $user = User::find(Auth::id());


        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'Withdrawal retrieved successfully.',
            'data' => $withdrawals,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|string',
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $user = User::find(Auth::id());

            // ✅ Check if user has sufficient balance
            if ($user->acc_balance < $validated['amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance for this withdrawal.',
                ], 422);
            }

            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'type' => $validated['type'],
                'status' => 'pending',
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'],
            ]);

            // ✅ Deduct from user balance
            $user->decrement('acc_balance', $validated['amount']);

            DB::commit();

            return response()->json([
                'success' => true,
                'user' => $user,
                'message' => 'Withdrawal request created.',
                'data' => $withdrawal,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create withdrawal.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Withdrawal $withdrawal)
    {
        if ($withdrawal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($withdrawal);
    }

    public function update(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'in:pending,approved,rejected',
        ]);

        $withdrawal->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal updated.',
            'data' => $withdrawal,
        ]);
    }

    public function destroy(Withdrawal $withdrawal)
    {
        if ($withdrawal->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $withdrawal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal deleted.',
        ]);
    }
}
