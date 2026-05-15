import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/services/api.js';

/**
 * Store de Paciente / Atendimento
 * Consome:
 *   GET  /api/atendimentos?setor_id=X  → lista por setor
 *   GET  /api/atendimentos/{id}         → prontuário completo
 *   POST /api/atendimentos              → admitir paciente
 *   POST /api/atendimentos/{id}/evolucoes → nova evolução médica
 */
export const usePacienteStore = defineStore('paciente', () => {
  const atendimentos     = ref([]);
  const atendimentoAtual = ref(null);
  const loading          = ref(false);
  const error            = ref(null);

  // ── Helpers ───────────────────────────────────────────────────

  /** Extrai string do _id do MongoDB (suporta { $oid: '...' } e string direta) */
  function _mongoId(obj) {
    if (!obj) return null;
    const id = obj._id || obj.id;
    if (!id) return null;
    if (typeof id === 'string') return id;
    if (typeof id === 'object' && id.$oid) return id.$oid;
    return String(id);
  }

  function _normalizeAtendimento(a) {
    const norm = {
      ...a,
      _id:        _mongoId(a),
      paciente_id: a.paciente_id
        ? (typeof a.paciente_id === 'object' && a.paciente_id.$oid
            ? a.paciente_id.$oid
            : String(a.paciente_id))
        : null,
    };
    // Normaliza _id do paciente embutido se existir
    if (norm.paciente) {
      norm.paciente = { ...norm.paciente, _id: _mongoId(norm.paciente) };
    }
    return norm;
  }

  // ── Actions ───────────────────────────────────────────────────

  /**
   * Carrega atendimentos de um setor.
   * Usa GET /api/atendimentos/por-setor?setor_id=X quando disponível,
   * senão GET /api/atendimentos e filtra no cliente.
   */
  async function fetchAtendimentosPorSetor(setorId) {
    loading.value = true;
    error.value   = null;
    try {
      // Tenta endpoint filtrado primeiro
      const { data } = await api.get('/atendimentos/por-setor', {
        params: { setor_id: setorId, status: 'internado' },
      });
      const lista = Array.isArray(data) ? data : data.data ?? [];
      atendimentos.value = lista.map(_normalizeAtendimento);
    } catch (err) {
      // Fallback: carrega tudo e filtra pelo setor no cliente
      if (err.response?.status === 404 || err.response?.status === 405) {
        try {
          const { data: all } = await api.get('/atendimentos');
          const lista = Array.isArray(all) ? all : all.data ?? [];
          atendimentos.value = lista
            .map(_normalizeAtendimento)
            .filter((a) => {
              const sid = a.alocacao_leito?.setor_id ?? a.setor_id ?? null;
              return sid === setorId;
            });
        } catch (fallbackErr) {
          error.value = fallbackErr.response?.data?.message ?? fallbackErr.message;
        }
      } else {
        error.value = err.response?.data?.message ?? err.message;
      }
    } finally {
      loading.value = false;
    }
  }

  /** Carrega um atendimento completo (prontuário) pelo ID */
  async function fetchAtendimento(id) {
    loading.value = true;
    error.value   = null;
    try {
      const { data } = await api.get(`/atendimentos/${id}`);
      atendimentoAtual.value = _normalizeAtendimento(data);
    } catch (err) {
      error.value = err.response?.data?.message ?? err.message;
      atendimentoAtual.value = null;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Admite um novo paciente → POST /api/atendimentos
   * Payload esperado: { paciente_id, hospital_id, setor_id, leito, protocolo_manchester, queixa_principal }
   */
  async function admitirPaciente(payload) {
    const body = {
      paciente_id:          payload.paciente_id,
      hospital_id:          payload.hospital_id,
      status:               'AGUARDANDO_TRIAGEM',
      protocolo_manchester: payload.protocolo_manchester ?? null,
      dados_iniciais: {
        queixa_principal: payload.queixa_principal ?? '',
      },
      alocacao_leito: {
        numero:   payload.leito ?? '',
        setor_id: payload.setor_id,
        setor:    payload.setor_nome ?? '',
        status:   'ocupado',
      },
      evolucoes_medicas:        [],
      administracao_enfermagem: [],
    };
    const { data } = await api.post('/atendimentos', body);
    const normalizado = _normalizeAtendimento(data);
    // Insere no topo da lista reativa
    atendimentos.value = [normalizado, ...atendimentos.value];
    return normalizado;
  }

  /**
   * Insere uma evolução médica → POST /api/atendimentos/{id}/evolucoes
   * Payload: { medico, crm, tipo, descricao }
   */
  async function pushEvolucao(atendimentoId, payload) {
    const { data } = await api.post(`/atendimentos/${atendimentoId}/evolucoes`, payload);
    // Atualiza array reativo localmente sem re-fetch
    if (atendimentoAtual.value?._id === atendimentoId) {
      atendimentoAtual.value.evolucoes_medicas = [
        data,
        ...(atendimentoAtual.value.evolucoes_medicas ?? []),
      ];
    }
    return data;
  }

  return {
    atendimentos,
    atendimentoAtual,
    loading,
    error,
    fetchAtendimentosPorSetor,
    fetchAtendimento,
    admitirPaciente,
    pushEvolucao,
  };
});
