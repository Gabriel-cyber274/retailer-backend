<?php

namespace App\Http\Controllers;

use App\Models\UserCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserCartController extends Controller
{
    public function index()
    {
        $cartItems = UserCart::with(['product', 'tag'])
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->get();

        return response()->json([
            'data' => $cartItems,
            'message' => 'Cart items retrieved successfully.',
            'success' => true
        ]);
    }

    // 🟢 Add an item to the cart or update quantity if it exists
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'tag_id'     => 'nullable|exists:product_tags,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        // Check if the cart item already exists
        $cartItem = UserCart::where([
            'user_id'    => Auth::id(),
            'product_id' => $validated['product_id'],
            'tag_id'     => $validated['tag_id'],
            'status' => 'active'
        ])->first();

        if ($cartItem) {
            if ($cartItem->status == 'active') {
                // Increment the quantity if it exists
                $cartItem->increment('quantity', $validated['quantity']);
            }
        } else {
            $cartItem = UserCart::create([
                'user_id'    => Auth::id(),
                'product_id' => $validated['product_id'],
                'tag_id'     => $validated['tag_id'],
                'quantity'   => $validated['quantity'],
            ]);
        }

        return response()->json([
            'data' => $cartItem->load(['product', 'tag']),
            'message' => 'Item added to cart successfully.',
            'success' => true
        ], 201);
    }
    // 🟢 Show a single cart item
    public function show(UserCart $userCart)
    {
        $this->authorizeAccess($userCart);

        return response()->json([
            'data' => $userCart->load(['product', 'tag']),
            'message' => 'Cart item retrieved successfully.',
            'success' => true
        ]);
    }

    // 🟢 Update quantity of an existing item
    public function update(Request $request, UserCart $userCart)
    {
        $this->authorizeAccess($userCart);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $userCart->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'data' => $userCart->load(['product', 'tag']),
            'message' => 'Cart item updated successfully.',
            'success' => true
        ]);
    }

    // 🟢 Remove an item from the cart
    public function destroy(UserCart $userCart)
    {
        $this->authorizeAccess($userCart);
        $userCart->delete();

        return response()->json([
            'data' => null,
            'message' => 'Item removed from cart successfully.',
            'success' => true
        ]);
    }

    // 🛡 Ensure the cart item belongs to the authenticated user
    protected function authorizeAccess(UserCart $userCart)
    {
        if ($userCart->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to cart item');
        }
    }
}
