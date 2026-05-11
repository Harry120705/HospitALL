<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    public function index()
    {
        return response()->json(Hospital::all());
    }

    public function store(Request $request)
    {
        $hospital = Hospital::create($request->all());
        return response()->json($hospital, 201);
    }

    public function show($id)
    {
        return response()->json(Hospital::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $hospital = Hospital::findOrFail($id);
        $hospital->update($request->all());
        return response()->json($hospital);
    }

    public function destroy($id)
    {
        Hospital::findOrFail($id)->delete();
        return response()->json(['message' => 'Removido com sucesso']);
    }
}
