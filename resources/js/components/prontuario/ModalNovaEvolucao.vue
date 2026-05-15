<template>
  <BaseModal
    v-model="open"
    title="Nova Evolução Médica"
    :icon="Stethoscope"
    iconBg="#EFF6FF"
    iconColor="#1D4ED8"
    width="600px"
  >
    <form @submit.prevent="submit" class="modal-form">

      <div class="form-grid-2">
        <div class="form-row">
          <label class="form-label" for="ev-medico">Nome do Médico *</label>
          <input
            id="ev-medico"
            v-model="form.medico"
            type="text"
            class="form-input"
            placeholder="Dr. Rafael Costa"
            required
          />
        </div>
        <div class="form-row">
          <label class="form-label" for="ev-crm">CRM *</label>
          <input
            id="ev-crm"
            v-model="form.crm"
            type="text"
            class="form-input"
            placeholder="145.220"
            required
          />
        </div>
      </div>

      <div class="form-row">
        <label class="form-label" for="ev-tipo">Tipo de Registro *</label>
        <div class="tipo-grid">
          <button
            v-for="opt in tipoOptions"
            :key="opt.value"
            type="button"
            class="tipo-btn"
            :class="{ 'tipo-btn--active': form.tipo === opt.value }"
            :style="form.tipo === opt.value ? { background: opt.bg, borderColor: opt.color, color: opt.color } : {}"
            @click="form.tipo = opt.value"
          >
            {{ opt.label }}
          </button>
        </div>
      </div>

      <div class="form-row">
        <label class="form-label" for="ev-descricao">Descrição / Observações *</label>
        <textarea
          id="ev-descricao"
          v-model="form.descricao"
          class="form-input form-textarea"
          placeholder="Descreva a evolução do quadro clínico, prescrições ou resultados de exames..."
          required
          rows="5"
        ></textarea>
      </div>

      <!-- Preview timestamp -->
      <div class="timestamp-preview">
        <Clock :size="12" />
        Será registrado em: {{ agora }}
      </div>

      <div v-if="errorMsg" class="form-error">
        <AlertCircle :size="14" /> {{ errorMsg }}
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn-ghost" @click="open = false">Cancelar</button>
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <Loader2 v-if="loading" :size="15" class="spin" />
          <FilePlus v-else :size="15" />
          {{ loading ? 'Salvando...' : 'Registrar Evolução' }}
        </button>
      </div>

    </form>
  </BaseModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Stethoscope, AlertCircle, Loader2, FilePlus, Clock } from 'lucide-vue-next';
import BaseModal from '@/components/ui/BaseModal.vue';
import { usePacienteStore } from '@/stores/paciente.js';

const props = defineProps({
  modelValue:    { type: Boolean, required: true },
  atendimentoId: { type: String,  required: true },
});
const emit = defineEmits(['update:modelValue', 'criada']);

const open = ref(props.modelValue);
watch(() => props.modelValue, (v) => (open.value = v));
watch(open, (v) => emit('update:modelValue', v));

const pacienteStore = usePacienteStore();
const loading  = ref(false);
const errorMsg = ref('');

const tipoOptions = [
  { value: 'evolucao',    label: '📋 Evolução',    color: '#1D4ED8', bg: '#EFF6FF' },
  { value: 'prescricao',  label: '💊 Prescrição',  color: '#15803D', bg: '#F0FDF4' },
  { value: 'exame',       label: '🔬 Exame',       color: '#C2410C', bg: '#FFF7ED' },
  { value: 'procedimento',label: '🩺 Procedimento',color: '#7E22CE', bg: '#FAF5FF' },
];

const form = ref({
  medico:    '',
  crm:       '',
  tipo:      'evolucao',
  descricao: '',
});

const agora = computed(() =>
  new Date().toLocaleString('pt-BR', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
  })
);

async function submit() {
  loading.value  = true;
  errorMsg.value = '';
  try {
    const criada = await pacienteStore.pushEvolucao(props.atendimentoId, form.value);
    emit('criada', criada);
    open.value = false;
    form.value = { medico: '', crm: '', tipo: 'evolucao', descricao: '' };
  } catch (err) {
    errorMsg.value =
      err.response?.data?.message ??
      (err.response?.data?.errors
        ? Object.values(err.response.data.errors).flat().join(', ')
        : err.message);
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.modal-form { display: flex; flex-direction: column; gap: 16px; }

.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.form-row { display: flex; flex-direction: column; gap: 6px; }

.form-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-secondary);
}

.form-input {
  padding: 9px 12px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  color: var(--color-text-primary);
  background: var(--color-surface);
  transition: border-color 150ms, box-shadow 150ms;
  outline: none;
}

.form-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgb(14 165 233 / 0.12);
}

.form-textarea { resize: vertical; min-height: 120px; }

/* Tipo selector */
.tipo-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
}

.tipo-btn {
  padding: 8px 4px;
  border-radius: 8px;
  border: 1.5px solid var(--color-border);
  background: none;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  text-align: center;
  color: var(--color-text-secondary);
  transition: all 150ms;
  line-height: 1.4;
}

.tipo-btn:hover { border-color: var(--color-primary); }

/* Timestamp */
.timestamp-preview {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  color: var(--color-text-muted);
  background: var(--color-bg);
  padding: 6px 10px;
  border-radius: 6px;
}

.form-error {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #EF4444;
  background: #FFF1F2;
  border: 1px solid #FECDD3;
  border-radius: 8px;
  padding: 8px 12px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 8px;
  border-top: 1px solid var(--color-border);
}

.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
