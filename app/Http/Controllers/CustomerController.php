<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth()->id();
        $customers = Customer::where('user_id', $userId)->get();


        $response = [
            'customers' => $customers,
            'message' => 'customers retrieved successfully',
            'success' => true
        ];

        return response($response);
    }

    public function store(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string',
            'phone_no' => 'required',
            'address' => 'required',
        ]);

        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        $userId = auth()->id();

        $checkCustomer = Customer::where('email', $request->email)->get();

        if (count($checkCustomer) > 0) {
            $response = [
                'customer' => $checkCustomer->first(),
                'message' => 'customer retrieved successfully',
                'success' => true
            ];

            return response($response);
        }

        $customer = Customer::create([
            'user_id' => $userId,
            'address' => $request->address,
            'name' => $request->name,
            'email' => $request->email,
            'phone_no' => $request->phone_no
        ]);


        $response = [
            'customer' => $customer,
            'message' => 'customer created successfully',
            'success' => true
        ];

        return response($response);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $customer = Customer::findorfail($id);


            $response = [
                'customer' => $customer,
                'message' => 'customer retrieved successfully',
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

    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::findorfail($id);

            $customer->update($request->all());

            $response = [
                'customer' => $customer,
                'message' => 'customer updated successfully',
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $customer = Customer::findorfail($id);

            $customer->delete();

            $response = [
                'message' => 'customer deleted successfully',
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
}
