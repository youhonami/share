<template>
  <div class="h-full flex items-center justify-center py-6 px-5 overflow-hidden">
    <div class="w-full max-w-[420px] bg-white rounded-xl shadow-xl py-12 px-10">
      <h1 class="text-2xl font-semibold text-gray-800 text-center mb-8">
        退会
      </h1>

      <p class="text-sm text-gray-600 text-center mb-8">
        アカウントを退会する場合は、メールアドレスとパスワードを入力してから手続きを行ってください。
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
          {{ loading ? '処理中...' : '退会する' }}
        </button>

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
  email: '',
  password: '',
})

const loading = ref(false)

function handleSubmit() {
  // TODO: 退会APIを呼び出す処理は後日実装
  console.log('退会:', form)
}
</script>
