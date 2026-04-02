import type { Theme } from "~/composables/useTheme";
import { pickFieldErrors } from "~/utils/validationErrors";

export type SettingsTabId = "options" | "account" | "block";

export type BlockUserRow = { id: number; name: string; email: string };

export function useSettingsPage() {
  const USER_NAME_MAX = 20;
  const tabs: { id: SettingsTabId; label: string }[] = [
    { id: "options", label: "オプション" },
    { id: "account", label: "アカウント" },
    { id: "block", label: "ブロック" },
  ];

  const route = useRoute();
  const router = useRouter();
  const activeTab = computed({
    get: () => {
      const t = route.query.tab as string | undefined;
      return tabs.some((x) => x.id === t) ? (t as SettingsTabId) : "options";
    },
    set: (v: SettingsTabId) =>
      router.replace({ query: { ...route.query, tab: v } }),
  });

  const tabBtn = (on: boolean) =>
    on
      ? "text-violet-400 border-violet-400"
      : "text-gray-400 border-transparent hover:text-gray-300";

  const config = useRuntimeConfig();
  const apiBase = computed(() => config.public.apiBase);
  const ctl =
    "w-full py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border";

  function xsrf(): string | null {
    if (!process.client) return null;
    const p = decodeURIComponent(document.cookie ?? "").split("; ");
    const c = p.find((x) => x.startsWith("XSRF-TOKEN="));
    return c ? c.slice(11) : null;
  }

  async function get<T>(path: string, o: Record<string, unknown> = {}) {
    return $fetch<T>(path, {
      baseURL: apiBase.value,
      credentials: "include",
      ...o,
    });
  }

  async function post<T>(path: string, o: Record<string, unknown> = {}) {
    if (!xsrf()) await get("/sanctum/csrf-cookie");
    const t = xsrf();
    return get<T>(path, {
      headers: t ? { "X-XSRF-TOKEN": t } : undefined,
      ...o,
    });
  }

  const errTxt = (e: unknown) => {
    const x = e as {
      data?: { message?: string };
      response?: { _data?: { message?: string } };
    };
    return x?.data?.message || x?.response?._data?.message || null;
  };

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

  const passwordForm = reactive({
    currentPassword: "",
    newPassword: "",
    newPasswordConfirm: "",
  });
  const passwordLoading = ref(false);
  const passwordMessage = ref<string | null>(null);
  const passwordError = ref(false);
  const passwordFieldErrors = reactive({
    current_password: "",
    password: "",
    password_confirmation: "",
  });
  const pwdOk = computed(() => {
    const a = passwordForm;
    return !!(
      a.currentPassword.trim() &&
      a.newPassword.trim() &&
      a.newPasswordConfirm.trim() &&
      a.newPassword.length >= 6 &&
      a.newPassword === a.newPasswordConfirm
    );
  });

  watch(
    passwordForm,
    () =>
      Object.assign(passwordFieldErrors, {
        current_password: "",
        password: "",
        password_confirmation: "",
      }),
    { deep: true },
  );

  const { theme, setTheme } = useTheme();
  const selectedTheme = computed<Theme>({
    get: () => theme.value,
    set: (v) => setTheme(v),
  });

  const blockCandidates = ref<BlockUserRow[]>([]);
  const blockCandidatesLoading = ref(false);
  const blockCandidatesError = ref<string | null>(null);
  const blockedUsers = ref<BlockUserRow[]>([]);
  const selectedBlockEmail = ref("");
  const blockAddLoading = ref(false);
  const blockFormMessage = ref<string | null>(null);
  const blockFormError = ref(false);
  const blockUnblockLoadingId = ref<number | null>(null);
  const blockedSet = computed(() => new Set(blockedUsers.value.map((u) => u.email)));
  const blockChoices = computed(() =>
    blockCandidates.value.filter((u) => !blockedSet.value.has(u.email)),
  );

  function parseUsers(data: unknown): BlockUserRow[] {
    if (Array.isArray(data)) return data as BlockUserRow[];
    if (data && typeof data === "object" && Array.isArray((data as { users?: unknown }).users))
      return (data as { users: BlockUserRow[] }).users;
    return [];
  }

  async function loadBlocks(silent = false) {
    try {
      const data = await get<BlockUserRow[]>("/api/blocks");
      blockedUsers.value = Array.isArray(data) ? data : [];
    } catch {
      blockedUsers.value = [];
      if (!silent) {
        blockFormMessage.value = "一覧の取得に失敗しました。";
        blockFormError.value = true;
      }
    }
  }

  async function loadBlockCandidates() {
    blockCandidatesLoading.value = true;
    blockCandidatesError.value = null;
    try {
      blockCandidates.value = parseUsers(await get<unknown>("/api/users"));
    } catch {
      blockCandidates.value = [];
      blockCandidatesError.value = "ユーザー一覧を取得できませんでした。";
    } finally {
      blockCandidatesLoading.value = false;
    }
  }

  async function addBlock() {
    const email = selectedBlockEmail.value.trim();
    if (!email || blockedSet.value.has(email)) return;
    const row = blockCandidates.value.find((u) => u.email === email);
    if (!row) {
      blockFormMessage.value = "追加できません。";
      blockFormError.value = true;
      return;
    }
    blockAddLoading.value = true;
    blockFormMessage.value = null;
    blockFormError.value = false;
    try {
      await post("/api/blocks", { method: "POST", body: { blocked_user_id: row.id } });
      selectedBlockEmail.value = "";
      blockFormMessage.value = "ブロックしました。";
      await loadBlocks(true);
    } catch (e) {
      blockFormMessage.value = errTxt(e) || "失敗しました。";
      blockFormError.value = true;
    } finally {
      blockAddLoading.value = false;
    }
  }

  async function removeBlock(id: number) {
    blockUnblockLoadingId.value = id;
    blockFormMessage.value = null;
    blockFormError.value = false;
    try {
      await post(`/api/blocks/${id}`, { method: "DELETE" });
      blockFormMessage.value = "解除しました。";
      await loadBlocks(true);
    } catch {
      blockFormMessage.value = "解除に失敗しました。";
      blockFormError.value = true;
    } finally {
      blockUnblockLoadingId.value = null;
    }
  }

  async function loadUser() {
    try {
      currentUserName.value = (await get<{ name: string }>("/api/user")).name;
    } catch {
      currentUserName.value = null;
    }
  }

  onMounted(() => {
    loadUser();
    loadBlocks(true);
    loadBlockCandidates();
  });

  async function saveName() {
    if (!canSubmitUserName.value) return;
    userNameMessage.value = null;
    userNameError.value = false;
    userNameFieldError.value = "";
    userNameLoading.value = true;
    try {
      const res = await post<{ user: { name: string } }>("/api/user", {
        method: "PATCH",
        body: { name: userNameForm.name.trim() },
      });
      currentUserName.value = res.user.name;
      userNameMessage.value = "更新しました。";
    } catch (e) {
      const fe = pickFieldErrors(e);
      if (fe.name) userNameFieldError.value = fe.name;
      else {
        userNameMessage.value = errTxt(e) || "変更に失敗しました。";
        userNameError.value = true;
      }
    } finally {
      userNameLoading.value = false;
    }
  }

  async function savePassword() {
    if (!pwdOk.value) return;
    passwordMessage.value = null;
    passwordError.value = false;
    Object.assign(passwordFieldErrors, {
      current_password: "",
      password: "",
      password_confirmation: "",
    });
    passwordLoading.value = true;
    try {
      await post("/api/user/password", {
        method: "PATCH",
        body: {
          current_password: passwordForm.currentPassword,
          password: passwordForm.newPassword,
          password_confirmation: passwordForm.newPasswordConfirm,
        },
      });
      passwordMessage.value = "更新しました。";
      passwordForm.currentPassword = "";
      passwordForm.newPassword = "";
      passwordForm.newPasswordConfirm = "";
    } catch (e) {
      const fe = pickFieldErrors(e);
      if (Object.keys(fe).length) {
        if (fe.current_password) passwordFieldErrors.current_password = fe.current_password;
        if (fe.password) passwordFieldErrors.password = fe.password;
        if (fe.password_confirmation)
          passwordFieldErrors.password_confirmation = fe.password_confirmation;
      } else {
        passwordMessage.value = errTxt(e) || "変更に失敗しました。";
        passwordError.value = true;
      }
    } finally {
      passwordLoading.value = false;
    }
  }

  return {
    USER_NAME_MAX,
    tabs,
    activeTab,
    tabBtn,
    controlClass: ctl,
    currentUserName,
    userNameForm,
    userNameLoading,
    userNameMessage,
    userNameError,
    userNameFieldError,
    canSubmitUserName,
    saveName,
    passwordForm,
    passwordLoading,
    passwordMessage,
    passwordError,
    passwordFieldErrors,
    isPasswordFormValid: pwdOk,
    savePassword,
    selectedTheme,
    blockCandidatesLoading,
    blockCandidatesError,
    selectedBlockEmail,
    blockAddLoading,
    blockFormMessage,
    blockFormError,
    blockUnblockLoadingId,
    blockedUsers,
    blockChoices,
    addBlock,
    removeBlock,
    handleShare: (_: string) => {},
    handleLogout: () => navigateTo("/login"),
  };
}
