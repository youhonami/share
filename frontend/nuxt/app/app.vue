<script setup lang="ts">
import { useTheme } from '~/composables/useTheme'

// 初期テーマを適用するだけでOK
useTheme()
</script>

<template>
  <div class="app">
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<style>
html,
body {
  margin: 0;
  padding: 0;
}

.app {
  min-height: 100vh;
}
</style>
