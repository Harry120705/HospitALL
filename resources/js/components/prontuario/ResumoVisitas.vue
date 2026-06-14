<template>
  <div class="resumo-visitas card">
    <div class="card-header">
      <History :size="16" class="card-icon" />
      <h3 class="card-title">Últimas Visitas</h3>
    </div>

    <div class="card-body">
      <template v-if="visitas && visitas.length > 0">
        <ul class="visitas-list">
          <li v-for="visita in visitas" :key="visita.atendimento_id" class="visita-item">
            <div class="visita-info">
              <span class="visita-data">{{ formatData(visita.data_admissao) }}</span>
              <span class="visita-motivo">{{ visita.motivo_principal || 'Sem motivo registrado' }}</span>
              <span class="visita-setor">{{ visita.setor_nome || 'Setor desconhecido' }}</span>
            </div>
            <RouterLink :to="'/prontuario/' + visita.atendimento_id" class="visita-link">
              Ver detalhadamente
            </RouterLink>
          </li>
        </ul>
      </template>
      <div v-else class="empty-state-visitas">
        Nenhuma visita anterior registrada no resumo.
      </div>
    </div>
  </div>
</template>

<script setup>
import { History } from 'lucide-vue-next';
import { RouterLink } from 'vue-router';

const props = defineProps({
  visitas: {
    type: Array,
    default: () => []
  }
});

function formatData(isoDate) {
  if (!isoDate) return '--/--/----';
  try {
    const d = new Date(isoDate);
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(d);
  } catch (e) {
    return isoDate;
  }
}
</script>

<style scoped>
.resumo-visitas {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid var(--color-border);
  padding-bottom: 8px;
}

.card-icon {
  color: var(--color-primary);
}

.card-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-primary);
}

.visitas-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.visita-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 10px;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 6px;
}

.visita-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.visita-data {
  font-size: 12px;
  font-weight: 600;
  color: var(--color-text-primary);
}

.visita-motivo {
  font-size: 13px;
  color: var(--color-text-secondary);
}

.visita-setor {
  font-size: 11px;
  color: var(--color-text-muted);
}

.visita-link {
  align-self: flex-start;
  font-size: 12px;
  font-weight: 600;
  color: var(--color-primary);
  text-decoration: none;
  margin-top: 4px;
}

.visita-link:hover {
  text-decoration: underline;
}

.empty-state-visitas {
  font-size: 13px;
  color: var(--color-text-muted);
  text-align: center;
  padding: 12px 0;
}
</style>
