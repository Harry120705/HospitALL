<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\Atendimento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * HospitALL — Controller de operações NoSQL em sub-documentos
 * Trata operações de push em arrays embutidos do MongoDB.
 * NÃO altera os controllers de CRUD padrão existentes.
 */
class NoSQLController extends Controller
{
    // ─── HOSPITAIS ────────────────────────────────────────────────

    /**
     * POST /api/hospitais/{id}/setores
     * Adiciona um novo setor ao array embebido do hospital.
     */
    public function adicionarSetor(Request $request, $id)
    {
        $request->validate([
            'nome'             => 'required|string|max:100',
            'descricao'        => 'required|string|max:255',
            'icone'            => 'nullable|string|max:50',
            'capacidade_maxima'=> 'required|integer|min:1',
        ]);

        $hospital = Hospital::findOrFail($id);

        $novoSetor = [
            '_id'              => (string) Str::uuid(),
            'nome'             => $request->nome,
            'descricao'        => $request->descricao,
            'icone'            => $request->input('icone', 'bed-double'),
            'ocupacao_atual'   => 0,
            'capacidade_maxima'=> (int) $request->capacidade_maxima,
        ];

        // Push no array embutido usando operador MongoDB $push
        $hospital->push('setores', $novoSetor);

        return response()->json($novoSetor, 201);
    }

    // ─── ATENDIMENTOS ─────────────────────────────────────────────

    /**
     * POST /api/atendimentos/{id}/evolucoes
     * Insere uma nova evolução médica no array evolucoes_medicas.
     */
    public function adicionarEvolucao(Request $request, $id)
    {
        $request->validate([
            'medico'    => 'required|string|max:100',
            'crm'       => 'required|string|max:20',
            'tipo'      => 'required|in:evolucao,prescricao,exame,procedimento',
            'descricao' => 'required|string',
        ]);

        $atendimento = Atendimento::findOrFail($id);

        $novaEvolucao = [
            'id'        => (string) Str::uuid(),
            'data_hora' => now()->toISOString(),
            'medico'    => $request->medico,
            'crm'       => $request->crm,
            'tipo'      => $request->tipo,
            'descricao' => $request->descricao,
        ];

        $atendimento->push('evolucoes_medicas', $novaEvolucao);

        return response()->json($novaEvolucao, 201);
    }

    /**
     * GET /api/atendimentos?setor_id={id}&status=internado
     * Filtra atendimentos por setor — reutiliza AtendimentoController::index
     * com query params. Este método centraliza a lógica de filtro.
     */
    public function atendimentosPorSetor(Request $request)
    {
        $query = Atendimento::query();

        if ($request->has('setor_id')) {
            $query->where('alocacao_leito.setor_id', $request->setor_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Trazer dados do paciente embutidos (lookup manual se não estiver embedded)
        $atendimentos = $query->get();

        // Enriquecer com dados do paciente (se armazenados por referência)
        $atendimentos->transform(function ($atend) {
            if ($atend->paciente_id && !isset($atend->paciente)) {
                $atend->paciente = \App\Models\Paciente::find($atend->paciente_id);
            }
            return $atend;
        });

        return response()->json($atendimentos);
    }
}
