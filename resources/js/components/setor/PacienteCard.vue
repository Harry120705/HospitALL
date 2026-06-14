<template>
  <button
    type="button"
    class="paciente-card card"
    :id="`card-paciente-${cardId}`"
    @click="emit('click')"
  >
    <div class="pac-card__left">
      <!-- Avatar -->
      <div class="pac-avatar" :style="{ background: manchesterBg }">
        {{ initials }}
      </div>
    </div>

    <div class="pac-card__body">
      <div class="pac-card__top">
        <h4 class="pac-name">{{ nomePaciente }}</h4>
        <!-- Manchester Badge -->
        <span
          v-if="temAtendimento"
          class="badge"
          :style="{ background: manchesterBg + '22', color: manchesterColor }"
        >
          <span class="badge-dot" :style="{ background: manchesterColor }"></span>
          {{ manchesterLabel }}
        </span>
        <span v-else class="badge badge--muted">Sem leito</span>
      </div>

      <div class="pac-card__meta">
        <span class="meta-item">
          <BedDouble :size="12" />
          Leito {{ atendimento?.alocacao_leito?.numero ?? '—' }}
        </span>
        <span class="meta-divider">·</span>
        <span class="meta-item">
          <Stethoscope :size="12" />
          {{ atendimento?.dados_iniciais?.queixa_principal ?? 'Sem queixa registrada' }}
        </span>
      </div>
    </div>

    <div class="pac-card__arrow">
      <ChevronRight :size="16" />
    </div>
  </button>
</template>

<script setup>
import { computed } from 'vue';
import { BedDouble, Stethoscope, ChevronRight } from 'lucide-vue-next';
import { MANCHESTER_MAP } from '@/constants/manchester.js';

const props = defineProps({
  paciente: { type: Object, required: true },
  atendimento: { type: Object, default: null },
});
const emit = defineEmits(['click']);

const manchesterData = computed(() => {
  const code = props.atendimento?.protocolo_manchester || props.atendimento?.dados_iniciais?.protocolo_manchester;
  return MANCHESTER_MAP[code] ?? { label: '—', color: '#94A3B8', bg: '#94A3B8' };
});

const manchesterLabel = computed(() => manchesterData.value.label);
const manchesterColor = computed(() => manchesterData.value.color);
const manchesterBg    = computed(() => manchesterData.value.bg);

const nomePaciente = computed(() => props.paciente?.nome ?? '—');
const initials = computed(() =>
  (nomePaciente.value || '?').split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase()
);
const temAtendimento = computed(() => Boolean(props.atendimento));
const cardId = computed(() => props.atendimento?._id ?? props.paciente?._id ?? 'paciente');
</script>

<style scoped>
.paciente-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  text-decoration: none;
  width: 100%;
  text-align: left;
  background: none;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
  cursor: pointer;
}

.paciente-card:hover {
  border-color: var(--color-primary);
  box-shadow: 0 4px 16px -2px rgb(14 165 233 / 0.12);
}

/* Avatar */
.pac-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
  opacity: 0.9;
}

/* Body */
.pac-card__body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.pac-card__top {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.pac-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.badge--muted {
  background: #FEF3C7;
  color: #B45309;
  border: 1px solid #FDE68A;
}

.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

/* Meta */
.pac-card__meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: var(--color-text-secondary);
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.meta-divider {
  color: var(--color-text-muted);
}

/* Arrow */
.pac-card__arrow {
  color: var(--color-text-muted);
  flex-shrink: 0;
  transition: transform var(--transition-fast);
}

.paciente-card:hover .pac-card__arrow {
  transform: translateX(4px);
  color: var(--color-primary);
}
</style>
