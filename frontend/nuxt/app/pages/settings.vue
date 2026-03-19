<template>
  <div class="min-h-full flex flex-1 min-w-0">
    <Sidebar @share="handleShare" @logout="handleLogout" />

    <main class="flex-1 min-w-0 flex flex-col overflow-y-auto">
      <h1
        class="text-white text-xl font-semibold py-4 px-6 border-b border-gray-600"
      >
        ユーザー設定
      </h1>

      <!-- タブ -->
      <nav
        class="flex gap-0 border-b border-gray-600 px-6"
        aria-label="設定タブ"
      >
        <button
          v-for="t in tabs"
          :key="t.id"
          type="button"
          class="py-3 px-4 text-sm font-medium border-b-2 transition-colors -mb-px"
          :class="
            activeTab === t.id
              ? 'text-violet-400 border-violet-400'
              : 'text-gray-400 border-transparent hover:text-gray-300'
          "
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
                <label
                  for="user-name"
                  class="text-sm font-medium text-gray-300"
                >
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
              <p
                v-if="userNameMessage"
                class="text-sm"
                :class="userNameError ? 'text-red-400' : 'text-green-400'"
              >
                {{ userNameMessage }}
              </p>
              <SubmitButton
                label="ユーザーネームを変更する"
                :loading="userNameLoading"
                :disabled="userNameForm.name.trim() === ''"
              />
            </form>
          </section>

          <section class="flex flex-col gap-4">
            <h2 class="text-white text-base font-medium">パスワードの変更</h2>
            <form
              class="flex flex-col gap-4"
              @submit.prevent="onSubmitPassword"
            >
              <PasswordInput
                id="current-password"
                v-model="passwordForm.currentPassword"
                label="現在のパスワード"
                placeholder="現在のパスワード"
                autocomplete="current-password"
              />
              <PasswordInput
                id="new-password"
                v-model="passwordForm.newPassword"
                label="新しいパスワード"
                placeholder="新しいパスワード"
                autocomplete="new-password"
              />
              <PasswordInput
                id="new-password-confirm"
                v-model="passwordForm.newPasswordConfirm"
                label="新しいパスワード（確認）"
                placeholder="新しいパスワード（確認）"
                autocomplete="new-password"
              />
              <p
                v-if="passwordMessage"
                class="text-sm"
                :class="passwordError ? 'text-red-400' : 'text-green-400'"
              >
                {{ passwordMessage }}
              </p>
              <SubmitButton
                label="パスワードを変更する"
                :loading="passwordLoading"
                :disabled="!isPasswordFormValid"
              />
            </form>
          </section>
        </template>

        <!-- オプションタブ -->
        <template v-else-if="activeTab === 'options'">
          <section class="flex flex-col gap-4">
            <h2 class="text-white text-base font-medium">表示テーマ</h2>
            <p class="text-sm text-gray-400">
              ページ全体の背景色を変更できます。
            </p>
            <div class="flex flex-col gap-2 max-w-xs">
              <label
                for="theme-select"
                class="text-sm font-medium text-gray-300"
              >
                テーマ
              </label>
              <select
                id="theme-select"
                v-model="selectedTheme"
                class="w-full py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
              >
                <option value="dark" class="text-gray-900">
                  ダーク（標準）
                </option>
                <option value="blue" class="text-gray-900">ブルー</option>
                <option value="green" class="text-gray-900">グリーン</option>
              </select>
            </div>
          </section>
        </template>

        <!-- ブロックタブ（フロントのみ・ローカル保存） -->
        <template v-else-if="activeTab === 'block'">
          <section class="flex flex-col gap-6">
            <div class="flex flex-col gap-2">
              <h2 class="text-white text-base font-medium">ブロック設定</h2>
              <p class="text-sm text-gray-400">
                ユーザー一覧はデータベースから取得します。ブロック一覧の追加・解除はまだブラウザにのみ保存され、サーバーには送信されません。
              </p>
            </div>

            <!-- ブロックするユーザーの選択フォーム -->
            <div class="flex flex-col gap-3 max-w-md">
              <label
                for="block-user-select"
                class="text-sm font-medium text-gray-300"
              >
                ブロックするユーザーの選択
              </label>
              <p
                v-if="blockCandidatesLoading"
                class="text-sm text-gray-400"
              >
                候補を読み込み中...
              </p>
              <p
                v-else-if="blockCandidatesError"
                class="text-sm text-red-400"
              >
                {{ blockCandidatesError }}
              </p>
              <select
                v-else
                id="block-user-select"
                v-model="selectedBlockEmail"
                class="w-full py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
              >
                <option value="" disabled>ユーザーを選択</option>
                <option
                  v-for="u in blockSelectCandidates"
                  :key="u.id"
                  :value="u.email"
                >
                  {{ u.name }}（{{ u.email }}）
                </option>
              </select>
              <SubmitButton
                label="ブロックに追加"
                loading-label="追加中..."
                :loading="blockAddLoading"
                button-type="button"
                :disabled="selectedBlockEmail === ''"
                @click="onAddBlockedUserFront"
              />
              <p
                v-if="blockFormMessage"
                class="text-sm"
                :class="blockFormError ? 'text-red-400' : 'text-green-400'"
              >
                {{ blockFormMessage }}
              </p>
            </div>

            <!-- ブロックしているユーザー人数・名前・解除 -->
            <div class="flex flex-col gap-4 border-t border-gray-600 pt-6">
              <div class="flex flex-col gap-1">
                <h3 class="text-white text-sm font-medium">
                  ブロックしているユーザー
                </h3>
                <p class="text-sm text-gray-300">
                  人数:
                  <span class="font-semibold text-white">{{
                    blockedUsersFront.length
                  }}</span>
                  人
                </p>
              </div>

              <div
                v-if="blockedUsersFront.length > 0"
                class="flex flex-col gap-3"
              >
                <p class="text-xs text-gray-500 uppercase tracking-wide">
                  ユーザー名
                </p>
                <ul class="flex flex-col gap-2">
                  <li
                    v-for="u in blockedUsersFront"
                    :key="u.id"
                    class="flex items-center justify-between gap-3 p-3 bg-gray-700/30 border border-gray-600 rounded-lg"
                  >
                    <span class="text-sm text-white truncate">{{
                      u.name
                    }}</span>
                    <SubmitButton
                      label="解除"
                      loading-label="解除中..."
                      :loading="blockUnblockLoadingId === u.id"
                      :disabled="
                        blockUnblockLoadingId !== null &&
                        blockUnblockLoadingId !== u.id
                      "
                      button-type="button"
                      @click="onUnblockUserFront(u.id)"
                    />
                  </li>
                </ul>
              </div>
              <p v-else class="text-sm text-gray-400">
                ブロックしているユーザーはいません。
              </p>
            </div>
          </section>
        </template>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: "home", middleware: "auth" });

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
  set: (value: TabId) => {
    router.replace({ query: { ...route.query, tab: value } });
  },
});

const config = useRuntimeConfig();
const { theme, setTheme } = useTheme();
const currentUserName = ref<string | null>(null);
const userNameForm = reactive({
  name: "",
});
const userNameLoading = ref(false);
const userNameMessage = ref<string | null>(null);
const userNameError = ref(false);

function getXsrfToken(): string | null {
  if (process.client) {
    const name = "XSRF-TOKEN=";
    const decodedCookie = decodeURIComponent(document.cookie ?? "");
    const parts = decodedCookie.split("; ");
    const cookie = parts.find((c) => c.startsWith(name));
    if (cookie) {
      return cookie.substring(name.length);
    }
  }
  return null;
}

const passwordForm = reactive({
  currentPassword: "",
  newPassword: "",
  newPasswordConfirm: "",
});
const passwordLoading = ref(false);
const passwordMessage = ref<string | null>(null);
const passwordError = ref(false);

const selectedTheme = computed<Theme>({
  get: () => theme.value,
  set: (value) => setTheme(value),
});

const isPasswordFormValid = computed(() => {
  const { currentPassword, newPassword, newPasswordConfirm } = passwordForm;
  return (
    currentPassword.trim() !== "" &&
    newPassword.trim() !== "" &&
    newPasswordConfirm.trim() !== "" &&
    newPassword === newPasswordConfirm
  );
});

/** ブロックタブ（フロントのみ・localStorage） */
type BlockUserRow = {
  id: number;
  name: string;
  email: string;
};

const BLOCKED_USERS_LS_KEY = "share-block-users-front";

const blockCandidates = ref<BlockUserRow[]>([]);
const blockCandidatesLoading = ref(false);
const blockCandidatesError = ref<string | null>(null);

const blockedUsersFront = ref<BlockUserRow[]>([]);
const selectedBlockEmail = ref("");
const blockAddLoading = ref(false);
const blockFormMessage = ref<string | null>(null);
const blockFormError = ref(false);
const blockUnblockLoadingId = ref<number | null>(null);

const blockedEmailsFrontSet = computed(() => {
  return new Set(blockedUsersFront.value.map((u) => u.email));
});

const blockSelectCandidates = computed(() => {
  return blockCandidates.value.filter(
    (u) => !blockedEmailsFrontSet.value.has(u.email),
  );
});

function loadBlockedUsersFromLocalStorage() {
  if (!process.client) return;
  try {
    const raw = window.localStorage.getItem(BLOCKED_USERS_LS_KEY);
    if (!raw) return;
    const parsed = JSON.parse(raw) as BlockUserRow[];
    if (Array.isArray(parsed)) blockedUsersFront.value = parsed;
  } catch {
    blockedUsersFront.value = [];
  }
}

function persistBlockedUsersToLocalStorage() {
  if (!process.client) return;
  window.localStorage.setItem(
    BLOCKED_USERS_LS_KEY,
    JSON.stringify(blockedUsersFront.value),
  );
}

async function fetchBlockUserCandidates() {
  blockCandidatesLoading.value = true;
  blockCandidatesError.value = null;
  try {
    const apiBase = config.public.apiBase;
    const data = await $fetch<unknown>("/api/users", {
      baseURL: apiBase,
      credentials: "include",
    });
    if (Array.isArray(data)) {
      blockCandidates.value = data as BlockUserRow[];
    } else if (
      data &&
      typeof data === "object" &&
      Array.isArray((data as { users?: unknown }).users)
    ) {
      blockCandidates.value = (data as { users: BlockUserRow[] }).users;
    } else {
      blockCandidates.value = [];
    }
  } catch {
    blockCandidates.value = [];
    blockCandidatesError.value =
      "ユーザー一覧の取得に失敗しました。通信状況を確認して再度お試しください。";
  } finally {
    blockCandidatesLoading.value = false;
  }
}

function onAddBlockedUserFront() {
  const email = selectedBlockEmail.value.trim();
  if (!email) return;
  if (blockedEmailsFrontSet.value.has(email)) return;

  const row = blockCandidates.value.find((u) => u.email === email);
  if (!row) {
    blockFormMessage.value = "選択したユーザーを追加できません。";
    blockFormError.value = true;
    return;
  }

  blockAddLoading.value = true;
  blockFormMessage.value = null;
  blockFormError.value = false;

  setTimeout(() => {
    blockedUsersFront.value = [...blockedUsersFront.value, row];
    selectedBlockEmail.value = "";
    blockFormMessage.value = "ブロック一覧に追加しました（ローカルのみ）。";
    blockFormError.value = false;
    blockAddLoading.value = false;
  }, 120);
}

function onUnblockUserFront(userId: number) {
  blockUnblockLoadingId.value = userId;
  blockFormMessage.value = null;
  blockFormError.value = false;

  setTimeout(() => {
    blockedUsersFront.value = blockedUsersFront.value.filter(
      (u) => u.id !== userId,
    );
    blockUnblockLoadingId.value = null;
    blockFormMessage.value = "ブロックを解除しました（ローカルのみ）。";
    blockFormError.value = false;
  }, 120);
}

watch(blockedUsersFront, () => {
  persistBlockedUsersToLocalStorage();
});

function handleShare(_text: string) {
  // 設定ページではシェアは未使用（必要なら親で処理）
}

function handleLogout() {
  navigateTo("/login");
}

async function fetchUser() {
  try {
    const apiBase = config.public.apiBase;
    const user = await $fetch<{ name: string }>("/api/user", {
      baseURL: apiBase,
      credentials: "include",
    });
    currentUserName.value = user.name;
  } catch {
    currentUserName.value = null;
  }
}

onMounted(() => {
  fetchUser();
  loadBlockedUsersFromLocalStorage();
  fetchBlockUserCandidates();
});

async function onSubmitUserName() {
  if (userNameForm.name.trim() === "") return;
  userNameMessage.value = null;
  userNameError.value = false;
  userNameLoading.value = true;

  try {
    const apiBase = config.public.apiBase;
    if (!getXsrfToken()) {
      await $fetch("/sanctum/csrf-cookie", {
        baseURL: apiBase,
        credentials: "include",
      });
    }
    const xsrfToken = getXsrfToken();

    const res = await $fetch<{ user: { name: string } }>("/api/user", {
      baseURL: apiBase,
      method: "PATCH",
      credentials: "include",
      headers: xsrfToken ? { "X-XSRF-TOKEN": xsrfToken } : undefined,
      body: { name: userNameForm.name.trim() },
    });

    currentUserName.value = res.user.name;
    userNameMessage.value = "ユーザーネームを変更しました。";
    userNameError.value = false;
  } catch (err: any) {
    const message =
      err?.data?.message ||
      err?.data?.errors?.name?.[0] ||
      err?.response?._data?.message ||
      err?.response?._data?.errors?.name?.[0] ||
      "ユーザーネームの変更に失敗しました。";
    userNameMessage.value = message;
    userNameError.value = true;
  } finally {
    userNameLoading.value = false;
  }
}

async function onSubmitPassword() {
  if (!isPasswordFormValid.value) return;
  if (passwordForm.newPassword !== passwordForm.newPasswordConfirm) return;
  passwordMessage.value = null;
  passwordError.value = false;
  passwordLoading.value = true;

  try {
    const apiBase = config.public.apiBase;
    if (!getXsrfToken()) {
      await $fetch("/sanctum/csrf-cookie", {
        baseURL: apiBase,
        credentials: "include",
      });
    }
    const xsrfToken = getXsrfToken();

    await $fetch("/api/user/password", {
      baseURL: apiBase,
      method: "PATCH",
      credentials: "include",
      headers: xsrfToken ? { "X-XSRF-TOKEN": xsrfToken } : undefined,
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
  } catch (err: any) {
    const message =
      err?.data?.message ||
      err?.data?.errors?.current_password?.[0] ||
      err?.data?.errors?.password?.[0] ||
      err?.response?._data?.message ||
      err?.response?._data?.errors?.current_password?.[0] ||
      err?.response?._data?.errors?.password?.[0] ||
      "パスワードの変更に失敗しました。";
    passwordMessage.value = message;
    passwordError.value = true;
  } finally {
    passwordLoading.value = false;
  }
}
</script>
