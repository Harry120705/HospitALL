<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="modelValue" class="modal-overlay" @mousedown.self="onOverlayClick">
        <div
          class="modal-box"
          :style="{ maxWidth: width }"
          role="dialog"
          :aria-label="title"
        >
          <!-- Header -->
          <div class="modal-header">
            <div class="modal-header__left">
              <div v-if="icon" class="modal-icon" :style="{ background: iconBg }">
                <component :is="icon" :size="18" :color="iconColor" />
              </div>
              <h2 class="modal-title">{{ title }}</h2>
            </div>
            <button class="modal-close" @click="emit('update:modelValue', false)" aria-label="Fechar">
              <X :size="18" />
            </button>
          </div>

          <!-- Body -->
          <div class="modal-body">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { X } from 'lucide-vue-next';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  title:      { type: String,  required: true },
  icon:       { type: Object,  default: null },
  iconBg:     { type: String,  default: '#E0F2FE' },
  iconColor:  { type: String,  default: '#0EA5E9' },
  width:      { type: String,  default: '520px' },
  closeOnOverlay: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue']);

function onOverlayClick() {
  if (props.closeOnOverlay) emit('update:modelValue', false);
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
  padding: 20px;
}

.modal-box {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 24px 64px -12px rgb(0 0 0 / 0.25);
  width: 100%;
  max-height: calc(100vh - 40px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px 16px;
  border-bottom: 1px solid var(--color-border);
}

.modal-header__left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.modal-title {
  font-size: 17px;
  font-weight: 700;
  color: var(--color-text-primary);
}

.modal-close {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  background: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-muted);
  transition: all var(--transition-fast);
}

.modal-close:hover {
  background: var(--color-bg);
  color: var(--color-text-primary);
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
}

/* Transition */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 200ms;
}

.modal-fade-enter-active .modal-box,
.modal-fade-leave-active .modal-box {
  transition: transform 200ms cubic-bezier(0.34, 1.56, 0.64, 1), opacity 200ms;
}

.modal-fade-enter-from {
  opacity: 0;
}
.modal-fade-enter-from .modal-box {
  transform: scale(0.95) translateY(-10px);
  opacity: 0;
}
.modal-fade-leave-to {
  opacity: 0;
}
</style>
