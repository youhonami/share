<template>
  <aside class="shrink-0 w-64 flex flex-col p-6 border-r border-gray-600">
    <a
      href="/"
      class="block no-underline mb-8"
      @click.prevent="navigateTo('/')"
    >
      <img src="/icons/logo.png" alt="SHARE" class="block h-8 w-auto" />
    </a>

    <nav class="flex flex-col gap-2 mb-8">
      <NuxtLink
        to="/"
        class="flex items-center gap-3 py-2 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50"
        :class="{ 'bg-gray-600/50': route.path === '/' }"
      >
        <img :src="homeIcon" alt="" class="w-5 h-5 shrink-0 object-contain" />
        <span class="text-sm font-medium">ホーム</span>
      </NuxtLink>
      <NuxtLink
        to="/login"
        class="flex items-center gap-3 py-2 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50"
        @click.prevent="emit('logout')"
      >
        <img :src="logoutIcon" alt="" class="w-5 h-5 shrink-0 object-contain" />
        <span class="text-sm">ログアウト</span>
      </NuxtLink>
    </nav>

    <div class="flex flex-col gap-3">
      <h2 class="text-white text-sm font-medium">シェア</h2>
      <textarea
        v-model="shareText"
        placeholder="つぶやきを入力..."
        class="w-full min-h-[120px] py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg resize-y placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
        rows="4"
      />
      <button
        type="button"
        class="w-full py-3 px-4 text-sm font-semibold text-white bg-violet-600 rounded-lg hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500"
        @click="onShareClick"
      >
        シェアする
      </button>
    </div>
  </aside>
</template>

<script setup lang="ts">
import homeIcon from '~/assets/icons/home.png'
import logoutIcon from '~/assets/icons/logout.png'

const route = useRoute()
const emit = defineEmits<{
  share: [text: string]
  logout: []
}>()

const shareText = ref('')

function onShareClick() {
  const text = shareText.value.trim()
  if (!text) return
  emit('share', text)
  shareText.value = ''
}
</script>
