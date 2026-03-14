<template>
  <button
    :type="buttonType"
    class="px-6 py-2.5 text-sm font-semibold text-white bg-violet-600 rounded-lg hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500 disabled:opacity-60 disabled:cursor-not-allowed"
    :class="block ? 'w-full' : 'w-fit'"
    :disabled="disabled || loading"
    @click="$emit('click')"
  >
    {{ loading ? loadingLabel : label }}
  </button>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    label: string
    loadingLabel?: string
    loading?: boolean
    disabled?: boolean
    /** submit: フォーム送信用, button: @click 用 */
    buttonType?: 'submit' | 'button'
    /** true のとき幅いっぱい（サイドバー等） */
    block?: boolean
  }>(),
  {
    loadingLabel: '変更中...',
    loading: false,
    disabled: false,
    buttonType: 'submit',
    block: false,
  }
)

defineEmits<{
  click: []
}>()
</script>
