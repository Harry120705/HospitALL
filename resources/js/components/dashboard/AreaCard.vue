<template>
  <div class="area-card card" :class="{ 'area-card--active': isHovered }" @mouseenter="isHovered = true" @mouseleave="isHovered = false; menuOpen = false" @click="goToSetor">

    <!-- Header -->
    <div class="area-card__header">
      <div class="area-card__icon-wrap">
        <component :is="resolvedIcon" :size="22" class="area-card__icon" />
      </div>
      <!-- Context menu -->
      <div class="dropdown" ref="dropdownRef">
        <button class="icon-btn-sm" :id="`btn-menu-${setor._id || setor.id}`" @click.stop="menuOpen = !menuOpen">
          <MoreVertical :size="16" />
        </button>
        <Transition name="dropdown-fade">
          <div v-if="menuOpen" class="dropdown-menu area-card__menu">
            <button v-if="appStore.userRole !== 'RECEPCIONISTA'" class="dropdown-item" @click.stop="emit('edit', setor); menuOpen = false">
              <Pencil :size="14" /> Editar área
            </button>
            <div v-if="appStore.userRole !== 'RECEPCIONISTA'" class="dropdown-divider"></div>
            <button v-if="appStore.userRole !== 'RECEPCIONISTA'" class="dropdown-item danger" @click.stop="emit('delete', setor); menuOpen = false">
              <Trash2 :size="14" /> Excluir
            </button>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Content -->
    <div class="area-card__content">
      <h3 class="area-card__title">{{ setor.nome }}</h3>
      <p class="area-card__desc">{{ setor.descricao }}</p>
    </div>

    <!-- Occupation Bar -->
    <div class="area-card__occ">
      <div class="occ-label-row">
        <span class="occ-label">Ocupação</span>
        <span class="occ-count">{{ setor.ocupacao_atual }}/{{ setor.capacidade_maxima }}</span>
      </div>
      <div class="occ-bar">
        <div
          class="occ-bar__fill"
          :class="occClass"
          :style="{ width: occPercent + '%' }"
        ></div>
      </div>
    </div>



  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import {
  MoreVertical, Pencil, BarChart2, Trash2, ArrowRight,
  Baby, Scissors, Activity, AlarmCheck, HeartHandshake,
  HeartPulse, Bone, Brain, BedDouble, Eye, Stethoscope,
} from 'lucide-vue-next';
import { useAppStore } from '@/stores/app.js';

const appStore = useAppStore();

const props = defineProps({
  setor: { type: Object, required: true },
});

const emit = defineEmits(['edit', 'report', 'delete']);

const isHovered = ref(false);
const menuOpen = ref(false);

// Map icon string → Lucide component
const ICON_MAP = {
  baby: Baby,
  scissors: Scissors,
  activity: Activity,
  'alarm-check': AlarmCheck,
  'heart-handshake': HeartHandshake,
  'heart-pulse': HeartPulse,
  bone: Bone,
  brain: Brain,
  eye: Eye,
  stethoscope: Stethoscope,
};

const resolvedIcon = computed(() => ICON_MAP[props.setor.icone] ?? BedDouble);

// Occupation logic
const occPercent = computed(() =>
  Math.min(100, Math.round((props.setor.ocupacao_atual / props.setor.capacidade_maxima) * 100))
);

const occClass = computed(() => {
  if (occPercent.value >= 91) return 'occ-bar__fill--high';
  if (occPercent.value >= 60)  return 'occ-bar__fill--medium';
  return 'occ-bar__fill--low';
});

const dropdownRef = ref(null);
const router = useRouter();

function goToSetor() {
  const id = props.setor._id || props.setor.id || props.setor.id_setor;
  if (id) {
    router.push(`/setor/${id}`);
  }
}

function handleClickOutside(event) {
  if (menuOpen.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    menuOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.area-card {
  padding: 20px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-height: 180px;
}

.area-card--active {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 1px var(--color-primary), 0 4px 16px -2px rgb(14 165 233 / 0.15);
}

/* Header */
.area-card__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.area-card__icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: var(--color-primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
}

.area-card__icon {
  color: var(--color-primary);
}

.icon-btn-sm {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: none;
  background: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-muted);
  transition: all var(--transition-fast);
}

.icon-btn-sm:hover {
  background: var(--color-bg);
  color: var(--color-text-primary);
}

.area-card__menu {
  right: 0;
  top: calc(100% + 4px);
}

/* Content */
.area-card__content {
  flex: 1;
}

.area-card__title {
  font-size: 16px;
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: 4px;
}

.area-card__desc {
  font-size: 12px;
  color: var(--color-text-secondary);
  line-height: 1.4;
}

/* Occupation */
.area-card__occ {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.occ-label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.occ-label {
  font-size: 12px;
  color: var(--color-text-secondary);
}

.occ-count {
  font-size: 12px;
  font-weight: 600;
  color: var(--color-text-primary);
}



.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 150ms, transform 150ms;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
