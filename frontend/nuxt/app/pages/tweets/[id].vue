<template>
  <div class="min-h-full flex flex-1 min-w-0">
    <Sidebar @share="handleShare" @logout="handleLogout" />

    <main class="flex-1 min-w-0 flex flex-col">
      <h1
        class="text-white text-xl font-semibold py-4 px-6 border-b border-gray-600"
      >
        コメント
      </h1>

      <!-- 元投稿 -->
      <div class="border-b border-gray-600">
        <PostItem
          v-if="post"
          :id="post.id"
          :user-name="post.userName"
          :text="post.text"
          :like-count="post.likeCount"
          :created-at="post.createdAt"
          :liked-by-me="post.likedByMe"
          :show-delete="true"
          @delete="handleDeleteTweet"
          @toggle-like="handleToggleLikePost"
        />
        <p v-else class="text-gray-400 text-sm px-6 py-4">読み込み中...</p>
      </div>

      <!-- コメント一覧 -->
      <div class="py-4 px-6 flex flex-col">
        <h2 class="text-white text-sm font-medium mb-3 text-center">コメント</h2>

        <!-- コメントリスト（ここだけスクロール・約10件分の高さ） -->
        <div class="max-h-[32rem] overflow-y-auto pr-2">
          <div class="flex flex-col gap-4 divide-y divide-gray-600">
            <div
              v-for="comment in comments"
              :key="comment.id"
              class="pt-3 first:pt-0"
            >
              <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                  <p class="text-white font-medium text-sm">
                    {{ comment.userName }}
                  </p>
                  <p class="text-xs text-gray-400">
                    {{ comment.createdAt }}
                  </p>
                </div>
                <div class="flex items-center gap-1">
                  <button
                    v-if="comment.canEdit"
                    type="button"
                    class="p-1 text-gray-400 hover:text-white"
                    aria-label="コメントを編集"
                    @click="openCommentEdit(comment)"
                  >
                    <img src="/icons/feather.png" alt="編集" class="w-4 h-4" />
                  </button>
                  <button
                    v-if="comment.canDelete"
                    type="button"
                    class="p-1 text-gray-400 hover:text-white"
                    aria-label="コメントを削除"
                    @click="handleDeleteComment(comment.id)"
                  >
                    <img src="/icons/cross.png" alt="" class="w-4 h-4" />
                  </button>
                </div>
              </div>
              <p class="text-white text-sm mt-1">{{ comment.text }}</p>
            </div>
          </div>
        </div>

        <!-- コメント入力（必須・120文字以内・ツイートのシェアと同様） -->
        <div class="flex flex-col gap-2 mt-6">
          <textarea
            v-model="newComment"
            maxlength="120"
            rows="3"
            placeholder="コメントを入力..."
            class="w-full py-3 px-4 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg resize-y placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
            :class="commentInputError ? 'border-red-500' : ''"
            @input="commentInputError = ''"
          />
          <p class="text-xs text-right transition-colors" :class="newComment.length >= COMMENT_MAX ? 'text-red-400' : 'text-gray-400'">{{ newComment.length }}/{{ COMMENT_MAX }}</p>
          <p v-if="commentInputError" class="text-sm text-red-400">{{ commentInputError }}</p>
          <SubmitButton label="コメント" button-type="button" class="w-fit" @click="submitComment" />
        </div>

        <EditTextModal
          :open="commentEditOpen"
          title="コメントを編集"
          label="コメント内容"
          :initial-text="editingComment?.text ?? ''"
          :max-length="COMMENT_MAX"
          placeholder="コメント内容を入力..."
          :loading="commentEditLoading"
          save-label="更新する"
          save-loading-label="更新中..."
          @update:open="commentEditOpen = $event"
          @save="handleCommentEditSave"
        />
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { pickFieldErrors } from "~/utils/validationErrors";

definePageMeta({ layout: "home", middleware: "auth" });

const COMMENT_MAX = 120;

type Post = {
  id: number;
  userName: string;
  text: string;
  likeCount: number;
  createdAt: string;
  likedByMe: boolean;
};

type Comment = {
  id: number;
  userName: string;
  text: string;
  createdAt: string;
  canDelete: boolean;
  canEdit: boolean;
};

const route = useRoute();
const config = useRuntimeConfig();
const tweetId = computed(() => Number(route.params.id) || 0);

const post = ref<Post | null>(null);
const comments = ref<Comment[]>([]);
const newComment = ref("");
const commentInputError = ref("");
const commentEditOpen = ref(false);
const commentEditLoading = ref(false);
const editingComment = ref<Comment | null>(null);

function getXsrfToken(): string | null {
  if (process.client) {
    const name = "XSRF-TOKEN=";
    const decodedCookie = decodeURIComponent(document.cookie ?? "");
    const parts = decodedCookie.split("; ");
    const cookie = parts.find((c) => c.startsWith(name));
    if (cookie) {
      return cookie.substring(name.length);
    }
  }
  return null;
}

onMounted(async () => {
  if (!tweetId.value) return;

  try {
    const apiBase = config.public.apiBase;
    const data = await $fetch<{ post: Post; comments: Comment[] }>(
      `/api/tweets/${tweetId.value}`,
      {
        baseURL: apiBase,
        credentials: "include",
      },
    );
    post.value = data.post;
    comments.value = data.comments;
  } catch (error) {
    console.error("ツイート詳細の取得に失敗しました", error);
  }
});

function handleShare(text: string) {
  // ホームと同様に新規ツイート投稿だけ行う（画面の再取得はホームで実施）
  const apiBase = config.public.apiBase;

  // 必要であれば CSRF Cookie を取得
  const ensurePost = async () => {
    if (!getXsrfToken()) {
      await $fetch("/sanctum/csrf-cookie", {
        baseURL: apiBase,
        credentials: "include",
      });
    }

    const xsrfToken = getXsrfToken();

    await $fetch("/api/tweets", {
      baseURL: apiBase,
      method: "POST",
      credentials: "include",
      headers: xsrfToken
        ? {
            "X-XSRF-TOKEN": xsrfToken,
          }
        : undefined,
      body: { text },
    });
  };

  ensurePost().catch((error) => {
    console.error("ツイートの投稿に失敗しました", error);
  });
}

async function handleDeleteTweet() {
  if (!tweetId.value) return;

  try {
    const apiBase = config.public.apiBase;

    if (!getXsrfToken()) {
      await $fetch("/sanctum/csrf-cookie", {
        baseURL: apiBase,
        credentials: "include",
      });
    }

    const xsrfToken = getXsrfToken();

    await $fetch(`/api/tweets/${tweetId.value}`, {
      baseURL: apiBase,
      method: "DELETE",
      credentials: "include",
      headers: xsrfToken
        ? {
            "X-XSRF-TOKEN": xsrfToken,
          }
        : undefined,
    });

    await navigateTo("/");
  } catch (error) {
    console.error("ツイートの削除に失敗しました", error);
  }
}

function handleLogout() {
  navigateTo("/login");
}

async function submitComment() {
  commentInputError.value = "";
  const text = newComment.value.trim();
  if (!text) {
    commentInputError.value = "コメントを入力してください。";
    return;
  }
  if (newComment.value.length > COMMENT_MAX) {
    commentInputError.value = `コメントは${COMMENT_MAX}文字以内で入力してください。`;
    return;
  }

  const apiBase = config.public.apiBase;
  try {
    if (!getXsrfToken()) {
      await $fetch("/sanctum/csrf-cookie", { baseURL: apiBase, credentials: "include" });
    }
    const xsrfToken = getXsrfToken();
    const newItem = await $fetch<Comment>(`/api/tweets/${tweetId.value}/comments`, {
      baseURL: apiBase,
      method: "POST",
      credentials: "include",
      headers: xsrfToken ? { "X-XSRF-TOKEN": xsrfToken } : undefined,
      body: { text },
    });
    comments.value = [newItem, ...comments.value];
    newComment.value = "";
  } catch (err: unknown) {
    const fe = pickFieldErrors(err);
    if (fe.text) commentInputError.value = fe.text;
    else console.error("コメントの投稿に失敗しました", err);
  }
}

function openCommentEdit(comment: Comment) {
  editingComment.value = comment;
  commentEditOpen.value = true;
}

async function handleDeleteComment(commentId: number) {
  try {
    const apiBase = config.public.apiBase;

    if (!getXsrfToken()) {
      await $fetch("/sanctum/csrf-cookie", {
        baseURL: apiBase,
        credentials: "include",
      });
    }

    const xsrfToken = getXsrfToken();

    await $fetch(`/api/comments/${commentId}`, {
      baseURL: apiBase,
      method: "DELETE",
      credentials: "include",
      headers: xsrfToken
        ? {
            "X-XSRF-TOKEN": xsrfToken,
          }
        : undefined,
    });

    comments.value = comments.value.filter(
      (comment) => comment.id !== commentId,
    );
  } catch (error) {
    console.error("コメントの削除に失敗しました", error);
  }
}

async function handleToggleLikePost() {
  if (!post.value) return;

  try {
    const apiBase = config.public.apiBase;

    if (!getXsrfToken()) {
      await $fetch("/sanctum/csrf-cookie", {
        baseURL: apiBase,
        credentials: "include",
      });
    }

    const xsrfToken = getXsrfToken();

    const method = post.value.likedByMe ? "DELETE" : "POST";

    const result = await $fetch<{ likeCount: number; likedByMe: boolean }>(
      `/api/tweets/${tweetId.value}/like`,
      {
        baseURL: apiBase,
        method,
        credentials: "include",
        headers: xsrfToken
          ? {
              "X-XSRF-TOKEN": xsrfToken,
            }
          : undefined,
      },
    );

    post.value.likeCount = result.likeCount;
    post.value.likedByMe = result.likedByMe;
  } catch (error) {
    console.error("いいねの更新に失敗しました", error);
  }
}

async function handleCommentEditSave(text: string) {
  if (!editingComment.value) return;

  const initial = editingComment.value.text;
  const trimmed = text.trim();
  if (!trimmed || trimmed === initial) {
    commentEditOpen.value = false;
    return;
  }

  commentEditLoading.value = true;

  try {
    const apiBase = config.public.apiBase;

    if (!getXsrfToken()) {
      await $fetch("/sanctum/csrf-cookie", {
        baseURL: apiBase,
        credentials: "include",
      });
    }

    const xsrfToken = getXsrfToken();

    const updated = await $fetch<Comment>(
      `/api/comments/${editingComment.value.id}`,
      {
        baseURL: apiBase,
        method: "PATCH",
        credentials: "include",
        headers: xsrfToken
          ? {
              "X-XSRF-TOKEN": xsrfToken,
            }
          : undefined,
        body: { text: trimmed },
      },
    );

    comments.value = comments.value.map((comment) =>
      comment.id === updated.id ? updated : comment,
    );
    commentEditOpen.value = false;
  } catch (error) {
    console.error("コメントの編集に失敗しました", error);
  } finally {
    commentEditLoading.value = false;
  }
}
</script>
