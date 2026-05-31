import { http } from './http'

export interface User {
  id: number
  name: string
  email: string
  email_verified_at?: string | null
}

export async function login(email: string, password: string): Promise<{ token: string; user: User }> {
  const { data } = await http.post<{ data: { token: string; user: User } }>('/api/login', {
    email,
    password,
  })
  return data.data
}

export async function sendCode(email: string, purpose: 'register' | 'reset_password'): Promise<void> {
  await http.post('/api/auth/send-code', { email, purpose })
}

export async function register(payload: {
  email: string
  code: string
  password: string
  password_confirmation: string
  name?: string
}): Promise<{ token: string; user: User }> {
  const { data } = await http.post<{ data: { token: string; user: User } }>('/api/auth/register', payload)
  return data.data
}

export async function resetPassword(payload: {
  email: string
  code: string
  password: string
  password_confirmation: string
}): Promise<void> {
  await http.post('/api/auth/reset-password', payload)
}

export async function fetchMe(): Promise<User> {
  const { data } = await http.get<{ data: User }>('/api/me')
  return data.data
}

export async function logout(): Promise<void> {
  await http.post('/api/logout')
}
