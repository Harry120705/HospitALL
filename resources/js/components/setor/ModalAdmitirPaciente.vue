<template>
  <BaseModal
    v-model="open"
    title="Admitir Paciente"
    :icon="UserPlus"
    iconBg="#FFF7ED"
    iconColor="#F97316"
  >
    <form @submit.prevent="submit" class="modal-form">

      <!-- Busca de paciente -->
      <div class="form-row">
        <label class="form-label" for="admitir-paciente-nome">Nome do Paciente *</label>
        <div class="input-search">
          <Search :size="15" style="color: var(--color-text-muted); flex-shrink:0" />
          <input
            id="admitir-paciente-nome"
            v-model="buscaNome"
            type="text"
            placeholder="Digite o nome para buscar..."
            @input="onBusca"
            autocomplete="off"
          />
        </div>
        <!-- Resultados da busca -->
        <div v-if="buscaNome.length >= 2 && !pacienteSelecionado" class="busca-dropdown">
          <button
            v-for="p in resultados"
            :key="mongoId(p)"
            type="button"
            class="busca-item"
            @click="selecionarPaciente(p)"
          >
            <div class="busca-item__name">{{ p.nome }}</div>
            <div class="busca-item__meta">
              {{ p.dados_clinicos_fixos?.tipo_sanguineo ?? '—' }} ·
              {{ calcularIdade(p.data_nascimento) }} anos
            </div>
          </button>
          <div v-if="resultados.length === 0 && !isSearching" class="busca-empty">
            Nenhum paciente encontrado com esse nome.
          </div>
          <div v-if="isSearching" class="busca-empty">
            Buscando...
          </div>
        </div>
        <!-- Paciente selecionado -->
        <div v-if="pacienteSelecionado" class="paciente-badge">
          <CheckCircle :size="14" /> {{ pacienteSelecionado.nome }}
          <button type="button" class="badge-remove" @click="pacienteSelecionado = null; buscaNome = ''">
            <X :size="12" />
          </button>
        </div>
      </div>

      <div class="form-grid-2">
        <div class="form-row">
          <label class="form-label" for="admitir-leito">Número do Leito</label>
          <input
            id="admitir-leito"
            v-model="form.leito"
            type="text"
            class="form-input"
            placeholder="Ex: P-06"
          />
        </div>

        <div class="form-row">
          <label class="form-label" for="admitir-manchester">Protocolo de Manchester</label>
          <select id="admitir-manchester" v-model="form.protocolo_manchester" class="form-input">
            <option value="">Não classificado</option>
            <option value="emergencia">🔴 Emergência</option>
            <option value="muito_urgente">🟠 Muito Urgente</option>
            <option value="urgente">🟡 Urgente</option>
            <option value="pouco_urgente">🟢 Pouco Urgente</option>
            <option value="nao_urgente">🔵 Não Urgente</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <label class="form-label" for="admitir-queixa">Queixa Principal *</label>
        <textarea
          id="admitir-queixa"
          v-model="form.queixa_principal"
          class="form-input form-textarea"
          placeholder="Descreva a queixa principal do paciente..."
          required
          rows="3"
        ></textarea>
      </div>

      <div v-if="errorMsg" class="form-error">
        <AlertCircle :size="14" /> {{ errorMsg }}
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn-ghost" @click="open = false">Cancelar</button>
        <button type="submit" class="btn btn-primary" :disabled="loading || !pacienteSelecionado">
          <Loader2 v-if="loading" :size="15" class="spin" />
          <UserPlus v-else :size="15" />
          {{ loading ? 'Admitindo...' : 'Confirmar Admissão' }}
        </button>
      </div>

    </form>
  </BaseModal>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { UserPlus, Search, CheckCircle, X, AlertCircle, Loader2 } from 'lucide-vue-next';
import BaseModal from '@/components/ui/BaseModal.vue';
import { usePacienteStore } from '@/stores/paciente.js';
import { useHospitalStore  } from '@/stores/hospital.js';
import api from '@/services/api.js';
import { mongoId } from '@/utils/mongo.js';
import { calcularIdade } from '@/utils/date.js';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  setorId:    { type: String,  required: true },
  setorNome:  { type: String,  default: '' },
  pacienteInicial: { type: Object, default: null },
});
const emit = defineEmits(['update:modelValue', 'admitido']);

const open = ref(props.modelValue);
watch(() => props.modelValue, (v) => (open.value = v));
watch(open, (v) => emit('update:modelValue', v));

const pacienteStore = usePacienteStore();
const hospitalStore = useHospitalStore();

const loading           = ref(false);
const errorMsg          = ref('');
const buscaNome         = ref('');
const resultados        = ref([]);
const pacienteSelecionado = ref(null);

const pacientesList = ref([]);
const isFetchingPacientes = ref(false);
const isSearching = computed(() => isFetchingPacientes.value);

const form = ref({
  leito:                '',
  protocolo_manchester: '',
  queixa_principal:     '',
});

watch(() => props.pacienteInicial, (value) => {
  if (!value) return;
  pacienteSelecionado.value = value;
  buscaNome.value = value.nome ?? '';
  resultados.value = [];
});

// ── Cache de pacientes ────────────────────────────────────────
async function carregarPacientesParaBusca() {
  if (pacientesList.value.length > 0) return;
  isFetchingPacientes.value = true;
  try {
    const { data } = await api.get('/pacientes');
    pacientesList.value = Array.isArray(data) ? data : data.data ?? [];
  } catch (err) {
    console.error('Erro ao carregar lista de pacientes:', err);
  } finally {
    isFetchingPacientes.value = false;
  }
}

watch(open, (v) => {
  if (v) {
    carregarPacientesParaBusca();
  }
});

// Filtro local reativo em tempo real
watch([buscaNome, pacientesList, pacienteSelecionado], () => {
  if (buscaNome.value.length < 2 || pacienteSelecionado.value) {
    resultados.value = [];
    return;
  }
  const q = buscaNome.value.toLowerCase();
  resultados.value = pacientesList.value
    .filter((p) => p.nome?.toLowerCase().includes(q))
    .slice(0, 6);
});

function onBusca() {
  pacienteSelecionado.value = null; // reseta seleção ao digitar
}

function selecionarPaciente(p) {
  pacienteSelecionado.value = p;
  buscaNome.value           = p.nome;
  resultados.value          = [];
}

async function submit() {
  if (!pacienteSelecionado.value) return;
  loading.value  = true;
  errorMsg.value = '';
  try {
    const admitido = await pacienteStore.admitirPaciente({
      paciente_id:          mongoId(pacienteSelecionado.value),
      paciente_obj:         pacienteSelecionado.value,
      hospital_id:          mongoId(hospitalStore.hospital),
      setor_id:             props.setorId,
      setor_nome:           props.setorNome,
      leito:                form.value.leito,
      protocolo_manchester: form.value.protocolo_manchester || null,
      queixa_principal:     form.value.queixa_principal,
    });
    emit('admitido', admitido);
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
  buscaNome.value           = '';
  resultados.value          = [];
  pacienteSelecionado.value = null;
  form.value                = { leito: '', protocolo_manchester: '', queixa_principal: '' };
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

.form-textarea { resize: vertical; min-height: 80px; }

/* Busca dropdown */
.busca-dropdown {
  border: 1px solid var(--color-border);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 16px -4px rgb(0 0 0 / 0.1);
}

.busca-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
  width: 100%;
  text-align: left;
  padding: 10px 14px;
  background: none;
  border: none;
  border-bottom: 1px solid var(--color-border);
  cursor: pointer;
  transition: background var(--transition-fast);
}

.busca-item:last-child { border-bottom: none; }
.busca-item:hover { background: var(--color-bg); }

.busca-item__name {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-primary);
}

.busca-item__meta {
  font-size: 12px;
  color: var(--color-text-muted);
}

.busca-empty {
  padding: 12px 14px;
  font-size: 13px;
  color: var(--color-text-secondary);
  text-align: center;
}

/* Paciente badge */
.paciente-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ECFDF5;
  color: #16A34A;
  font-size: 13px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 8px;
  border: 1px solid #BBF7D0;
}

.badge-remove {
  background: none;
  border: none;
  cursor: pointer;
  color: #16A34A;
  display: flex;
  align-items: center;
  padding: 0;
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
