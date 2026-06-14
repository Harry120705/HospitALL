import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api.js';
import { mongoId } from '@/utils/mongo.js';

/**
 * Store de Hospital
 * Consome GET /api/hospitais (retorna array) e operações de setor.
 * Utiliza o PRIMEIRO hospital retornado pela API como hospital ativo.
 * Para múltiplos hospitais, use hospitalAtivo via setHospital(id).
 */
export const useHospitalStore = defineStore('hospital', () => {
  const hospital  = ref(null);
  const hospitais = ref([]);
  const loading   = ref(false);
  const error     = ref(null);

  // ── Computed ──────────────────────────────────────────────────
  const setores      = computed(() => hospital.value?.setores ?? []);
  const nomeHospital = computed(() => hospital.value?.nome    ?? 'HospitALL');
  const unidade      = computed(() => hospital.value?.unidade ?? '');

  const stats = computed(() => {
    const todos = setores.value;
    const totalLeitos    = todos.reduce((s, t) => s + (t.capacidade_maxima ?? 0), 0);
    const totalOcupados  = todos.reduce((s, t) => s + (t.ocupacao_atual    ?? 0), 0);
    const pct = totalLeitos ? Math.round((totalOcupados / totalLeitos) * 100) : 0;
    return {
      total_leitos:          totalLeitos,
      pacientes_internados:  totalOcupados,
      atendimentos_hoje:     null,   // ← endpoint futuro: /api/dashboard/stats
      variacao_atendimentos: null,
      pct_ocupacao:          pct,
    };
  });

  // ── Actions ───────────────────────────────────────────────────

  /** Carrega todos os hospitais e define o primeiro como ativo */
  async function fetchHospital(id = null) {
    loading.value = true;
    error.value   = null;
    try {
      if (id) {
        const { data } = await api.get(`/hospitais/${id}`);
        hospital.value = _normalizeHospital(data);
      } else {
        const { data } = await api.get('/hospitais');
        hospitais.value = (Array.isArray(data) ? data : data.data ?? []).map(_normalizeHospital);
        hospital.value  = hospitais.value[0] ?? null;
      }

      // Recontagem dinâmica da ocupação dos setores
      try {
        const { data: atendimentosData } = await api.get('/atendimentos');
        const listaAtendimentos = Array.isArray(atendimentosData) ? atendimentosData : atendimentosData.data ?? [];
        if (hospital.value && hospital.value.setores) {
          hospital.value.setores.forEach(setor => {
            // Zera estritamente antes de contar
            setor.ocupacao_atual = 0;
            const count = listaAtendimentos.filter(a => {
              const sid = a.alocacao_leito?.setor_id ?? a.setor_id ?? null;
              if (!sid) return false;
              const status = String(a.status).toUpperCase();
              return mongoId(sid) === String(setor._id) && status !== 'ALTA' && status !== 'OBITO';
            }).length;
            setor.ocupacao_atual = count;
          });
        }
      } catch (errOcc) {
        console.warn('Não foi possível recalcular a ocupação:', errOcc);
        // Mesmo em erro, garante que a ocupação seja limpa se não for confiável
        if (hospital.value && hospital.value.setores) {
          hospital.value.setores.forEach(setor => { setor.ocupacao_atual = 0; });
        }
      }
    } catch (err) {
      error.value = err.response?.data?.message ?? err.message;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Adiciona um novo setor ao hospital ativo via POST.
   * Se a store ainda não tiver um hospital carregado, tenta buscar
   * automaticamente antes de lançar erro. Usa ID de fallback conhecido
   * caso o GET /hospitais retorne vazio.
   */
  async function adicionarSetor(payload) {
    // ── Garante que há um hospital ativo ──────────────────────────
    if (!hospital.value) {
      await fetchHospital(); // tenta carregar o primeiro da lista
    }

    // Fallback: ID do hospital padrão conhecido no banco
    const FALLBACK_ID = '6a00cba8f2bee9d15eff5191';

    if (!hospital.value) {
      // Ainda sem hospital — tenta buscar pelo ID fixo de fallback
      try {
        await fetchHospital(FALLBACK_ID);
      } catch {
        // ignora e deixa o próximo throw tratar
      }
    }

    if (!hospital.value) {
      throw new Error(
        'Não foi possível identificar o hospital ativo. ' +
        'Verifique se o banco MongoDB está acessível e se a coleção HOSPITAIS possui documentos.'
      );
    }

    const hospitalId = hospital.value._id || hospital.value.id;

    if (!hospitalId) {
      throw new Error(
        'Hospital carregado mas sem _id válido. ' +
        'Verifique se o campo _id está sendo retornado pela API.'
      );
    }

    const { data } = await api.post(`/hospitais/${hospitalId}/setores`, payload);

    // Atualiza array reativo localmente (sem re-fetch completo)
    const newSetor = { ...data, _id: mongoId(data) };
    hospital.value = {
      ...hospital.value,
      setores: [...(hospital.value.setores ?? []), newSetor],
    };

    return newSetor;
  }

  async function editarSetor(setorId, payload) {
    const hospitalId = hospital.value?._id || hospital.value?.id;
    if (!hospitalId) throw new Error('Hospital ativo não encontrado.');
    const { data } = await api.put(`/hospitais/${hospitalId}/setores/${setorId}`, payload);
    if (hospital.value && hospital.value.setores) {
      const idx = hospital.value.setores.findIndex(s => s._id === setorId);
      if (idx !== -1) {
        // Mantém a ocupação atual se não vier no payload
        const prevOcupacao = hospital.value.setores[idx].ocupacao_atual;
        hospital.value.setores[idx] = { ...hospital.value.setores[idx], ...data, _id: mongoId(data) };
        if (hospital.value.setores[idx].ocupacao_atual === undefined) {
          hospital.value.setores[idx].ocupacao_atual = prevOcupacao;
        }
      }
    }
    return data;
  }

  async function excluirSetor(setorId) {
    const hospitalId = hospital.value?._id || hospital.value?.id;
    if (!hospitalId) throw new Error('Hospital ativo não encontrado.');
    await api.delete(`/hospitais/${hospitalId}/setores/${setorId}`);
    if (hospital.value && hospital.value.setores) {
      hospital.value.setores = hospital.value.setores.filter(s => s._id !== setorId);
    }
  }

  function getSetorById(id) {
    return setores.value.find((s) => mongoId(s) === id) ?? null;
  }

  // ── Helpers privados ──────────────────────────────────────────

  /** Normaliza o _id do MongoDB: extrai string de { $oid: '...' } ou usa direto */
  function _normalizeHospital(h) {
    return {
      ...h,
      _id: mongoId(h),
      setores: (h.setores ?? []).map((s) => ({ ...s, _id: mongoId(s) || s.id_setor })),
    };
  }

  return {
    hospital,
    hospitais,
    loading,
    error,
    stats,
    setores,
    nomeHospital,
    unidade,
    fetchHospital,
    adicionarSetor,
    editarSetor,
    excluirSetor,
    getSetorById,
  };
});
