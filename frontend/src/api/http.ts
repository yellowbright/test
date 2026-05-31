import axios, { type AxiosError } from 'axios'

const TOKEN_KEY = 'jieri_token'

export const http = axios.create({
  baseURL: '',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

export function getStoredToken(): string | null {
  return localStorage.getItem(TOKEN_KEY)
}

export function setStoredToken(token: string | null): void {
  if (token) {
    localStorage.setItem(TOKEN_KEY, token)
  } else {
    localStorage.removeItem(TOKEN_KEY)
  }
}

http.interceptors.request.use((config) => {
  const token = getStoredToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

http.interceptors.response.use(
  (res) => res,
  (err: AxiosError) => {
    if (err.response?.status === 401) {
      setStoredToken(null)
      window.dispatchEvent(new CustomEvent('jieri:unauthorized'))
    }
    return Promise.reject(err)
  },
)

export interface ApiErrorBody {
  message?: string
  errors?: Record<string, string[]>
}

export function formatApiError(err: unknown): string {
  const ax = err as AxiosError<ApiErrorBody>
  const msg = ax.response?.data?.message
  if (msg) {
    return msg
  }
  const errors = ax.response?.data?.errors
  if (errors) {
    const first = Object.values(errors)[0]?.[0]
    if (first) {
      return first
    }
  }
  return ax.message || '请求失败'
}
