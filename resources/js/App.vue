<template>
  <div class="page-layout">
    <AppHeader />
    <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>
  </div>
</template>

<script setup>
import AppHeader from '@/components/layout/AppHeader.vue';
</script>
