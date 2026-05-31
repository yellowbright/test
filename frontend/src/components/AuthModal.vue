<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import {
  NModal,
  NTabs,
  NTabPane,
  NForm,
  NFormItem,
  NInput,
  NButton,
  NSpace,
  useMessage,
} from 'naive-ui'
import { useAuthStore } from '../stores/auth'
import * as authApi from '../api/auth'
import { formatApiError } from '../api/http'

const props = defineProps<{
  show: boolean
}>()

const emit = defineEmits<{
  'update:show': [v: boolean]
  authed: []
}>()

const message = useMessage()
const auth = useAuthStore()

const tab = ref<'login' | 'register' | 'reset'>('login')
const loading = ref(false)

const loginEmail = ref('')
const loginPassword = ref('')

const regEmail = ref('')
const regCode = ref('')
const regPassword = ref('')
const regPassword2 = ref('')
const regName = ref('')

const resetEmail = ref('')
const resetCode = ref('')
const resetPassword = ref('')
const resetPassword2 = ref('')

const codeCooldown = ref(0)
let cooldownTimer: ReturnType<typeof setInterval> | null = null

const modalTitle = computed(() => {
  if (tab.value === 'login') {
    return '登录'
  }
  if (tab.value === 'register') {
    return '注册'
  }
  return '重置密码'
})

watch(
  () => props.show,
  (v) => {
    if (v) {
      loading.value = false
    }
  },
)

watch(tab, () => {
  loading.value = false
})

function close(): void {
  emit('update:show', false)
}

function startCooldown(seconds: number): void {
  codeCooldown.value = seconds
  if (cooldownTimer) {
    clearInterval(cooldownTimer)
  }
  cooldownTimer = setInterval(() => {
    codeCooldown.value -= 1
    if (codeCooldown.value <= 0 && cooldownTimer) {
      clearInterval(cooldownTimer)
      cooldownTimer = null
    }
  }, 1000)
}

async function sendRegisterCode(): Promise<void> {
  if (!regEmail.value) {
    message.warning('请填写邮箱')
    return
  }
  try {
    loading.value = true
    await authApi.sendCode(regEmail.value, 'register')
    message.success('验证码已发送')
    startCooldown(60)
  } catch (e) {
    message.error(formatApiError(e))
  } finally {
    loading.value = false
  }
}

async function sendResetCode(): Promise<void> {
  if (!resetEmail.value) {
    message.warning('请填写邮箱')
    return
  }
  try {
    loading.value = true
    await authApi.sendCode(resetEmail.value, 'reset_password')
    message.success('验证码已发送')
    startCooldown(60)
  } catch (e) {
    message.error(formatApiError(e))
  } finally {
    loading.value = false
  }
}

async function submitLogin(): Promise<void> {
  try {
    loading.value = true
    await auth.login(loginEmail.value, loginPassword.value)
    message.success('登录成功')
    close()
    emit('authed')
  } catch (e) {
    message.error(formatApiError(e))
  } finally {
    loading.value = false
  }
}

async function submitRegister(): Promise<void> {
  try {
    loading.value = true
    await auth.register({
      email: regEmail.value,
      code: regCode.value,
      password: regPassword.value,
      password_confirmation: regPassword2.value,
      name: regName.value || undefined,
    })
    message.success('注册成功')
    close()
    emit('authed')
  } catch (e) {
    message.error(formatApiError(e))
  } finally {
    loading.value = false
  }
}

async function submitReset(): Promise<void> {
  try {
    loading.value = true
    await authApi.resetPassword({
      email: resetEmail.value,
      code: resetCode.value,
      password: resetPassword.value,
      password_confirmation: resetPassword2.value,
    })
    message.success('密码已重置，请登录')
    tab.value = 'login'
    loginEmail.value = resetEmail.value
  } catch (e) {
    message.error(formatApiError(e))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <n-modal
    :show="show"
    preset="card"
    :title="modalTitle"
    :style="{ width: 'min(420px, 92vw)', borderRadius: 'var(--j-radius-lg)' }"
    :mask-closable="false"
    @update:show="emit('update:show', $event)"
  >
    <n-tabs v-model:value="tab" type="line" animated>
      <n-tab-pane name="login" tab="登录">
        <n-form label-placement="left" label-width="72">
          <n-form-item label="邮箱">
            <n-input v-model:value="loginEmail" type="text" placeholder="you@example.com" />
          </n-form-item>
          <n-form-item label="密码">
            <n-input v-model:value="loginPassword" type="password" show-password-on="click" />
          </n-form-item>
          <n-button type="primary" block :loading="loading" class="mt-2 w-full" @click="submitLogin">登录</n-button>
        </n-form>
      </n-tab-pane>
      <n-tab-pane name="register" tab="注册">
        <n-form label-placement="left" label-width="72">
          <n-form-item label="邮箱">
            <n-input v-model:value="regEmail" type="text" placeholder="you@example.com" />
          </n-form-item>
          <n-form-item label="验证码">
            <n-space :size="8" class="w-full">
              <n-input v-model:value="regCode" placeholder="6 位数字" maxlength="6" />
              <n-button :disabled="codeCooldown > 0 || loading" @click="sendRegisterCode">
                {{ codeCooldown > 0 ? `${codeCooldown}s` : '发送验证码' }}
              </n-button>
            </n-space>
          </n-form-item>
          <n-form-item label="密码">
            <n-input v-model:value="regPassword" type="password" show-password-on="click" />
          </n-form-item>
          <n-form-item label="确认密码">
            <n-input v-model:value="regPassword2" type="password" show-password-on="click" />
          </n-form-item>
          <n-form-item label="昵称">
            <n-input v-model:value="regName" placeholder="可选" />
          </n-form-item>
          <n-button type="primary" block :loading="loading" class="mt-2" @click="submitRegister">
            注册并登录
          </n-button>
        </n-form>
      </n-tab-pane>
      <n-tab-pane name="reset" tab="找回密码">
        <n-form label-placement="left" label-width="72">
          <n-form-item label="邮箱">
            <n-input v-model:value="resetEmail" type="text" />
          </n-form-item>
          <n-form-item label="验证码">
            <n-space :size="8" class="w-full">
              <n-input v-model:value="resetCode" maxlength="6" />
              <n-button :disabled="codeCooldown > 0 || loading" @click="sendResetCode">
                {{ codeCooldown > 0 ? `${codeCooldown}s` : '发送验证码' }}
              </n-button>
            </n-space>
          </n-form-item>
          <n-form-item label="新密码">
            <n-input v-model:value="resetPassword" type="password" show-password-on="click" />
          </n-form-item>
          <n-form-item label="确认密码">
            <n-input v-model:value="resetPassword2" type="password" show-password-on="click" />
          </n-form-item>
          <n-button type="primary" block :loading="loading" class="mt-2" @click="submitReset">
            重置密码
          </n-button>
        </n-form>
      </n-tab-pane>
    </n-tabs>
  </n-modal>
</template>
