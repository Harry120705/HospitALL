<template>
  <div class="timeline-section">
    <h3 class="tl-title">
      <ClipboardList :size="15" /> Administração de Enfermagem
    </h3>

    <div v-if="registros.length === 0" class="tl-empty">
      <p>Nenhum registro de enfermagem encontrado.</p>
    </div>

    <div v-else class="timeline">
      <div
        v-for="reg in registros"
        :key="reg.id"
        class="timeline-item timeline-item--nursing"
      >
        <div class="tl-card card">
          <div class="tl-card__header">
            <div class="tl-meta">
              <span class="tl-tipo-badge" :class="`tl-tipo-badge--${reg.tipo}`">
                {{ tipoLabel(reg.tipo) }}
              </span>
              <span class="tl-enfermeiro">{{ reg.enfermeiro }}</span>
            </div>
            <time class="tl-time">{{ formatDateTime(reg.data_hora) }}</time>
          </div>
          <p class="tl-descricao">{{ reg.descricao }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ClipboardList } from 'lucide-vue-next';
import { formatDateTime } from '@/utils/date.js';

defineProps({
  registros: { type: Array, default: () => [] },
});

const TIPO_LABELS = {
  medicacao:    'Medicação',
  sinais_vitais: 'Sinais Vitais',
  procedimento: 'Procedimento',
  observacao:   'Observação',
};

function tipoLabel(tipo) {
  return TIPO_LABELS[tipo] ?? tipo;
}
</script>

<style scoped>
.timeline-section { margin-bottom: 32px; }

.tl-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 15px;
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: 20px;
}

.tl-empty {
  color: var(--color-text-muted);
  font-size: 14px;
  font-style: italic;
  padding: 16px 0;
}

/* Override timeline dot color for nursing = teal */
.timeline-item--nursing::before {
  background: #0D9488;
  box-shadow: 0 0 0 2px #0D9488;
}

.tl-card {
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.tl-card:hover { transform: none; }

.tl-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.tl-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.tl-tipo-badge {
  padding: 2px 10px;
  border-radius: 100px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.tl-tipo-badge--medicacao    { background: #F0FDFA; color: #0F766E; }
.tl-tipo-badge--sinais_vitais{ background: #EFF6FF; color: #1D4ED8; }
.tl-tipo-badge--procedimento { background: #FAF5FF; color: #7E22CE; }
.tl-tipo-badge--observacao   { background: #FAFAF9; color: #57534E; }

.tl-enfermeiro {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-primary);
}

.tl-time {
  font-size: 12px;
  color: var(--color-text-muted);
  white-space: nowrap;
}

.tl-descricao {
  font-size: 14px;
  color: var(--color-text-secondary);
  line-height: 1.6;
}
</style>
