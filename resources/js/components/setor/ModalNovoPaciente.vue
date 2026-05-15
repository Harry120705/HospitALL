<template>
  <BaseModal
    v-model="open"
    title="Cadastrar Novo Paciente"
    :icon="UserPlus"
    iconBg="#F0FDF4"
    iconColor="#16A34A"
  >
    <form @submit.prevent="submit" class="modal-form">

      <div class="form-row">
        <label class="form-label" for="np-nome">Nome Completo *</label>
        <input
          id="np-nome"
          v-model="form.nome"
          type="text"
          class="form-input"
          placeholder="Ex: João da Silva"
          required
        />
      </div>

      <div class="form-grid-2">
        <div class="form-row">
          <label class="form-label" for="np-cpf">CPF (Identificação) *</label>
          <input
            id="np-cpf"
            v-model="form.cpf"
            type="text"
            class="form-input"
            placeholder="000.000.000-00"
            required
          />
        </div>

        <div class="form-row">
          <label class="form-label" for="np-nasc">Data de Nascimento *</label>
          <input
            id="np-nasc"
            v-model="form.data_nascimento"
            type="date"
            class="form-input"
            required
          />
        </div>
      </div>

      <div class="form-row">
        <label class="form-label" for="np-email">E-mail</label>
        <input
          id="np-email"
          v-model="form.email"
          type="email"
          class="form-input"
          placeholder="joao@email.com"
        />
      </div>

      <div v-if="errorMsg" class="form-error">
        <AlertCircle :size="14" /> {{ errorMsg }}
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn-ghost" @click="open = false">Cancelar</button>
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <Loader2 v-if="loading" :size="15" class="spin" />
          <UserPlus v-else :size="15" />
          {{ loading ? 'Salvando...' : 'Salvar Paciente' }}
        </button>
      </div>

    </form>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { UserPlus, AlertCircle, Loader2 } from 'lucide-vue-next';
import BaseModal from '@/components/ui/BaseModal.vue';
import api from '@/services/api.js';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
});
const emit = defineEmits(['update:modelValue', 'criado']);

const open = ref(props.modelValue);
watch(() => props.modelValue, (v) => (open.value = v));
watch(open, (v) => emit('update:modelValue', v));

const loading  = ref(false);
const errorMsg = ref('');

const form = ref({
  nome: '',
  cpf: '',
  data_nascimento: '',
  email: '',
});

async function submit() {
  loading.value  = true;
  errorMsg.value = '';
  try {
    const payload = {
      nome: form.value.nome,
      data_nascimento: form.value.data_nascimento,
      contato: {
        cpf: form.value.cpf,
        email: form.value.email,
      }
    };
    const { data } = await api.post('/pacientes', payload);
    emit('criado', data);
    open.value = false;
    resetForm();
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

function resetForm() {
  form.value = { nome: '', cpf: '', data_nascimento: '', email: '' };
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
