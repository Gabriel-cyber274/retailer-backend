<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CustomeSiteController extends Controller
{
    public function index($name, $shop_id)
    {
        $user = User::with(['retails' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('shop_id', $shop_id)->first();

        return view('custom_home_page', compact('user'));
    }
   
    
}
