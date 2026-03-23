<template>
  <aside class="shrink-0 w-64 flex flex-col p-6 border-r border-gray-600">
    <a href="/" class="block no-underline mb-8" @click.prevent="navigateTo('/')">
      <img src="/icons/logo.png" alt="SHARE" class="block h-8 w-auto" />
    </a>

    <nav class="flex flex-col gap-2 mb-8">
      <NuxtLink to="/" class="flex items-center gap-3 py-2 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50" :class="{ 'bg-gray-600/50': route.path === '/' }">
        <img src="/icons/home.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
        <span class="text-sm font-medium">ホーム</span>
      </NuxtLink>
      <NuxtLink to="/filter" class="flex items-center gap-3 py-2 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50" :class="{ 'bg-gray-600/50': route.path === '/filter' }">
        <img src="/icons/feather.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
        <span class="text-sm font-medium">マイ投稿</span>
      </NuxtLink>
      <NuxtLink to="/settings" class="flex items-center gap-3 py-2 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50" :class="{ 'bg-gray-600/50': route.path === '/settings' }">
        <img src="/icons/profile.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
        <span class="text-sm font-medium">ユーザー設定</span>
      </NuxtLink>
      <NuxtLink to="/login" class="flex items-center gap-3 py-2 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50" @click.prevent="emit('logout')">
        <img src="/icons/logout.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
        <span class="text-sm">ログアウト</span>
      </NuxtLink>
    </nav>

    <!-- シェア（必須・120文字以内） -->
    <div class="flex flex-col gap-3">
      <h2 class="text-white text-sm font-medium">シェア</h2>
      <textarea
        v-model="shareText"
        maxlength="120"
        placeholder="つぶやきを入力..."
        rows="4"
        class="w-full min-h-[120px] py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg resize-y placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
        :class="shareError ? 'border-red-500' : ''"
        @input="shareError = ''"
      />
      <p class="text-xs text-right transition-colors" :class="shareText.length >= SHARE_MAX ? 'text-red-400' : 'text-gray-400'">{{ shareText.length }}/{{ SHARE_MAX }}</p>
      <p v-if="shareError" class="text-sm text-red-400">{{ shareError }}</p>
      <SubmitButton label="シェアする" button-type="button" block @click="onShareClick" />
    </div>
  </aside>
</template>

<script setup lang="ts">
const SHARE_MAX = 120;

const route = useRoute();
const emit = defineEmits<{ share: [text: string]; logout: [] }>();

const shareText = ref("");
const shareError = ref("");

function onShareClick() {
  shareError.value = "";
  const t = shareText.value.trim();
  if (!t) {
    shareError.value = "つぶやきを入力してください。";
    return;
  }
  if (shareText.value.length > SHARE_MAX) {
    shareError.value = `つぶやきは${SHARE_MAX}文字以内で入力してください。`;
    return;
  }
  emit("share", t);
  shareText.value = "";
}
</script>
