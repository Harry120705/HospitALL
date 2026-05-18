<template>
  <header class="app-header">
    <div class="header-inner">

      <!-- Logo -->
      <RouterLink to="/" class="header-brand">
        <div class="header-brand__logo">
          <span>H+</span>
        </div>
        <div class="header-brand__text">
          <span class="brand-name">HospitALL</span>
          <span class="brand-unit">{{ hospitalStore.nomeHospital }} — {{ hospitalStore.unidade }}</span>
        </div>
      </RouterLink>



      <!-- Right Actions -->
      <div class="header-actions">

        <!-- Notifications -->
        <button class="icon-btn" id="btn-notifications" @click="toggleNotif">
          <Bell :size="18" />
          <span class="notif-dot" aria-label="3 notificações"></span>
        </button>

        <!-- User -->
        <div class="dropdown" ref="userDropdownRef">
          <button class="user-menu-btn" id="btn-user-menu" @click="toggleUserMenu">
            <div class="avatar avatar--green">GM</div>
            <div class="user-info">
              <span class="user-name">Dr. Gabriel Martins</span>
              <span class="user-role">Médico • CRM 142.880</span>
            </div>
            <ChevronDown :size="16" class="chevron" :class="{ 'chevron--open': userMenuOpen }" />
          </button>

          <Transition name="dropdown-fade">
            <div v-if="userMenuOpen" class="dropdown-menu">
              <RouterLink to="/" class="dropdown-item" @click="userMenuOpen = false">
                <User :size="15" /> Meu Perfil
              </RouterLink>
              <RouterLink to="/" class="dropdown-item" @click="userMenuOpen = false">
                <Settings :size="15" /> Configurações
              </RouterLink>
              <div class="dropdown-divider"></div>
              <button class="dropdown-item danger">
                <LogOut :size="15" /> Sair
              </button>
            </div>
          </Transition>
        </div>

      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { RouterLink } from 'vue-router';
import {
  Search, Bell, ChevronDown, User, Settings,
  LogOut, X,
} from 'lucide-vue-next';
import { useHospitalStore } from '@/stores/hospital.js';

const hospitalStore = useHospitalStore();
const userMenuOpen  = ref(false);
const userDropdownRef = ref(null);

function toggleUserMenu() {
  userMenuOpen.value = !userMenuOpen.value;
}

function toggleNotif() {
  // Future: open notifications panel
}

// ── Click-outside handler ─────────────────────────────────────────
/**
 * Fecha o dropdown do usuário quando o clique acontece
 * fora do elemento referenciado por `userDropdownRef`.
 * O listener é adicionado no `document` para capturar TODOS os cliques,
 * e removido no onUnmounted para evitar vazamento de memória.
 */
function handleClickOutside(event) {
  if (
    userMenuOpen.value &&
    userDropdownRef.value &&
    !userDropdownRef.value.contains(event.target)
  ) {
    userMenuOpen.value = false;
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
.app-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: #fff;
  border-bottom: 1px solid var(--color-border);
  box-shadow: 0 1px 0 0 var(--color-border);
}

.header-inner {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 0 32px;
  height: 64px;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
}

/* Brand */
.header-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  flex-shrink: 0;
}

.header-brand__logo {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--color-primary) 0%, #0284C7 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  color: #fff;
  letter-spacing: -0.5px;
}

.header-brand__text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.brand-name {
  font-size: 15px;
  font-weight: 700;
  color: var(--color-text-primary);
  line-height: 1.2;
}

.brand-unit {
  font-size: 11px;
  color: var(--color-text-secondary);
  line-height: 1.2;
}



/* Actions */
.header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: auto;
}

.icon-btn {
  position: relative;
  width: 38px;
  height: 38px;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  background: var(--color-surface);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-secondary);
  transition: all var(--transition-fast);
}

.icon-btn:hover {
  background: var(--color-bg);
  color: var(--color-text-primary);
  border-color: var(--color-primary);
}

.notif-dot {
  position: absolute;
  top: 7px;
  right: 7px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #EF4444;
  border: 2px solid #fff;
}

/* User menu */
.user-menu-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  padding: 5px 10px 5px 6px;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.user-menu-btn:hover {
  background: var(--color-bg);
  border-color: var(--color-primary);
}

.avatar {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
  color: #fff;
  flex-shrink: 0;
}

.avatar--green {
  background: linear-gradient(135deg, #22C55E 0%, #16A34A 100%);
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 1px;
  text-align: left;
}

.user-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-primary);
  line-height: 1.2;
  white-space: nowrap;
}

.user-role {
  font-size: 11px;
  color: var(--color-text-secondary);
  line-height: 1.2;
  white-space: nowrap;
}

.chevron {
  color: var(--color-text-muted);
  transition: transform var(--transition-fast);
}

.chevron--open {
  transform: rotate(180deg);
}

.dropdown-divider {
  height: 1px;
  background: var(--color-border);
  margin: 4px 0;
}

/* Dropdown transition */
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
