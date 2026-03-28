<template>
  <article class="py-4 px-4 lg:px-6 flex flex-col gap-2 border-b border-gray-600">
  <div class="flex flex-col gap-2">
    <div class="flex items-center gap-2">
      <span class="text-white font-medium text-sm">{{ userName }}</span>
      <span class="text-xs text-gray-400">{{ createdAt }}</span>
    </div>
    <div class="flex items-center gap-3 shrink-0">
        <button
          type="button"
          class="flex items-center gap-1 text-sm"
          :class="
            likedByMe
              ? 'text-pink-500 hover:text-pink-400'
              : 'text-gray-400 hover:text-white'
          "
          @click="$emit('toggle-like')"
        >
          <img src="/icons/heart.png" alt="いいね" class="w-4 h-4" />
          <span>{{ likeCount }}</span>
        </button>

        <button
          v-if="showEdit"
          type="button"
          class="p-1 text-gray-400 hover:text-white"
          aria-label="編集"
          @click="$emit('edit')"
        >
          <img src="/icons/feather.png" alt="編集" class="w-4 h-4" />
        </button>

        <button
          v-if="showDelete"
          type="button"
          class="p-1 text-gray-400 hover:text-white"
          aria-label="削除"
          @click="$emit('delete')"
        >
          <img src="/icons/cross.png" alt="" class="w-4 h-4" />
        </button>

        <NuxtLink
          v-if="showDetail && detailTo"
          :to="detailTo"
          class="p-1 inline-flex items-center justify-center rounded-full bg-gray-700 hover:bg-gray-600"
          aria-label="コメント"
        >
          <img src="/icons/detail.png" alt="詳細" class="w-4 h-4" />
        </NuxtLink>

        <NuxtLink
          v-if="showClose"
          to="/"
          class="p-1 text-gray-400 hover:text-white"
          aria-label="閉じる"
        >
          <img src="/icons/cross.png" alt="" class="w-4 h-4" />
        </NuxtLink>
      </div>
    </div>
    <p class="text-white text-sm">
      {{ text }}
    </p>
  </article>
</template>

<script setup lang="ts">
const props = defineProps<{
  id: number
  userName: string
  text: string
  likeCount: number
  createdAt: string
  likedByMe: boolean
  showDelete?: boolean
  showEdit?: boolean
  showDetail?: boolean
  showClose?: boolean
  detailTo?: string
}>()

defineEmits<{
  delete: []
  edit: []
  'toggle-like': []
}>()
</script>

