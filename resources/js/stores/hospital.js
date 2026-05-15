import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api.js';

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
    hospital.value = {
      ...hospital.value,
      setores: [...(hospital.value.setores ?? []), data],
    };

    return data;
  }

  function getSetorById(id) {
    return setores.value.find((s) => _mongoId(s) === id) ?? null;
  }

  // ── Helpers privados ──────────────────────────────────────────

  /** Normaliza o _id do MongoDB: extrai string de { $oid: '...' } ou usa direto */
  function _normalizeHospital(h) {
    return {
      ...h,
      _id: _mongoId(h),
      setores: (h.setores ?? []).map((s) => ({ ...s, _id: _mongoId(s) })),
    };
  }

  function _mongoId(obj) {
    if (!obj) return null;
    const id = obj._id || obj.id;
    if (!id) return null;
    if (typeof id === 'string') return id;
    if (typeof id === 'object' && id.$oid) return id.$oid;
    return String(id);
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
    getSetorById,
  };
});
