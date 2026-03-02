<template>
  <div class="min-h-full flex flex-1 min-w-0">
    <Sidebar @share="handleShare" @logout="handleLogout" />

    <!-- メイン（タイムライン） -->
    <main class="flex-1 min-w-0 flex flex-col">
      <h1 class="text-white text-xl font-semibold py-4 px-6 border-b border-gray-600">
        ホーム
      </h1>

      <div class="flex flex-col divide-y divide-gray-600">
        <article
          v-for="post in posts"
          :key="post.id"
          class="py-4 px-6 flex flex-col gap-2"
        >
          <div class="flex items-center justify-between gap-2">
            <span class="text-white font-medium text-sm">{{ post.userName }}</span>
            <div class="flex items-center gap-4 shrink-0">
              <button
                type="button"
                class="flex items-center gap-1 text-gray-400 hover:text-white text-sm"
              >
                <img src="/icons/heart.png" alt="いいね" class="w-4 h-4" />
                <span>{{ post.likeCount }}</span>
              </button>
              <button
                type="button"
                class="p-1 text-gray-400 hover:text-white"
                aria-label="削除"
                @click="handleDelete(post.id)"
              >
                <img src="/icons/cross.png" alt="" class="w-4 h-4" />
              </button>
              <NuxtLink
                :to="`/tweets/${post.id}`"
                class="p-1 text-gray-400 hover:text-white inline-block"
                aria-label="コメント"
              >
                <img src="/icons/detail.png" alt="" class="w-4 h-4" />
              </NuxtLink>
            </div>
          </div>
          <p class="text-white text-sm">{{ post.text }}</p>
        </article>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'home' })

const posts = ref([
  { id: 1, userName: 'test', text: 'test message', likeCount: 1 },
])

function handleShare(text: string) {
  posts.value = [
    { id: Date.now(), userName: 'test', text, likeCount: 0 },
    ...posts.value,
  ]
}

function handleDelete(id: number) {
  posts.value = posts.value.filter((p) => p.id !== id)
}

function handleLogout() {
  // TODO: 認証トークン削除等
  navigateTo('/login')
}
</script>
