<?php

namespace App\Http\Controllers;

use App\Mail\OrderCreatedMail;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\retailProduct;
use App\Models\User;
use App\Models\UserCart;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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



    public function getMoneyMadeAllTime()
    {
        $totalAmount = Order::where('status', 'completed')->sum('amount');

        return response()->json([
            'amount' => $totalAmount,
            'message' => 'Order retrieved successfully',
            'success' => true,
        ]);
    }

    public function getMoneyMonthly()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $totalAmount = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        return response()->json([
            'amount' => $totalAmount,
            'message' => 'Monthly total retrieved successfully',
            'success' => true,
        ]);
    }

    public function getMoneyWeekly()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $totalAmount = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->sum('amount');

        return response()->json([
            'amount' => $totalAmount,
            'message' => 'Weekly total retrieved successfully',
            'success' => true,
        ]);
    }
    public function getMoneyDaily()
    {
        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();

        $totalAmount = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('amount');

        return response()->json([
            'amount' => $totalAmount,
            'message' => 'Daily total retrieved successfully',
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
        $orders = Order::with(['products'])->where('user_id', $userId)->orderBy('id', 'desc')->get();

        return response([
            'orders' => $orders,
            'message' => 'order retrieved successfully',
            'success' => true,
        ]);
    }

    public function directOrders()
    {
        $userId = auth()->id();
        $orders = Order::with(['products'])->where('user_id', $userId)->where('type', 'direct_purchase')->orderBy('id', 'desc')->get();

        $orders = $orders->map(function ($order) use ($userId) {
            $order->products = $order->products->map(function ($product) use ($userId) {
                $product->has_reviewed = $product->reviews()->where('user_id', $userId)->exists();
                return $product;
            });
            return $order;
        });

        return response([
            'orders' => $orders,
            'message' => 'order retrieved successfully',
            'success' => true,
        ]);
    }

    public function customerOrders()
    {
        $userId = auth()->id();

        $orders = Order::with(['resells.product', 'customer', 'products.reviews'])->where('user_id', $userId)
            ->where('type', 'customer_purchase')
            ->orderBy('id', 'desc')
            ->get();

        $orders = $orders->map(function ($order) use ($userId) {
            // Add has_reviewed to each product
            $order->products->map(function ($product) use ($userId) {
                $product->has_reviewed = $product->reviews->where('user_id', $userId)->isNotEmpty();
                return $product;
            });

            // Attach product data (with has_reviewed if needed) to each resell
            $order->resells->map(function ($resell) use ($userId) {
                $product = $resell->product;
                if ($product) {
                    $product->has_reviewed = $product->reviews()->where('user_id', $userId)->exists();
                    $resell->setRelation('product', $product);
                }
                return $resell;
            });

            return $order;
        });

        return response([
            'orders' => $orders,
            'message' => 'Orders retrieved successfully',
            'success' => true,
        ]);
    }



    public function store(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:1',
            'amount' => 'required|numeric|min:0',
            'retail_id' => 'nullable|array',
            'retail_id.*' => 'nullable|exists:retail_products,id',
            'customer_id' => 'nullable|exists:customers,id',
            'address' => 'required|string|max:255',
            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'type' => 'required|in:direct_purchase,customer_purchase',
            'reference' => 'nullable|string',
            'payment_method' => 'required|in:paystack,shop_balance',
            'state_id' => 'nullable|exists:states,id'
        ]);

        if ($fields->fails()) {
            return response([
                'errors' => $fields->errors(),
                'success' => false
            ], 400);
        }

        $user = User::find($request->user_id);
        DB::beginTransaction();

        try {
            if ($request->type === 'direct_purchase') {
                UserCart::where('user_id', $request->user_id)
                    ->where('status', '!=', 'completed')
                    ->update(['status' => 'completed']);
            }

            // Check balance if using shop_balance
            if ($request->payment_method === 'shop_balance' && $user->acc_balance < $request->amount) {
                DB::rollBack();
                return response([
                    'message' => "Insufficient Balance",
                    'success' => false
                ], 200);
            }

            if (!is_null($request->reference)) {
                $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
                    ->get("https://api.paystack.co/transaction/verify/{$request->reference}");


                if (!$response->successful() || !isset($response['data']['status']) || $response['data']['status'] !== 'success') {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment verification failed',
                        'data' => $response->json()
                    ]);
                }
            }




            $order = Order::create([
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'address' => $request->address,
                'status' => 'pending',
                'type' => $request->type,
                'payment_method' => $request->payment_method,
                'reference' => $request->reference,
                'customer_id' => $request->customer_id,
                'state_id' => $request->state_id ?? null
            ]);


            if ($user && !empty($user->email)) {
                Mail::to($user->email)->queue(new OrderCreatedMail($order, 'user'));
            }

            Mail::to('admin@email.com')
                ->queue(new OrderCreatedMail($order, 'admin'));

            if (
                $request->type === 'customer_purchase'
                && $order->customer
                && !empty($order->customer->email)
            ) {
                Mail::to($order->customer->email)
                    ->queue(new OrderCreatedMail($order, 'customer'));
            }

            // Attach products with quantity
            foreach ($request->product_id as $index => $productId) {
                $order->products()->attach($productId, [
                    'quantity' => $request->quantity[$index] ?? 1
                ]);
            }

            // Attach retail products if provided
            if ($request->has('retail_id')) {
                foreach ($request->retail_id as $index => $retailId) {
                    $order->resells()->attach($retailId, [
                        'quantity' => $request->quantity[$index] ?? 1
                    ]);
                }
            }

            // If using shop_balance, withdraw
            if ($request->payment_method === 'shop_balance') {
                Withdrawal::create([
                    'user_id' => $request->user_id,
                    'amount' => $request->amount,
                    'type' => $request->type,
                    'status' => 'completed'
                ]);

                $user->decrement('acc_balance', $request->amount);
            }

            DB::commit();

            return response([
                'order' => $order->load('products', 'resells'),
                'message' => 'Order created successfully',
                'success' => true,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

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
            $order = Order::with(['user', 'product'])->findOrFail($id);

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
            $order = Order::with(['resells', 'products'])->findOrFail($id);
            $user = User::findOrFail($order->user_id);

            if (!is_null($request->status)) {
                if ($order->resells->isNotEmpty()) {
                    $order->update(['status' => 'completed']);

                    $creditAmount = 0;

                    foreach ($order->resells as $retail) {
                        $creditAmount += $retail->gain * $retail->pivot->quantity;
                    }

                    $user->increment('acc_balance', $creditAmount);

                    Deposit::create([
                        'user_id' => $order->user_id,
                        'amount' => $creditAmount,
                        'customer_id' => $order->customer_id,
                        'status' => 'completed',
                        'order_id' => $order->id,
                        'payment_method' => 'retail_deposit'
                    ]);
                } else {
                    $order->update(['status' => 'completed']);
                }
            } else if (!is_null($request->dispatch_number)) {
                $order->update(['dispatch_number' => $request->dispatch_number]);
            } else {
                return response([
                    'message' => 'No update parameters provided',
                    'success' => false,
                ], 400);
            }

            DB::commit();

            return response([
                'order' => $order,
                'message' => 'Order updated successfully',
                'success' => true,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response([
                'message' => 'Failed to update order: ' . $th->getMessage(),
                'success' => false,
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $order = Order::with(['user', 'product'])->findOrFail($id);

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
