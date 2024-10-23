<?php

namespace App\Http\Controllers;

use App\Models\ProductFeatureImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class ProductFeatureImagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($productId)
    {
        $featuredImages = ProductFeatureImages::with('product')
            ->where('product_id', $productId)
            ->orderBy('id', 'desc')
            ->get();

        $response = [
            'featured_images' => $featuredImages,
            'message' => 'Featured images retrieved successfully',
            'success' => true,
        ];

        return response()->json($response, 200);
    }


    public function store(Request $request)
    {
        // Validate multiple images
        $fields = Validator::make($request->all(), [
            'product_id' => 'required',
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validate each image in the array
        ]);

        if ($fields->fails()) {
            $response = [
                'errors' => $fields->errors(),
                'success' => false
            ];

            return response($response);
        }

        $imageUrls = [];

        // Check if multiple files are uploaded
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Store each image in the 'products' folder of 'public' disk
                $imagePath = $image->store('products', 'public');
                $filename = basename($imagePath);

                // Generate the API URL for each image using your custom route
                $imageUrl = route('prodimgs.get', ['filename' => $filename]);

                // Add each image URL to the array
                $imageUrls[] = $imageUrl;

                // Save each image to the database
                ProductFeatureImages::create([
                    'product_id' => $request->product_id,
                    'image' => $imageUrl
                ]);
            }
        }

        $response = [
            'featured_images' => $imageUrls, // Return array of image URLs
            'message' => 'Featured images added successfully',
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
            $featuredImages = ProductFeatureImages::with(['product'])->findorfail($id);

            return response([
                'featured_images' => $featuredImages,
                'message' => 'featured images retrieved successfully',
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
            // Retrieve the record with the product relationship
            $featuredImages = ProductFeatureImages::with('product')->findOrFail($id);

            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($featuredImages->image) {
                    $oldImagePath = str_replace('/api/prodimgs/', 'products/', parse_url($featuredImages->image, PHP_URL_PATH));

                    if (Storage::disk('public')->exists($oldImagePath)) {
                        Storage::disk('public')->delete($oldImagePath);
                    }
                }

                // Store the new image
                $image = $request->file('image');
                $imagePath = $image->store('products', 'public');
                $imageUrl = route('prodimgs.get', ['filename' => basename($imagePath)]);

                // Update the image path in the database
                $featuredImages->image = $imageUrl;
                $featuredImages->save(); // Save the changes
            }

            return response([
                'featured_images' => $featuredImages,
                'message' => 'Image updated successfully',
                'success' => true,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response([
                'message' => 'Product feature image not found',
                'success' => false,
            ], 404); // Return a 404 if the record is not found

        } catch (\Exception $e) {
            return response([
                'message' => 'An error occurred: ' . $e->getMessage(),
                'success' => false,
            ], 500); // Return a 500 for general exceptions
        }
    }


    public function destroy($id)
    {
        try {
            // Find the featured image record
            $featuredImages = ProductFeatureImages::with('product')->findOrFail($id);

            // Delete the associated image file if it exists
            if ($featuredImages->image) {
                $imagePath = str_replace('/api/prodimgs/', 'products/', parse_url($featuredImages->image, PHP_URL_PATH));

                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            // Delete the database record
            $featuredImages->delete();

            // Respond with no content since the deletion was successful
            return response()->json([
                'message' => 'Featured image deleted successfully',
                'success' => true,
            ], 204); // 204 indicates successful deletion with no content

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Featured image not found',
                'success' => false,
            ], 404); // 404 for not found

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
                'success' => false,
            ], 500); // 500 for other errors
        }
    }
}
