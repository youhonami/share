<template>
  <div class="flex flex-col gap-2">
    <label v-if="label" :for="id" class="text-sm font-medium text-gray-300">
      {{ label }}
    </label>
    <div class="relative">
      <input
        :id="id"
        :value="modelValue"
        :type="showPassword ? 'text' : 'password'"
        class="w-full py-3 pl-4 pr-20 text-sm text-white bg-gray-700/50 border border-gray-500 rounded-lg placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 box-border"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
      />
      <button
        type="button"
        class="absolute right-2 top-1/2 -translate-y-1/2 py-1.5 px-2.5 text-xs text-gray-400 hover:text-white rounded border border-gray-500 hover:border-gray-400"
        @click="showPassword = !showPassword"
      >
        {{ showPassword ? '隠す' : '表示' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue: string
  id: string
  label?: string
  placeholder?: string
  autocomplete?: string
}>()

defineEmits<{
  'update:modelValue': [value: string]
}>()

const showPassword = ref(false)
</script>
