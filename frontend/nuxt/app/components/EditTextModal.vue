<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      role="dialog"
      aria-modal="true"
      aria-labelledby="edit-modal-title"
      @click.self="handleClose"
    >
      <div class="w-full max-w-md bg-white rounded-xl shadow-xl p-6">
        <h2
          id="edit-modal-title"
          class="text-lg font-semibold text-gray-800 mb-4"
        >
          {{ title }}
        </h2>

        <label class="flex flex-col gap-2 text-sm text-gray-700 mb-4">
          <span v-if="label" class="font-medium">
            {{ label }}
          </span>
          <textarea
            v-model="localText"
            :rows="textareaRows"
            class="w-full py-2.5 px-3 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg resize-y placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
            :placeholder="placeholder"
          />
        </label>

        <p v-if="errorMessage" class="text-xs text-red-500 mb-3">
          {{ errorMessage }}
        </p>

        <div class="flex gap-3 justify-end">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-60"
            :disabled="loading"
            @click="handleClose"
          >
            キャンセル
          </button>
          <button
            type="button"
            class="px-4 py-2 text-sm font-semibold text-white bg-violet-600 rounded-lg hover:bg-violet-500 disabled:opacity-60"
            :disabled="loading || !canSave"
            @click="handleSave"
          >
            {{ loading ? saveLoadingLabel : saveLabel }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    open: boolean
    title: string
    label?: string
    initialText: string
    placeholder?: string
    saveLabel?: string
    saveLoadingLabel?: string
    loading?: boolean
    textareaRows?: number
  }>(),
  {
    label: '',
    placeholder: '',
    saveLabel: '保存',
    saveLoadingLabel: '保存中...',
    loading: false,
    textareaRows: 4,
  },
)

const emit = defineEmits<{
  'update:open': [value: boolean]
  save: [text: string]
}>()

const localText = ref(props.initialText)
const errorMessage = ref<string | null>(null)

watch(
  () => props.open,
  (val) => {
    if (val) {
      localText.value = props.initialText
      errorMessage.value = null
    }
  },
)

const canSave = computed(() => localText.value.trim().length > 0)

function handleClose() {
  if (!props.loading) {
    emit('update:open', false)
  }
}

function handleSave() {
  const text = localText.value.trim()
  if (!text) {
    errorMessage.value = '内容を入力してください。'
    return
  }
  errorMessage.value = null
  emit('save', text)
}
</script>

