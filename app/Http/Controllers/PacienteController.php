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
                'tipagem_sanguinea' => $request->input('tipagem_sanguinea', $request->input('dados_clinicos_fixos.tipagem_sanguinea')),
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
        $rules = [
            'nome' => ['required', 'string'],
            'cpf' => ['required', 'string'],
            'data_nascimento' => ['required', 'date'],
            'genero' => ['required', 'string'],
            'cartao_sus' => ['required', 'string'],
            'telefone' => ['required', 'string'],
            'contato.nome' => ['required_without:contato_emergencia_nome', 'string'],
            'contato.telefone' => ['required_without:contato_emergencia_telefone', 'string'],
            'dados_clinicos_fixos.tipagem_sanguinea' => ['required', 'string'],
            'dados_clinicos_fixos.peso_kg' => ['nullable', 'numeric', 'min:0'],
            'dados_clinicos_fixos.altura_cm' => ['nullable', 'numeric', 'min:0'],
            'setor_id' => ['required', 'string'],
        ];

        $messages = [
            'required' => 'O campo :attribute é obrigatório.',
            'required_without' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto válido.',
            'date' => 'O campo :attribute deve ser uma data válida.',
            'numeric' => 'O campo :attribute deve ser um número.',
            'min' => 'O campo :attribute não pode ter valor negativo.',
        ];

        $attributes = [
            'nome' => 'Nome',
            'cpf' => 'CPF',
            'data_nascimento' => 'Data de Nascimento',
            'genero' => 'Gênero',
            'cartao_sus' => 'Cartão SUS',
            'telefone' => 'Telefone',
            'contato.nome' => 'Nome do Contato',
            'contato.telefone' => 'Telefone do Contato',
            'dados_clinicos_fixos.tipagem_sanguinea' => 'Tipo Sanguíneo',
            'dados_clinicos_fixos.peso_kg' => 'Peso',
            'dados_clinicos_fixos.altura_cm' => 'Altura',
            'setor_id' => 'Setor',
        ];

        $request->validate($rules, $messages, $attributes);

        $paciente = Paciente::create($this->normalizePayload($request));
        return response()->json($paciente, 201);
    }

    public function show($id)
    {
        return response()->json(Paciente::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'nome' => ['sometimes', 'string'],
            'cpf' => ['sometimes', 'string'],
            'data_nascimento' => ['sometimes', 'date'],
            'genero' => ['sometimes', 'string'],
            'cartao_sus' => ['sometimes', 'string'],
            'telefone' => ['sometimes', 'string'],
            'contato.nome' => ['sometimes', 'string'],
            'contato.telefone' => ['sometimes', 'string'],
            'dados_clinicos_fixos.tipagem_sanguinea' => ['sometimes', 'string'],
            'dados_clinicos_fixos.peso_kg' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'dados_clinicos_fixos.altura_cm' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'setor_id' => ['sometimes', 'string'],
        ];

        $messages = [
            'string' => 'O campo :attribute deve ser um texto válido.',
            'date' => 'O campo :attribute deve ser uma data válida.',
            'numeric' => 'O campo :attribute deve ser um número.',
            'min' => 'O campo :attribute não pode ter valor negativo.',
        ];

        $attributes = [
            'nome' => 'Nome',
            'cpf' => 'CPF',
            'data_nascimento' => 'Data de Nascimento',
            'genero' => 'Gênero',
            'cartao_sus' => 'Cartão SUS',
            'telefone' => 'Telefone',
            'contato.nome' => 'Nome do Contato',
            'contato.telefone' => 'Telefone do Contato',
            'dados_clinicos_fixos.tipagem_sanguinea' => 'Tipo Sanguíneo',
            'dados_clinicos_fixos.peso_kg' => 'Peso',
            'dados_clinicos_fixos.altura_cm' => 'Altura',
            'setor_id' => 'Setor',
        ];

        $request->validate($rules, $messages, $attributes);

        $paciente = Paciente::findOrFail($id);
        
        $dataToUpdate = [];
        $fields = ['nome', 'cpf', 'data_nascimento', 'genero', 'cartao_sus', 'telefone', 'status_paciente', 'setor_id', 'setor_nome'];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $dataToUpdate[$field] = $request->input($field);
            }
        }

        if ($request->has('contato') || $request->has('contato_emergencia_nome')) {
            // Preserva os dados do DB que não vieram, ou apenas usa os novos
            $dataToUpdate['contato'] = [
                'nome' => $request->input('contato.nome', $request->input('contato_emergencia_nome', $paciente->contato['nome'] ?? null)),
                'telefone' => $request->input('contato.telefone', $request->input('contato_emergencia_telefone', $paciente->contato['telefone'] ?? null)),
                'parentesco' => $request->input('contato.parentesco', $paciente->contato['parentesco'] ?? null),
            ];
        }

        if ($request->has('dados_clinicos_fixos') || $request->has('tipagem_sanguinea')) {
            $dataToUpdate['dados_clinicos_fixos'] = [
                'tipagem_sanguinea' => $request->input('tipagem_sanguinea', $request->input('dados_clinicos_fixos.tipagem_sanguinea', $paciente->dados_clinicos_fixos['tipagem_sanguinea'] ?? null)),
                'peso_kg' => $request->input('peso_kg', $request->input('dados_clinicos_fixos.peso_kg', $paciente->dados_clinicos_fixos['peso_kg'] ?? null)),
                'altura_cm' => $request->input('altura_cm', $request->input('dados_clinicos_fixos.altura_cm', $paciente->dados_clinicos_fixos['altura_cm'] ?? null)),
                'alergias' => $request->input('alergias', $request->input('dados_clinicos_fixos.alergias', $paciente->dados_clinicos_fixos['alergias'] ?? null)),
                'comorbidades' => $request->input('comorbidades', $request->input('dados_clinicos_fixos.comorbidades', $paciente->dados_clinicos_fixos['comorbidades'] ?? null)),
            ];
        }

        $paciente->update($dataToUpdate);
        return response()->json($paciente);
    }

    public function destroy($id)
    {
        Paciente::findOrFail($id)->delete();
        return response()->json(['message' => 'Removido com sucesso']);
    }
}
