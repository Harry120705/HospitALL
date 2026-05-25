<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    private function normalizePayload(Request $request)
    {
        return [
            'nome' => $request->input('nome'),
            'cpf' => $request->input('cpf'),
            'data_nascimento' => $request->input('data_nascimento'),
            'genero' => $request->input('genero'),
            'cartao_sus' => $request->input('cartao_sus'),
            'telefone' => $request->input('telefone'),
            'status_paciente' => $request->input('status_paciente', 'Novo Paciente'),
            'setor_id' => $request->input('setor_id'),
            'setor_nome' => $request->input('setor_nome'),
            'contato' => [
                'nome' => $request->input('contato_emergencia_nome', $request->input('contato.nome')),
                'telefone' => $request->input('contato_emergencia_telefone', $request->input('contato.telefone')),
                'parentesco' => $request->input('contato.parentesco'),
            ],
            'dados_clinicos_fixos' => [
                'tipo_sanguineo' => $request->input('tipo_sanguineo', $request->input('dados_clinicos_fixos.tipo_sanguineo')),
                'peso_kg' => $request->input('peso_kg', $request->input('dados_clinicos_fixos.peso_kg')),
                'altura_cm' => $request->input('altura_cm', $request->input('dados_clinicos_fixos.altura_cm')),
                'alergias' => $request->input('alergias', $request->input('dados_clinicos_fixos.alergias')),
                'comorbidades' => $request->input('comorbidades', $request->input('dados_clinicos_fixos.comorbidades')),
            ],
        ];
    }

    public function index()
    {
        return response()->json(Paciente::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => ['required', 'string'],
            'cpf' => ['required', 'string'],
            'data_nascimento' => ['required', 'date'],
            'genero' => ['required', 'string'],
            'cartao_sus' => ['required', 'string'],
            'telefone' => ['required', 'string'],
            'contato_emergencia_nome' => ['required_without:contato.nome', 'string'],
            'contato_emergencia_telefone' => ['required_without:contato.telefone', 'string'],
            'tipo_sanguineo' => ['required', 'string'],
            'peso_kg' => ['nullable', 'numeric', 'min:0'],
            'altura_cm' => ['nullable', 'numeric', 'min:0'],
            'setor_id' => ['required', 'string'],
        ]);
        $paciente = Paciente::create($this->normalizePayload($request));
        return response()->json($paciente, 201);
    }

    public function show($id)
    {
        return response()->json(Paciente::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => ['sometimes', 'string'],
            'cpf' => ['sometimes', 'string'],
            'data_nascimento' => ['sometimes', 'date'],
            'genero' => ['sometimes', 'string'],
            'cartao_sus' => ['sometimes', 'string'],
            'telefone' => ['sometimes', 'string'],
            'contato_emergencia_nome' => ['sometimes', 'string'],
            'contato_emergencia_telefone' => ['sometimes', 'string'],
            'tipo_sanguineo' => ['sometimes', 'string'],
            'peso_kg' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'altura_cm' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'setor_id' => ['sometimes', 'string'],
        ]);
        $paciente = Paciente::findOrFail($id);
        $paciente->update($this->normalizePayload($request));
        return response()->json($paciente);
    }

    public function destroy($id)
    {
        Paciente::findOrFail($id)->delete();
        return response()->json(['message' => 'Removido com sucesso']);
    }
}
