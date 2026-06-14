<template>
  <div class="page-layout">
    <AppHeader />
    <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>
    <ToastNotification />
  </div>
</template>

<script setup>
import AppHeader from '@/components/layout/AppHeader.vue';
import ToastNotification from '@/components/ui/ToastNotification.vue';
</script>
