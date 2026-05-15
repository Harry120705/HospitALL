<template>
  <div class="manchester-filters">
    <span class="manchester-label">
      <SlidersHorizontal :size="13" />
      Manchester:
    </span>
    <div class="manchester-buttons">
      <button
        v-for="opt in options"
        :key="opt.value"
        class="manchester-btn"
        :class="{ 'manchester-btn--active': modelValue === opt.value }"
        :style="modelValue === opt.value ? { background: opt.color, borderColor: opt.color } : { borderColor: opt.color }"
        @click="emit('update:modelValue', opt.value)"
        :id="`btn-manchester-${opt.value}`"
      >
        <span
          v-if="opt.value !== 'todos'"
          class="manchester-dot"
          :style="{ background: modelValue === opt.value ? '#fff' : opt.color }"
        ></span>
        {{ opt.label }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { SlidersHorizontal } from 'lucide-vue-next';

defineProps({
  modelValue: { type: String, default: 'todos' },
});

const emit = defineEmits(['update:modelValue']);

const options = [
  { value: 'todos',          label: 'Todos',        color: '#64748B' },
  { value: 'emergencia',     label: 'Emergência',   color: '#EF4444' },
  { value: 'muito_urgente',  label: 'Muito Urgente',color: '#F97316' },
  { value: 'urgente',        label: 'Urgente',      color: '#EAB308' },
  { value: 'pouco_urgente',  label: 'Pouco Urgente',color: '#22C55E' },
  { value: 'nao_urgente',    label: 'Não Urgente',  color: '#0EA5E9' },
];
</script>

<style scoped>
.manchester-filters {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.manchester-label {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 13px;
  font-weight: 500;
  color: var(--color-text-secondary);
  white-space: nowrap;
}

.manchester-buttons {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.manchester-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 5px 14px;
  border-radius: 100px;
  border: 1.5px solid var(--color-border);
  background: transparent;
  font-size: 13px;
  font-weight: 500;
  color: var(--color-text-secondary);
  cursor: pointer;
  transition: all var(--transition-fast);
  line-height: 1;
  white-space: nowrap;
}

.manchester-btn:hover {
  opacity: 0.85;
}

.manchester-btn--active {
  color: #fff !important;
  font-weight: 600;
}

.manchester-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}
</style>
