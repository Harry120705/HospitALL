<template>
  <main class="page-content">

    <!-- Back -->
    <RouterLink to="/" class="back-link">
      <ArrowLeft :size="15" /> Áreas
    </RouterLink>

    <!-- Page Header -->
    <div class="page-header" v-if="setor">
      <div>
        <p class="section-label">Setor</p>
        <h1 class="page-title">{{ setor.nome }}</h1>
        <p class="page-subtitle">{{ setor.descricao }}</p>
      </div>
      <div style="display: flex; gap: 8px;">
        <button class="btn btn-ghost" @click="modalNovoPaciente = true">
          <UserPlus :size="16" /> Novo Paciente
        </button>
        <button class="btn btn-primary" id="btn-admitir-paciente" @click="modalAdmitir = true">
          <Plus :size="16" /> Admitir paciente
        </button>
      </div>
    </div>

    <ModalAdmitirPaciente
      v-if="setor"
      v-model="modalAdmitir"
      :setor-id="setor._id"
      :setor-nome="setor.nome"
      @admitido="onPacienteAdmitido"
    />

    <ModalNovoPaciente
      v-model="modalNovoPaciente"
      @criado="onPacienteCriado"
    />

    <!-- Stat Cards -->
    <div class="stats-grid" v-if="setor">
      <div class="card setor-stat">
        <div class="setor-stat__icon" style="background:#E0F2FE">
          <BedDouble :size="20" color="#0EA5E9" />
        </div>
        <div>
          <p class="setor-stat__label">Total de leitos</p>
          <p class="setor-stat__value">{{ setor.capacidade_maxima }}</p>
        </div>
      </div>
      <div class="card setor-stat">
        <div class="setor-stat__icon" style="background:#FFF7ED">
          <Users :size="20" color="#F97316" />
        </div>
        <div>
          <p class="setor-stat__label">Pacientes atuais</p>
          <p class="setor-stat__value">{{ setor.ocupacao_atual }}</p>
        </div>
      </div>
      <div class="card setor-stat">
        <div class="setor-stat__icon" style="background:#ECFDF5">
          <BedDouble :size="20" color="#22C55E" />
        </div>
        <div>
          <p class="setor-stat__label">Leitos disponíveis</p>
          <p class="setor-stat__value">{{ setor.capacidade_maxima - setor.ocupacao_atual }}</p>
        </div>
      </div>
    </div>

    <!-- Search + Manchester Filters -->
    <div class="filters-bar card">
      <div class="input-search filters-search">
        <Search :size="15" class="search-icon-sm" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Buscar paciente por nome..."
        />
      </div>
      <Manchester v-model="manchesterFilter" />
    </div>

    <!-- Patient List -->
    <div v-if="pacienteStore.loading" class="loading-state">
      <div v-for="n in 4" :key="n" class="card skeleton-card skeleton-pac"></div>
    </div>

    <div v-else-if="filteredAtendimentos.length === 0" class="empty-state">
      <UserX :size="36" class="empty-icon" />
      <p>Nenhum paciente encontrado com esses filtros.</p>
    </div>

    <div v-else class="patients-list">
      <PacienteCard
        v-for="atend in filteredAtendimentos"
        :key="atend._id"
        :atendimento="atend"
      />
    </div>

  </main>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import { ArrowLeft, Plus, BedDouble, Users, Search, UserX, UserPlus } from 'lucide-vue-next';
import { useHospitalStore } from '@/stores/hospital.js';
import { usePacienteStore } from '@/stores/paciente.js';
import Manchester from '@/components/setor/Manchester.vue';
import PacienteCard from '@/components/setor/PacienteCard.vue';
import ModalAdmitirPaciente from '@/components/setor/ModalAdmitirPaciente.vue';
import ModalNovoPaciente from '@/components/setor/ModalNovoPaciente.vue';

const route = useRoute();
const hospitalStore = useHospitalStore();
const pacienteStore = usePacienteStore();

const searchQuery     = ref('');
const manchesterFilter = ref('todos');
const modalAdmitir    = ref(false);
const modalNovoPaciente = ref(false);

const setor = computed(() => hospitalStore.getSetorById(route.params.id));

const filteredAtendimentos = computed(() => {
  let list = pacienteStore.atendimentos;
  if (manchesterFilter.value !== 'todos') {
    list = list.filter((a) => a.protocolo_manchester === manchesterFilter.value);
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter((a) => a.paciente?.nome?.toLowerCase().includes(q));
  }
  return list;
});

async function load() {
  if (!hospitalStore.hospital) await hospitalStore.fetchHospital();
  await pacienteStore.fetchAtendimentosPorSetor(route.params.id);
}

function onPacienteAdmitido(atend) {
  // Store já insere no topo da lista reativamente
  console.info('Paciente admitido:', atend._id);
}

function onPacienteCriado(paciente) {
  console.info('Novo paciente criado:', paciente.nome);
  // Pode fechar o modal e opcionalmente abrir o modal de admissão:
  modalNovoPaciente.value = false;
  modalAdmitir.value = true;
}

onMounted(load);
watch(() => route.params.id, load);
</script>

<style scoped>
.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: var(--color-text-secondary);
  text-decoration: none;
  margin-bottom: 20px;
  transition: color var(--transition-fast);
}

.back-link:hover { color: var(--color-primary); }

/* Page Header */
.page-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
}

.page-title {
  font-size: 30px;
  font-weight: 700;
  color: var(--color-text-primary);
  letter-spacing: -0.7px;
  margin-top: 4px;
  margin-bottom: 4px;
}

.page-subtitle {
  font-size: 14px;
  color: var(--color-primary);
}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.setor-stat {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 20px;
}

.setor-stat__icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.setor-stat__label {
  font-size: 13px;
  color: var(--color-text-secondary);
}

.setor-stat__value {
  font-size: 30px;
  font-weight: 700;
  color: var(--color-text-primary);
  letter-spacing: -0.5px;
  line-height: 1.1;
}

/* Filters */
.filters-bar {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 16px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.filters-search {
  flex: 1;
  min-width: 200px;
}

.search-icon-sm { color: var(--color-text-muted); }

/* Patients list */
.patients-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Loading skeleton */
.skeleton-pac { height: 72px; }

@keyframes shimmer {
  0%   { background-color: #f1f5f9; }
  50%  { background-color: #e2e8f0; }
  100% { background-color: #f1f5f9; }
}

.skeleton-card { animation: shimmer 1.4s infinite; border-radius: var(--radius-md); }

.loading-state {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Empty state */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--color-text-secondary);
}

.empty-icon {
  margin: 0 auto 12px;
  color: var(--color-text-muted);
}
</style>
