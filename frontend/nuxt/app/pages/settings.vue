<template>
  <div class="min-h-full flex flex-1 min-w-0">
    <Sidebar @share="handleShare" @logout="handleLogout" />

    <main class="flex-1 min-w-0 flex flex-col overflow-y-auto">
      <h1 class="text-white text-xl font-semibold py-4 px-6 border-b border-gray-600">
        ユーザー設定
      </h1>

      <!-- タブ -->
      <nav class="flex gap-0 border-b border-gray-600 px-6" aria-label="設定タブ">
        <button
          v-for="t in tabs"
          :key="t.id"
          type="button"
          class="py-3 px-4 text-sm font-medium border-b-2 transition-colors -mb-px"
          :class="activeTab === t.id
            ? 'text-violet-400 border-violet-400'
            : 'text-gray-400 border-transparent hover:text-gray-300'"
          @click="activeTab = t.id"
        >
          {{ t.label }}
        </button>
      </nav>

      <div class="p-6 flex flex-col gap-10 max-w-xl">
        <!-- アカウントタブ -->
        <template v-if="activeTab === 'account'">
          <section class="flex flex-col gap-4">
            <h2 class="text-white text-base font-medium">
              ユーザーネームの変更
            </h2>
            <p v-if="currentUserName" class="text-sm text-gray-400">
              現在のユーザーネーム: {{ currentUserName }}
            </p>
            <form
              class="flex flex-col gap-4"
              @submit.prevent="onSubmitUserName"
            >
              <div class="flex flex-col gap-2">
                <label for="user-name" class="text-sm font-medium text-gray-300">
                  新しいユーザーネーム
                </label>
                <input
                  id="user-name"
                  v-model="userNameForm.name"
                  type="text"
                  class="w-full py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
                  placeholder="ユーザーネーム"
                />
              </div>
              <p v-if="userNameMessage" class="text-sm" :class="userNameError ? 'text-red-400' : 'text-green-400'">
                {{ userNameMessage }}
              </p>
              <button
                type="submit"
                class="w-fit px-6 py-2.5 text-sm font-semibold text-white bg-violet-600 rounded-lg hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500 disabled:opacity-60"
                :disabled="userNameForm.name.trim() === '' || userNameLoading"
              >
                {{ userNameLoading ? '変更中...' : 'ユーザーネームを変更する' }}
              </button>
            </form>
          </section>

          <section class="flex flex-col gap-4">
            <h2 class="text-white text-base font-medium">
              パスワードの変更
            </h2>
            <form
              class="flex flex-col gap-4"
              @submit.prevent="onSubmitPassword"
            >
              <div class="flex flex-col gap-2">
                <label for="current-password" class="text-sm font-medium text-gray-300">
                  現在のパスワード
                </label>
                <input
                  id="current-password"
                  v-model="passwordForm.currentPassword"
                  type="password"
                  class="w-full py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
                  placeholder="現在のパスワード"
                  autocomplete="current-password"
                />
              </div>
              <div class="flex flex-col gap-2">
                <label for="new-password" class="text-sm font-medium text-gray-300">
                  新しいパスワード
                </label>
                <input
                  id="new-password"
                  v-model="passwordForm.newPassword"
                  type="password"
                  class="w-full py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
                  placeholder="新しいパスワード"
                  autocomplete="new-password"
                />
              </div>
              <div class="flex flex-col gap-2">
                <label for="new-password-confirm" class="text-sm font-medium text-gray-300">
                  新しいパスワード（確認）
                </label>
                <input
                  id="new-password-confirm"
                  v-model="passwordForm.newPasswordConfirm"
                  type="password"
                  class="w-full py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
                  placeholder="新しいパスワード（確認）"
                  autocomplete="new-password"
                />
              </div>
              <button
                type="submit"
                class="w-fit px-6 py-2.5 text-sm font-semibold text-white bg-violet-600 rounded-lg hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500 disabled:opacity-60"
                :disabled="!isPasswordFormValid"
              >
                パスワードを変更する
              </button>
            </form>
          </section>
        </template>

        <!-- その他タブ（今後の設定項目用） -->
        <template v-else-if="activeTab === 'other'">
          <p class="text-gray-400 text-sm">
            設定項目は今後追加予定です。
          </p>
        </template>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'home', middleware: 'auth' })

type TabId = 'account' | 'other'

const tabs: { id: TabId; label: string }[] = [
  { id: 'account', label: 'アカウント' },
  { id: 'other', label: 'その他' },
]

const route = useRoute()
const router = useRouter()

const activeTab = computed({
  get: () => {
    const t = route.query.tab as string | undefined
    return tabs.some((tab) => tab.id === t) ? (t as TabId) : 'account'
  },
  set: (value: TabId) => {
    router.replace({ query: { ...route.query, tab: value } })
  },
})

const config = useRuntimeConfig()
const currentUserName = ref<string | null>(null)
const userNameForm = reactive({
  name: '',
})
const userNameLoading = ref(false)
const userNameMessage = ref<string | null>(null)
const userNameError = ref(false)

function getXsrfToken(): string | null {
  if (process.client) {
    const name = 'XSRF-TOKEN='
    const decodedCookie = decodeURIComponent(document.cookie ?? '')
    const parts = decodedCookie.split('; ')
    const cookie = parts.find((c) => c.startsWith(name))
    if (cookie) {
      return cookie.substring(name.length)
    }
  }
  return null
}

const passwordForm = reactive({
  currentPassword: '',
  newPassword: '',
  newPasswordConfirm: '',
})

const isPasswordFormValid = computed(() => {
  const { currentPassword, newPassword, newPasswordConfirm } = passwordForm
  return (
    currentPassword.trim() !== '' &&
    newPassword.trim() !== '' &&
    newPasswordConfirm.trim() !== '' &&
    newPassword === newPasswordConfirm
  )
})

function handleShare(_text: string) {
  // 設定ページではシェアは未使用（必要なら親で処理）
}

function handleLogout() {
  navigateTo('/login')
}

async function fetchUser() {
  try {
    const apiBase = config.public.apiBase
    const user = await $fetch<{ name: string }>('/api/user', {
      baseURL: apiBase,
      credentials: 'include',
    })
    currentUserName.value = user.name
  } catch {
    currentUserName.value = null
  }
}

onMounted(fetchUser)

async function onSubmitUserName() {
  if (userNameForm.name.trim() === '') return
  userNameMessage.value = null
  userNameError.value = false
  userNameLoading.value = true

  try {
    const apiBase = config.public.apiBase
    if (!getXsrfToken()) {
      await $fetch('/sanctum/csrf-cookie', {
        baseURL: apiBase,
        credentials: 'include',
      })
    }
    const xsrfToken = getXsrfToken()

    const res = await $fetch<{ user: { name: string } }>('/api/user', {
      baseURL: apiBase,
      method: 'PATCH',
      credentials: 'include',
      headers: xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : undefined,
      body: { name: userNameForm.name.trim() },
    })

    currentUserName.value = res.user.name
    userNameMessage.value = 'ユーザーネームを変更しました。'
    userNameError.value = false
  } catch (err: any) {
    const message =
      err?.data?.message ||
      err?.data?.errors?.name?.[0] ||
      err?.response?._data?.message ||
      err?.response?._data?.errors?.name?.[0] ||
      'ユーザーネームの変更に失敗しました。'
    userNameMessage.value = message
    userNameError.value = true
  } finally {
    userNameLoading.value = false
  }
}

function onSubmitPassword() {
  // TODO: パスワード変更APIを呼び出す
  if (!isPasswordFormValid.value) return
  if (passwordForm.newPassword !== passwordForm.newPasswordConfirm) return
  console.log('パスワード変更')
  passwordForm.currentPassword = ''
  passwordForm.newPassword = ''
  passwordForm.newPasswordConfirm = ''
}
</script>
