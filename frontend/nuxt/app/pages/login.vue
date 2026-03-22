<template>
  <!-- レイアウト -->
  <div class="h-full flex items-center justify-center py-6 px-5 overflow-hidden">
    <div class="w-full max-w-[420px] bg-white rounded-xl shadow-xl py-12 px-10">
      <h1 class="text-2xl font-semibold text-gray-800 text-center mb-8">ログイン</h1>
      <!-- 入力フォーム（novalidate: サーバー422のフィールドエラーを表示するため） -->
      <form class="flex flex-col gap-6" novalidate @submit.prevent="handleSubmit">
        <div class="flex flex-col gap-2">
          <label for="email" :class="labelClass">メールアドレス</label>
          <input id="email" v-model="form.email" type="text" inputmode="email" autocomplete="email" :class="inputClass" placeholder="example@example.com" />
          <p v-if="fieldErrors.email" class="text-sm text-red-600">{{ fieldErrors.email }}</p>
        </div>
        <div class="flex flex-col gap-2">
          <label for="password" :class="labelClass">パスワード</label>
          <input id="password" v-model="form.password" type="password" autocomplete="current-password" :class="inputClass" placeholder="パスワード" />
          <p v-if="fieldErrors.password" class="text-sm text-red-600">{{ fieldErrors.password }}</p>
        </div>
        <!-- 送信・全体エラー -->
        <button type="submit" :class="submitClass" :disabled="loading">{{ loading ? "ログイン中..." : "ログイン" }}</button>
        <p v-if="errorMessage" class="text-sm text-red-600 text-center">{{ errorMessage }}</p>
        <!-- 補助リンク -->
        <NuxtLink to="/withdraw" class="block text-center text-sm text-gray-500 hover:text-gray-700 mt-2">退会する方はこちら</NuxtLink>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { fetchStatus, xsrfAfterCsrfCookie } from "~/utils/sanctumCsrf";
import { pickFieldErrors } from "~/utils/validationErrors";

// --- 見た目（Tailwind クラスを定数化） ---
const labelClass = "text-sm font-medium text-gray-800";
const inputClass = "w-full py-3 px-4 text-sm border border-gray-300 rounded-lg box-border focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20";
const submitClass = "w-full py-3.5 px-6 text-base font-semibold text-white bg-gradient-to-b from-violet-600 to-violet-400 border-0 rounded-full cursor-pointer shadow-lg shadow-[0_4px_12px_rgba(124,58,237,0.4)] mt-2 hover:opacity-95 disabled:opacity-60 disabled:cursor-not-allowed";

// --- フォーム状態 ---
const form = reactive({ email: "", password: "" });
const loading = ref(false);
const errorMessage = ref<string | null>(null);
const fieldErrors = reactive({ email: "", password: "" });
const config = useRuntimeConfig();

// --- API: POST /api/login（CSRFヘッダ付き） ---
function postLogin(base: string, xsrf: string | null) {
  return $fetch("/api/login", { baseURL: base, method: "POST", credentials: "include", headers: xsrf ? { "X-XSRF-TOKEN": xsrf } : undefined, body: { ...form } });
}

// --- 送信: CSRF確保 → ログイン → 419ならCookie取り直して1回再送 → 成功時はホームへ ---
async function handleSubmit() {
  errorMessage.value = null;
  Object.assign(fieldErrors, { email: "", password: "" });
  loading.value = true;
  try {
    const base = config.public.apiBase;
    let xsrf = await xsrfAfterCsrfCookie(base);
    try {
      await postLogin(base, xsrf);
    } catch (first: unknown) {
      if (fetchStatus(first) !== 419) throw first;
      xsrf = await xsrfAfterCsrfCookie(base);
      await postLogin(base, xsrf);
    }
    await navigateTo("/");
  } catch (err: unknown) {
    const fe = pickFieldErrors(err);
    if (Object.keys(fe).length) Object.assign(fieldErrors, { email: fe.email ?? "", password: fe.password ?? "" });
    else {
      const e = err as { data?: { message?: string }; response?: { _data?: { message?: string } } };
      errorMessage.value = e?.data?.message || e?.response?._data?.message || "ログインに失敗しました。";
    }
  } finally {
    loading.value = false;
  }
}
</script>
