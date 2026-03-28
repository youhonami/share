<template>
  <div class="flex flex-1 min-w-0 min-h-0 flex-col lg:flex-row">
    <Sidebar @share="handleShare" @logout="handleLogout" />

    <main class="flex-1 min-w-0 min-h-0 flex flex-col overflow-y-auto">
      <!-- ページタイトル（モバイルは Sidebar トップバーに表示） -->
      <h1
        class="hidden lg:block text-white text-xl font-semibold py-4 px-4 lg:px-6 border-b border-gray-600"
      >
        ユーザー設定
      </h1>

      <!-- タブ（URLクエリ tab と同期） -->
      <nav class="flex gap-0 border-b border-gray-600 px-4 lg:px-6 overflow-x-auto" aria-label="設定タブ">
        <button
          v-for="t in tabs"
          :key="t.id"
          type="button"
          class="shrink-0 py-3 px-4 text-sm font-medium border-b-2 transition-colors -mb-px"
          :class="activeTab === t.id ? 'text-violet-400 border-violet-400' : 'text-gray-400 border-transparent hover:text-gray-300'"
          @click="activeTab = t.id"
        >
          {{ t.label }}
        </button>
      </nav>

      <div class="p-4 lg:p-6 flex flex-col gap-10 max-w-xl">
        <!-- ========== アカウント（名前・パスワード） ========== -->
        <template v-if="activeTab === 'account'">
          <section class="flex flex-col gap-4">
            <h2 class="text-white text-base font-medium">ユーザーネームの変更</h2>
            <p v-if="currentUserName" class="text-sm text-gray-400">現在のユーザーネーム: {{ currentUserName }}</p>
            <form class="flex flex-col gap-4" novalidate @submit.prevent="onSubmitUserName">
              <div class="flex flex-col gap-2">
                <label for="user-name" class="text-sm font-medium text-gray-300">新しいユーザーネーム</label>
                <input id="user-name" v-model="userNameForm.name" type="text" maxlength="20" autocomplete="username" :class="[controlClass, userNameFieldError ? 'border-red-500' : '']" placeholder="ユーザーネーム" @input="userNameFieldError = ''" />
                <p class="text-xs text-right transition-colors" :class="userNameForm.name.length >= USER_NAME_MAX ? 'text-red-400' : 'text-gray-400'">{{ userNameForm.name.length }}/{{ USER_NAME_MAX }}</p>
                <p v-if="userNameFieldError" class="text-sm text-red-400">{{ userNameFieldError }}</p>
              </div>
              <p v-if="userNameMessage" class="text-sm" :class="userNameError ? 'text-red-400' : 'text-green-400'">{{ userNameMessage }}</p>
              <SubmitButton label="ユーザーネームを変更する" :loading="userNameLoading" :disabled="!canSubmitUserName" />
            </form>
          </section>

          <section class="flex flex-col gap-4">
            <h2 class="text-white text-base font-medium">パスワードの変更</h2>
            <p class="text-sm text-gray-400">新しいパスワードは6文字以上の英数字で入力してください。</p>
            <form class="flex flex-col gap-4" novalidate @submit.prevent="onSubmitPassword">
              <div class="flex flex-col gap-2">
                <PasswordInput id="current-password" v-model="passwordForm.currentPassword" label="現在のパスワード" placeholder="現在のパスワード" autocomplete="current-password" />
                <p v-if="passwordFieldErrors.current_password" class="text-sm text-red-400 -mt-1">{{ passwordFieldErrors.current_password }}</p>
              </div>
              <div class="flex flex-col gap-2">
                <PasswordInput id="new-password" v-model="passwordForm.newPassword" label="新しいパスワード" placeholder="新しいパスワード（6文字以上）" autocomplete="new-password" />
                <p v-if="passwordFieldErrors.password" class="text-sm text-red-400 -mt-1">{{ passwordFieldErrors.password }}</p>
              </div>
              <div class="flex flex-col gap-2">
                <PasswordInput id="new-password-confirm" v-model="passwordForm.newPasswordConfirm" label="新しいパスワード（確認）" placeholder="新しいパスワード（確認）" autocomplete="new-password" />
                <p v-if="passwordFieldErrors.password_confirmation" class="text-sm text-red-400 -mt-1">{{ passwordFieldErrors.password_confirmation }}</p>
              </div>
              <p v-if="passwordMessage" class="text-sm" :class="passwordError ? 'text-red-400' : 'text-green-400'">{{ passwordMessage }}</p>
              <SubmitButton label="パスワードを変更する" :loading="passwordLoading" :disabled="!isPasswordFormValid" />
            </form>
          </section>
        </template>

        <!-- ========== オプション（テーマ） ========== -->
        <template v-else-if="activeTab === 'options'">
          <section class="flex flex-col gap-4">
            <h2 class="text-white text-base font-medium">表示テーマ</h2>
            <p class="text-sm text-gray-400">ページ全体の背景色を変更できます。</p>
            <div class="flex flex-col gap-2 max-w-xs">
              <label for="theme-select" class="text-sm font-medium text-gray-300">テーマ</label>
              <select id="theme-select" v-model="selectedTheme" :class="controlClass">
                <option value="dark" class="text-gray-900">ダーク（標準）</option>
                <option value="blue" class="text-gray-900">ブルー</option>
                <option value="green" class="text-gray-900">グリーン</option>
              </select>
            </div>
          </section>
        </template>

        <!-- ========== ブロック（候補選択・一覧・解除） ========== -->
        <template v-else-if="activeTab === 'block'">
          <section class="flex flex-col gap-6">
            <div class="flex flex-col gap-2">
              <h2 class="text-white text-base font-medium">ブロック設定</h2>
              <p class="text-sm text-gray-400">ユーザーをブロックすると、そのユーザーにはあなたの投稿がタイムライン・詳細に表示されなくなります。</p>
            </div>

            <!-- 候補取得 → select → POST /api/blocks -->
            <div class="flex flex-col gap-3 max-w-md">
              <label for="block-user-select" class="text-sm font-medium text-gray-300">ブロックするユーザーの選択</label>
              <p v-if="blockCandidatesLoading" class="text-sm text-gray-400">候補を読み込み中...</p>
              <p v-else-if="blockCandidatesError" class="text-sm text-red-400">{{ blockCandidatesError }}</p>
              <select v-else id="block-user-select" v-model="selectedBlockEmail" :class="controlClass">
                <option value="" disabled>ユーザーを選択</option>
                <option v-for="u in blockSelectCandidates" :key="u.id" :value="u.email">{{ u.name }}（{{ u.email }}）</option>
              </select>
              <SubmitButton label="ブロックに追加" loading-label="追加中..." :loading="blockAddLoading" button-type="button" :disabled="selectedBlockEmail === ''" @click="onAddBlockedUser" />
              <p v-if="blockFormMessage" class="text-sm" :class="blockFormError ? 'text-red-400' : 'text-green-400'">{{ blockFormMessage }}</p>
            </div>

            <!-- GET /api/blocks の一覧・DELETE /api/blocks/:id -->
            <div class="flex flex-col gap-4 border-t border-gray-600 pt-6">
              <div class="flex flex-col gap-1">
                <h3 class="text-white text-sm font-medium">ブロックしているユーザー</h3>
                <p class="text-sm text-gray-300">人数: <span class="font-semibold text-white">{{ blockedUsers.length }}</span> 人</p>
              </div>
              <div v-if="blockedUsers.length > 0" class="flex flex-col gap-3">
                <p class="text-xs text-gray-500 uppercase tracking-wide">ユーザー名</p>
                <ul class="flex flex-col gap-2">
                  <li v-for="u in blockedUsers" :key="u.id" class="flex items-center justify-between gap-3 p-3 bg-gray-700/30 border border-gray-600 rounded-lg">
                    <span class="text-sm text-white truncate">{{ u.name }}</span>
                    <SubmitButton label="解除" loading-label="解除中..." :loading="blockUnblockLoadingId === u.id" :disabled="blockUnblockLoadingId !== null && blockUnblockLoadingId !== u.id" button-type="button" @click="onUnblockUser(u.id)" />
                  </li>
                </ul>
              </div>
              <p v-else class="text-sm text-gray-400">ブロックしているユーザーはいません。</p>
            </div>
          </section>
        </template>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { pickFieldErrors } from "~/utils/validationErrors";

definePageMeta({ layout: "home", middleware: "auth" });

const USER_NAME_MAX = 20;

// --- タブ定義・URL同期（?tab=account など） ---
type TabId = "options" | "account" | "block";
const tabs: { id: TabId; label: string }[] = [
  { id: "options", label: "オプション" },
  { id: "account", label: "アカウント" },
  { id: "block", label: "ブロック" },
];
const route = useRoute();
const router = useRouter();
const activeTab = computed({
  get: () => {
    const t = route.query.tab as string | undefined;
    return tabs.some((tab) => tab.id === t) ? (t as TabId) : "options";
  },
  set: (value: TabId) => router.replace({ query: { ...route.query, tab: value } }),
});

// --- 共通：APIベースURL・入力系の見た目（テンプレで使う class 文字列） ---
const config = useRuntimeConfig();
const controlClass =
  "w-full py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border";

// --- Sanctum：Cookie から XSRF を取り、無ければ csrf-cookie を叩いてから再取得 ---
function getXsrfToken(): string | null {
  if (!process.client) return null;
  const name = "XSRF-TOKEN=";
  const parts = decodeURIComponent(document.cookie ?? "").split("; ");
  const cookie = parts.find((c) => c.startsWith(name));
  return cookie ? cookie.substring(name.length) : null;
}
async function ensureXsrfCookie(): Promise<string | null> {
  const apiBase = config.public.apiBase;
  if (!getXsrfToken()) await $fetch("/sanctum/csrf-cookie", { baseURL: apiBase, credentials: "include" });
  return getXsrfToken();
}

// --- アカウント：ユーザーネーム（GET/PATCH /api/user） ---
const currentUserName = ref<string | null>(null);
const userNameForm = reactive({ name: "" });
const userNameLoading = ref(false);
const userNameMessage = ref<string | null>(null);
const userNameError = ref(false);
const userNameFieldError = ref("");
const canSubmitUserName = computed(() => {
  const t = userNameForm.name.trim();
  return t.length > 0 && userNameForm.name.length <= USER_NAME_MAX;
});

// --- アカウント：パスワード（PATCH /api/user/password） ---
const passwordForm = reactive({ currentPassword: "", newPassword: "", newPasswordConfirm: "" });
const passwordLoading = ref(false);
const passwordMessage = ref<string | null>(null);
const passwordError = ref(false);
const passwordFieldErrors = reactive({ current_password: "", password: "", password_confirmation: "" });
const isPasswordFormValid = computed(() => {
  const a = passwordForm;
  if (!a.currentPassword.trim() || !a.newPassword.trim() || !a.newPasswordConfirm.trim()) return false;
  if (a.newPassword.length < 6) return false;
  if (a.newPassword !== a.newPasswordConfirm) return false;
  return true;
});

watch(
  passwordForm,
  () => {
    Object.assign(passwordFieldErrors, { current_password: "", password: "", password_confirmation: "" });
  },
  { deep: true },
);

// --- オプション：テーマ（useTheme） ---
const { theme, setTheme } = useTheme();
const selectedTheme = computed<Theme>({ get: () => theme.value, set: (v) => setTheme(v) });

// --- ブロック：型・状態・候補（GET /api/users）・一覧（GET /api/blocks） ---
type BlockUserRow = { id: number; name: string; email: string };
const blockCandidates = ref<BlockUserRow[]>([]);
const blockCandidatesLoading = ref(false);
const blockCandidatesError = ref<string | null>(null);
const blockedUsers = ref<BlockUserRow[]>([]);
const selectedBlockEmail = ref("");
const blockAddLoading = ref(false);
const blockFormMessage = ref<string | null>(null);
const blockFormError = ref(false);
const blockUnblockLoadingId = ref<number | null>(null);
const blockedEmailsSet = computed(() => new Set(blockedUsers.value.map((u) => u.email)));
const blockSelectCandidates = computed(() => blockCandidates.value.filter((u) => !blockedEmailsSet.value.has(u.email)));

async function fetchBlockedUsers(silent = false) {
  try {
    const data = await $fetch<BlockUserRow[]>("/api/blocks", { baseURL: config.public.apiBase, credentials: "include" });
    blockedUsers.value = Array.isArray(data) ? data : [];
  } catch {
    blockedUsers.value = [];
    if (!silent) {
      blockFormMessage.value = "ブロック一覧の取得に失敗しました。";
      blockFormError.value = true;
    }
  }
}

async function fetchBlockUserCandidates() {
  blockCandidatesLoading.value = true;
  blockCandidatesError.value = null;
  try {
    const data = await $fetch<unknown>("/api/users", { baseURL: config.public.apiBase, credentials: "include" });
    if (Array.isArray(data)) blockCandidates.value = data as BlockUserRow[];
    else if (data && typeof data === "object" && Array.isArray((data as { users?: unknown }).users)) blockCandidates.value = (data as { users: BlockUserRow[] }).users;
    else blockCandidates.value = [];
  } catch {
    blockCandidates.value = [];
    blockCandidatesError.value = "ユーザー一覧の取得に失敗しました。通信状況を確認して再度お試しください。";
  } finally {
    blockCandidatesLoading.value = false;
  }
}

/** POST /api/blocks { blocked_user_id } */
async function onAddBlockedUser() {
  const email = selectedBlockEmail.value.trim();
  if (!email || blockedEmailsSet.value.has(email)) return;
  const row = blockCandidates.value.find((u) => u.email === email);
  if (!row) {
    blockFormMessage.value = "選択したユーザーを追加できません。";
    blockFormError.value = true;
    return;
  }
  blockAddLoading.value = true;
  blockFormMessage.value = null;
  blockFormError.value = false;
  try {
    const xsrf = await ensureXsrfCookie();
    await $fetch("/api/blocks", {
      baseURL: config.public.apiBase,
      method: "POST",
      credentials: "include",
      headers: xsrf ? { "X-XSRF-TOKEN": xsrf } : undefined,
      body: { blocked_user_id: row.id },
    });
    selectedBlockEmail.value = "";
    blockFormMessage.value = "ブロックしました。";
    blockFormError.value = false;
    await fetchBlockedUsers(true);
  } catch (err: unknown) {
    const e = err as { data?: { message?: string }; response?: { _data?: { message?: string } } };
    blockFormMessage.value = e?.data?.message || e?.response?._data?.message || "ブロックに失敗しました。";
    blockFormError.value = true;
  } finally {
    blockAddLoading.value = false;
  }
}

/** DELETE /api/blocks/:id */
async function onUnblockUser(userId: number) {
  blockUnblockLoadingId.value = userId;
  blockFormMessage.value = null;
  blockFormError.value = false;
  try {
    const xsrf = await ensureXsrfCookie();
    await $fetch(`/api/blocks/${userId}`, { baseURL: config.public.apiBase, method: "DELETE", credentials: "include", headers: xsrf ? { "X-XSRF-TOKEN": xsrf } : undefined });
    blockFormMessage.value = "ブロックを解除しました。";
    blockFormError.value = false;
    await fetchBlockedUsers(true);
  } catch {
    blockFormMessage.value = "ブロックの解除に失敗しました。";
    blockFormError.value = true;
  } finally {
    blockUnblockLoadingId.value = null;
  }
}

// --- ヘッダー Sidebar 用 ---
function handleShare(_text: string) {}
function handleLogout() {
  navigateTo("/login");
}

// --- 初期表示：ログインユーザー名 + ブロック関連 ---
async function fetchUser() {
  try {
    const user = await $fetch<{ name: string }>("/api/user", { baseURL: config.public.apiBase, credentials: "include" });
    currentUserName.value = user.name;
  } catch {
    currentUserName.value = null;
  }
}
onMounted(() => {
  fetchUser();
  fetchBlockedUsers(true);
  fetchBlockUserCandidates();
});

// --- 送信：ユーザーネーム ---
async function onSubmitUserName() {
  if (!canSubmitUserName.value) return;
  userNameMessage.value = null;
  userNameError.value = false;
  userNameFieldError.value = "";
  userNameLoading.value = true;
  try {
    const xsrf = await ensureXsrfCookie();
    const res = await $fetch<{ user: { name: string } }>("/api/user", {
      baseURL: config.public.apiBase,
      method: "PATCH",
      credentials: "include",
      headers: xsrf ? { "X-XSRF-TOKEN": xsrf } : undefined,
      body: { name: userNameForm.name.trim() },
    });
    currentUserName.value = res.user.name;
    userNameMessage.value = "ユーザーネームを変更しました。";
    userNameError.value = false;
  } catch (err: unknown) {
    const fe = pickFieldErrors(err);
    if (fe.name) {
      userNameFieldError.value = fe.name;
      return;
    }
    const e = err as { data?: { message?: string }; response?: { _data?: { message?: string } } };
    userNameMessage.value = e?.data?.message || e?.response?._data?.message || "ユーザーネームの変更に失敗しました。";
    userNameError.value = true;
  } finally {
    userNameLoading.value = false;
  }
}

// --- 送信：パスワード ---
async function onSubmitPassword() {
  if (!isPasswordFormValid.value) return;
  passwordMessage.value = null;
  passwordError.value = false;
  Object.assign(passwordFieldErrors, { current_password: "", password: "", password_confirmation: "" });
  passwordLoading.value = true;
  try {
    const xsrf = await ensureXsrfCookie();
    await $fetch("/api/user/password", {
      baseURL: config.public.apiBase,
      method: "PATCH",
      credentials: "include",
      headers: xsrf ? { "X-XSRF-TOKEN": xsrf } : undefined,
      body: {
        current_password: passwordForm.currentPassword,
        password: passwordForm.newPassword,
        password_confirmation: passwordForm.newPasswordConfirm,
      },
    });
    passwordMessage.value = "パスワードを変更しました。";
    passwordError.value = false;
    passwordForm.currentPassword = "";
    passwordForm.newPassword = "";
    passwordForm.newPasswordConfirm = "";
  } catch (err: unknown) {
    const fe = pickFieldErrors(err);
    if (Object.keys(fe).length) {
      if (fe.current_password) passwordFieldErrors.current_password = fe.current_password;
      if (fe.password) passwordFieldErrors.password = fe.password;
      if (fe.password_confirmation) passwordFieldErrors.password_confirmation = fe.password_confirmation;
      return;
    }
    const e = err as { data?: { message?: string }; response?: { _data?: { message?: string } } };
    passwordMessage.value = e?.data?.message || e?.response?._data?.message || "パスワードの変更に失敗しました。";
    passwordError.value = true;
  } finally {
    passwordLoading.value = false;
  }
}
</script>
