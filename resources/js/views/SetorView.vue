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
        <button class="btn btn-ghost" @click="modalBusca = true">
          <UserPlus :size="16" /> Admitir Paciente
        </button>
      </div>
    </div>

    <ModalAdmitirPaciente
      v-if="setor"
      v-model="modalAdmitir"
      :setor-id="setorId"
      :setor-nome="setor.nome"
      :paciente-inicial="pacienteParaAdmitir"
      @admitido="onPacienteAdmitido"
    />

    <ModalBuscaPaciente
      v-model="modalBusca"
      @selecionado="onPacienteSelecionadoBusca"
      @cadastrar="onCadastrarNovoDaBusca"
    />

    <ModalNovoPaciente
      v-model="modalNovoPaciente"
      :setor-id="setorId"
      :setor-nome="setor?.nome"
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

    <div v-else-if="filteredPacientes.length === 0" class="empty-state">
      <UserX :size="36" class="empty-icon" />
      <p>Nenhum paciente encontrado com esses filtros.</p>
    </div>

    <div v-else class="patients-list">
      <PacienteCard
        v-for="item in filteredPacientes"
        :key="item.paciente._id"
        :paciente="item.paciente"
        :atendimento="item.atendimento"
        @click="onPacienteClick(item)"
      />
    </div>

    <!-- Toast Notification -->
    <Transition name="toast">
      <div v-if="toastMsg" class="toast-notification">
        <CheckCircle :size="16" />
        {{ toastMsg }}
      </div>
    </Transition>

  </main>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import { ArrowLeft, BedDouble, Users, Search, UserX, UserPlus, CheckCircle } from 'lucide-vue-next';
import { useHospitalStore } from '@/stores/hospital.js';
import { usePacienteStore } from '@/stores/paciente.js';
import Manchester from '@/components/setor/Manchester.vue';
import PacienteCard from '@/components/setor/PacienteCard.vue';
import ModalAdmitirPaciente from '@/components/setor/ModalAdmitirPaciente.vue';
import ModalNovoPaciente from '@/components/setor/ModalNovoPaciente.vue';
import ModalBuscaPaciente from '@/components/setor/ModalBuscaPaciente.vue';

const route = useRoute();
const router = useRouter();
const hospitalStore = useHospitalStore();
const pacienteStore = usePacienteStore();

const searchQuery     = ref('');
const manchesterFilter = ref('todos');
const modalAdmitir    = ref(false);
const modalNovoPaciente = ref(false);
const modalBusca      = ref(false);
const toastMsg        = ref('');
const pacienteParaAdmitir = ref(null);

const setor = computed(() => hospitalStore.getSetorById(route.params.id));
const setorId = computed(() => {
  const id = setor.value?._id ?? null;
  if (!id) return null;
  if (typeof id === 'string') return id;
  if (typeof id === 'object' && id.$oid) return id.$oid;
  return String(id);
});

const pacientesComAtendimento = computed(() => {
  const mapa = new Map();
  for (const atend of pacienteStore.atendimentos) {
    if (atend.paciente_id) mapa.set(atend.paciente_id, atend);
  }
  return pacienteStore.pacientes.map((paciente) => ({
    paciente,
    atendimento: mapa.get(paciente._id) ?? null,
  }));
});

const filteredPacientes = computed(() => {
  let list = pacientesComAtendimento.value;
  if (manchesterFilter.value !== 'todos') {
    list = list.filter((item) => {
      const code = item.atendimento?.protocolo_manchester
        || item.atendimento?.dados_iniciais?.protocolo_manchester;
      return code === manchesterFilter.value;
    });
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter((item) =>
      item.paciente?.nome?.toLowerCase().includes(q)
    );
  }
  return list;
});

async function load() {
  if (!hospitalStore.hospital) await hospitalStore.fetchHospital();
  const id = setorId.value ?? route.params.id;
  await pacienteStore.fetchAtendimentosPorSetor(id);
  await pacienteStore.fetchPacientesPorSetor(id);
}

function onPacienteAdmitido(atend) {
  // Atualiza o gráfico de ocupação do setor
  if (setor.value) {
    setor.value.ocupacao_atual = (setor.value.ocupacao_atual || 0) + 1;
  }
  
  // Garantir que o paciente admitido esteja na lista local de pacientes do setor
  if (atend.paciente) {
    const pId = atend.paciente._id || atend.paciente_id;
    if (!pacienteStore.pacientes.some(p => p._id === pId)) {
      pacienteStore.pacientes.unshift(atend.paciente);
    }
  }

  pacienteParaAdmitir.value = null;
  toastMsg.value = 'Paciente admitido com sucesso no setor!';
  setTimeout(() => toastMsg.value = '', 4000);
}

function onPacienteCriado(paciente) {
  toastMsg.value = `Paciente ${paciente.nome} cadastrado com sucesso!`;
  setTimeout(() => toastMsg.value = '', 4000);

  // Fecha o modal e oferece admissão imediata se quiser
  modalNovoPaciente.value = false;
  pacienteParaAdmitir.value = paciente;
  modalAdmitir.value = true;
  pacienteStore.fetchPacientesPorSetor(setorId.value ?? route.params.id);
}

function onPacienteClick(item) {
  if (item.atendimento?._id) {
    router.push(`/prontuario/${item.atendimento._id}`);
    return;
  }
  pacienteParaAdmitir.value = item.paciente;
  modalAdmitir.value = true;
}

function onPacienteSelecionadoBusca(paciente) {
  pacienteParaAdmitir.value = paciente;
  modalAdmitir.value = true;
}

function onCadastrarNovoDaBusca() {
  modalNovoPaciente.value = true;
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

/* Toast */
.toast-notification {
  position: fixed;
  bottom: 24px;
  right: 24px;
  background: #10B981;
  color: #fff;
  padding: 12px 20px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  z-index: 9999;
  font-weight: 500;
  font-size: 14px;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  transform: translateY(20px);
  opacity: 0;
}
</style>
