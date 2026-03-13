<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      role="dialog"
      aria-modal="true"
      aria-labelledby="confirm-modal-title"
      @click.self="close"
    >
      <div class="w-full max-w-sm bg-white rounded-xl shadow-xl p-6">
        <h2
          id="confirm-modal-title"
          class="text-lg font-semibold text-gray-800 mb-4"
        >
          {{ title }}
        </h2>
        <p class="text-sm text-gray-600 mb-6">
          {{ message }}
        </p>
        <div class="flex gap-3 justify-end">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-60"
            :disabled="loading"
            @click="close"
          >
            {{ cancelLabel }}
          </button>
          <button
            type="button"
            :class="confirmButtonClass"
            :disabled="loading"
            @click="$emit('confirm')"
          >
            {{ loading ? loadingLabel : confirmLabel }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    modelValue: boolean
    title: string
    message: string
    confirmLabel?: string
    cancelLabel?: string
    loadingLabel?: string
    loading?: boolean
    variant?: 'danger' | 'primary'
  }>(),
  {
    confirmLabel: '確認',
    cancelLabel: 'キャンセル',
    loadingLabel: '処理中...',
    loading: false,
    variant: 'primary',
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  confirm: []
}>()

const confirmButtonClass = computed(() => {
  const base =
    'px-4 py-2 text-sm font-semibold text-white rounded-lg disabled:opacity-60'
  if (props.variant === 'danger') {
    return `${base} bg-red-600 hover:bg-red-700`
  }
  return `${base} bg-violet-600 hover:bg-violet-700`
})

function close() {
  if (!props.loading) {
    emit('update:modelValue', false)
  }
}
</script>
