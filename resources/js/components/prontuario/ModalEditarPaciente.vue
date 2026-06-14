<template>
  <BaseModal
    v-model="open"
    title="Editar Dados do Paciente"
    :icon="UserPen"
    iconBg="#E0F2FE"
    iconColor="#0EA5E9"
  >
    <form @submit.prevent="submit" class="modal-form">
      
      <!-- Seção Pessoal -->
      <h3 class="section-title">Dados Pessoais</h3>
      
      <div class="form-row">
        <label class="form-label" for="ep-nome">Nome Completo</label>
        <input
          id="ep-nome"
          v-model="form.nome"
          type="text"
          class="form-input"
          placeholder="Ex: João da Silva"
          required
        />
      </div>

      <!-- Seção Contato -->
      <h3 class="section-title mt-2">Contato e Emergência</h3>
      
      <div class="form-row">
        <label class="form-label" for="ep-telefone">Telefone / Celular</label>
        <input
          id="ep-telefone"
          v-model="form.telefone"
          type="text"
          class="form-input"
          placeholder="(93) 99123-4567"
        />
      </div>

      <div class="form-grid-2">
        <div class="form-row">
          <label class="form-label" for="ep-contato-nome">Contato de Emergência</label>
          <input
            id="ep-contato-nome"
            v-model="form.contato.nome"
            type="text"
            class="form-input"
            placeholder="Nome do responsável"
          />
        </div>

        <div class="form-row">
          <label class="form-label" for="ep-contato-telefone">Telefone de Emergência</label>
          <input
            id="ep-contato-telefone"
            v-model="form.contato.telefone"
            type="text"
            class="form-input"
            placeholder="(93) 99234-5678"
          />
        </div>
      </div>

      <!-- Seção Clínica -->
      <h3 class="section-title mt-2">Dados Clínicos Fixos</h3>

      <div class="form-row">
        <label class="form-label" for="ep-tipo-sanguineo">Tipo Sanguíneo</label>
        <select
          id="ep-tipo-sanguineo"
          v-model="form.dados_clinicos_fixos.tipagem_sanguinea"
          class="form-input"
        >
          <option value="">Não informado</option>
          <option value="A+">A+</option>
          <option value="A-">A-</option>
          <option value="B+">B+</option>
          <option value="B-">B-</option>
          <option value="AB+">AB+</option>
          <option value="AB-">AB-</option>
          <option value="O+">O+</option>
          <option value="O-">O-</option>
        </select>
      </div>

      <div class="form-grid-2">
        <div class="form-row">
          <label class="form-label" for="ep-peso">Peso (kg)</label>
          <input
            id="ep-peso"
            v-model.number="form.dados_clinicos_fixos.peso_kg"
            type="number"
            min="0"
            step="0.1"
            class="form-input"
          />
        </div>

        <div class="form-row">
          <label class="form-label" for="ep-altura">Altura (cm)</label>
          <input
            id="ep-altura"
            v-model.number="form.dados_clinicos_fixos.altura_cm"
            type="number"
            min="0"
            step="0.1"
            class="form-input"
          />
        </div>
      </div>

      <div class="form-row">
        <label class="form-label" for="ep-alergias">Alergias Conhecidas</label>
        <textarea
          id="ep-alergias"
          v-model="form.dados_clinicos_fixos.alergias"
          class="form-input"
          rows="2"
          placeholder="Ex: Dipirona, Neosaldina"
        ></textarea>
      </div>

      <div class="form-row">
        <label class="form-label" for="ep-comorbidades">Condições Crônicas / Comorbidades</label>
        <textarea
          id="ep-comorbidades"
          v-model="form.dados_clinicos_fixos.comorbidades"
          class="form-input"
          rows="2"
          placeholder="Ex: Hipertensão, Diabetes"
        ></textarea>
      </div>

      <!-- Error -->
      <div v-if="errorMsg" class="form-error">
        <AlertCircle :size="14" /> {{ errorMsg }}
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn-ghost" @click="open = false">Cancelar</button>
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <Loader2 v-if="loading" :size="15" class="spin" />
          <Save v-else :size="15" />
          {{ loading ? 'Salvando...' : 'Salvar Alterações' }}
        </button>
      </div>

    </form>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { UserPen, AlertCircle, Save, Loader2 } from 'lucide-vue-next';
import BaseModal from '@/components/ui/BaseModal.vue';
import { usePacienteStore } from '@/stores/paciente.js';
import { useToastStore } from '@/stores/toast.js';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  paciente: { type: Object, required: true },
});
const emit = defineEmits(['update:modelValue', 'editado']);

const open = ref(props.modelValue);
watch(() => props.modelValue, (v) => {
  open.value = v;
  if (v && props.paciente) {
    const p = props.paciente;
    form.value = {
      nome: p.nome || '',
      telefone: p.telefone || '',
      contato: {
        nome: p.contato?.nome || p.contato_emergencia_nome || '',
        telefone: p.contato?.telefone || p.contato_emergencia_telefone || '',
      },
      dados_clinicos_fixos: {
        tipagem_sanguinea: p.dados_clinicos_fixos?.tipagem_sanguinea || '',
        peso_kg: p.dados_clinicos_fixos?.peso_kg || null,
        altura_cm: p.dados_clinicos_fixos?.altura_cm || null,
        alergias: Array.isArray(p.dados_clinicos_fixos?.alergias) 
          ? p.dados_clinicos_fixos.alergias.join(', ') 
          : (p.dados_clinicos_fixos?.alergias || ''),
        comorbidades: Array.isArray(p.dados_clinicos_fixos?.comorbidades) 
          ? p.dados_clinicos_fixos.comorbidades.join(', ') 
          : (p.dados_clinicos_fixos?.comorbidades || ''),
      }
    };
  }
});
watch(open, (v) => emit('update:modelValue', v));

const pacienteStore = usePacienteStore();
const toastStore = useToastStore();
const loading  = ref(false);
const errorMsg = ref('');

const form = ref({
  nome: '',
  telefone: '',
  contato: { nome: '', telefone: '' },
  dados_clinicos_fixos: { tipagem_sanguinea: '', peso_kg: null, altura_cm: null, alergias: '', comorbidades: '' }
});

async function submit() {
  loading.value  = true;
  errorMsg.value = '';
  try {
    const editado = await pacienteStore.editarPaciente(props.paciente._id, form.value);
    toastStore.success('Dados do paciente atualizados!');
    emit('editado', editado);
    open.value = false;
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

.section-title {
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--color-text-secondary);
  border-bottom: 1px solid var(--color-border);
  padding-bottom: 4px;
  margin-top: 4px;
  margin-bottom: 4px;
}

.mt-2 { margin-top: 8px; }

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
