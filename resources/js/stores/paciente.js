import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/services/api.js';
import { mongoId } from '@/utils/mongo.js';

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
  const pacientes        = ref([]);
  const atendimentoAtual = ref(null);
  const loading          = ref(false);
  const error            = ref(null);

  // ── Helpers ───────────────────────────────────────────────────

  function _normalizeAtendimento(a) {
    const norm = {
      ...a,
      _id:         mongoId(a),
      paciente_id: mongoId(a.paciente_id),
    };
    // Normaliza _id do paciente embutido se existir
    if (norm.paciente) {
      norm.paciente = { ...norm.paciente, _id: mongoId(norm.paciente) };
    }
    return norm;
  }

  function _normalizePaciente(p) {
    const norm = {
      ...p,
      _id: mongoId(p),
      setor_id: mongoId(p?.setor_id),
    };
    if (norm.resumo_ultimas_visitas && Array.isArray(norm.resumo_ultimas_visitas)) {
      norm.resumo_ultimas_visitas = norm.resumo_ultimas_visitas.map(visita => ({
        ...visita,
        atendimento_id: mongoId(visita.atendimento_id)
      }));
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

  /** Carrega pacientes por setor (criados no setor) */
  async function fetchPacientesPorSetor(setorId) {
    loading.value = true;
    error.value   = null;
    try {
      const { data } = await api.get('/pacientes');
      const lista = Array.isArray(data) ? data : data.data ?? [];
      pacientes.value = lista
        .map(_normalizePaciente)
        .filter((p) => (p.setor_id ?? null) === setorId);
    } catch (err) {
      error.value = err.response?.data?.message ?? err.message;
    } finally {
      loading.value = false;
    }
  }

  /** Carrega um paciente pelo ID */
  async function fetchPaciente(id) {
    loading.value = true;
    error.value   = null;
    try {
      const { data } = await api.get(`/pacientes/${id}`);
      return _normalizePaciente(data);
    } catch (err) {
      error.value = err.response?.data?.message ?? err.message;
      return null;
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

  async function darAlta(atendimentoId) {
    loading.value = true;
    error.value = null;
    try {
      const { data } = await api.put(`/atendimentos/${atendimentoId}`, { status: 'ALTA' });
      if (atendimentoAtual.value && atendimentoAtual.value._id === atendimentoId) {
        atendimentoAtual.value.status = 'ALTA';
      }
      return data;
    } catch (err) {
      error.value = err.response?.data?.message ?? err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function editarPaciente(id, payload) {
    loading.value = true;
    error.value = null;
    try {
      const { data } = await api.put(`/pacientes/${id}`, payload);
      // Atualiza estado local se o paciente em exibição for o editado
      if (atendimentoAtual.value?.paciente?._id === id) {
        atendimentoAtual.value.paciente = { ...atendimentoAtual.value.paciente, ...data };
      }
      return data;
    } catch (err) {
      error.value = err.response?.data?.message ?? err.message;
      throw err;
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
      dados_iniciais: {
        queixa_principal: payload.queixa_principal ?? '',
        protocolo_manchester: payload.protocolo_manchester ?? null,
        paciente_nome: payload.paciente_obj?.nome ?? '',
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
    
    if (!data.paciente && payload.paciente_obj) {
      data.paciente = payload.paciente_obj;
    }
    
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

  async function editarEvolucao(idEvolucao, descricao) {
    if (!atendimentoAtual.value) return;
    try {
      await api.put(`/atendimentos/${atendimentoAtual.value._id}/evolucoes/${idEvolucao}`, { descricao });
      const idx = atendimentoAtual.value.evolucoes_medicas?.findIndex(e => e.id === idEvolucao);
      if (idx !== undefined && idx !== -1) {
        atendimentoAtual.value.evolucoes_medicas[idx].descricao = descricao;
      }
    } catch (err) {
      error.value = err.response?.data?.message ?? err.message;
      throw err;
    }
  }

  return {
    atendimentos,
    pacientes,
    atendimentoAtual,
    loading,
    error,
    fetchAtendimentosPorSetor,
    fetchPacientesPorSetor,
    fetchPaciente,
    fetchAtendimento,
    darAlta,
    editarPaciente,
    admitirPaciente,
    pushEvolucao,
    editarEvolucao,
  };
});
