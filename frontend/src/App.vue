<script setup lang="ts">
import { NConfigProvider, NMessageProvider } from 'naive-ui'
import MainLayout from './MainLayout.vue'

const themeOverrides = {
  common: {
    primaryColor: '#3b82f6',
    primaryColorHover: '#2563eb',
    primaryColorPressed: '#1d4ed8',
    borderRadius: '8px',
  },
}
</script>

<template>
  <n-config-provider :theme-overrides="themeOverrides">
    <n-message-provider>
      <MainLayout />
    </n-message-provider>
  </n-config-provider>
</template>
