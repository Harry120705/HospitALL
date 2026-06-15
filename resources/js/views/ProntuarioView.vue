<template>
  <main class="prontuario-layout">

    <!-- Sidebar Esquerda -->
    <aside class="prontuario-aside">
      <!-- Back -->
      <RouterLink :to="backLink" class="back-link">
        <ArrowLeft :size="15" /> Voltar para Pacientes
      </RouterLink>

      <div class="aside-section-label">
        <FileText :size="13" /> Prontuário Médico
      </div>

      <!-- Leito info -->
      <div class="leito-badge" v-if="atendimento">
        <BedDouble :size="14" />
        Leito {{ atendimento.alocacao_leito?.numero ?? '—' }}
        <span class="leito-setor">· {{ atendimento.alocacao_leito?.setor ?? '—' }}</span>
      </div>

      <template v-if="atendimento">
        <DadosGerais :paciente="pacienteView" @edit="modalEditarPaciente = true" />
        <DadosClinicos :dadosClinicosFixos="dadosClinicos" @edit="modalEditarPaciente = true" />
        <ResumoVisitas :visitas="pacienteView.resumo_ultimas_visitas" />
      </template>

      <template v-else-if="pacienteStore.loading">
        <div class="card skeleton-card" style="height:160px; margin-bottom:12px;"></div>
        <div class="card skeleton-card" style="height:200px;"></div>
      </template>
    </aside>

    <!-- Área Direita: Timeline -->
    <section class="prontuario-main">

      <!-- Header inline -->
      <div class="prontuario-main__header" v-if="atendimento">
        <div>
          <p class="section-label" style="margin-bottom:2px">Evoluções do Atendimento</p>
          <h1 class="prontuario-main__title">{{ pacienteView.nome }}</h1>
        </div>
        <div class="prontuario-actions">
          <span class="badge" :style="manchesterStyle">
            <span class="badge-dot" :style="{ background: manchesterColor }"></span>
            {{ manchesterLabel }}
          </span>

          <select 
            class="status-select"
            v-if="appStore.userRole !== 'RECEPCIONISTA' && atendimento.status !== 'ALTA'"
            :value="atendimento.status"
            @change="mudarStatus($event.target.value)"
          >
            <option value="AGUARDANDO_TRIAGEM">Aguardando Triagem</option>
            <option value="EM_ATENDIMENTO">Em Atendimento</option>
            <option value="INTERNADO">Internado</option>
          </select>

          <button v-if="appStore.userRole !== 'RECEPCIONISTA' && atendimento.status !== 'ALTA'" class="btn btn-outline" @click="confirmarAlta">
            <LogOut :size="15" /> Dar Alta
          </button>

          <button v-if="appStore.userRole !== 'RECEPCIONISTA' && atendimento.status !== 'ALTA'" class="btn btn-primary" id="btn-nova-evolucao" @click="modalEvolucao = true">
            <Plus :size="15" /> Nova evolução
          </button>
        </div>
      </div>

      <!-- Modal Nova Evolução -->
      <ModalNovaEvolucao
        v-if="atendimento"
        v-model="modalEvolucao"
        :atendimento-id="atendimento._id"
        @criada="onEvolucaoCriada"
      />

      <!-- Modal Confirmar Alta -->
      <ModalConfirmacao
        v-model="modalAltaOpen"
        title="Dar Alta"
        message="Tem certeza que deseja registrar a alta e encerrar o atendimento deste paciente?"
        type="primary"
        confirm-text="Confirmar Alta"
        :loading="loadingAlta"
        @confirm="realizarAlta"
      />

      <!-- Modal Editar Paciente -->
      <ModalEditarPaciente
        v-if="paciente"
        v-model="modalEditarPaciente"
        :paciente="paciente"
      />

      <!-- Queixa principal -->
      <div class="queixa-card card" v-if="atendimento">
        <span class="queixa-label">
          <AlertCircle :size="13" /> Queixa principal
        </span>
        <p class="queixa-text">{{ atendimento.dados_iniciais?.queixa_principal ?? '—' }}</p>
      </div>

      <!-- Timelines -->
      <template v-if="atendimento">
        <TimelineEvolution :evolucoes="atendimento.evolucoes_medicas ?? []" />
        <TimelineEnfermagem :registros="atendimento.administracao_enfermagem ?? []" />
      </template>

      <template v-else-if="pacienteStore.loading">
        <div class="card skeleton-card" style="height:120px; margin-bottom:12px;"></div>
        <div class="card skeleton-card" style="height:200px;"></div>
      </template>

      <div v-else-if="!atendimento && !pacienteStore.loading" class="empty-state">
        <FileX :size="36" class="empty-icon" />
        <p>Atendimento não encontrado.</p>
      </div>

    </section>
  </main>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import { ArrowLeft, BedDouble, Plus, FileText, AlertCircle, FileX, LogOut } from 'lucide-vue-next';
import { usePacienteStore } from '@/stores/paciente.js';
import { useHospitalStore } from '@/stores/hospital.js';
import { useToastStore } from '@/stores/toast.js';
import { MANCHESTER_MAP } from '@/constants/manchester.js';
import DadosGerais from '@/components/prontuario/DadosGerais.vue';
import DadosClinicos from '@/components/prontuario/DadosClinicos.vue';
import ResumoVisitas from '@/components/prontuario/ResumoVisitas.vue';
import TimelineEvolution from '@/components/prontuario/TimelineEvolution.vue';
import TimelineEnfermagem from '@/components/prontuario/TimelineEnfermagem.vue';
import ModalNovaEvolucao from '@/components/prontuario/ModalNovaEvolucao.vue';
import ModalConfirmacao from '@/components/ui/ModalConfirmacao.vue';
import ModalEditarPaciente from '@/components/prontuario/ModalEditarPaciente.vue';
import { useAppStore } from '@/stores/app.js';

const route = useRoute();
const pacienteStore = usePacienteStore();
const hospitalStore = useHospitalStore();
const toastStore = useToastStore();
const appStore = useAppStore();

const modalEvolucao = ref(false);
const modalAltaOpen = ref(false);
const modalEditarPaciente = ref(false);
const loadingAlta = ref(false);

const atendimento = computed(() => pacienteStore.atendimentoAtual);
const paciente    = computed(() => atendimento.value?.paciente ?? null);
const pacienteView = computed(() =>
  paciente.value
  ?? {
    nome: atendimento.value?.dados_iniciais?.paciente_nome ?? 'Paciente',
    data_nascimento: null,
  }
);
const dadosClinicos = computed(() =>
  pacienteView.value?.dados_clinicos_fixos ?? pacienteView.value ?? {}
);

function onEvolucaoCriada(ev) {
  // Store já insere no topo do array reativamente
  console.info('Evolução registrada:', ev.id);
}

function confirmarAlta() {
  modalAltaOpen.value = true;
}

async function mudarStatus(novoStatus) {
  try {
    await pacienteStore.atualizarStatusAtendimento(atendimento.value._id, novoStatus);
    toastStore.success(`Status alterado para: ${novoStatus}`);
  } catch (e) {
    toastStore.error('Erro ao alterar status: ' + e.message);
  }
}

async function realizarAlta() {
  loadingAlta.value = true;
  try {
    await pacienteStore.darAlta(atendimento.value._id);
    
    // Decrementa a ocupação do setor localmente para atualizar a interface imediatamente
    const idSetor = atendimento.value?.alocacao_leito?.setor_id;
    if (idSetor && hospitalStore.setores) {
      const setorObj = hospitalStore.setores.find(s => {
        const sid = s._id?.$oid || s._id || s.id;
        return String(sid) === String(idSetor);
      });
      if (setorObj && setorObj.ocupacao_atual > 0) {
        setorObj.ocupacao_atual--;
      }
    }

    toastStore.success('Alta registrada com sucesso!');
    modalAltaOpen.value = false;
  } catch (e) {
    toastStore.error('Erro ao registrar alta: ' + e.message);
  } finally {
    loadingAlta.value = false;
  }
}

const backLink = computed(() => {
  const idSetor = atendimento.value?.alocacao_leito?.setor_id;
  return idSetor ? `/setor/${idSetor}` : '/';
});

const manchesterData  = computed(() =>
  MANCHESTER_MAP[atendimento.value?.protocolo_manchester] ?? { label: '—', color: '#94A3B8' }
);
const manchesterLabel = computed(() => manchesterData.value.label);
const manchesterColor = computed(() => manchesterData.value.color);
const manchesterStyle = computed(() => ({
  background: manchesterData.value.color + '22',
  color: manchesterData.value.color,
}));

onMounted(async () => {
  if (!hospitalStore.hospital) {
    await hospitalStore.fetchHospital();
  }
  await pacienteStore.fetchAtendimento(route.params.atendimentoId);
});

async function hydratePaciente(atual) {
  if (!atual?.paciente_id) return;
  const pacienteCarregado = await pacienteStore.fetchPaciente(atual.paciente_id);
  if (pacienteCarregado && pacienteStore.atendimentoAtual?._id === atual._id) {
    pacienteStore.atendimentoAtual.paciente = pacienteCarregado;
  }
}

watch(
  () => atendimento.value,
  (atual) => {
    if (!atual) return;
    if (!atual.paciente || !atual.paciente?.contato) {
      hydratePaciente(atual);
    }
  },
  { immediate: true }
);
</script>

<style scoped>
/* Two-column prontuário layout */
.prontuario-layout {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 24px;
  padding: 28px 32px;
  max-width: 100%;
  width: 100%;
  margin: 0;
  min-height: calc(100vh - 64px);
  align-items: start;
}

/* Aside */
.prontuario-aside {
  display: flex;
  flex-direction: column;
  gap: 12px;
  position: sticky;
  top: 80px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: var(--color-text-secondary);
  text-decoration: none;
  transition: color var(--transition-fast);
  margin-bottom: 4px;
}

.back-link:hover { color: var(--color-primary); }

.aside-section-label {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--color-primary);
}

.leito-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--color-primary-light);
  color: var(--color-primary-dark);
  font-size: 13px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 8px;
}

.leito-setor {
  font-weight: 400;
  color: var(--color-primary);
}

/* Main */
.prontuario-main {
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-width: 0; /* Impede que o conteúdo flex quebre o grid para fora da tela */
}

.prontuario-main__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 4px;
}

.prontuario-main__title {
  font-size: 22px;
  font-weight: 700;
  color: var(--color-text-primary);
  letter-spacing: -0.4px;
}

.prontuario-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Queixa */
.queixa-card {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 14px 16px;
  background: #FFFBEB;
  border-color: #FDE68A;
}

.queixa-label {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #92400E;
}

.queixa-text {
  font-size: 15px;
  font-weight: 500;
  color: var(--color-text-primary);
}

.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

/* Skeleton */
@keyframes shimmer {
  0%   { background-color: #f1f5f9; }
  50%  { background-color: #e2e8f0; }
  100% { background-color: #f1f5f9; }
}

.skeleton-card { animation: shimmer 1.4s infinite; }

/* Empty */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--color-text-secondary);
}

.empty-icon {
  margin: 0 auto 12px;
  color: var(--color-text-muted);
}

/* Responsive */
@media (max-width: 900px) {
  .prontuario-layout {
    grid-template-columns: 1fr;
    padding: 20px;
  }

  .prontuario-aside { position: static; }
}
</style>
