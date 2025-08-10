<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $states = State::all();

        return response()->json([
            'data' => $states,
            'message' => 'States retrieved successfully.',
            'success' => true,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:states,name',
            'country_name' => 'required|string|max:255',
            'dispatch_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        $state = State::create($validated);

        return response()->json([
            'data' => $state,
            'message' => 'State created successfully.',
            'success' => true,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(State $state)
    {
        return response()->json([
            'data' => $state,
            'message' => 'State retrieved successfully.',
            'success' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, State $state)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:states,name,' . $state->id,
            'country_name' => 'required|string|max:255',
            'dispatch_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        $state->update($validated);

        return response()->json([
            'data' => $state,
            'message' => 'State updated successfully.',
            'success' => true,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(State $state)
    {
        $state->delete();

        return response()->json([
            'data' => null,
            'message' => 'State deleted successfully.',
            'success' => true,
        ]);
    }
}
