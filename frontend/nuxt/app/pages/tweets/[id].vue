<template>
  <div class="min-h-full flex flex-1 min-w-0">
    <Sidebar @share="handleShare" @logout="handleLogout" />

    <main class="flex-1 min-w-0 flex flex-col">
      <h1 class="text-white text-xl font-semibold py-4 px-6 border-b border-gray-600">
        コメント
      </h1>

      <!-- 元投稿 -->
      <article class="py-4 px-6 flex flex-col gap-2 border-b border-gray-600">
        <div class="flex items-center justify-between gap-2">
          <span class="text-white font-medium text-sm">{{ post.userName }}</span>
          <div class="flex items-center gap-4 shrink-0">
            <span class="flex items-center gap-1 text-gray-400 text-sm">
              <img src="/icons/heart.png" alt="いいね" class="w-4 h-4" />
              {{ post.likeCount }}
            </span>
            <NuxtLink
              to="/"
              class="p-1 text-gray-400 hover:text-white"
              aria-label="閉じる"
            >
              <img src="/icons/cross.png" alt="" class="w-4 h-4" />
            </NuxtLink>
          </div>
        </div>
        <p class="text-white text-sm">{{ post.text }}</p>
      </article>

      <!-- コメント一覧 -->
      <div class="py-4 px-6">
        <h2 class="text-white text-sm font-medium mb-3 text-right">コメント</h2>
        <div class="flex flex-col gap-4 divide-y divide-gray-600">
          <div
            v-for="comment in comments"
            :key="comment.id"
            class="pt-3 first:pt-0"
          >
            <p class="text-white font-medium text-sm">{{ comment.userName }}</p>
            <p class="text-white text-sm mt-1">{{ comment.text }}</p>
          </div>
        </div>

        <!-- コメント入力 -->
        <div class="flex gap-3 mt-6">
          <input
            v-model="newComment"
            type="text"
            placeholder="コメントを入力..."
            class="flex-1 py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
            @keydown.enter.prevent="submitComment"
          >
          <button
            type="button"
            class="shrink-0 py-3 px-6 text-sm font-semibold text-white bg-violet-600 rounded-lg hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500"
            @click="submitComment"
          >
            コメント
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'home' })

const route = useRoute()
const tweetId = computed(() => Number(route.params.id) || 0)

// TODO: API から投稿・コメント取得
const post = ref({
  id: tweetId.value,
  userName: 'test',
  text: 'test message',
  likeCount: 1,
})

const comments = ref([
  { id: 1, userName: 'test', text: 'test comment' },
])

const newComment = ref('')

function handleShare(text: string) {
  // TODO: API で投稿
  console.log('Share:', text)
}

function handleLogout() {
  navigateTo('/login')
}

function submitComment() {
  const text = newComment.value.trim()
  if (!text) return
  comments.value = [
    ...comments.value,
    { id: Date.now(), userName: 'test', text },
  ]
  newComment.value = ''
}
</script>
