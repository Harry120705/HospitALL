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

      <div class="form-grid-2">
        <div class="form-row">
          <label class="form-label" for="np-genero">Gênero / Sexo *</label>
          <select
            id="np-genero"
            v-model="form.genero"
            class="form-input"
            required
          >
            <option value="" disabled>Selecione</option>
            <option value="Masculino">Masculino</option>
            <option value="Feminino">Feminino</option>
            <option value="Outro">Outro</option>
          </select>
        </div>

        <div class="form-row">
          <label class="form-label" for="np-sus">Cartão SUS *</label>
          <input
            id="np-sus"
            v-model="form.cartao_sus"
            type="text"
            class="form-input"
            placeholder="700401234567890"
            required
          />
        </div>
      </div>

      <div class="form-row">
        <label class="form-label" for="np-telefone">Telefone / Celular *</label>
        <input
          id="np-telefone"
          v-model="form.telefone"
          type="text"
          class="form-input"
          placeholder="(93) 99123-4567"
          required
        />
      </div>

      <div class="form-grid-2">
        <div class="form-row">
          <label class="form-label" for="np-contato-nome">Contato de Emergência - Nome *</label>
          <input
            id="np-contato-nome"
            v-model="form.contato_emergencia_nome"
            type="text"
            class="form-input"
            placeholder="Nome do responsável"
            required
          />
        </div>

        <div class="form-row">
          <label class="form-label" for="np-contato-telefone">Contato de Emergência - Telefone *</label>
          <input
            id="np-contato-telefone"
            v-model="form.contato_emergencia_telefone"
            type="text"
            class="form-input"
            placeholder="(93) 99234-5678"
            required
          />
        </div>
      </div>
      
      <div class="form-row">
        <label class="form-label" for="np-contato-parentesco">Parentesco do Contato</label>
        <input
          id="np-contato-parentesco"
          v-model="form.contato_parentesco"
          type="text"
          class="form-input"
          placeholder="Ex: Mãe, Cônjuge, Filho..."
        />
      </div>

      <div class="form-row">
        <label class="form-label" for="np-tipo-sanguineo">Tipo Sanguíneo *</label>
        <select
          id="np-tipo-sanguineo"
          v-model="form.tipo_sanguineo"
          class="form-input"
          required
        >
          <option value="" disabled>Selecione</option>
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
          <label class="form-label" for="np-peso">Peso (kg)</label>
          <input
            id="np-peso"
            v-model.number="form.peso_kg"
            type="number"
            min="0"
            step="0.1"
            class="form-input"
            placeholder="Ex: 22"
          />
        </div>

        <div class="form-row">
          <label class="form-label" for="np-altura">Altura (cm)</label>
          <input
            id="np-altura"
            v-model.number="form.altura_cm"
            type="number"
            min="0"
            step="0.1"
            class="form-input"
            placeholder="Ex: 115"
          />
        </div>
      </div>

      <div class="form-row">
        <label class="form-label" for="np-alergias">Alergias Conhecidas</label>
        <textarea
          id="np-alergias"
          v-model="form.alergias"
          class="form-input"
          rows="3"
          placeholder="Ex: Dipirona, Neosaldina"
        ></textarea>
      </div>

      <div class="form-row">
        <label class="form-label" for="np-comorbidades">Condições Crônicas / Comorbidades</label>
        <textarea
          id="np-comorbidades"
          v-model="form.comorbidades"
          class="form-input"
          rows="3"
          placeholder="Ex: Hipertensão, Diabetes"
        ></textarea>
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
  setorId: { type: String, default: null },
  setorNome: { type: String, default: '' },
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
  genero: '',
  cartao_sus: '',
  telefone: '',
  contato_emergencia_nome: '',
  contato_emergencia_telefone: '',
  contato_parentesco: '',
  tipo_sanguineo: '',
  peso_kg: null,
  altura_cm: null,
  alergias: '',
  comorbidades: '',
});

async function submit() {
  loading.value  = true;
  errorMsg.value = '';
  try {
    const payload = {
      nome: form.value.nome,
      cpf: form.value.cpf,
      data_nascimento: form.value.data_nascimento,
      genero: form.value.genero,
      cartao_sus: form.value.cartao_sus,
      telefone: form.value.telefone,
      contato: {
        nome: form.value.contato_emergencia_nome,
        telefone: form.value.contato_emergencia_telefone,
        parentesco: form.value.contato_parentesco || 'Não informado',
      },
      dados_clinicos_fixos: {
        tipagem_sanguinea: form.value.tipo_sanguineo,
        peso_kg: form.value.peso_kg,
        altura_cm: form.value.altura_cm,
        alergias: form.value.alergias,
        comorbidades: form.value.comorbidades,
      },
      status_paciente: 'Novo Paciente',
      setor_id: props.setorId,
      setor_nome: props.setorNome,
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
  form.value = {
    nome: '',
    cpf: '',
    data_nascimento: '',
    genero: '',
    cartao_sus: '',
    telefone: '',
    contato_emergencia_nome: '',
    contato_emergencia_telefone: '',
    contato_parentesco: '',
    tipo_sanguineo: '',
    peso_kg: null,
    altura_cm: null,
    alergias: '',
    comorbidades: '',
  };
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
