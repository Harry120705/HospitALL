<template>
  <main class="page-content">

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <p class="section-label">Dashboard de Gestão</p>
        <h1 class="page-title">Áreas Hospitalares</h1>
        <p class="page-subtitle" v-if="!hospitalStore.loading">
          {{ hospitalStore.setores.length }} setores ativos •
          {{ hospitalStore.stats?.pacientes_internados ?? '—' }} de
          {{ hospitalStore.stats?.total_leitos ?? '—' }} leitos ocupados
        </p>
        <div v-else class="skeleton skeleton-subtitle"></div>
      </div>

      <button class="btn btn-primary" id="btn-cadastrar-area" @click="modalArea = true">
        <Plus :size="16" />
        Cadastrar nova área
      </button>
    </div>

    <!-- Modal Nova Área -->
    <ModalNovaArea v-model="modalArea" @criado="onAreaCriada" />

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
        v-for="setor in hospitalStore.setores"
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
import { ref, onMounted } from 'vue';
import { Plus, BedDouble, Users, TrendingUp } from 'lucide-vue-next';
import { useHospitalStore } from '@/stores/hospital.js';
import StatCard from '@/components/dashboard/StatCard.vue';
import AreaCard from '@/components/dashboard/AreaCard.vue';
import ModalNovaArea from '@/components/dashboard/ModalNovaArea.vue';

const hospitalStore = useHospitalStore();
const modalArea = ref(false);

onMounted(() => {
  hospitalStore.fetchHospital();
});

function onAreaCriada(setor) {
  // Store já atualiza setores reativamente — nada a fazer aqui
  console.info('Setor criado:', setor.nome);
}

function onEdit(setor)   { 
  alert(`Funcionalidade de Editar Área (${setor.nome}) em desenvolvimento pelo backend.`); 
}
function onReport(setor) { 
  alert(`Relatório da Área (${setor.nome}) em desenvolvimento.`); 
}
function onDelete(setor) { 
  if(confirm(`Tem certeza que deseja excluir a área ${setor.nome}? Essa ação precisará de um endpoint no backend.`)) {
    alert('A exclusão precisa ser implementada na API do backend.');
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
