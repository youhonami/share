<template>
  <!-- モバイル：トップバー -->
  <header
    class="lg:hidden shrink-0 flex items-center gap-3 px-3 py-3 border-b border-gray-600 bg-gray-800/50"
  >
    <button
      type="button"
      class="p-2 rounded-lg text-white hover:bg-gray-600/50 -m-1"
      aria-label="メニューを開く"
      @click="menuOpen = true"
    >
      <span class="block w-5 h-0.5 bg-current mb-1.5 rounded" />
      <span class="block w-5 h-0.5 bg-current mb-1.5 rounded" />
      <span class="block w-5 h-0.5 bg-current rounded" />
    </button>
    <h1 class="text-white text-base font-semibold flex-1 min-w-0 truncate">
      {{ mobileTitle }}
    </h1>
    <NuxtLink to="/" class="shrink-0 p-1" aria-label="ホームへ" @click="menuOpen = false">
      <img src="/icons/logo.png" alt="SHARE" class="block h-7 w-auto" />
    </NuxtLink>
  </header>

  <!-- デスクトップ：サイドバー -->
  <aside class="hidden lg:flex shrink-0 w-64 flex flex-col p-6 border-r border-gray-600">
    <a href="/" class="block no-underline mb-8" @click.prevent="navigateTo('/')">
      <img src="/icons/logo.png" alt="SHARE" class="block h-8 w-auto" />
    </a>

    <nav class="flex flex-col gap-2 mb-8">
      <NuxtLink
        to="/"
        class="flex items-center gap-3 py-2 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50"
        :class="{ 'bg-gray-600/50': route.path === '/' }"
      >
        <img src="/icons/home.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
        <span class="text-sm font-medium">ホーム</span>
      </NuxtLink>
      <NuxtLink
        to="/filter"
        class="flex items-center gap-3 py-2 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50"
        :class="{ 'bg-gray-600/50': route.path === '/filter' }"
      >
        <img src="/icons/feather.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
        <span class="text-sm font-medium">マイ投稿</span>
      </NuxtLink>
      <NuxtLink
        to="/settings"
        class="flex items-center gap-3 py-2 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50"
        :class="{ 'bg-gray-600/50': route.path === '/settings' }"
      >
        <img src="/icons/profile.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
        <span class="text-sm font-medium">ユーザー設定</span>
      </NuxtLink>
      <NuxtLink
        to="/login"
        class="flex items-center gap-3 py-2 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50"
        @click.prevent="emit('logout')"
      >
        <img src="/icons/logout.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
        <span class="text-sm">ログアウト</span>
      </NuxtLink>
    </nav>

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

  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="menuOpen"
        class="fixed inset-0 z-[100] flex lg:hidden"
        aria-modal="true"
        role="dialog"
      >
        <div class="absolute inset-0 bg-black/50" aria-hidden="true" @click="menuOpen = false" />
        <nav
          class="relative z-[101] w-[min(20rem,85vw)] max-w-full h-full flex flex-col bg-gray-900 border-r border-gray-600 shadow-xl"
        >
          <div
            class="flex items-center justify-between gap-2 px-4 py-3 border-b border-gray-600 shrink-0"
          >
            <span class="text-white text-sm font-medium">メニュー</span>
            <button
              type="button"
              class="p-2 text-gray-400 hover:text-white rounded-lg"
              aria-label="閉じる"
              @click="menuOpen = false"
            >
              <img src="/icons/cross.png" alt="" class="w-5 h-5" />
            </button>
          </div>

          <div class="flex flex-col gap-1 p-4 overflow-y-auto flex-1">
            <NuxtLink
              to="/"
              class="flex items-center gap-3 py-3 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50"
              :class="{ 'bg-gray-600/50': route.path === '/' }"
              @click="menuOpen = false"
            >
              <img src="/icons/home.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
              <span class="text-sm font-medium">ホーム</span>
            </NuxtLink>
            <NuxtLink
              to="/filter"
              class="flex items-center gap-3 py-3 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50"
              :class="{ 'bg-gray-600/50': route.path === '/filter' }"
              @click="menuOpen = false"
            >
              <img src="/icons/feather.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
              <span class="text-sm font-medium">マイ投稿</span>
            </NuxtLink>
            <NuxtLink
              to="/settings"
              class="flex items-center gap-3 py-3 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50"
              :class="{ 'bg-gray-600/50': route.path === '/settings' }"
              @click="menuOpen = false"
            >
              <img src="/icons/profile.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
              <span class="text-sm font-medium">ユーザー設定</span>
            </NuxtLink>
            <NuxtLink
              to="/login"
              class="flex items-center gap-3 py-3 px-3 text-white no-underline rounded-lg hover:bg-gray-600/50"
              @click.prevent="onLogoutClick"
            >
              <img src="/icons/logout.png" alt="" class="w-5 h-5 shrink-0 object-contain" />
              <span class="text-sm">ログアウト</span>
            </NuxtLink>

            <div class="border-t border-gray-600 my-4" />

            <div class="flex flex-col gap-3">
              <h2 class="text-white text-sm font-medium">シェア</h2>
              <textarea
                v-model="drawerShareText"
                maxlength="120"
                placeholder="つぶやきを入力..."
                rows="4"
                class="w-full min-h-[100px] py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg resize-y placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
                :class="drawerShareError ? 'border-red-500' : ''"
                @input="drawerShareError = ''"
              />
              <p
                class="text-xs text-right transition-colors"
                :class="drawerShareText.length >= SHARE_MAX ? 'text-red-400' : 'text-gray-400'"
              >
                {{ drawerShareText.length }}/{{ SHARE_MAX }}
              </p>
              <p v-if="drawerShareError" class="text-sm text-red-400">{{ drawerShareError }}</p>
              <SubmitButton label="シェアする" button-type="button" block @click="onDrawerShareClick" />
            </div>
          </div>
        </nav>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
const SHARE_MAX = 120;

const route = useRoute();
const emit = defineEmits<{ share: [text: string]; logout: [] }>();

const menuOpen = ref(false);

const shareText = ref("");
const shareError = ref("");
const drawerShareText = ref("");
const drawerShareError = ref("");

const mobileTitle = computed(() => {
  const p = route.path;
  if (p === "/") return "ホーム";
  if (p === "/filter") return "マイ投稿";
  if (p === "/settings") return "ユーザー設定";
  if (p.startsWith("/tweets/")) return "コメント";
  return "SHARE";
});

watch(
  () => route.path,
  () => {
    menuOpen.value = false;
  },
);

function onLogoutClick() {
  menuOpen.value = false;
  emit("logout");
}

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

function onDrawerShareClick() {
  drawerShareError.value = "";
  const t = drawerShareText.value.trim();
  if (!t) {
    drawerShareError.value = "つぶやきを入力してください。";
    return;
  }
  if (drawerShareText.value.length > SHARE_MAX) {
    drawerShareError.value = `つぶやきは${SHARE_MAX}文字以内で入力してください。`;
    return;
  }
  emit("share", t);
  drawerShareText.value = "";
  menuOpen.value = false;
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
