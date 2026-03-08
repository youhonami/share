<template>
  <div
    class="h-full flex items-center justify-center py-6 px-5 overflow-hidden"
  >
    <div class="w-full max-w-[420px] bg-white rounded-xl shadow-xl py-12 px-10">
      <h1 class="text-2xl font-semibold text-gray-800 text-center mb-8">
        退会
      </h1>

      <p class="text-sm text-gray-600 text-center mb-8">
        退会する場合は、メールアドレスとパスワードを入力してください。
      </p>

      <form class="flex flex-col gap-6" @submit.prevent="handleSubmit">
        <div class="flex flex-col gap-2">
          <label for="email" class="text-sm font-medium text-gray-800">
            メールアドレス
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="w-full py-3 px-4 text-sm border border-gray-300 rounded-lg box-border focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"
            placeholder="example@example.com"
            required
          />
        </div>
        <div class="flex flex-col gap-2">
          <label for="password" class="text-sm font-medium text-gray-800">
            パスワード
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="w-full py-3 px-4 text-sm border border-gray-300 rounded-lg box-border focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"
            placeholder="パスワード"
            required
          />
        </div>
        <button
          type="submit"
          class="w-full py-3.5 px-6 text-base font-semibold text-white bg-gradient-to-b from-violet-600 to-violet-400 border-0 rounded-full cursor-pointer shadow-lg shadow-[0_4px_12px_rgba(124,58,237,0.4)] hover:opacity-95 disabled:opacity-60 disabled:cursor-not-allowed"
          :disabled="loading"
        >
          {{ loading ? "処理中..." : "退会する" }}
        </button>

        <p v-if="errorMessage" class="text-sm text-red-600 text-center">
          {{ errorMessage }}
        </p>

        <NuxtLink
          to="/login"
          class="block w-full py-3 px-6 text-base font-medium text-center text-gray-600 hover:text-gray-800"
        >
          ログインに戻る
        </NuxtLink>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
const form = reactive({
  email: "",
  password: "",
});

const loading = ref(false);
const errorMessage = ref<string | null>(null);

const config = useRuntimeConfig();

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

async function handleSubmit() {
  errorMessage.value = null;
  loading.value = true;

  try {
    const apiBase = config.public.apiBase;

    await $fetch("/sanctum/csrf-cookie", {
      baseURL: apiBase,
      credentials: "include",
    });

    const xsrfToken = getXsrfToken();

    await $fetch("/api/withdraw", {
      baseURL: apiBase,
      method: "POST",
      credentials: "include",
      headers: xsrfToken
        ? {
            "X-XSRF-TOKEN": xsrfToken,
          }
        : undefined,
      body: {
        email: form.email,
        password: form.password,
      },
    });

    await navigateTo("/login");
  } catch (err: any) {
    const message =
      err?.data?.message ||
      err?.data?.errors?.email?.[0] ||
      err?.response?._data?.message ||
      err?.response?._data?.errors?.email?.[0] ||
      "退会に失敗しました。";
    errorMessage.value = message;
  } finally {
    loading.value = false;
  }
}
</script>
