<template>
  <BaseModal
    v-model="open"
    title="Buscar Paciente"
    :icon="Search"
    iconBg="#EFF6FF"
    iconColor="#3B82F6"
  >
    <div class="search-container">
      <div class="form-row">
        <div class="input-wrapper">
          <Search :size="16" class="input-icon" />
          <input
            v-model="searchQuery"
            type="text"
            class="form-input search-input"
            placeholder="Buscar por CPF ou Nome..."
            autofocus
          />
        </div>
      </div>

      <div class="results-container">
        <div v-if="loading" class="state-message">
          <Loader2 :size="20" class="spin" /> Buscando pacientes...
        </div>
        
        <div v-else-if="filteredPacientes.length === 0" class="state-message empty-state">
          <UserX :size="24" class="empty-icon" />
          <p v-if="searchQuery">Nenhum paciente encontrado para "{{ searchQuery }}"</p>
          <p v-else>Digite algo para buscar no cadastro geral.</p>
        </div>

        <div v-else class="patients-list">
          <div
            v-for="p in filteredPacientes"
            :key="p._id"
            class="patient-item"
          >
            <div class="patient-info">
              <div class="patient-avatar">{{ p.nome.charAt(0).toUpperCase() }}</div>
              <div>
                <p class="patient-name">{{ p.nome }}</p>
                <p class="patient-doc">CPF: {{ p.cpf }}</p>
              </div>
            </div>
            <button class="btn btn-sm btn-primary" @click="selecionar(p)">
              Admitir
            </button>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <p class="footer-text">O paciente não possui cadastro?</p>
        <button type="button" class="btn btn-ghost w-full" @click="cadastrarNovo">
          <UserPlus :size="16" /> Cadastrar Novo Paciente
        </button>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Search, Loader2, UserX, UserPlus } from 'lucide-vue-next';
import BaseModal from '@/components/ui/BaseModal.vue';
import api from '@/services/api.js';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
});
const emit = defineEmits(['update:modelValue', 'selecionado', 'cadastrar']);

const open = ref(props.modelValue);
watch(() => props.modelValue, (v) => {
  open.value = v;
  if (v) {
    searchQuery.value = '';
    fetchPacientes();
  }
});
watch(open, (v) => emit('update:modelValue', v));

const searchQuery = ref('');
const pacientes = ref([]);
const loading = ref(false);

async function fetchPacientes() {
  if (pacientes.value.length > 0) return; // Cache simples
  loading.value = true;
  try {
    const { data } = await api.get('/pacientes');
    pacientes.value = Array.isArray(data) ? data : (data.data ?? []);
  } catch (err) {
    console.error('Erro ao buscar pacientes', err);
  } finally {
    loading.value = false;
  }
}

const filteredPacientes = computed(() => {
  if (!searchQuery.value.trim()) return [];
  const q = searchQuery.value.toLowerCase();
  return pacientes.value.filter(p => 
    p.nome?.toLowerCase().includes(q) || 
    p.cpf?.replace(/\D/g, '').includes(q.replace(/\D/g, ''))
  ).slice(0, 10); // Mostra até 10 resultados para não poluir
});

function selecionar(paciente) {
  emit('selecionado', paciente);
  open.value = false;
}

function cadastrarNovo() {
  emit('cadastrar');
  open.value = false;
}

onMounted(() => {
  if (open.value) fetchPacientes();
});
</script>

<style scoped>
.search-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 12px;
  color: var(--color-text-muted);
}

.search-input {
  width: 100%;
  padding-left: 36px !important;
  height: 42px;
}

.form-input {
  padding: 9px 12px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 14px;
  color: var(--color-text-primary);
  background: var(--color-surface);
  outline: none;
  transition: all 0.2s;
}

.form-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgb(14 165 233 / 0.12);
}

.results-container {
  min-height: 200px;
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: #FAFAFA;
}

.state-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 200px;
  color: var(--color-text-muted);
  font-size: 14px;
  text-align: center;
}

.empty-icon {
  color: var(--color-border);
  margin-bottom: 4px;
}

.patients-list {
  display: flex;
  flex-direction: column;
}

.patient-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid var(--color-border);
  background: #FFF;
  transition: background 0.2s;
}

.patient-item:last-child {
  border-bottom: none;
}

.patient-item:hover {
  background: #F8FAFC;
}

.patient-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.patient-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--color-primary);
  color: #FFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 15px;
}

.patient-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: 2px;
}

.patient-doc {
  font-size: 12px;
  color: var(--color-text-secondary);
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
  border-radius: 6px;
}

.modal-footer {
  margin-top: 4px;
  padding-top: 16px;
  border-top: 1px dashed var(--color-border);
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: center;
}

.footer-text {
  font-size: 13px;
  color: var(--color-text-muted);
}

.w-full {
  width: 100%;
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
