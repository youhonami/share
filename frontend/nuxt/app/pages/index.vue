<template>
  <div class="min-h-full flex flex-1 min-w-0">
    <Sidebar @share="handleShare" @logout="handleLogout" />

    <!-- メイン（タイムライン） -->
    <main class="flex-1 min-w-0 flex flex-col overflow-y-auto">
      <h1 class="text-white text-xl font-semibold py-4 px-6 border-b border-gray-600">
        ホーム
      </h1>

      <div class="flex flex-col divide-y divide-gray-600">
        <PostItem
          v-for="post in posts"
          :key="post.id"
          :id="post.id"
          :user-name="post.userName"
          :text="post.text"
          :like-count="post.likeCount"
          :created-at="post.createdAt"
          :show-delete="true"
          :show-detail="true"
          :detail-to="`/tweets/${post.id}`"
          @delete="handleDelete(post.id)"
        />
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
  createdAt: string
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

async function handleDelete(id: number) {
  try {
    const apiBase = config.public.apiBase

    if (!getXsrfToken()) {
      await $fetch('/sanctum/csrf-cookie', {
        baseURL: apiBase,
        credentials: 'include',
      })
    }

    const xsrfToken = getXsrfToken()

    await $fetch(`/api/tweets/${id}`, {
      baseURL: apiBase,
      method: 'DELETE',
      credentials: 'include',
      headers: xsrfToken
        ? {
            'X-XSRF-TOKEN': xsrfToken,
          }
        : undefined,
    })

    posts.value = posts.value.filter((p) => p.id !== id)
  } catch (error) {
    console.error('ツイートの削除に失敗しました', error)
  }
}

function handleLogout() {
  // TODO: 認証トークン削除等
  navigateTo('/login')
}
</script>
