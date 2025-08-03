<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAddressController extends Controller
{
    public function index()
    {
        $addresses = UserAddress::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return response()->json([
            'data' => $addresses,
            'message' => 'Addresses retrieved successfully.',
            'success' => true,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nickname' => 'required|string|max:255',
            'fullAddress' => 'required|string',
            'is_default' => 'boolean',
        ]);

        // Check if the nickname already exists for the user
        $existingAddress = UserAddress::where('user_id', Auth::id())
            ->where('nickname', $validated['nickname'])
            ->first();

        // If setting this address as default, reset all others
        if ($validated['is_default'] ?? false) {
            UserAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        if ($existingAddress) {
            // Update the existing address
            $existingAddress->update([
                'fullAddress' => $validated['fullAddress'],
                'is_default' => $validated['is_default'] ?? false,
            ]);
            $address = $existingAddress;
        } else {
            // Create new address
            $address = UserAddress::create([
                'user_id' => Auth::id(),
                'nickname' => $validated['nickname'],
                'fullAddress' => $validated['fullAddress'],
                'is_default' => $validated['is_default'] ?? false,
            ]);
        }

        return response()->json([
            'data' => $address,
            'message' => $existingAddress ? 'Address updated successfully.' : 'Address created successfully.',
            'success' => true,
        ], $existingAddress ? 200 : 201);
    }


    public function show(UserAddress $userAddress)
    {
        $this->authorizeAccess($userAddress);

        return response()->json([
            'data' => $userAddress,
            'message' => 'Address retrieved successfully.',
            'success' => true,
        ]);
    }

    public function update(Request $request, UserAddress $userAddress)
    {
        $this->authorizeAccess($userAddress);

        $validated = $request->validate([
            'nickname' => 'nullable|string|max:255',
            'fullAddress' => 'required|string',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            UserAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        $userAddress->update([
            'nickname' => $validated['nickname'] ?? $userAddress->nickname,
            'fullAddress' => $validated['fullAddress'],
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return response()->json([
            'data' => $userAddress,
            'message' => 'Address updated successfully.',
            'success' => true,
        ]);
    }

    public function destroy(UserAddress $userAddress)
    {
        $this->authorizeAccess($userAddress);

        $userAddress->delete();

        return response()->json([
            'data' => null,
            'message' => 'Address deleted successfully.',
            'success' => true,
        ]);
    }

    // Ensure the cart item belongs to the authenticated user
    protected function authorizeAccess(UserAddress $userAddress)
    {
        if ($userAddress->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to cart item');
        }
    }
}
