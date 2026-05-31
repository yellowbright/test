<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useMessage } from 'naive-ui'
import { useAuthStore } from './stores/auth'
import { useReminderDraftStore } from './stores/reminderDraft'
import { useIsMobile } from './composables/useIsMobile'
import { formatApiError } from './api/http'
import AppHeader from './components/AppHeader.vue'
import CalendarPanel from './components/CalendarPanel.vue'
import ToolbarPanel from './components/ToolbarPanel.vue'
import MobileDrawer from './components/MobileDrawer.vue'
import AuthModal from './components/AuthModal.vue'

const message = useMessage()
const auth = useAuthStore()
const draft = useReminderDraftStore()
const { isMobile } = useIsMobile(768)

const authOpen = ref(false)

function openAuth(): void {
  authOpen.value = true
}

function onCustomLimit(): void {
  message.warning('自定义提醒最多 10 条')
}

function onUnauthorized(): void {
  auth.onUnauthorized()
  message.warning('登录已过期，请重新登录')
}

async function onAuthed(): Promise<void> {
  try {
    const didSave = await draft.completePendingSave()
    if (didSave) {
      message.success('保存成功')
      draft.drawerExpanded = false
    }
    await draft.syncFromServer()
  } catch (e) {
    message.error(e instanceof Error ? e.message : formatApiError(e))
  }
}

onMounted(async () => {
  window.addEventListener('jieri:custom-limit', onCustomLimit)
  window.addEventListener('jieri:unauthorized', onUnauthorized)
  await auth.hydrateUser()
  if (auth.isLoggedIn) {
    try {
      await draft.syncFromServer()
    } catch (e) {
      message.error(formatApiError(e))
    }
  }
})

onUnmounted(() => {
  window.removeEventListener('jieri:custom-limit', onCustomLimit)
  window.removeEventListener('jieri:unauthorized', onUnauthorized)
})
</script>

<template>
  <div class="min-h-dvh flex flex-col bg-[var(--j-bg)]">
    <AppHeader @login="openAuth" />
    <main
      class="flex-1 flex flex-col md:flex-row gap-4 p-4 w-full max-w-[1200px] mx-auto box-border"
      :class="isMobile ? 'pb-40' : ''"
    >
      <div class="flex-1 min-w-0 min-h-[320px]">
        <h1 class="text-xl font-semibold text-[var(--j-text)] m-0 mb-3 md:text-2xl">节日提醒</h1>
        <CalendarPanel />
      </div>
      <div v-if="!isMobile" class="w-full md:w-[380px] shrink-0">
        <ToolbarPanel @need-auth="openAuth" />
      </div>
    </main>
    <MobileDrawer @need-auth="openAuth" />
    <AuthModal v-model:show="authOpen" @authed="onAuthed" />
  </div>
</template>
