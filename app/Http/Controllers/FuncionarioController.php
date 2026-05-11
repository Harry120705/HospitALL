<?php

namespace App\Http\Controllers;

use App\Models\Funcionario;
use Illuminate\Http\Request;

class FuncionarioController extends Controller
{
    public function index()
    {
        return response()->json(Funcionario::all());
    }

    public function store(Request $request)
    {
        $funcionario = Funcionario::create($request->all());
        return response()->json($funcionario, 201);
    }

    public function show($id)
    {
        return response()->json(Funcionario::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $funcionario = Funcionario::findOrFail($id);
        $funcionario->update($request->all());
        return response()->json($funcionario);
    }

    public function destroy($id)
    {
        Funcionario::findOrFail($id)->delete();
        return response()->json(['message' => 'Removido com sucesso']);
    }
}
