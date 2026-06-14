<template>
  <div class="timeline-section">
    <h3 class="tl-title">
      <Stethoscope :size="15" /> Evoluções Médicas
    </h3>

    <div v-if="evolucoes.length === 0" class="tl-empty">
      <p>Nenhuma evolução médica registrada.</p>
    </div>

    <div v-else class="timeline">
      <div
        v-for="ev in visibleEvolucoes"
        :key="ev.id"
        class="timeline-item"
      >
        <!-- Dot is styled via global CSS .timeline-item::before -->
        <div class="tl-card card">
          <div class="tl-card__header">
            <div class="tl-meta">
              <span class="tl-tipo-badge" :class="`tl-tipo-badge--${ev.tipo}`">
                {{ tipoLabel(ev.tipo) }}
              </span>
              <span class="tl-medico">{{ ev.medico }}</span>
              <span class="tl-crm">CRM {{ ev.crm }}</span>
            </div>
            <div class="tl-time-actions">
              <time class="tl-time">{{ formatDateTime(ev.data_hora) }}</time>
              <button 
                v-if="appStore.userRole !== 'RECEPCIONISTA'" 
                class="btn-icon-edit" 
                @click="startEdit(ev)" 
                title="Editar"
              >
                <Pencil :size="13" />
              </button>
            </div>
          </div>

          <!-- Edit Mode -->
          <div v-if="editingId === ev.id" class="tl-edit-mode">
            <textarea
              v-model="editContent"
              class="form-input"
              rows="3"
            ></textarea>
            <div class="tl-edit-actions">
              <button class="btn btn-ghost btn-sm" @click="cancelEdit" :disabled="savingEdit">Cancelar</button>
              <button class="btn btn-primary btn-sm" @click="saveEdit(ev.id)" :disabled="savingEdit">
                <Loader2 v-if="savingEdit" :size="14" class="spin" />
                <Save v-else :size="14" />
                Salvar
              </button>
            </div>
          </div>
          <!-- View Mode -->
          <div v-else>
            <p 
              class="tl-descricao" 
              :class="{ 'line-clamp-2': !expandedCards[ev.id] && ev.descricao?.length > 150 }"
            >
              {{ ev.descricao }}
            </p>
            <button 
              v-if="ev.descricao?.length > 150" 
              class="btn-expand-text" 
              @click="toggleCard(ev.id)"
            >
              {{ expandedCards[ev.id] ? 'Esconder detalhes' : 'Ler toda a evolução...' }}
            </button>
          </div>
        </div>
      </div>
      
      <div v-if="evolucoes.length > 4" class="tl-show-more">
        <button class="btn btn-ghost tl-btn-more" @click="expanded = !expanded">
          {{ expanded ? 'Ocultar evoluções antigas' : `Ver mais evoluções (${evolucoes.length - 4})` }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Stethoscope, Pencil, Save, Loader2 } from 'lucide-vue-next';
import { formatDateTime } from '@/utils/date.js';
import { usePacienteStore } from '@/stores/paciente.js';
import { useToastStore } from '@/stores/toast.js';
import { useAppStore } from '@/stores/app.js';

const props = defineProps({
  evolucoes: { type: Array, default: () => [] },
});

const pacienteStore = usePacienteStore();
const toastStore = useToastStore();
const appStore = useAppStore();

const editingId = ref(null);
const editContent = ref('');
const savingEdit = ref(false);
const expanded = ref(false);
const expandedCards = ref({});

function toggleCard(id) {
  expandedCards.value[id] = !expandedCards.value[id];
}

const visibleEvolucoes = computed(() => {
  if (expanded.value) return props.evolucoes;
  return props.evolucoes.slice(0, 4);
});

function startEdit(ev) {
  editingId.value = ev.id;
  editContent.value = ev.descricao;
}

function cancelEdit() {
  editingId.value = null;
  editContent.value = '';
}

async function saveEdit(id) {
  if (!editContent.value.trim()) return;
  savingEdit.value = true;
  try {
    await pacienteStore.editarEvolucao(id, editContent.value);
    toastStore.success('Evolução atualizada com sucesso!');
    editingId.value = null;
  } catch (err) {
    toastStore.error('Erro ao atualizar: ' + err.message);
  } finally {
    savingEdit.value = false;
  }
}

const TIPO_LABELS = {
  evolucao: 'Evolução',
  prescricao: 'Prescrição',
  exame: 'Exame',
  procedimento: 'Procedimento',
};

function tipoLabel(tipo) {
  return TIPO_LABELS[tipo] ?? tipo;
}
</script>

<style scoped>
.timeline-section { margin-bottom: 32px; }

.tl-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 15px;
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: 20px;
}

.tl-empty {
  color: var(--color-text-muted);
  font-size: 14px;
  font-style: italic;
  padding: 16px 0;
}

/* Timeline item */
.tl-show-more {
  display: flex;
  justify-content: center;
  padding-top: 12px;
}
.tl-btn-more {
  width: 100%;
  font-size: 13px;
  color: var(--color-text-secondary);
}
.tl-btn-more:hover {
  background: var(--color-bg);
}

.tl-card {
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.tl-card:hover { transform: none; } /* disable generic hover lift */

.tl-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.tl-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.tl-tipo-badge {
  padding: 2px 10px;
  border-radius: 100px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.tl-tipo-badge--evolucao    { background: #EFF6FF; color: #1D4ED8; }
.tl-tipo-badge--prescricao  { background: #F0FDF4; color: #15803D; }
.tl-tipo-badge--exame       { background: #FFF7ED; color: #C2410C; }
.tl-tipo-badge--procedimento{ background: #FAF5FF; color: #7E22CE; }

.tl-medico {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-primary);
}

.tl-crm {
  font-size: 12px;
  color: var(--color-text-muted);
}

.tl-time {
  font-size: 12px;
  color: var(--color-text-muted);
  white-space: nowrap;
}

.tl-descricao {
  font-size: 14px;
  color: var(--color-text-secondary);
  line-height: 1.6;
  word-break: break-word;
  white-space: pre-wrap;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.btn-expand-text {
  background: none;
  border: none;
  color: var(--color-primary);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  margin-top: 4px;
}

.btn-expand-text:hover {
  text-decoration: underline;
}

.tl-time-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-icon-edit {
  background: none;
  border: none;
  color: var(--color-text-muted);
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.btn-icon-edit:hover {
  background: var(--color-bg);
  color: var(--color-primary);
}

.tl-edit-mode {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 4px;
}

.form-input {
  padding: 8px 12px;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 13px;
  font-family: inherit;
  color: var(--color-text-primary);
  background: var(--color-surface);
  resize: vertical;
  outline: none;
}

.form-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgb(14 165 233 / 0.12);
}

.tl-edit-actions {
  display: flex;
  justify-content: flex-end;
  gap: 6px;
}

.btn-sm {
  padding: 4px 10px;
  font-size: 12px;
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
