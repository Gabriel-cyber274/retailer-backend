<?php

namespace App\Http\Controllers;

use App\Models\SavedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedProductController extends Controller
{
    public function index()
    {
        $savedProducts = SavedProduct::with('product')
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'data' => $savedProducts,
            'message' => 'Saved products retrieved successfully.',
            'success' => true
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $exists = SavedProduct::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'data' => null,
                'message' => 'Product already saved.',
                'success' => false
            ], 409);
        }

        $savedProduct = SavedProduct::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'data' => $savedProduct,
            'message' => 'Product saved successfully.',
            'success' => true
        ], 201);
    }

    public function show($id)
    {
        $savedProduct = SavedProduct::with('product')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'data' => $savedProduct,
            'message' => 'Saved product retrieved successfully.',
            'success' => true
        ]);
    }

    public function destroy($id)
    {
        $savedProduct = SavedProduct::where('user_id', Auth::id())
            ->findOrFail($id);

        $savedProduct->delete();

        return response()->json([
            'data' => null,
            'message' => 'Product unsaved successfully.',
            'success' => true
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $saved = SavedProduct::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($saved) {
            $saved->delete();

            return response()->json([
                'data' => null,
                'message' => 'Product removed from saved list.',
                'success' => true
            ]);
        }

        $saved = SavedProduct::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'data' => $saved,
            'message' => 'Product saved successfully.',
            'success' => true
        ]);
    }
}
