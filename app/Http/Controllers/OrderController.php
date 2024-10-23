<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function allPending()
    {
        $orders = Order::with(['user', 'deposit.resell', 'product'])->where('status', 'pending')->orderBy('id', 'desc')->get();

        return response([
            'orders' => $orders,
            'message' => 'order retrieved successfully',
            'success' => true,
        ]);
    }


    public function allCompleted()
    {
        $orders = Order::with(['user', 'deposit.resell', 'product'])->where('status', 'completed')->orderBy('id', 'desc')->get();

        return response([
            'orders' => $orders,
            'message' => 'order retrieved successfully',
            'success' => true,
        ]);
    }


    public function allCancelled()
    {
        $orders = Order::with(['user', 'deposit.resell', 'product'])->where('status', 'cancelled')->orderBy('id', 'desc')->get();

        return response([
            'orders' => $orders,
            'message' => 'order retrieved successfully',
            'success' => true,
        ]);
    }








    public function all()
    {
        $orders = Order::with(['user', 'deposit.resell', 'product'])->orderBy('id', 'desc')->get();

        return response([
            'orders' => $orders,
            'message' => 'order retrieved successfully',
            'success' => true,
        ]);
    }


    public function getMonthlyOrders()
    {
        $orders = Order::with(['user', 'deposit.resell', 'product'])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->orderBy('id', 'desc')
            ->get();

        return response([
            'orders' => $orders,
            'message' => 'Monthly orders retrieved successfully',
            'success' => true,
        ]);
    }

    public function getWeeklyOrders()
    {
        $orders = Order::with(['user', 'deposit.resell', 'product'])
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->orderBy('id', 'desc')
            ->get();

        return response([
            'orders' => $orders,
            'message' => 'Weekly orders retrieved successfully',
            'success' => true,
        ]);
    }

    public function getYearlyOrders()
    {
        $orders = Order::with(['user', 'deposit.resell', 'product'])
            ->whereYear('created_at', Carbon::now()->year)
            ->orderBy('id', 'desc')
            ->get();

        return response([
            'orders' => $orders,
            'message' => 'Yearly orders retrieved successfully',
            'success' => true,
        ]);
    }

    public function getDailyOrders()
    {
        $orders = Order::with(['user', 'deposit.resell', 'product'])
            ->whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->get();

        return response([
            'orders' => $orders,
            'message' => 'Daily orders retrieved successfully',
            'success' => true,
        ]);
    }


    public function ordersCreatedMonthly()
    {
        $currentYear = Carbon::now()->year;

        $monthlyOrders = [];

        for ($month = 1; $month <= 12; $month++) {
            $orderCount = Order::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->count();

            $monthlyOrders[] = [
                'month' => Carbon::createFromDate($currentYear, $month, 1)->format('F'), // Get full month name
                'order_count' => $orderCount
            ];
        }

        // Prepare the response
        $response = [
            'orders' => $monthlyOrders,
            'message' => 'Monthly order data retrieved successfully',
            'success' => true
        ];

        // Return the response as JSON
        return response()->json($response);
    }




















    public function index()
    {
        $userId = auth()->id();
        $orders = Order::with(['user', 'deposit.resell', 'product'])->where('user_id', $userId)->orderBy('id', 'desc')->get();

        return response([
            'orders' => $orders,
            'message' => 'order retrieved successfully',
            'success' => true,
        ]);
    }




    public function store(Request $request)
    {
        // Validate request fields
        $fields = Validator::make($request->all(), [
            'quantity' => 'required',
            'amount' => 'required',
            'deposit_id' => 'nullable|exists:deposits,id',
            'address' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'type' => 'required'
        ]);

        if ($fields->fails()) {
            return response([
                'errors' => $fields->errors(),
                'success' => false
            ], 400); // Return 400 Bad Request for validation errors
        }

        $userId = auth()->id();
        $user = User::find($userId);

        // $request->type is either 'account-balance-payment' or 'paystack-payment' or 'deposit-payment'

        // Start database transaction for safety
        DB::beginTransaction();

        try {
            $order = null;

            // Process based on payment type
            if ($request->type === 'account-balance-payment' || $request->type === 'deposit-payment') {
                $deposit_amount = Deposit::where('user_id', $userId)->where('status', 'pending')->sum('amount');
                $acc_bal = $user->acc_balance;

                $order_balance = ($acc_bal == $deposit_amount) ? $deposit_amount :  $acc_bal - $deposit_amount;

                if ($order_balance < $request->amount) {
                    DB::rollBack();

                    if ($request->type === 'account-balance-payment' && $deposit_amount == 0) {
                        $message = 'Insufficient account balance.';
                    } else if (($request->type === 'deposit-payment' || $request->type === 'account-balance-payment') && $deposit_amount > 0) {
                        $message = 'You have some pending sales you need to order';
                    }
                    return response([
                        'order_balance' => $order_balance,
                        'message' => $message,
                        'success' => false
                    ], 400);
                }
            }

            $order = Order::create([
                'user_id' => $userId,
                'amount' => $request->amount,
                'quantity' => $request->quantity,
                'deposit_id' => $request->deposit_id,
                'product_id' => $request->product_id,
                'address' => $request->address,
                'status' => 'pending',
                'type' => $request->type
            ]);

            if ($request->type === 'account-balance-payment' || $request->type === 'deposit-payment') {
                $user->decrement('acc_balance', $request->amount);
            }

            // Commit the transaction
            DB::commit();

            return response([
                'order' => $order,
                'message' => 'Order created successfully',
                'success' => true,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // Return error response
            return response([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $order = Order::with(['user', 'deposit.resell', 'product'])->findOrFail($id);

            return response([
                'order' => $order,
                'message' => 'order retrieved successfully',
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
        DB::beginTransaction();

        try {
            // Find the order with relations or fail
            $order = Order::with(['user', 'deposit.resell', 'product'])->findOrFail($id);

            // Update the order with the validated data
            $order->update($request->all());

            $user = User::find($order->user_id);

            // Handle order status change
            if (!is_null($request->status)) {
                if ($request->status == 'cancelled') {
                    $user->increment('acc_balance', $order->amount);
                } elseif ($request->status == 'completed' && !is_null($order->deposit_id)) {
                    Deposit::where('id', $order->deposit_id)->update(['status' => 'completed']);
                }
            }

            // Commit the transaction if everything is successful
            DB::commit();

            return response([
                'order' => $order,
                'message' => 'Order updated successfully',
                'success' => true,
            ], 200); // OK
        } catch (\Throwable $th) {
            DB::rollBack();

            return response([
                'message' => 'Failed to update order: ' . $th->getMessage(),
                'success' => false,
            ], 500); // Server error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $order = Order::with(['user', 'deposit.resell', 'product'])->findOrFail($id);

            $order->delete();

            return response([
                'message' => 'order deleted successfully',
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
