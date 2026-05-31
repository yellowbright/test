import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { FestivalCategory } from '../api/presets'
import { fetchPresets } from '../api/presets'
import { fetchReminders, saveReminders, type ReminderSaveItem } from '../api/reminders'
import { ymdForPresetYear } from '../utils/date'

export const MAX_CUSTOM_REMINDERS = 10

export type DraftType = 'preset' | 'custom'

export interface DraftItem {
  date: string
  content: string
  type: DraftType
  festival_preset_id?: number | null
  remind_before_days: number
}

export const useReminderDraftStore = defineStore('reminderDraft', () => {
  const items = ref<DraftItem[]>([])
  const activeDate = ref<string | null>(null)
  const calendarYear = ref(new Date().getFullYear())
  const currentCategory = ref<FestivalCategory | null>(null)
  const pendingSaveAfterAuth = ref(false)
  const drawerExpanded = ref(false)
  const saving = ref(false)

  const customCount = computed(() => items.value.filter((i) => i.type === 'custom').length)

  const sortedItems = computed(() => [...items.value].sort((a, b) => a.date.localeCompare(b.date)))

  function setCalendarYear(y: number): void {
    calendarYear.value = y
  }

  /** 切换日历时若已选分类，按新年份重算预设日期 */
  async function refreshPresetsForCurrentYear(): Promise<void> {
    const cat = currentCategory.value
    if (cat) {
      await applyCategory(cat)
    }
  }

  function findByDate(date: string): DraftItem | undefined {
    return items.value.find((i) => i.date === date)
  }

  function toggleDateFromCalendar(date: string): boolean {
    const existing = findByDate(date)
    if (existing) {
      items.value = items.value.filter((i) => i.date !== date)
      if (activeDate.value === date) {
        activeDate.value = sortedItems.value[0]?.date ?? null
      }
      return true
    }
    if (customCount.value >= MAX_CUSTOM_REMINDERS) {
      return false
    }
    items.value.push({
      date,
      content: '自定义',
      type: 'custom',
      festival_preset_id: null,
      remind_before_days: 1,
    })
    activeDate.value = date
    return true
  }

  function removeItem(date: string): void {
    items.value = items.value.filter((i) => i.date !== date)
    if (activeDate.value === date) {
      activeDate.value = sortedItems.value[0]?.date ?? null
    }
  }

  function updateItem(date: string, patch: Partial<Pick<DraftItem, 'content' | 'remind_before_days'>>): void {
    const idx = items.value.findIndex((i) => i.date === date)
    if (idx === -1) {
      return
    }
    const next = { ...items.value[idx], ...patch }
    items.value.splice(idx, 1, next)
  }

  function applyContentToAll(content: string): void {
    items.value = items.value.map((i) => ({ ...i, content }))
  }

  function applyRemindBeforeToAll(days: number): void {
    items.value = items.value.map((i) => ({ ...i, remind_before_days: days }))
  }

  async function applyCategory(category: FestivalCategory): Promise<void> {
    currentCategory.value = category
    items.value = items.value.filter((i) => i.type === 'custom')
    const presets = await fetchPresets(category)
    const year = calendarYear.value
    const nextPresetItems: DraftItem[] = []
    for (const p of presets) {
      const ymd = ymdForPresetYear(year, p.month, p.day)
      if (!ymd) {
        continue
      }
      nextPresetItems.push({
        date: ymd,
        content: p.name,
        type: 'preset',
        festival_preset_id: p.id,
        remind_before_days: 1,
      })
    }
    const byDate = new Map<string, DraftItem>()
    for (const i of items.value.filter((x) => x.type === 'custom')) {
      byDate.set(i.date, i)
    }
    for (const i of nextPresetItems) {
      byDate.set(i.date, i)
    }
    items.value = [...byDate.values()]
    if (!activeDate.value && items.value.length) {
      activeDate.value = sortedItems.value[0]!.date
    }
  }

  function rowsForApi(): ReminderSaveItem[] {
    return items.value.map((i) => ({
      date: i.date,
      content: i.content.trim(),
      remind_before_days: i.remind_before_days,
      festival_preset_id: i.festival_preset_id ?? null,
      channel: 'email' as const,
    }))
  }

  async function syncFromServer(): Promise<void> {
    const rows = await fetchReminders()
    items.value = rows.map((r) => ({
      date: typeof r.date === 'string' ? r.date : String(r.date).slice(0, 10),
      content: r.content,
      type: r.festival_preset_id ? 'preset' : 'custom',
      festival_preset_id: r.festival_preset_id,
      remind_before_days: r.remind_before_days,
    }))
    activeDate.value = sortedItems.value[0]?.date ?? null
  }

  async function persist(): Promise<void> {
    const payload = rowsForApi()
    if (!payload.length) {
      throw new Error('请先选择至少一个日期')
    }
    const bad = payload.find((p) => !p.content)
    if (bad) {
      throw new Error('请为所有已选日期填写提醒内容')
    }
    saving.value = true
    try {
      await saveReminders(payload)
    } finally {
      saving.value = false
    }
  }

  function requestSaveWithAuthGate(): void {
    pendingSaveAfterAuth.value = true
  }

  function clearPendingSave(): void {
    pendingSaveAfterAuth.value = false
  }

  /** 登录/注册成功后若因保存打开过弹窗，则继续提交；返回是否执行了保存 */
  async function completePendingSave(): Promise<boolean> {
    if (!pendingSaveAfterAuth.value) {
      return false
    }
    await persist()
    pendingSaveAfterAuth.value = false
    return true
  }

  return {
    items,
    activeDate,
    calendarYear,
    currentCategory,
    pendingSaveAfterAuth,
    drawerExpanded,
    saving,
    customCount,
    sortedItems,
    setCalendarYear,
    refreshPresetsForCurrentYear,
    findByDate,
    toggleDateFromCalendar,
    removeItem,
    updateItem,
    applyContentToAll,
    applyRemindBeforeToAll,
    applyCategory,
    syncFromServer,
    persist,
    requestSaveWithAuthGate,
    clearPendingSave,
    completePendingSave,
  }
})
