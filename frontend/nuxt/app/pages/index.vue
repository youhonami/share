<template>
  <div class="min-h-full flex flex-1 min-w-0">
    <Sidebar @share="handleShare" @logout="handleLogout" />

    <!-- メイン（タイムライン） -->
    <main class="flex-1 min-w-0 flex flex-col overflow-y-auto">
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
definePageMeta({ layout: 'home', middleware: 'auth' })

type Post = {
  id: number
  userName: string
  text: string
  likeCount: number
}

const config = useRuntimeConfig()
const posts = ref<Post[]>([])

function getXsrfToken(): string | null {
  if (process.client) {
    const name = 'XSRF-TOKEN='
    const decodedCookie = decodeURIComponent(document.cookie ?? '')
    const parts = decodedCookie.split('; ')
    const cookie = parts.find((c) => c.startsWith(name))
    if (cookie) {
      return cookie.substring(name.length)
    }
  }
  return null
}

async function fetchTweets() {
  try {
    const apiBase = config.public.apiBase
    const data = await $fetch<Post[]>('/api/tweets', {
      baseURL: apiBase,
      credentials: 'include',
    })
    posts.value = data
  } catch (error) {
    console.error('ツイートの取得に失敗しました', error)
  }
}

onMounted(fetchTweets)

async function handleShare(text: string) {
  try {
    const apiBase = config.public.apiBase

    // 必要であれば CSRF Cookie を取得
    if (!getXsrfToken()) {
      await $fetch('/sanctum/csrf-cookie', {
        baseURL: apiBase,
        credentials: 'include',
      })
    }

    const xsrfToken = getXsrfToken()
    const newPost = await $fetch<Post>('/api/tweets', {
      baseURL: apiBase,
      method: 'POST',
      credentials: 'include',
      headers: xsrfToken
        ? {
            'X-XSRF-TOKEN': xsrfToken,
          }
        : undefined,
      body: { text },
    })

    posts.value = [newPost, ...posts.value]
  } catch (error) {
    console.error('ツイートの投稿に失敗しました', error)
  }
}

function handleDelete(id: number) {
  posts.value = posts.value.filter((p) => p.id !== id)
}

function handleLogout() {
  // TODO: 認証トークン削除等
  navigateTo('/login')
}
</script>
