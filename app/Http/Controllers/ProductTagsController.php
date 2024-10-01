<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductTags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductTagsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = ProductTags::with(['product'])->orderBy('id', 'desc')->get();

        $response = [
            'tags' => $tags,
            'message' => 'tags retrieved successfully',
            'success' => true
        ];

        return response($response);
    }

    public function store(Request $request)
    {

        $fields = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required',
            'product_id' => 'required',
            'tag_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'make_default' => 'nullable'
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
        if ($request->hasFile('tag_image')) {
            $image = $request->file('tag_image');
            $imagePath = $image->store('products', 'public');
            $filename = basename($imagePath);

            // Generate the API URL for the image using your custom route
            $imageUrl = route('prodimgs.get', ['filename' => $filename]);
        }

        $tag = ProductTags::create([
            'name' => $request->name,
            'tag_image' => $imageUrl,
            'description' => $request->description,
            'product_id' => $request->product_id
        ]);


        $tag->update([
            'tag_code' => $verificationCode . $tag->id,
        ]);


        $product = Product::find($request->product_id);

        if (!is_null($request->make_default)) {
            $product->update([
                'default_tag' => $tag->id,
            ]);
        }


        $response = [
            'tag' => $tag,
            'message' => 'tag created successfully',
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
            $tag = ProductTags::with(['product'])->findorfail($id);

            return response([
                'message' => 'tag retrieved successfully',
                'success' => true,
                'tag' => $tag
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
            $tag = ProductTags::findOrFail($id);

            // Validate the incoming request
            $fields = Validator::make($request->all(), [
                'name' => 'nullable|string',
                'description' => 'nullable',
                'tag_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'make_default' => 'nullable'
            ]);

            if ($fields->fails()) {
                return response([
                    'errors' => $fields->errors(),
                    'success' => false
                ], 400);
            }

            if ($request->hasFile('tag_image')) {
                if ($tag->tag_image) {
                    $oldImagePath = str_replace('/api/prodimgs/', 'products/', parse_url($tag->tag_image, PHP_URL_PATH));

                    Storage::disk('public')->delete($oldImagePath);
                }

                // Store the new image
                $image = $request->file('tag_image');
                $imagePath = $image->store('products', 'public');
                $imageUrl = route('prodimgs.get', ['filename' => basename($imagePath)]);

                // Update the image path in the database
                $tag->tag_image = $imageUrl;
            }

            // Update other fields in the tag
            $tag->update($request->except(['tag_image']));


            $product = Product::find($tag->product_id);

            if (!is_null($request->make_default)) {
                $product->update([
                    'default_tag' => $tag->id,
                ]);
            }

            return response([
                'message' => 'tag updated successfully',
                'success' => true,
                'tag' => $tag
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'message' => 'An error occurred: ' . $th->getMessage(),
                'success' => false,
            ], 500); // Use 500 for internal server errors
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $tag = ProductTags::findorfail($id);
            if ($tag->product_image) {
                $imagePath = str_replace('/api/prodimgs/', 'products/', parse_url($tag->tag_image, PHP_URL_PATH));
                Storage::disk('public')->delete($imagePath);
            }
            $tag->delete();
            return response([
                'message' => 'tag deleted successfully',
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
