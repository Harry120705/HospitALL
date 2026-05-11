<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use Illuminate\Http\Request;

class AtendimentoController extends Controller
{
    public function index()
    {
        return response()->json(Atendimento::all());
    }

    public function store(Request $request)
    {
        $atendimento = Atendimento::create($request->all());
        return response()->json($atendimento, 201);
    }

    public function show($id)
    {
        return response()->json(Atendimento::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $atendimento = Atendimento::findOrFail($id);
        $atendimento->update($request->all());
        return response()->json($atendimento);
    }

    public function destroy($id)
    {
        Atendimento::findOrFail($id)->delete();
        return response()->json(['message' => 'Removido com sucesso']);
    }
}
