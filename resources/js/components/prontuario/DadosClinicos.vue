<template>
  <div class="card dados-clinicos">
    <h3 class="dc-title">
      <Stethoscope :size="15" /> Dados Clínicos Fixos
    </h3>

    <div class="dc-grid">
      <!-- Tipo sanguíneo -->
      <div class="dc-chip dc-chip--blood">
        <Droplets :size="14" />
        <span class="dc-chip__label">Tipo Sanguíneo</span>
        <span class="dc-chip__value">{{ clinicos.tipo_sanguineo ?? '—' }}</span>
      </div>

      <!-- Peso -->
      <div class="dc-chip dc-chip--weight">
        <Scale :size="14" />
        <span class="dc-chip__label">Peso</span>
        <span class="dc-chip__value">{{ clinicos.peso_kg ? `${clinicos.peso_kg} kg` : '—' }}</span>
      </div>

      <!-- Altura -->
      <div class="dc-chip dc-chip--height">
        <Ruler :size="14" />
        <span class="dc-chip__label">Altura</span>
        <span class="dc-chip__value">{{ clinicos.altura_cm ? `${clinicos.altura_cm} cm` : '—' }}</span>
      </div>
    </div>

    <!-- Alergias -->
    <div class="dc-section">
      <p class="dc-section__label">
        <AlertTriangle :size="13" /> Alergias
      </p>
      <div v-if="clinicos.alergias?.length" class="dc-tags">
        <span
          v-for="alergia in clinicos.alergias"
          :key="alergia"
          class="dc-tag dc-tag--alert"
        >{{ alergia }}</span>
      </div>
      <p v-else class="dc-empty">Nenhuma alergia registrada</p>
    </div>

    <div class="dc-section">
      <p class="dc-section__label">
        <AlertTriangle :size="13" /> Comorbidades
      </p>
      <div v-if="clinicos.comorbidades?.length" class="dc-tags">
        <span
          v-for="comorbidade in clinicos.comorbidades"
          :key="comorbidade"
          class="dc-tag"
        >{{ comorbidade }}</span>
      </div>
      <p v-else class="dc-empty">Nenhuma comorbidade registrada</p>
    </div>

    <!-- NoSQL note -->
    <div class="dc-nosql-note">
      <Info :size="12" />
      <span>Campos expandíveis via NoSQL — novos módulos não quebram o layout</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Stethoscope, Droplets, Scale, Ruler, AlertTriangle, Info } from 'lucide-vue-next';

const props = defineProps({
  dadosClinicosFixos: { type: Object, default: () => ({}) },
});

function normalizeList(value) {
  if (!value) return [];
  if (Array.isArray(value)) return value.filter((v) => String(v).trim().length > 0);
  if (typeof value === 'string') {
    return value.split(',').map((v) => v.trim()).filter(Boolean);
  }
  return [String(value)].filter(Boolean);
}

const clinicos = computed(() => {
  const base = props.dadosClinicosFixos ?? {};
  return {
    ...base,
    alergias: normalizeList(base.alergias),
    comorbidades: normalizeList(base.comorbidades),
  };
});
</script>

<style scoped>
.dados-clinicos { padding: 20px; }

.dc-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: 14px;
}

.dc-grid {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 14px;
}

.dc-chip {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 13px;
}

.dc-chip--blood  { background: #FFF1F2; color: #BE123C; }
.dc-chip--weight { background: #F0FDF4; color: #15803D; }
.dc-chip--height { background: #EFF6FF; color: #1D4ED8; }

.dc-chip__label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  opacity: 0.7;
  flex: 1;
}

.dc-chip__value {
  font-size: 14px;
  font-weight: 700;
}

/* Section */
.dc-section { margin-bottom: 12px; }

.dc-section__label {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--color-text-muted);
  margin-bottom: 8px;
}

.dc-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.dc-tag {
  padding: 3px 10px;
  border-radius: 100px;
  font-size: 12px;
  font-weight: 600;
}

.dc-tag--alert {
  background: #FFF1F2;
  color: #BE123C;
  border: 1px solid #FECDD3;
}

.dc-empty {
  font-size: 13px;
  color: var(--color-text-muted);
  font-style: italic;
}

/* NoSQL note */
.dc-nosql-note {
  display: flex;
  align-items: flex-start;
  gap: 5px;
  font-size: 11px;
  color: var(--color-text-muted);
  background: var(--color-bg);
  border-radius: 6px;
  padding: 6px 10px;
  margin-top: 4px;
}
</style>
