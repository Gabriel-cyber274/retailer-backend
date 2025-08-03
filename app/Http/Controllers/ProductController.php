<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['tags', 'categories', 'featuredimages'])->orderBy('id', 'desc')->get();

        $response = [
            'products' => $products,
            'message' => 'products retrieved successfully',
            'success' => true
        ];

        return response($response);
    }

    public function allInstock()
    {
        $products = Product::with(['tags', 'categories', 'featuredimages'])->where('in_stock', true)->orderBy('id', 'desc')->get();

        $response = [
            'products' => $products,
            'message' => 'products retrieved successfully',
            'success' => true
        ];

        return response($response);
    }

    public function allOutOfStock()
    {
        $products = Product::with(['tags', 'categories', 'featuredimages'])->where('in_stock', false)->orderBy('id', 'desc')->get();

        $response = [
            'products' => $products,
            'message' => 'products retrieved successfully',
            'success' => true
        ];

        return response($response);
    }




    public function store(Request $request)
    {
        $fields = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required',
            'price' => 'required',
            'product_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'in_stock' => 'required',
            'quantity' => 'nullable',
            'cost_price' => 'required',
        ]);



        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        $verificationCode = mt_rand(10000, 99999);

        $imageUrl = null;
        if ($request->hasFile('product_image')) {
            $image = $request->file('product_image');
            $imagePath = $image->store('products', 'public');
            $filename = basename($imagePath);

            // Generate the API URL for the image using your custom route
            $imageUrl = route('prodimgs.get', ['filename' => $filename]);
        }

        $product = Product::create([
            'name' => $request->name,
            'product_image' => $imageUrl,
            'description' => $request->description,
            'price' => $request->price,
            'suggested_profit' => $request->price * 20 / 100,
            'brand_name' => $request->brand_name,
            'video_url' => $request->video_url,
            'in_stock' => $request->in_stock,
            'quantity' => $request->quantity,
            'cost_price' => $request->cost_price,
        ]);


        $product->update([
            'product_code' => $verificationCode . $product->id,
        ]);


        $response = [
            'product' => $product,
            'message' => 'product created successfully',
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
            $userId = Auth::id();

            $product = Product::with(['tags', 'categories', 'featuredimages', 'savedProduct', 'reviews', 'retails'])
                ->findOrFail($id);

            $product->isSaved = $product->savedProduct->contains('user_id', $userId);
            $product->isInShop = $product->retails->contains('user_id', $userId);
            $product->averageReview = $product->reviews->avg('rate');
            unset($product->savedProduct);

            return response([
                'message' => 'Product retrieved successfully.',
                'success' => true,
                'product' => $product
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
            // Find the testimonial or fail
            $product = Product::findOrFail($id);

            // Validate the incoming request
            $fields = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'description' => 'nullable',
                'price' => 'nullable',
                'suggested_profit' => 'nullable',
                'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($fields->fails()) {
                return response([
                    'errors' => $fields->errors(),
                    'success' => false
                ], 400);
            }

            if ($request->hasFile('product_image')) {
                if ($product->product_image) {
                    $oldImagePath = str_replace('/api/prodimgs/', 'products/', parse_url($product->product_image, PHP_URL_PATH));

                    Storage::disk('public')->delete($oldImagePath);
                }

                // Store the new image
                $image = $request->file('product_image');
                $imagePath = $image->store('products', 'public');
                $imageUrl = route('prodimgs.get', ['filename' => basename($imagePath)]);

                // Update the image path in the database
                $product->product_image = $imageUrl;
            }

            // Update other fields in the product
            $product->update($request->except(['product_image']));

            return response([
                'message' => 'product updated successfully',
                'success' => true,
                'product' => $product
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => 'An error occurred: ' . $th->getMessage(),
                'success' => false,
            ], 500); // Use 500 for internal server errors
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findorfail($id);
            if ($product->product_image) {
                $imagePath = str_replace('/api/prodimgs/', 'products/', parse_url($product->product_image, PHP_URL_PATH));
                Storage::disk('public')->delete($imagePath);
            }
            $product->delete();
            return response([
                'message' => 'product deleted successfully',
                'success' => true,
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => $th->getMessage(),
                'success' => false,
            ], 200);
        }
    }


    public function search($slug)
    {
        try {
            $products = Product::with(['tags', 'categories', 'featuredimages'])
                ->where('name', 'like', "%{$slug}%")
                ->orWhere('brand_name', 'like', "%{$slug}%")
                ->orWhere('product_code', 'like', "%{$slug}%")
                ->orWhereHas('tags', function ($query) use ($slug) {
                    $query->where('name', 'like', "%{$slug}%");
                })
                ->orWhereHas('categories', function ($query) use ($slug) {
                    $query->where('name', 'like', "%{$slug}%");
                })
                ->orderBy('id', 'desc')
                ->get();

            return response([
                'message' => 'products retrived successfully',
                'success' => true,
                'products' => $products
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => 'An error occurred: ' . $th->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}
