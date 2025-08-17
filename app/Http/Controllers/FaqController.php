<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class FaqController extends Controller
{
    public function index(): JsonResponse
    {
        $faqs = Faq::all();
        return response()->json([
            'status' => true,
            'data' => $faqs,
        ]);
    }

    /**
     * Get only active FAQs
     */
    public function active(): JsonResponse
    {
        $faqs = Faq::where('is_active', true)->get();
        return response()->json([
            'status' => true,
            'data' => $faqs,
        ]);
    }
}
