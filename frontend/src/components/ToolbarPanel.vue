<script setup lang="ts">
import { computed, ref } from 'vue'
import {
  NButton,
  NSpace,
  NInput,
  NSelect,
  NEmpty,
  NCard,
  NTag,
  useMessage,
} from 'naive-ui'
import { storeToRefs } from 'pinia'
import { useReminderDraftStore } from '../stores/reminderDraft'
import type { FestivalCategory } from '../api/presets'
import { useSaveReminders } from '../composables/useSaveReminders'

const emit = defineEmits<{
  'need-auth': []
}>()

const message = useMessage()
const draft = useReminderDraftStore()
const { sortedItems, activeDate, saving, customCount } = storeToRefs(draft)
const { save } = useSaveReminders(() => emit('need-auth'))

const presetLoadingKey = ref<FestivalCategory | null>(null)

const categories: { key: FestivalCategory; label: string }[] = [
  { key: 'default', label: '默认' },
  { key: 'love', label: '爱情' },
  { key: 'western', label: '西方' },
  { key: 'traditional', label: '传统' },
  { key: 'memorial', label: '纪念' },
]

const remindOptions = [
  { label: '当天', value: 0 },
  { label: '提前 1 天', value: 1 },
  { label: '提前 3 天', value: 3 },
  { label: '提前 7 天', value: 7 },
]

const contentModel = computed({
  get: () => {
    const d = formTargetDate.value
    if (!d) {
      return ''
    }
    return draft.findByDate(d)?.content ?? ''
  },
  set: (v: string) => {
    const d = formTargetDate.value
    if (d) {
      draft.updateItem(d, { content: v })
    }
  },
})

const remindModel = computed({
  get: () => {
    const d = formTargetDate.value
    if (!d) {
      return 1
    }
    return draft.findByDate(d)?.remind_before_days ?? 1
  },
  set: (v: number) => {
    const d = formTargetDate.value
    if (d) {
      draft.updateItem(d, { remind_before_days: v })
    }
  },
})

const formTargetDate = computed(() => activeDate.value ?? sortedItems.value[0]?.date ?? null)

async function onCategory(key: FestivalCategory): Promise<void> {
  presetLoadingKey.value = key
  try {
    await draft.applyCategory(key)
    message.success('已导入节日')
  } catch (e) {
    message.error(String(e))
  } finally {
    presetLoadingKey.value = null
  }
}

function selectRow(date: string): void {
  draft.activeDate = date
}

function applyAllContent(): void {
  const t = contentModel.value.trim()
  if (!t) {
    message.warning('请先填写提醒内容')
    return
  }
  draft.applyContentToAll(t)
  message.success('已应用到全部日期')
}

function applyAllRemind(): void {
  draft.applyRemindBeforeToAll(remindModel.value)
  message.success('已应用到全部日期')
}
</script>

<template>
  <n-card
    :bordered="false"
    class="shadow-[var(--j-shadow-md)] rounded-[var(--j-radius-md)]! bg-[var(--j-surface)]"
    content-style="padding: 16px;"
  >
    <div class="text-16px font-semibold text-[var(--j-text)] mb-3">节日与提醒</div>
    <div class="text-12px text-[var(--j-muted)] mb-2">一键导入（保留自定义日期）</div>
    <n-space :size="8" class="flex flex-wrap mb-4">
      <n-button
        v-for="c in categories"
        :key="c.key"
        size="small"
        :type="draft.currentCategory === c.key ? 'primary' : 'default'"
        :loading="presetLoadingKey === c.key"
        @click="onCategory(c.key)"
      >
        {{ c.label }}
      </n-button>
    </n-space>

    <div class="text-14px font-medium text-[var(--j-text)] mb-2">已选日期（{{ sortedItems.length }}）</div>
    <div class="text-12px text-[var(--j-muted)] mb-2">自定义 {{ customCount }} / 10</div>

    <div v-if="!sortedItems.length" class="py-6">
      <n-empty description="点击日历上的日期添加" size="small" />
    </div>
    <ul v-else class="list-none m-0 p-0 max-h-[240px] overflow-y-auto space-y-2 mb-4">
      <li
        v-for="row in sortedItems"
        :key="row.date"
        class="flex items-center gap-2 rounded-[var(--j-radius-sm)] border border-[var(--j-border)] px-3 py-2 cursor-pointer transition-colors"
        :class="activeDate === row.date ? 'bg-blue-50 border-[var(--j-primary)]' : 'hover:bg-slate-50'"
        @click="selectRow(row.date)"
      >
        <span class="text-14px shrink-0">{{ row.date }}</span>
        <span class="text-14px text-[var(--j-text)] truncate flex-1">{{ row.content }}</span>
        <n-tag v-if="row.type === 'preset'" size="small" type="info">预设</n-tag>
        <n-tag v-else size="small">自定义</n-tag>
        <n-button size="tiny" quaternary type="error" @click.stop="draft.removeItem(row.date)">删除</n-button>
      </li>
    </ul>

    <div class="border-t border-[var(--j-border)] pt-4 space-y-3">
      <div class="text-14px text-[var(--j-muted)]">提醒内容</div>
      <n-input
        v-model:value="contentModel"
        type="textarea"
        placeholder="例如：记得订花、发祝福"
        :autosize="{ minRows: 2, maxRows: 5 }"
        :disabled="!formTargetDate"
      />
      <div class="flex flex-wrap items-center gap-3">
        <span class="text-14px text-[var(--j-muted)] shrink-0">提前提醒</span>
        <n-select
          v-model:value="remindModel"
          :options="remindOptions"
          class="min-w-[140px]"
          :disabled="!formTargetDate"
        />
      </div>
      <n-space :size="8" class="flex flex-wrap">
        <n-button secondary size="small" :disabled="!formTargetDate" @click="applyAllContent">内容应用到全部</n-button>
        <n-button secondary size="small" :disabled="!formTargetDate" @click="applyAllRemind">提前天数应用到全部</n-button>
      </n-space>
      <n-button type="primary" block :loading="saving" class="mt-2" style="transition: transform var(--j-transition-fast)" @click="save">
        保存提醒
      </n-button>
    </div>
  </n-card>
</template>
