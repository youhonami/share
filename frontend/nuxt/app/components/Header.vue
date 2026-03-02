<template>
  <header
    class="shrink-0 flex justify-between items-center py-6 px-10 w-full box-border"
  >
    <a
      href="/login"
      class="block no-underline"
      aria-label="SHARE ロゴ"
      @click.prevent="navigateTo('/login')"
    >
      <img src="/icons/logo.png" alt="SHARE" class="block h-8 w-auto" />
    </a>
    <nav class="flex gap-6">
      <NuxtLink
        to="/register"
        class="text-white no-underline text-sm hover:opacity-90"
      >
        新規登録
      </NuxtLink>
      <NuxtLink
        to="/login"
        class="text-white no-underline text-sm hover:opacity-90"
      >
        ログイン
      </NuxtLink>
    </nav>
  </header>
</template>
