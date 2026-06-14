<template>
  <main class="page-content">

    <!-- Page Header -->
    <div class="page-header">
      <div style="flex:1">
        <p class="section-label">Dashboard de Gestão</p>
        <h1 class="page-title">Áreas Hospitalares</h1>
        <p class="page-subtitle" v-if="!hospitalStore.loading">
          {{ hospitalStore.setores.length }} setores ativos •
          {{ hospitalStore.stats?.pacientes_internados ?? '—' }} de
          {{ hospitalStore.stats?.total_leitos ?? '—' }} leitos ocupados
        </p>
        <div v-else class="skeleton skeleton-subtitle"></div>
      </div>

      <div class="input-search" style="max-width: 300px; margin-right: 16px;">
        <Search :size="16" style="color: var(--color-text-muted); flex-shrink: 0;" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Buscar área por nome..."
        />
      </div>

      <button class="btn btn-primary" id="btn-cadastrar-area" @click="modalArea = true">
        <Plus :size="16" />
        Cadastrar nova área
      </button>
    </div>

    <!-- Modal Nova Área -->
    <ModalNovaArea v-model="modalArea" @criado="onAreaCriada" />

    <!-- Modal Editar Área -->
    <ModalEditarArea v-model="modalEditarArea" :initialData="areaToEdit" @editado="onAreaEditada" />

    <!-- Modal Confirmacao/Edicao -->
    <ModalConfirmacao
      v-model="modalConfirmOpen"
      :title="modalConfirmTitle"
      :message="modalConfirmMessage"
      :type="modalConfirmType"
      :confirm-text="modalConfirmBtn"
      :with-input="modalConfirmWithInput"
      :initial-value="modalConfirmInitial"
      :input-placeholder="modalConfirmPlaceholder"
      :loading="modalConfirmLoading"
      @confirm="handleConfirm"
    />

    <!-- Stat Cards -->
    <div class="stats-grid">
      <template v-if="!hospitalStore.loading">
        <StatCard
          :icon="BedDouble"
          label="Total de leitos"
          :value="hospitalStore.stats?.total_leitos ?? '—'"
          subtext="capacidade da unidade"
          iconBg="#E0F2FE"
          iconColor="#0EA5E9"
        />
        <StatCard
          :icon="Users"
          label="Pacientes internados"
          :value="hospitalStore.stats?.pacientes_internados ?? '—'"
          :subtext="hospitalStore.stats?.pct_ocupacao != null ? `${hospitalStore.stats.pct_ocupacao}% de ocupação` : 'calculando...'"
          iconBg="#FFF7ED"
          iconColor="#F97316"
        />
        <StatCard
          :icon="TrendingUp"
          label="Atendimentos hoje"
          :value="hospitalStore.stats?.atendimentos_hoje ?? '—'"
          :subtext="hospitalStore.stats?.variacao_atendimentos ? `${hospitalStore.stats.variacao_atendimentos} vs. ontem` : 'endpoint futuro'"
          subtextVariant="positive"
          iconBg="#ECFDF5"
          iconColor="#22C55E"
        />
      </template>
      <template v-else>
        <div v-for="n in 3" :key="n" class="card skeleton-card"></div>
      </template>
    </div>

    <!-- Area Cards Grid -->
    <div v-if="!hospitalStore.loading" class="areas-grid">
      <AreaCard
        v-for="setor in filteredSetores"
        :key="setor._id"
        :setor="setor"
        @edit="onEdit"
        @report="onReport"
        @delete="onDelete"
      />
    </div>
    <div v-else class="areas-grid">
      <div v-for="n in 8" :key="n" class="card skeleton-card skeleton-area"></div>
    </div>

  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Plus, BedDouble, Users, TrendingUp, Search } from 'lucide-vue-next';
import { useHospitalStore } from '@/stores/hospital.js';
import { useToastStore } from '@/stores/toast.js';
import StatCard from '@/components/dashboard/StatCard.vue';
import AreaCard from '@/components/dashboard/AreaCard.vue';
import ModalNovaArea from '@/components/dashboard/ModalNovaArea.vue';
import ModalEditarArea from '@/components/dashboard/ModalEditarArea.vue';
import ModalConfirmacao from '@/components/ui/ModalConfirmacao.vue';

const hospitalStore = useHospitalStore();
const toastStore = useToastStore();
const modalArea = ref(false);
const modalEditarArea = ref(false);
const areaToEdit = ref({});
const searchQuery = ref('');

// Modal Genérico state
const modalConfirmOpen = ref(false);
const modalConfirmTitle = ref('');
const modalConfirmMessage = ref('');
const modalConfirmType = ref('primary');
const modalConfirmBtn = ref('Confirmar');
const modalConfirmWithInput = ref(false);
const modalConfirmInitial = ref('');
const modalConfirmPlaceholder = ref('');
const modalConfirmLoading = ref(false);

let pendingAction = null;
let pendingSetor = null;

const filteredSetores = computed(() => {
  if (!searchQuery.value) return hospitalStore.setores;
  const q = searchQuery.value.toLowerCase();
  return hospitalStore.setores.filter(s => s.nome.toLowerCase().includes(q));
});

onMounted(() => {
  hospitalStore.fetchHospital();
});

function onAreaCriada(setor) {
  toastStore.success(`Área ${setor.nome} cadastrada com sucesso!`);
}

function onEdit(setor)   { 
  areaToEdit.value = setor;
  modalEditarArea.value = true;
}

function onReport(setor) { 
  toastStore.success(`Gerando relatório de ${setor.nome}...`);
}

function onAreaEditada(setor) {
  // O modal já dispara o toast, podemos adicionar lógica extra se necessário.
}

function onDelete(setor) { 
  pendingAction = 'delete';
  pendingSetor = setor;
  modalConfirmTitle.value = 'Excluir Área';
  modalConfirmMessage.value = `Atenção: Tem certeza que deseja excluir permanentemente a área ${setor.nome}? Esta ação não pode ser desfeita.`;
  modalConfirmType.value = 'danger';
  modalConfirmBtn.value = 'Excluir Permanentemente';
  modalConfirmWithInput.value = false;
  modalConfirmOpen.value = true;
}

async function handleConfirm(inputValue) {
  if (!pendingSetor) return;
  modalConfirmLoading.value = true;

  try {
    if (pendingAction === 'delete') {
      await hospitalStore.excluirSetor(pendingSetor._id);
      toastStore.success('Área excluída com sucesso!');
    }
    modalConfirmOpen.value = false;
  } catch(e) {
    toastStore.error('Erro: ' + e.message);
  } finally {
    modalConfirmLoading.value = false;
    pendingAction = null;
    pendingSetor = null;
  }
}
</script>

<style scoped>
/* Page Header */
.page-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 28px;
  gap: 16px;
}

.page-title {
  font-size: 32px;
  font-weight: 700;
  color: var(--color-text-primary);
  letter-spacing: -0.8px;
  margin-top: 4px;
  margin-bottom: 6px;
}

.page-subtitle {
  font-size: 14px;
  color: var(--color-text-secondary);
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 28px;
}

/* Areas Grid */
.areas-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

/* Skeleton Loaders */
.skeleton-card {
  height: 100px;
  animation: shimmer 1.4s infinite;
}

.skeleton-area {
  height: 190px;
}

.skeleton-subtitle {
  height: 16px;
  width: 240px;
  background: var(--color-border);
  border-radius: 4px;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  0%   { background-color: #f1f5f9; }
  50%  { background-color: #e2e8f0; }
  100% { background-color: #f1f5f9; }
}

/* Responsive */
@media (max-width: 1200px) {
  .areas-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 900px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .areas-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
  .stats-grid { grid-template-columns: 1fr; }
  .areas-grid { grid-template-columns: 1fr; }
  .page-header { flex-direction: column; align-items: flex-start; }
}
</style>
