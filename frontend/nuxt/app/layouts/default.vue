<template>
  <div class="min-h-screen h-screen flex flex-col overflow-hidden">
    <!-- Tailwind に背景色・文字色クラスを認識させるためのダミー要素 -->
    <span
      class="hidden bg-gray-700 bg-sky-900 bg-emerald-900 text-gray-100 text-sky-100 text-emerald-100"
    />
    <Header />
    <main class="flex-1 min-h-0 overflow-hidden">
      <slot />
    </main>
  </div>
</template>
