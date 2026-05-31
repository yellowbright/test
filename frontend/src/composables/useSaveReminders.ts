import { useMessage } from 'naive-ui'
import { useAuthStore } from '../stores/auth'
import { useReminderDraftStore } from '../stores/reminderDraft'
import { formatApiError } from '../api/http'

export function useSaveReminders(onNeedAuth: () => void): { save: () => Promise<void> } {
  const message = useMessage()
  const auth = useAuthStore()
  const draft = useReminderDraftStore()

  async function save(): Promise<void> {
    if (!draft.items.length) {
      message.warning('请先选择日期')
      return
    }
    if (!auth.isLoggedIn) {
      draft.requestSaveWithAuthGate()
      onNeedAuth()
      return
    }
    try {
      await draft.persist()
      message.success('保存成功')
      draft.drawerExpanded = false
      await draft.syncFromServer()
    } catch (e) {
      const msg = e instanceof Error ? e.message : formatApiError(e)
      message.error(msg)
    }
  }

  return { save }
}
