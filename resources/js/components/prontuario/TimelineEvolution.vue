<template>
  <div class="timeline-section">
    <h3 class="tl-title">
      <Stethoscope :size="15" /> Evoluções Médicas
    </h3>

    <div v-if="evolucoes.length === 0" class="tl-empty">
      <p>Nenhuma evolução médica registrada.</p>
    </div>

    <div v-else class="timeline">
      <div
        v-for="ev in evolucoes"
        :key="ev.id"
        class="timeline-item"
      >
        <!-- Dot is styled via global CSS .timeline-item::before -->
        <div class="tl-card card">
          <div class="tl-card__header">
            <div class="tl-meta">
              <span class="tl-tipo-badge" :class="`tl-tipo-badge--${ev.tipo}`">
                {{ tipoLabel(ev.tipo) }}
              </span>
              <span class="tl-medico">{{ ev.medico }}</span>
              <span class="tl-crm">CRM {{ ev.crm }}</span>
            </div>
            <time class="tl-time">{{ formatDateTime(ev.data_hora) }}</time>
          </div>
          <p class="tl-descricao">{{ ev.descricao }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Stethoscope } from 'lucide-vue-next';
import { formatDateTime } from '@/utils/date.js';

const props = defineProps({
  evolucoes: { type: Array, default: () => [] },
});

const TIPO_LABELS = {
  evolucao: 'Evolução',
  prescricao: 'Prescrição',
  exame: 'Exame',
  procedimento: 'Procedimento',
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

/* Timeline item */
.tl-card {
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.tl-card:hover { transform: none; } /* disable generic hover lift */

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

.tl-tipo-badge--evolucao    { background: #EFF6FF; color: #1D4ED8; }
.tl-tipo-badge--prescricao  { background: #F0FDF4; color: #15803D; }
.tl-tipo-badge--exame       { background: #FFF7ED; color: #C2410C; }
.tl-tipo-badge--procedimento{ background: #FAF5FF; color: #7E22CE; }

.tl-medico {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-primary);
}

.tl-crm {
  font-size: 12px;
  color: var(--color-text-muted);
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
