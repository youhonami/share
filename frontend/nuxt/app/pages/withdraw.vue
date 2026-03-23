<template>
  <div class="h-full flex items-center justify-center py-6 px-5 overflow-hidden">
    <div class="w-full max-w-[420px] bg-white rounded-xl shadow-xl py-12 px-10">
      <h1 class="text-2xl font-semibold text-gray-800 text-center mb-8">退会</h1>

      <p class="text-sm text-gray-600 text-center mb-8">退会する場合は、メールアドレスとパスワードを入力してください。</p>

      <form class="flex flex-col gap-6" novalidate @submit.prevent="openConfirmModal">
        <div class="flex flex-col gap-2">
          <label for="email" class="text-sm font-medium text-gray-800">メールアドレス</label>
          <input
            id="email"
            v-model="form.email"
            type="text"
            inputmode="email"
            autocomplete="email"
            class="w-full py-3 px-4 text-sm border border-gray-300 rounded-lg box-border focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"
            :class="fieldErrors.email ? 'border-red-500' : ''"
            placeholder="example@example.com"
            @input="fieldErrors.email = ''"
          />
          <p v-if="fieldErrors.email" class="text-sm text-red-600">{{ fieldErrors.email }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <label for="password" class="text-sm font-medium text-gray-800">パスワード</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            autocomplete="current-password"
            class="w-full py-3 px-4 text-sm border border-gray-300 rounded-lg box-border focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"
            :class="fieldErrors.password ? 'border-red-500' : ''"
            placeholder="パスワード"
            @input="fieldErrors.password = ''"
          />
          <p v-if="fieldErrors.password" class="text-sm text-red-600">{{ fieldErrors.password }}</p>
        </div>
        <button
          type="submit"
          class="w-full py-3.5 px-6 text-base font-semibold text-white bg-gradient-to-b from-violet-600 to-violet-400 border-0 rounded-full cursor-pointer shadow-lg shadow-[0_4px_12px_rgba(124,58,237,0.4)] hover:opacity-95 disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="loading"
        >
          {{ loading ? "処理中..." : "退会する" }}
        </button>

        <p v-if="errorMessage" class="text-sm text-red-600 text-center">{{ errorMessage }}</p>

        <NuxtLink to="/login" class="block w-full py-3 px-6 text-base font-medium text-center text-gray-600 hover:text-gray-800">ログインに戻る</NuxtLink>
      </form>
    </div>

    <ConfirmModal
      v-model="showModal"
      title="退会の確認"
      message="退会するとアカウント・投稿・コメントはすべて削除され、元に戻せません。本当に退会しますか？"
      confirm-label="退会する"
      cancel-label="キャンセル"
      loading-label="処理中..."
      :loading="loading"
      variant="danger"
      @confirm="handleSubmit"
    />
  </div>
</template>

<script setup lang="ts">
import { fetchStatus, xsrfAfterCsrfCookie } from "~/utils/sanctumCsrf";
import { pickFieldErrors } from "~/utils/validationErrors";

const form = reactive({ email: "", password: "" });
const fieldErrors = reactive({ email: "", password: "" });
const loading = ref(false);
const errorMessage = ref<string | null>(null);
const showModal = ref(false);

const config = useRuntimeConfig();

/** 確認モーダルを開く前のクライアント側チェック（サーバーと同条件） */
function validateWithdrawClient(): boolean {
  fieldErrors.email = "";
  fieldErrors.password = "";
  const email = form.email.trim();
  if (!email) {
    fieldErrors.email = "メールアドレスを入力してください。";
    return false;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    fieldErrors.email = "メールアドレスの形式が正しくありません。";
    return false;
  }
  if (!form.password) {
    fieldErrors.password = "パスワードを入力してください。";
    return false;
  }
  return true;
}

function openConfirmModal() {
  errorMessage.value = null;
  if (!validateWithdrawClient()) return;
  showModal.value = true;
}

async function postWithdraw(base: string, xsrf: string | null) {
  return $fetch("/api/withdraw", {
    baseURL: base,
    method: "POST",
    credentials: "include",
    headers: xsrf ? { "X-XSRF-TOKEN": xsrf } : undefined,
    body: { email: form.email.trim(), password: form.password },
  });
}

async function handleSubmit() {
  errorMessage.value = null;
  Object.assign(fieldErrors, { email: "", password: "" });
  loading.value = true;
  try {
    const base = config.public.apiBase;
    let xsrf = await xsrfAfterCsrfCookie(base);
    try {
      await postWithdraw(base, xsrf);
    } catch (first: unknown) {
      if (fetchStatus(first) !== 419) throw first;
      xsrf = await xsrfAfterCsrfCookie(base);
      await postWithdraw(base, xsrf);
    }
    showModal.value = false;
    await navigateTo("/login");
  } catch (err: unknown) {
    const fe = pickFieldErrors(err);
    if (Object.keys(fe).length) {
      fieldErrors.email = fe.email ?? "";
      fieldErrors.password = fe.password ?? "";
      showModal.value = false;
      return;
    }
    const e = err as { data?: { message?: string; errors?: Record<string, string[]> }; response?: { _data?: { message?: string; errors?: Record<string, string[]> } } };
    errorMessage.value =
      e?.data?.message ||
      (Array.isArray(e?.data?.errors?.email) ? e.data.errors.email[0] : undefined) ||
      e?.response?._data?.message ||
      (Array.isArray(e?.response?._data?.errors?.email) ? e.response._data.errors.email[0] : undefined) ||
      "退会に失敗しました。";
    showModal.value = false;
  } finally {
    loading.value = false;
  }
}
</script>
