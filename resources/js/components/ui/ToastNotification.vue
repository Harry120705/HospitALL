<template>
  <div class="toast-container">
    <TransitionGroup name="toast-fade">
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        class="toast"
        :class="`toast--${toast.type}`"
      >
        <div class="toast-icon">
          <CheckCircle v-if="toast.type === 'success'" :size="18" />
          <AlertCircle v-else-if="toast.type === 'error'" :size="18" />
          <Info v-else :size="18" />
        </div>
        <p class="toast-msg">{{ toast.message }}</p>
        <button class="toast-close" @click="toastStore.removeToast(toast.id)">
          <X :size="14" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToastStore } from '@/stores/toast.js';
import { CheckCircle, AlertCircle, Info, X } from 'lucide-vue-next';

const toastStore = useToastStore();
</script>

<style scoped>
.toast-container {
  position: fixed;
  bottom: 24px;
  right: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  z-index: 9999;
}

.toast {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  min-width: 250px;
  max-width: 350px;
  pointer-events: auto;
}

.toast--success {
  background: #F0FDF4;
  border-left: 4px solid #22C55E;
  color: #166534;
}
.toast--success .toast-icon { color: #22C55E; }

.toast--error {
  background: #FEF2F2;
  border-left: 4px solid #EF4444;
  color: #991B1B;
}
.toast--error .toast-icon { color: #EF4444; }

.toast-msg {
  font-size: 14px;
  font-weight: 500;
  flex: 1;
  margin: 0;
  line-height: 1.3;
}

.toast-close {
  background: transparent;
  border: none;
  cursor: pointer;
  color: inherit;
  opacity: 0.6;
  display: flex;
  align-items: center;
  padding: 4px;
}
.toast-close:hover {
  opacity: 1;
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.3s ease;
}
.toast-fade-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.toast-fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
