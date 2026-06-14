<template>
  <BaseModal
    v-model="open"
    :title="title"
    :icon="type === 'danger' ? Trash2 : Pencil"
    :iconBg="type === 'danger' ? '#FEF2F2' : '#EFF6FF'"
    :iconColor="type === 'danger' ? '#EF4444' : '#3B82F6'"
  >
    <div class="modal-content">
      <p class="modal-message">{{ message }}</p>

      <!-- Campo de input opcional para edição -->
      <div v-if="withInput" class="input-group">
        <input
          type="text"
          v-model="inputValue"
          class="form-input"
          :placeholder="inputPlaceholder"
          @keyup.enter="confirm"
        />
      </div>

      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" @click="cancel">Cancelar</button>
        <button type="button" class="btn" :class="type === 'danger' ? 'btn-danger' : 'btn-primary'" @click="confirm" :disabled="loading">
          {{ loading ? 'Aguarde...' : confirmText }}
        </button>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Trash2, Pencil } from 'lucide-vue-next';
import BaseModal from '@/components/ui/BaseModal.vue';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  title: { type: String, default: 'Confirmação' },
  message: { type: String, default: 'Tem certeza que deseja continuar?' },
  confirmText: { type: String, default: 'Confirmar' },
  type: { type: String, default: 'primary' }, // 'primary' | 'danger'
  withInput: { type: Boolean, default: false },
  initialValue: { type: String, default: '' },
  inputPlaceholder: { type: String, default: 'Digite aqui...' },
  loading: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);

const open = ref(props.modelValue);
const inputValue = ref(props.initialValue);

watch(() => props.modelValue, (v) => {
  open.value = v;
  if (v) inputValue.value = props.initialValue;
});

watch(open, (v) => emit('update:modelValue', v));

function confirm() {
  if (props.withInput) {
    if (!inputValue.value.trim()) return;
    emit('confirm', inputValue.value);
  } else {
    emit('confirm');
  }
}

function cancel() {
  open.value = false;
  emit('cancel');
}
</script>

<style scoped>
.modal-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.modal-message {
  font-size: 14px;
  color: var(--color-text-secondary);
  line-height: 1.5;
  margin: 0;
}

.input-group {
  display: flex;
  flex-direction: column;
}

.form-input {
  padding: 10px 14px;
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

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 8px;
}

.btn-danger {
  background-color: #EF4444;
  color: white;
  border: none;
}
.btn-danger:hover:not(:disabled) {
  background-color: #DC2626;
}
.btn-danger:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
