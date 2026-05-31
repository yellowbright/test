import { http } from './http'

export interface ReminderRow {
  id: number
  user_id: number
  festival_preset_id: number | null
  date: string
  content: string
  remind_before_days: number
  channel: string
  status: string
}

export interface ReminderSaveItem {
  date: string
  content: string
  remind_before_days: number
  festival_preset_id?: number | null
  channel?: 'email' | 'sms'
}

export async function fetchReminders(): Promise<ReminderRow[]> {
  const { data } = await http.get<{ data: ReminderRow[] }>('/api/reminders')
  return data.data
}

export async function saveReminders(dates: ReminderSaveItem[]): Promise<void> {
  await http.post('/api/reminders', { dates })
}
