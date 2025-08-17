<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;

class CustomeSiteController extends Controller
{
    public function index($name, $shop_id)
    {
        $user = User::with(['retails' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('shop_id', $shop_id)->first()->load('retails.product.tags');


        $states = State::all();



        return view('custom_home_page', compact('user', 'states'));
    }



    public function updateOrder($order_id)
    {
        $order = Order::find($order_id);
        $user = $order->user;
        $states = State::all();

        return view('update_order', compact('user', 'states', 'order'));
    }
}
