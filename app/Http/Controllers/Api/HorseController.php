<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horse;
use Illuminate\Http\Request;
use Validator;

class HorseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $horses = Horse::all();
        return response()->json($horses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' =>'required',
            'breed' =>'required',
            'date_of_birth' =>'required',
            'sick' =>'required',
            'observations' =>'required',
            'price' =>'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $horse = Horse::create($request->all());

        return response()->json($horse, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $horse = Horse::find($id);

        if (is_null($horse)) {
            return response()->json(['message' => 'Horse not found'], 404);
        }

        return response()->json($horse, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $horse = Horse::find($id);

        if (is_null($horse)) {
            return response()->json(['message' => 'Horse not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' =>'required',
            'breed' =>'required',
            'date_of_birth' =>'required',
            'sick' =>'required',
            'observations' =>'required',
            'price' =>'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $horse->update($request->all());

        return response()->json($horse, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $horse = Horse::find($id);

        if (is_null($horse)) {
            return response()->json(['message' => 'Horse not found'], 404);
        }

        $horse->delete();

        return response()->json(['message' => 'Horse deleted successfully'], 204);
    }
}

