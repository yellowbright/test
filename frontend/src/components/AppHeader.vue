<script setup lang="ts">
import { NButton, NSpace } from 'naive-ui'
import { useAuthStore } from '../stores/auth'
import { storeToRefs } from 'pinia'

const auth = useAuthStore()
const { user, isLoggedIn } = storeToRefs(auth)

const emit = defineEmits<{
  login: []
}>()

function onLoginClick(): void {
  emit('login')
}

async function onLogout(): Promise<void> {
  await auth.logout()
}
</script>

<template>
  <header
    class="flex items-center justify-end px-4 py-3 border-b border-[var(--j-border)] bg-[var(--j-surface)] shadow-[var(--j-shadow-sm)]"
    style="transition: border-color var(--j-transition-panel)"
  >
    <n-space align="center" :size="12">
      <span v-if="isLoggedIn" class="text-14px text-[var(--j-muted)] max-w-[200px] truncate" :title="user?.email">
        {{ user?.email }}
      </span>
      <n-button v-if="!isLoggedIn" type="primary" @click="onLoginClick">登录</n-button>
      <n-button v-else quaternary @click="onLogout">退出</n-button>
    </n-space>
  </header>
</template>
