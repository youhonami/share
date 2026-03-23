<template>
  <div class="min-h-full flex flex-1 min-w-0">
    <Sidebar @share="handleShare" @logout="handleLogout" />

    <main class="flex-1 min-w-0 flex flex-col overflow-y-auto">
      <h1
        class="text-white text-xl font-semibold py-4 px-6 border-b border-gray-600"
      >
        マイ投稿
      </h1>

      <div v-if="loading" class="px-6 py-4 text-sm text-gray-400">
        読み込み中...
      </div>
      <div v-else-if="filteredPosts.length === 0" class="px-6 py-4 text-sm text-gray-400">
        あなたの投稿はまだありません。
      </div>
      <div v-else class="flex flex-col divide-y divide-gray-600">
        <PostItem
          v-for="post in filteredPosts"
          :key="post.id"
          :id="post.id"
          :user-name="post.userName"
          :text="post.text"
          :like-count="post.likeCount"
          :created-at="post.createdAt"
          :liked-by-me="post.likedByMe"
          :show-delete="post.canDelete"
          :show-edit="post.canDelete"
          :show-detail="true"
          :detail-to="`/tweets/${post.id}`"
          @delete="handleDelete(post.id)"
          @edit="handleEdit(post)"
          @toggle-like="handleToggleLike(post.id)"
        />
      </div>

      <EditTextModal
        :open="editModalOpen"
        title="投稿を編集"
        label="投稿内容"
        :initial-text="editingPost?.text ?? ''"
        :max-length="120"
        placeholder="投稿内容を入力..."
        :loading="editModalLoading"
        save-label="更新する"
        save-loading-label="更新中..."
        @update:open="editModalOpen = $event"
        @save="handleEditSave"
      />
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
  likedByMe: boolean
  canDelete: boolean
}

const config = useRuntimeConfig()
const posts = ref<Post[]>([])
const filteredPosts = computed(() => {
  if (!currentUserName.value) return []
  return posts.value.filter((p) => p.userName === currentUserName.value)
})
const currentUserName = ref<string | null>(null)
const loading = ref(true)
const editModalOpen = ref(false)
const editModalLoading = ref(false)
const editingPost = ref<Post | null>(null)

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

async function fetchUserAndTweets() {
  try {
    const apiBase = config.public.apiBase

    // ユーザー情報取得
    const user = await $fetch<{ name: string }>('/api/user', {
      baseURL: apiBase,
      credentials: 'include',
    })
    currentUserName.value = user.name

    // 全投稿取得（簡易的にフロント側で絞り込み）
    const data = await $fetch<Post[]>('/api/tweets', {
      baseURL: apiBase,
      credentials: 'include',
    })
    posts.value = data
  } catch (error) {
    console.error('マイ投稿の取得に失敗しました', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchUserAndTweets)

async function handleShare(text: string) {
  try {
    const apiBase = config.public.apiBase

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

async function handleEdit(post: Post) {
  editingPost.value = post
  editModalOpen.value = true
}

async function handleEditSave(text: string) {
  if (!editingPost.value) return

  const initial = editingPost.value.text
  const trimmed = text.trim()
  if (!trimmed || trimmed === initial) {
    editModalOpen.value = false
    return
  }

  editModalLoading.value = true

  try {
    const apiBase = config.public.apiBase

    if (!getXsrfToken()) {
      await $fetch('/sanctum/csrf-cookie', {
        baseURL: apiBase,
        credentials: 'include',
      })
    }

    const xsrfToken = getXsrfToken()

    const updated = await $fetch<Post>(`/api/tweets/${editingPost.value.id}`, {
      baseURL: apiBase,
      method: 'PATCH',
      credentials: 'include',
      headers: xsrfToken
        ? {
            'X-XSRF-TOKEN': xsrfToken,
          }
        : undefined,
      body: { text: trimmed },
    })

    posts.value = posts.value.map((p) => (p.id === updated.id ? updated : p))
    editModalOpen.value = false
  } catch (error) {
    console.error('ツイートの編集に失敗しました', error)
  } finally {
    editModalLoading.value = false
  }
}

async function handleToggleLike(id: number) {
  try {
    const apiBase = config.public.apiBase

    if (!getXsrfToken()) {
      await $fetch('/sanctum/csrf-cookie', {
        baseURL: apiBase,
        credentials: 'include',
      })
    }

    const xsrfToken = getXsrfToken()

    const target = posts.value.find((p) => p.id === id)
    if (!target) return

    const method = target.likedByMe ? 'DELETE' : 'POST'

    const result = await $fetch<{ likeCount: number; likedByMe: boolean }>(
      `/api/tweets/${id}/like`,
      {
        baseURL: apiBase,
        method,
        credentials: 'include',
        headers: xsrfToken
          ? {
              'X-XSRF-TOKEN': xsrfToken,
            }
          : undefined,
      },
    )

    target.likeCount = result.likeCount
    target.likedByMe = result.likedByMe
  } catch (error) {
    console.error('いいねの更新に失敗しました', error)
  }
}

function handleLogout() {
  navigateTo('/login')
}
</script>

