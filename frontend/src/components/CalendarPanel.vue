<script setup lang="ts">
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import zhCnLocale from '@fullcalendar/core/locales/zh-cn'
import { computed, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useReminderDraftStore } from '../stores/reminderDraft'
import { toYmd } from '../utils/date'

const draft = useReminderDraftStore()
const { items } = storeToRefs(draft)

const lastYear = ref<number | null>(null)

const calendarOptions = computed(() => {
  void items.value.map((i) => `${i.date}:${i.content}`).join('|')
  return {
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: zhCnLocale,
    firstDay: 1,
    headerToolbar: {
      left: 'prev',
      center: 'title',
      right: 'next',
    },
    height: 'auto',
    fixedWeekCount: false,
    dateClick: (info: { dateStr: string }) => {
      const ok = draft.toggleDateFromCalendar(info.dateStr)
      if (!ok) {
        window.dispatchEvent(new CustomEvent('jieri:custom-limit'))
      }
    },
    datesSet: (info: { view: { currentStart: Date } }) => {
      const y = info.view.currentStart.getFullYear()
      draft.setCalendarYear(y)
      if (lastYear.value !== null && lastYear.value !== y) {
        void draft.refreshPresetsForCurrentYear()
      }
      lastYear.value = y
    },
    dayCellClassNames: (arg: { date: Date; isToday: boolean }) => {
      const dateStr = toYmd(arg.date)
      const classes: string[] = []
      if (arg.isToday) {
        classes.push('fc-day-jieri-today')
      }
      const item = draft.findByDate(dateStr)
      if (item) {
        classes.push('fc-day-jieri-selected')
        if (item.content.trim()) {
          classes.push('fc-day-jieri-reminder')
        }
      }
      return classes
    },
  }
})

</script>

<template>
  <section
    class="calendar-panel bg-[var(--j-surface)] rounded-[var(--j-radius-md)] p-3 md:p-4 shadow-[var(--j-shadow-md)] min-h-[380px]"
  >
    <FullCalendar :options="calendarOptions" />
  </section>
</template>
