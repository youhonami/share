<template>
  <!-- レイアウト -->
  <div class="h-full flex items-center justify-center py-6 px-5 overflow-hidden">
    <div class="w-full max-w-[420px] bg-white rounded-xl shadow-xl py-12 px-10">
      <h1 class="text-2xl font-semibold text-gray-800 text-center mb-8">退会</h1>
      <p class="text-sm text-gray-600 text-center mb-8">退会する場合は、メールアドレスとパスワードを入力してください。</p>
      <!-- フォーム → 確認モーダル → POST /api/withdraw -->
      <form class="flex flex-col gap-6" novalidate @submit.prevent="openConfirmModal">
        <div class="flex flex-col gap-2">
          <label for="email" :class="labelClass">メールアドレス</label>
          <input id="email" v-model="form.email" type="text" inputmode="email" autocomplete="email" :class="[inputClass, fieldErrors.email && 'border-red-500']" placeholder="example@example.com" @input="fieldErrors.email = ''" />
          <p v-if="fieldErrors.email" class="text-sm text-red-600">{{ fieldErrors.email }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <label for="password" :class="labelClass">パスワード</label>
          <input id="password" v-model="form.password" type="password" autocomplete="current-password" :class="[inputClass, fieldErrors.password && 'border-red-500']" placeholder="パスワード" @input="fieldErrors.password = ''" />
          <p v-if="fieldErrors.password" class="text-sm text-red-600">{{ fieldErrors.password }}</p>
        </div>
        <button type="submit" :class="submitClass" :disabled="loading">{{ loading ? "処理中..." : "退会する" }}</button>
        <p v-if="errorMessage" class="text-sm text-red-600 text-center">{{ errorMessage }}</p>
        <NuxtLink to="/login" class="block w-full py-3 px-6 text-base font-medium text-center text-gray-600 hover:text-gray-800">ログインに戻る</NuxtLink>
      </form>
    </div>
    <ConfirmModal v-model="showModal" title="退会の確認" message="退会するとアカウント・投稿・コメントはすべて削除され、元に戻せません。本当に退会しますか？" confirm-label="退会する" cancel-label="キャンセル" loading-label="処理中..." :loading="loading" variant="danger" @confirm="handleSubmit" />
  </div>
</template>

<script setup lang="ts">
import { fetchStatus, xsrfAfterCsrfCookie } from "~/utils/sanctumCsrf";
import { pickFieldErrors } from "~/utils/validationErrors";

// --- 見た目 ---
const labelClass = "text-sm font-medium text-gray-800";
const inputClass = "w-full py-3 px-4 text-sm border border-gray-300 rounded-lg box-border focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20";
const submitClass = "w-full py-3.5 px-6 text-base font-semibold text-white bg-gradient-to-b from-violet-600 to-violet-400 border-0 rounded-full cursor-pointer shadow-lg shadow-[0_4px_12px_rgba(124,58,237,0.4)] hover:opacity-95 disabled:opacity-60 disabled:cursor-not-allowed";

// --- 状態 ---
const form = reactive({ email: "", password: "" });
const fieldErrors = reactive({ email: "", password: "" });
const loading = ref(false);
const errorMessage = ref<string | null>(null);
const showModal = ref(false);
const config = useRuntimeConfig();

const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/** モーダル表示前（WithdrawRequest と同条件の簡易チェック） */
function validateWithdrawClient(): boolean {
  Object.assign(fieldErrors, { email: "", password: "" });
  const em = form.email.trim();
  if (!em) { fieldErrors.email = "メールアドレスを入力してください。"; return false; }
  if (!emailPattern.test(em)) { fieldErrors.email = "メールアドレスの形式が正しくありません。"; return false; }
  if (!form.password) { fieldErrors.password = "パスワードを入力してください。"; return false; }
  return true;
}

function openConfirmModal() {
  errorMessage.value = null;
  if (validateWithdrawClient()) showModal.value = true;
}

function postWithdraw(base: string, xsrf: string | null) {
  return $fetch("/api/withdraw", { baseURL: base, method: "POST", credentials: "include", headers: xsrf ? { "X-XSRF-TOKEN": xsrf } : undefined, body: { email: form.email.trim(), password: form.password } });
}

function withdrawFallbackMessage(err: unknown): string {
  const e = err as { data?: { message?: string; errors?: { email?: string[] } }; response?: { _data?: { message?: string; errors?: { email?: string[] } } } };
  return e?.data?.message || e?.data?.errors?.email?.[0] || e?.response?._data?.message || e?.response?._data?.errors?.email?.[0] || "退会に失敗しました。";
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
      Object.assign(fieldErrors, { email: fe.email ?? "", password: fe.password ?? "" });
      showModal.value = false;
    } else {
      errorMessage.value = withdrawFallbackMessage(err);
      showModal.value = false;
    }
  } finally {
    loading.value = false;
  }
}
</script>
