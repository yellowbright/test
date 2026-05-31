<script setup lang="ts">
import { computed, ref } from 'vue'
import {
  NButton,
  NSpace,
  NInput,
  NSelect,
  NEmpty,
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
const { sortedItems, activeDate, saving, customCount, drawerExpanded } = storeToRefs(draft)
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

const formTargetDate = computed(() => activeDate.value ?? sortedItems.value[0]?.date ?? null)

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

function toggleExpand(): void {
  draft.drawerExpanded = !draft.drawerExpanded
}

function openExpand(): void {
  draft.drawerExpanded = true
}

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
  <div
    class="fixed left-0 right-0 bottom-0 z-40 flex flex-col md:hidden pointer-events-none"
    aria-label="提醒工具栏"
  >
    <div
      class="pointer-events-auto bg-[var(--j-surface)] border-t border-[var(--j-border)] rounded-t-[var(--j-radius-md)] shadow-[var(--j-shadow-lg)] flex flex-col overflow-hidden transition-[max-height] ease-out"
      :style="{ transitionDuration: '220ms' }"
      :class="drawerExpanded ? 'max-h-[78vh]' : 'max-h-[140px]'"
    >
      <button
        type="button"
        class="w-full py-2 flex flex-col items-center gap-1 border-none bg-transparent cursor-pointer text-[var(--j-muted)]"
        :aria-expanded="drawerExpanded"
        @click="toggleExpand"
      >
        <span class="block w-10 h-1 rounded-full bg-[var(--j-border)]" />
        <span class="text-12px">{{ drawerExpanded ? '收起' : '拖动或点击展开' }}</span>
      </button>

      <div v-if="!drawerExpanded" class="px-4 pb-3 flex items-center justify-between gap-3">
        <span class="text-14px text-[var(--j-text)]">已选 {{ sortedItems.length }} 天</span>
        <n-button type="primary" size="medium" @click="openExpand">去填写提醒</n-button>
      </div>

      <div v-else class="px-4 pb-4 overflow-y-auto flex-1 min-h-0 space-y-4">
        <div>
          <div class="text-12px text-[var(--j-muted)] mb-2">一键导入</div>
          <n-space :size="8" class="flex flex-wrap">
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
        </div>

        <div class="text-12px text-[var(--j-muted)]">自定义 {{ customCount }} / 10</div>

        <div v-if="!sortedItems.length">
          <n-empty description="在日历上点选日期" size="small" />
        </div>
        <ul v-else class="list-none m-0 p-0 space-y-2">
          <li
            v-for="row in sortedItems"
            :key="row.date"
            class="flex items-center gap-2 rounded-[var(--j-radius-sm)] border border-[var(--j-border)] px-2 py-2"
            :class="activeDate === row.date ? 'bg-blue-50 border-[var(--j-primary)]' : ''"
            @click="selectRow(row.date)"
          >
            <span class="text-13px shrink-0">{{ row.date }}</span>
            <span class="text-13px truncate flex-1">{{ row.content }}</span>
            <n-tag v-if="row.type === 'preset'" size="small" type="info">预</n-tag>
            <n-button size="tiny" quaternary type="error" @click.stop="draft.removeItem(row.date)">删</n-button>
          </li>
        </ul>

        <div class="space-y-2 border-t border-[var(--j-border)] pt-3">
          <n-input
            v-model:value="contentModel"
            type="textarea"
            placeholder="提醒内容"
            :autosize="{ minRows: 2, maxRows: 4 }"
            :disabled="!formTargetDate"
          />
          <div class="flex items-center gap-2 flex-wrap">
            <span class="text-13px text-[var(--j-muted)]">提前</span>
            <n-select
              v-model:value="remindModel"
              :options="remindOptions"
              class="min-w-[130px]"
              :disabled="!formTargetDate"
            />
          </div>
          <n-space :size="8">
            <n-button size="small" secondary :disabled="!formTargetDate" @click="applyAllContent">内容应用全部</n-button>
            <n-button size="small" secondary :disabled="!formTargetDate" @click="applyAllRemind">天数应用全部</n-button>
          </n-space>
          <n-button type="primary" block :loading="saving" @click="save">保存提醒</n-button>
        </div>
      </div>
    </div>
  </div>
</template>
