<template>
  <div class="min-h-screen h-screen flex flex-col overflow-hidden bg-gray-700">
    <Header />
    <main class="flex-1 min-h-0 overflow-hidden">
      <slot />
    </main>
  </div>
</template>
