<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductReviewController extends Controller
{
    public function index()
    {
        $reviews = ProductReview::with(['user', 'product'])->latest()->get();

        return response()->json([
            'data' => $reviews,
            'message' => 'Product reviews fetched successfully.',
            'success' => true,
        ]);
    }

    public function productReview($id)
    {
        try {
            $product = Product::findOrFail($id);

            $reviews = ProductReview::with('user')->where('product_id', $id)->get();

            return response()->json([
                'data' => $reviews,
                'average' => $reviews->avg('rate'),
                'message' => 'Product reviews fetched successfully.',
                'success' => true,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'data' => null,
                'message' => 'Product not found.',
                'success' => false,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'message' => 'An unexpected error occurred.',
                'success' => false,
            ], 500);
        }
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rate' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = ProductReview::create([
            'user_id' => Auth::id(),
            'product_id' => $validated['product_id'],
            'rate' => $validated['rate'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'data' => $review,
            'message' => 'Review submitted successfully.',
            'success' => true,
        ], 201);
    }

    public function show(ProductReview $productReview)
    {
        return response()->json([
            'data' => $productReview->load(['user', 'product']),
            'message' => 'Product review retrieved successfully.',
            'success' => true,
        ]);
    }

    public function update(Request $request, ProductReview $productReview)
    {
        if ($productReview->user_id !== Auth::id()) {
            return response()->json([
                'data' => null,
                'message' => 'Unauthorized.',
                'success' => false,
            ], 403);
        }

        $validated = $request->validate([
            'rate' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $productReview->update($validated);

        return response()->json([
            'data' => $productReview,
            'message' => 'Review updated successfully.',
            'success' => true,
        ]);
    }

    public function destroy(ProductReview $productReview)
    {
        if ($productReview->user_id !== Auth::id()) {
            return response()->json([
                'data' => null,
                'message' => 'Unauthorized.',
                'success' => false,
            ], 403);
        }

        $productReview->delete();

        return response()->json([
            'data' => null,
            'message' => 'Review deleted successfully.',
            'success' => true,
        ]);
    }
}
