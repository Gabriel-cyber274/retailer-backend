<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $category = Category::with(['products'])->orderBy('id', 'desc')->get();

        $response = [
            'categories' => $category,
            'message' => 'category retrieved successfully',
            'success' => true
        ];

        return response($response);
    }

    public function store(Request $request)
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

        $category = Category::create([
            'name' => Str::title($request->name),
        ]);



        $response = [
            'category' => $category,
            'message' => 'category created successfully',
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
            $category = Category::with(['products'])->findorfail($id);


            $response = [
                'category' => $category,
                'message' => 'category retrieved successfully',
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
            $category = Category::with(['products'])->findorfail($id);

            $category->update($request->all());

            $response = [
                'category' => $category,
                'message' => 'category updated successfully',
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
            $category = Category::with(['products'])->findorfail($id);

            $category->delete();

            $response = [
                'message' => 'category deleted successfully',
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

    public function attachProductToCategory(Request $request, $id)
    {
        $fields = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*' => 'required|integer|exists:products,id',
        ]);

        if ($fields->fails()) {
            return response([
                'errors' => $fields->errors(),
                'success' => false
            ], 422);
        }

        try {
            $category = Category::findOrFail($id);

            $category->products()->syncWithoutDetaching($request->products);

            return response([
                'category' => $category->load('products'),
                'message' => 'Products added to category successfully',
                'success' => true
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function attachCategoryToProduct(Request $request, $id)
    {
        $fields = Validator::make($request->all(), [
            'categories' => 'required|array',
            'categories.*' => 'required|integer|exists:categories,id',
        ]);

        if ($fields->fails()) {
            return response([
                'errors' => $fields->errors(),
                'success' => false
            ], 422);
        }


        try {
            $product = Product::findOrFail($id);

            $product->categories()->syncWithoutDetaching($request->categories);

            return response([
                'product' => $product->load('categories'),
                'message' => 'Products added to category successfully',
                'success' => true
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function detachProductFromCategory(Request $request, $id)
    {
        $fields = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*' => 'required|integer|exists:products,id',
        ]);

        if ($fields->fails()) {
            return response([
                'errors' => $fields->errors(),
                'success' => false
            ], 422);
        }

        try {
            $category = Category::findOrFail($id);

            // Detach the products from the category
            $category->products()->detach($request->products);

            return response([
                'category' => $category->load('products'), // Load the updated product relation
                'message' => 'Products removed from category successfully',
                'success' => true
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 500);
        }
    }









    public function attachRetailProductToCategory(Request $request, $id)
    {
        $fields = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*' => 'required|integer|exists:products,id',
        ]);

        if ($fields->fails()) {
            return response([
                'errors' => $fields->errors(),
                'success' => false
            ], 422);
        }

        try {
            $category = Category::findOrFail($id);

            $category->retailProducts()->syncWithoutDetaching($request->products);

            return response([
                'category' => $category->load('retailProducts'),
                'message' => 'Retail product added to category successfully',
                'success' => true
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 500);
        }
    }



    public function detachRetailProductToCategory(Request $request, $id)
    {
        $fields = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*' => 'required|integer|exists:products,id',
        ]);

        if ($fields->fails()) {
            return response([
                'errors' => $fields->errors(),
                'success' => false
            ], 422);
        }

        try {
            $category = Category::findOrFail($id);

            // Detach the products from the category
            $category->retailProducts()->detach($request->products);

            return response([
                'category' => $category->load('retailProducts'), // Load the updated product relation
                'message' => 'Retail product removed from category successfully',
                'success' => true
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 500);
        }
    }



    public function getMyCategoryWithRetailProduct()
    {
        $userId = auth()->id();


        $categories = Category::with(['retailProducts.product.tags'])
            ->whereHas('retailProducts', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();



        $response = [
            'categories' => $categories,
            'message' => 'categories retrieved successfully',
            'success' => true
        ];

        return response($response);
    }

    public function getSingleCategoryRetailProducts($catId)
    {
        $userId = auth()->id();

        $category = Category::with(['retailProducts.product.tags'])
            ->whereHas('retailProducts', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('id', $catId)
            ->first();

        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
                'success' => false
            ], 404);
        }

        $retailProducts = $category->retailProducts;

        $response = [
            'retail_products' => $retailProducts,
            'message' => 'Retail products retrieved successfully',
            'success' => true
        ];

        return response()->json($response);
    }
}
