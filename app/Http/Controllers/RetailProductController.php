<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\retailProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RetailProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth()->id();
        $user = User::find($userId);
        $retailProducts = retailProduct::with(['user', 'product.tags', 'orders'])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($product) {
                $totalSold = DB::table('order_retail_product')
                    ->where('retail_id', $product->id)
                    ->sum('quantity');

                $product->total_sold = $totalSold;

                return $product;
            });
        $customers = Customer::with('orders')
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($customer) {
                $totalSpent = $customer->orders->sum('amount');
                $totalOrders = $customer->orders->count();
                $lastOrderDate = $customer->orders->max('created_at');

                $customer->total_spent = $totalSpent;
                $customer->total_orders = $totalOrders;
                $customer->last_order_date = $lastOrderDate;

                return $customer;
            });


        return response([
            'retailProducts' => $retailProducts,
            'user' => $user,
            'customers' => $customers,
            'message' => 'all products retrieved successfully',
            'success' => true,
        ]);
    }

    public function store(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'gain' => 'required',
            'product_id' => 'required',
        ]);


        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        $userId = auth()->id();

        $user = User::find($userId);

        if (is_null($user->shop_name)) {
            return response([
                'message' => 'you have not activated your shop',
                'success' => false,
            ]);
        } else {
            $retail = retailProduct::create([
                'gain' => $request->gain,
                'product_id' => $request->product_id,
                'user_id' => $userId,
            ]);

            return response([
                'retail' => $retail,
                'user' => $user,
                'message' => 'product added to shop successfully',
                'success' => true,
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $retail = retailProduct::with(['user', 'product.tags'])->findOrFail($id);

            return response([
                'retail' => $retail,
                'message' => 'retail retrieved successfully',
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
            $retail = retailProduct::with(['user', 'product'])->findOrFail($id);

            $retail->update($request->all());

            return response([
                'retail' => $retail,
                'message' => 'retail updated successfully',
                'success' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $retail = retailProduct::with(['user', 'product'])->findOrFail($id);

            $retail->delete();

            return response([
                'message' => 'retail deleted successfully',
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
