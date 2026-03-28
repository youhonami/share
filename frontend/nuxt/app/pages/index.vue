<template>
  <div class="flex flex-1 min-w-0 min-h-0 flex-col lg:flex-row">
    <Sidebar @share="handleShare" @logout="handleLogout" />

    <!-- メイン（タイムライン） -->
    <main class="flex-1 min-w-0 min-h-0 flex flex-col overflow-y-auto">
      <h1
        class="hidden lg:block text-white text-xl font-semibold py-4 px-4 lg:px-6 border-b border-gray-600"
      >
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

async function apiFetch<T>(path: string, options: any = {}) {
  const apiBase = config.public.apiBase
  return await $fetch<T>(path, {
    baseURL: apiBase,
    credentials: 'include',
    ...options,
  })
}

async function apiFetchWithCsrf<T>(path: string, options: any = {}) {
  if (!getXsrfToken()) {
    await apiFetch('/sanctum/csrf-cookie')
  }
  const xsrfToken = getXsrfToken()
  return await apiFetch<T>(path, {
    headers: xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : undefined,
    ...options,
  })
}

async function fetchTweets() {
  try {
    posts.value = await apiFetch<Post[]>('/api/tweets')
  } catch (error) {
    console.error('ツイートの取得に失敗しました', error)
  }
}

onMounted(fetchTweets)

async function handleShare(text: string) {
  try {
    const newPost = await apiFetchWithCsrf<Post>('/api/tweets', {
      method: 'POST',
      body: { text },
    })

    posts.value = [newPost, ...posts.value]
  } catch (error) {
    console.error('ツイートの投稿に失敗しました', error)
  }
}

async function handleDelete(id: number) {
  try {
    await apiFetchWithCsrf(`/api/tweets/${id}`, {
      method: 'DELETE',
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
    const updated = await apiFetchWithCsrf<Post>(
      `/api/tweets/${editingPost.value.id}`,
      { method: 'PATCH', body: { text: trimmed } },
    )

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
    const target = posts.value.find((p) => p.id === id)
    if (!target) return

    const method = target.likedByMe ? 'DELETE' : 'POST'

    const result = await apiFetchWithCsrf<{
      likeCount: number
      likedByMe: boolean
    }>(`/api/tweets/${id}/like`, { method })

    target.likeCount = result.likeCount
    target.likedByMe = result.likedByMe
  } catch (error) {
    console.error('いいねの更新に失敗しました', error)
  }
}

function handleLogout() {
  // TODO: 認証トークン削除等
  navigateTo('/login')
}
</script>
