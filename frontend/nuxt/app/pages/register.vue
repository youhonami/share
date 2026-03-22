<template>
  <!-- レイアウト -->
  <div class="h-full flex items-center justify-center py-6 px-5 overflow-hidden">
    <div class="w-full max-w-[420px] bg-white rounded-xl shadow-xl py-12 px-10">
      <h1 class="text-2xl font-semibold text-gray-800 text-center mb-8">新規登録</h1>
      <!-- 入力フォーム（novalidate: サーバー422のフィールドエラーを表示） -->
      <form class="flex flex-col gap-6" novalidate @submit.prevent="handleSubmit">
        <div class="flex flex-col gap-2">
          <label for="username" :class="labelClass">ユーザーネーム</label>
          <input id="username" v-model="form.name" type="text" autocomplete="username" :class="inputClass" placeholder="ユーザーネーム" />
          <p v-if="fieldErrors.name" class="text-sm text-red-600">{{ fieldErrors.name }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <label for="email" :class="labelClass">メールアドレス</label>
          <input id="email" v-model="form.email" type="text" inputmode="email" autocomplete="email" :class="inputClass" placeholder="example@example.com" />
          <p v-if="fieldErrors.email" class="text-sm text-red-600">{{ fieldErrors.email }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <label for="password" :class="labelClass">パスワード</label>
          <input id="password" v-model="form.password" type="password" autocomplete="new-password" :class="inputClass" placeholder="パスワード" />
          <p v-if="fieldErrors.password" class="text-sm text-red-600">{{ fieldErrors.password }}</p>
        </div>
        <!-- 送信・全体エラー -->
        <button type="submit" :class="submitClass" :disabled="loading">{{ loading ? "登録中..." : "新規登録" }}</button>
        <p v-if="errorMessage" class="text-sm text-red-600 text-center">{{ errorMessage }}</p>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { fetchStatus, xsrfAfterCsrfCookie } from "~/utils/sanctumCsrf";
import { pickFieldErrors } from "~/utils/validationErrors";

// --- 見た目 ---
const labelClass = "text-sm font-medium text-gray-800";
const inputClass = "w-full py-3 px-4 text-sm border border-gray-300 rounded-lg box-border focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20";
const submitClass = "w-full py-3.5 px-6 text-base font-semibold text-white bg-gradient-to-b from-violet-600 to-violet-400 border-0 rounded-full cursor-pointer shadow-lg shadow-[0_4px_12px_rgba(124,58,237,0.4)] mt-2 hover:opacity-95 disabled:opacity-60 disabled:cursor-not-allowed";

// --- フォーム状態 ---
const form = reactive({ name: "", email: "", password: "" });
const loading = ref(false);
const errorMessage = ref<string | null>(null);
const fieldErrors = reactive({ name: "", email: "", password: "" });
const config = useRuntimeConfig();

// --- API: POST /api/register ---
function postRegister(base: string, xsrf: string | null) {
  return $fetch("/api/register", { baseURL: base, method: "POST", credentials: "include", headers: xsrf ? { "X-XSRF-TOKEN": xsrf } : undefined, body: { ...form } });
}

// --- 送信: CSRF → 登録 → 419なら再取得して1回再送 → 成功時はログインへ ---
async function handleSubmit() {
  errorMessage.value = null;
  Object.assign(fieldErrors, { name: "", email: "", password: "" });
  loading.value = true;
  try {
    const base = config.public.apiBase;
    let xsrf = await xsrfAfterCsrfCookie(base);
    try {
      await postRegister(base, xsrf);
    } catch (first: unknown) {
      if (fetchStatus(first) !== 419) throw first;
      xsrf = await xsrfAfterCsrfCookie(base);
      await postRegister(base, xsrf);
    }
    await navigateTo("/login");
  } catch (err: unknown) {
    const fe = pickFieldErrors(err);
    if (Object.keys(fe).length) Object.assign(fieldErrors, { name: fe.name ?? "", email: fe.email ?? "", password: fe.password ?? "" });
    else {
      const e = err as { data?: { message?: string }; response?: { _data?: { message?: string } } };
      errorMessage.value = e?.data?.message || e?.response?._data?.message || "新規登録に失敗しました。";
    }
  } finally {
    loading.value = false;
  }
}
</script>
