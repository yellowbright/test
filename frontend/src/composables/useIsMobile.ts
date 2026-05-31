import { ref, onMounted, onUnmounted } from 'vue'

export function useIsMobile(breakpoint = 768): { isMobile: ReturnType<typeof ref<boolean>> } {
  const isMobile = ref(false)
  let mq: MediaQueryList | null = null

  function update(): void {
    if (mq) {
      isMobile.value = mq.matches
    }
  }

  onMounted(() => {
    mq = window.matchMedia(`(max-width: ${breakpoint}px)`)
    update()
    mq.addEventListener('change', update)
  })

  onUnmounted(() => {
    mq?.removeEventListener('change', update)
  })

  return { isMobile }
}
