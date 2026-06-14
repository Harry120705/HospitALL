<template>
  <div class="stat-card card">
    <div class="stat-card__icon" :style="{ background: iconBg }">
      <component :is="icon" :size="20" :color="iconColor" />
    </div>
    <div class="stat-card__body">
      <p class="stat-card__label">{{ label }}</p>
      <p class="stat-card__value">{{ value }}</p>
      <p v-if="subtext" class="stat-card__sub" :class="subtextClass">{{ subtext }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  icon: { type: Object, required: true },
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  subtext: { type: String, default: '' },
  subtextVariant: { type: String, default: 'neutral' }, // 'positive' | 'negative' | 'neutral'
  iconBg: { type: String, default: '#E0F2FE' },
  iconColor: { type: String, default: '#0EA5E9' },
});

const subtextClass = computed(() => {
  return {
    positive: 'stat-card__sub--positive',
    negative: 'stat-card__sub--negative',
    neutral: 'stat-card__sub--neutral',
  }[props.subtextVariant] ?? 'stat-card__sub--neutral';
});
</script>

<style scoped>
.stat-card {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 24px;
}

.stat-card__icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-card__body {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.stat-card__label {
  font-size: 13px;
  color: var(--color-text-secondary);
  font-weight: 500;
}

.stat-card__value {
  font-size: 36px;
  font-weight: 700;
  color: var(--color-text-primary);
  line-height: 1.1;
  letter-spacing: -1px;
}

.stat-card__sub {
  font-size: 12px;
  margin-top: 2px;
}

.stat-card__sub--positive { color: #22C55E; }
.stat-card__sub--negative { color: #EF4444; }
.stat-card__sub--neutral  { color: var(--color-text-secondary); }
</style>
