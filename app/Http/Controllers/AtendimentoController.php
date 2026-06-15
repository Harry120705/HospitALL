<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Hospital;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AtendimentoController extends Controller
{
    public function index()
    {
        return response()->json(Atendimento::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'paciente_id' => 'required|string',
            'hospital_id' => 'required|string',
            'alocacao_leito.setor_id' => 'required|string',
        ]);

        try {
            $atendimento = null;

            DB::connection('mongodb')->transaction(function () use ($request, &$atendimento) {
                $hospitalId = $request->hospital_id;
                $setorId = $request->input('alocacao_leito.setor_id');
                $setorNome = $request->input('alocacao_leito.setor') ?? 'Desconhecido';
                $pacienteId = $request->paciente_id;
                $motivoAdmissao = $request->input('dados_iniciais.queixa_principal') ?? 'Sem motivo registrado';
                $numeroLeito = $request->input('alocacao_leito.numero');

                // 1. Reserva Segura do Leito (Hospitais)
                if (!empty($numeroLeito)) {
                    $hospital = Hospital::findOrFail($hospitalId);
                    
                    $setor = collect($hospital->setores)->first(function ($s) use ($setorId) {
                        $id = $s['_id'] ?? $s['id_setor'] ?? null;
                        return (string) $id === (string) $setorId;
                    });

                    if (!$setor) {
                        throw ValidationException::withMessages(['setor' => 'Setor não encontrado no hospital.']);
                    }

                    if (($setor['ocupacao_atual'] ?? 0) >= ($setor['capacidade_maxima'] ?? 1)) {
                        throw ValidationException::withMessages(['setor' => 'Capacidade máxima atingida para o setor selecionado.']);
                    }

                    $updated = Hospital::where('_id', $hospitalId)
                        ->where('setores._id', $setorId)
                        ->update(['$inc' => ['setores.$.ocupacao_atual' => 1]]);
                    
                    if (!$updated) {
                        Hospital::where('_id', $hospitalId)
                            ->where('setores.id_setor', $setorId)
                            ->update(['$inc' => ['setores.$.ocupacao_atual' => 1]]);
                    }
                }

                // 2. Registro da Visita
                $atendimento = Atendimento::create($request->all());

                // 3. Atualização do Prontuário Rápido (Subset Pattern na Coleção Pacientes)
                $novoResumo = [
                    'atendimento_id' => $atendimento->_id ?? $atendimento->id,
                    'data_admissao' => now()->toISOString(),
                    'motivo_principal' => $motivoAdmissao,
                    'setor_nome' => $setorNome,
                    'status' => $request->input('status', 'AGUARDANDO_TRIAGEM'),
                ];

                Paciente::where('_id', $pacienteId)->update([
                    'status_paciente' => $request->input('status', 'AGUARDANDO_TRIAGEM'),
                    'setor_id' => $setorId,
                    'setor_nome' => $setorNome,
                    '$push' => [
                        'resumo_ultimas_visitas' => [
                            '$each' => [$novoResumo],
                            '$slice' => -3,
                            '$sort' => ['data_admissao' => -1]
                        ]
                    ]
                ]);
            });

            return response()->json($atendimento, 201);
            
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erro de validação', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno na transação de admissão: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        return response()->json(Atendimento::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        try {
            \Log::info("=== INICIANDO UPDATE ATENDIMENTO ===", [
                'id' => $id,
                'request_data' => $request->all(),
            ]);

            $atendimento = Atendimento::findOrFail($id);
            $novoStatus = $request->input('status');

            \Log::info("Status atual do atendimento: " . $atendimento->status);
            \Log::info("Novo status recebido: " . $novoStatus);

            // Se for atualização comum (não é transição para alta)
            if ($novoStatus !== 'ALTA' || $atendimento->status === 'ALTA') {
                \Log::info("Entrou no IF de atualização comum. bypassando transação.");
                $atendimento->update($request->all());
                return response()->json($atendimento);
            }

            \Log::info("Entrando na transação ACID para ALTA...");
            // É uma transição para ALTA (Transação ACID)
            DB::connection('mongodb')->transaction(function () use ($request, $atendimento) {
                \Log::info("Dentro da transação: atualizando atendimento para ALTA.");
                // 1. Atualiza o Atendimento para ALTA
                $atendimento->update($request->all());
                
                // 2. Libera o leito se estava ocupando um
                $setorId = $atendimento->alocacao_leito['setor_id'] ?? null;
                $numeroLeito = $atendimento->alocacao_leito['numero'] ?? null;
                $hospitalId = $atendimento->hospital_id;

                \Log::info("Dados de leito:", [
                    'setorId' => $setorId,
                    'numeroLeito' => $numeroLeito,
                    'hospitalId' => $hospitalId
                ]);

                if (!empty($numeroLeito) && $setorId && $hospitalId) {
                    $updated = Hospital::where('_id', $hospitalId)
                        ->where('setores._id', $setorId)
                        ->where('setores.ocupacao_atual', '>', 0) // Segurança extra para não negativar
                        ->update(['$inc' => ['setores.$.ocupacao_atual' => -1]]);
                        
                    \Log::info("Leito decrementado (1a tentativa): " . ($updated ? 'SIM' : 'NAO'));

                    if (!$updated) {
                        $updated2 = Hospital::where('_id', $hospitalId)
                            ->where('setores.id_setor', $setorId)
                            ->where('setores.ocupacao_atual', '>', 0)
                            ->update(['$inc' => ['setores.$.ocupacao_atual' => -1]]);
                        \Log::info("Leito decrementado (2a tentativa): " . ($updated2 ? 'SIM' : 'NAO'));
                    }
                }

                \Log::info("Atualizando status do Paciente para ALTA...");
                // 3. Atualiza o Paciente (Retira do Setor e atualiza status)
                Paciente::where('_id', $atendimento->paciente_id)->update([
                    'status_paciente' => 'ALTA',
                    '$unset' => ['setor_id' => '', 'setor_nome' => '']
                ]);

                // Opcional: Atualiza o status no histórico rápido (se ainda estiver entre os 3 últimos)
                Paciente::where('_id', $atendimento->paciente_id)
                    ->where('resumo_ultimas_visitas.atendimento_id', $atendimento->_id ?? $atendimento->id)
                    ->update([
                        'resumo_ultimas_visitas.$.status' => 'ALTA'
                    ]);
                
                \Log::info("Transação concluída com sucesso.");
            });

            \Log::info("=== FIM UPDATE ATENDIMENTO ===");
            return response()->json($atendimento);

        } catch (\Exception $e) {
            \Log::error("Erro no update atendimento: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno na transação de alta: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        Atendimento::findOrFail($id)->delete();
        return response()->json(['message' => 'Removido com sucesso']);
    }
}
