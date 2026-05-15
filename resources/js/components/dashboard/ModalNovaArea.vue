<template>
  <BaseModal
    v-model="open"
    title="Cadastrar Nova Área"
    :icon="LayoutGrid"
    iconBg="#E0F2FE"
    iconColor="#0EA5E9"
  >
    <form @submit.prevent="submit" class="modal-form">

      <div class="form-row">
        <label class="form-label" for="setor-nome">Nome do Setor *</label>
        <input
          id="setor-nome"
          v-model="form.nome"
          type="text"
          class="form-input"
          placeholder="Ex: Ortopedia"
          required
          maxlength="100"
        />
      </div>

      <div class="form-row">
        <label class="form-label" for="setor-desc">Descrição *</label>
        <input
          id="setor-desc"
          v-model="form.descricao"
          type="text"
          class="form-input"
          placeholder="Ex: Lesões ósseas, articulares e musculares"
          required
          maxlength="255"
        />
      </div>

      <div class="form-grid-2">
        <div class="form-row">
          <label class="form-label" for="setor-capacidade">Capacidade (leitos) *</label>
          <input
            id="setor-capacidade"
            v-model.number="form.capacidade_maxima"
            type="number"
            class="form-input"
            placeholder="Ex: 20"
            min="1"
            max="500"
            required
          />
        </div>

        <div class="form-row">
          <label class="form-label" for="setor-icone">Ícone</label>
          <select id="setor-icone" v-model="form.icone" class="form-input">
            <option v-for="opt in iconeOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- Error -->
      <div v-if="errorMsg" class="form-error">
        <AlertCircle :size="14" /> {{ errorMsg }}
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn-ghost" @click="open = false">Cancelar</button>
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <Loader2 v-if="loading" :size="15" class="spin" />
          <Plus v-else :size="15" />
          {{ loading ? 'Salvando...' : 'Cadastrar Área' }}
        </button>
      </div>

    </form>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { LayoutGrid, AlertCircle, Plus, Loader2 } from 'lucide-vue-next';
import BaseModal from '@/components/ui/BaseModal.vue';
import { useHospitalStore } from '@/stores/hospital.js';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
});
const emit = defineEmits(['update:modelValue', 'criado']);

const open = ref(props.modelValue);
watch(() => props.modelValue, (v) => (open.value = v));
watch(open, (v) => emit('update:modelValue', v));

const hospitalStore = useHospitalStore();
const loading  = ref(false);
const errorMsg = ref('');

const iconeOptions = [
  { value: 'bed-double',      label: '🛏  Leito Genérico' },
  { value: 'baby',            label: '👶 Bebê / Pediatria' },
  { value: 'scissors',        label: '✂️  Cirurgia' },
  { value: 'activity',        label: '📈 UTI / Monitoramento' },
  { value: 'alarm-check',     label: '🚨 Pronto Socorro' },
  { value: 'heart-handshake', label: '💝 Maternidade' },
  { value: 'heart-pulse',     label: '❤️  Cardiologia' },
  { value: 'bone',            label: '🦴 Ortopedia' },
  { value: 'brain',           label: '🧠 Neurologia' },
  { value: 'eye',             label: '👁  Oftalmologia' },
  { value: 'stethoscope',     label: '🩺 Clínica Geral' },
];

const defaultForm = () => ({
  nome:              '',
  descricao:         '',
  capacidade_maxima: null,
  icone:             'bed-double',
});

const form = ref(defaultForm());

async function submit() {
  loading.value  = true;
  errorMsg.value = '';
  try {
    const criado = await hospitalStore.adicionarSetor(form.value);
    emit('criado', criado);
    open.value = false;
    form.value = defaultForm();
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
.modal-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.form-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

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
  transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
  outline: none;
}

.form-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgb(14 165 233 / 0.12);
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

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
