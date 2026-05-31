import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import * as authApi from '../api/auth'
import { getStoredToken, setStoredToken } from '../api/http'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(getStoredToken())
  const user = ref<authApi.User | null>(null)

  const isLoggedIn = computed(() => Boolean(token.value))

  function applyToken(next: string | null, u?: authApi.User | null): void {
    token.value = next
    setStoredToken(next)
    if (u !== undefined) {
      user.value = u
    }
  }

  async function hydrateUser(): Promise<void> {
    if (!token.value) {
      user.value = null
      return
    }
    try {
      user.value = await authApi.fetchMe()
    } catch {
      applyToken(null, null)
    }
  }

  async function login(email: string, password: string): Promise<void> {
    const { token: t, user: u } = await authApi.login(email, password)
    applyToken(t, u)
  }

  async function register(payload: Parameters<typeof authApi.register>[0]): Promise<void> {
    const { token: t, user: u } = await authApi.register(payload)
    applyToken(t, u)
  }

  async function logout(): Promise<void> {
    try {
      if (token.value) {
        await authApi.logout()
      }
    } finally {
      applyToken(null, null)
    }
  }

  function onUnauthorized(): void {
    applyToken(null, null)
  }

  return {
    token,
    user,
    isLoggedIn,
    applyToken,
    hydrateUser,
    login,
    register,
    logout,
    onUnauthorized,
  }
})
