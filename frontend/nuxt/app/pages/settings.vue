<template>
  <div class="flex flex-1 min-w-0 min-h-0 flex-col lg:flex-row">
    <Sidebar @share="handleShare" @logout="handleLogout" />
    <main class="flex-1 min-w-0 min-h-0 flex flex-col overflow-y-auto">
      <h1
        class="hidden lg:block text-white text-xl font-semibold py-4 px-4 lg:px-6 border-b border-gray-600"
      >
        ユーザー設定
      </h1>
      <nav
        class="flex gap-0 border-b border-gray-600 px-4 lg:px-6 overflow-x-auto"
        aria-label="設定"
      >
        <button
          v-for="t in tabs"
          :key="t.id"
          type="button"
          class="shrink-0 py-3 px-4 text-sm font-medium border-b-2 transition-colors -mb-px"
          :class="tabBtn(activeTab === t.id)"
          @click="activeTab = t.id"
        >
          {{ t.label }}
        </button>
      </nav>

      <div class="p-4 lg:p-6 flex flex-col gap-10 max-w-xl">
        <template v-if="activeTab === 'account'">
          <section class="flex flex-col gap-4">
            <h2 class="text-white text-base font-medium">名前</h2>
            <p v-if="currentUserName" class="text-sm text-gray-400">
              現在: {{ currentUserName }}
            </p>
            <form
              class="flex flex-col gap-4"
              novalidate
              @submit.prevent="saveName"
            >
              <div class="flex flex-col gap-2">
                <label for="user-name" class="text-sm font-medium text-gray-300"
                  >新しい名前</label
                >
                <input
                  id="user-name"
                  v-model="userNameForm.name"
                  type="text"
                  maxlength="20"
                  autocomplete="username"
                  :class="[
                    controlClass,
                    userNameFieldError ? 'border-red-500' : '',
                  ]"
                  placeholder="20文字以内"
                  @input="userNameFieldError = ''"
                />
                <p
                  class="text-xs text-right transition-colors"
                  :class="
                    userNameForm.name.length >= USER_NAME_MAX
                      ? 'text-red-400'
                      : 'text-gray-400'
                  "
                >
                  {{ userNameForm.name.length }}/{{ USER_NAME_MAX }}
                </p>
                <p v-if="userNameFieldError" class="text-sm text-red-400">
                  {{ userNameFieldError }}
                </p>
              </div>
              <p
                v-if="userNameMessage"
                class="text-sm"
                :class="userNameError ? 'text-red-400' : 'text-green-400'"
              >
                {{ userNameMessage }}
              </p>
              <SubmitButton
                label="名前を更新"
                :loading="userNameLoading"
                :disabled="!canSubmitUserName"
              />
            </form>
          </section>
          <section class="flex flex-col gap-4">
            <h2 class="text-white text-base font-medium">パスワード</h2>
            <p class="text-sm text-gray-400">新パスは6文字以上の英数字。</p>
            <form
              class="flex flex-col gap-4"
              novalidate
              @submit.prevent="savePassword"
            >
              <div class="flex flex-col gap-2">
                <PasswordInput
                  id="current-password"
                  v-model="passwordForm.currentPassword"
                  label="現在"
                  placeholder="現在"
                  autocomplete="current-password"
                />
                <p
                  v-if="passwordFieldErrors.current_password"
                  class="text-sm text-red-400 -mt-1"
                >
                  {{ passwordFieldErrors.current_password }}
                </p>
              </div>
              <div class="flex flex-col gap-2">
                <PasswordInput
                  id="new-password"
                  v-model="passwordForm.newPassword"
                  label="新規（6文字〜）"
                  placeholder="新規"
                  autocomplete="new-password"
                />
                <p
                  v-if="passwordFieldErrors.password"
                  class="text-sm text-red-400 -mt-1"
                >
                  {{ passwordFieldErrors.password }}
                </p>
              </div>
              <div class="flex flex-col gap-2">
                <PasswordInput
                  id="new-password-confirm"
                  v-model="passwordForm.newPasswordConfirm"
                  label="確認"
                  placeholder="再入力"
                  autocomplete="new-password"
                />
                <p
                  v-if="passwordFieldErrors.password_confirmation"
                  class="text-sm text-red-400 -mt-1"
                >
                  {{ passwordFieldErrors.password_confirmation }}
                </p>
              </div>
              <p
                v-if="passwordMessage"
                class="text-sm"
                :class="passwordError ? 'text-red-400' : 'text-green-400'"
              >
                {{ passwordMessage }}
              </p>
              <SubmitButton
                label="パスワードを更新"
                :loading="passwordLoading"
                :disabled="!isPasswordFormValid"
              />
            </form>
          </section>
        </template>

        <template v-else-if="activeTab === 'options'">
          <section class="flex flex-col gap-4">
            <h2 class="text-white text-base font-medium">テーマ</h2>
            <p class="text-sm text-gray-400">背景色を切り替え。</p>
            <div class="flex flex-col gap-2 max-w-xs">
              <label
                for="theme-select"
                class="text-sm font-medium text-gray-300"
                >選択</label
              >
              <select
                id="theme-select"
                v-model="selectedTheme"
                :class="controlClass"
              >
                <option value="dark" class="text-gray-900">ダーク</option>
                <option value="blue" class="text-gray-900">ブルー</option>
                <option value="green" class="text-gray-900">グリーン</option>
              </select>
            </div>
          </section>
        </template>

        <template v-else-if="activeTab === 'block'">
          <section class="flex flex-col gap-6">
            <div class="flex flex-col gap-2">
              <h2 class="text-white text-base font-medium">ブロック</h2>
              <p class="text-sm text-gray-400">
                相手には自分の投稿を表示しません。
              </p>
            </div>
            <div class="flex flex-col gap-3 max-w-md">
              <label
                for="block-user-select"
                class="text-sm font-medium text-gray-300"
                >ユーザーを選択</label
              >
              <p v-if="blockCandidatesLoading" class="text-sm text-gray-400">
                読込中…
              </p>
              <p v-else-if="blockCandidatesError" class="text-sm text-red-400">
                {{ blockCandidatesError }}
              </p>
              <select
                v-else
                id="block-user-select"
                v-model="selectedBlockEmail"
                :class="controlClass"
              >
                <option value="" disabled>選択してください</option>
                <option v-for="u in blockChoices" :key="u.id" :value="u.email">
                  {{ u.name }}（{{ u.email }}）
                </option>
              </select>
              <SubmitButton
                label="追加"
                loading-label="追加中…"
                :loading="blockAddLoading"
                button-type="button"
                :disabled="selectedBlockEmail === ''"
                @click="addBlock"
              />
              <p
                v-if="blockFormMessage"
                class="text-sm"
                :class="blockFormError ? 'text-red-400' : 'text-green-400'"
              >
                {{ blockFormMessage }}
              </p>
            </div>
            <div class="flex flex-col gap-4 border-t border-gray-600 pt-6">
              <h3 class="text-white text-sm font-medium">
                ブロック中（{{ blockedUsers.length }}人）
              </h3>
              <ul v-if="blockedUsers.length" class="flex flex-col gap-2">
                <li
                  v-for="u in blockedUsers"
                  :key="u.id"
                  class="flex items-center justify-between gap-3 p-3 bg-gray-700/30 border border-gray-600 rounded-lg"
                >
                  <span class="text-sm text-white truncate">{{ u.name }}</span>
                  <SubmitButton
                    label="解除"
                    loading-label="中…"
                    :loading="blockUnblockLoadingId === u.id"
                    :disabled="
                      blockUnblockLoadingId != null &&
                      blockUnblockLoadingId !== u.id
                    "
                    button-type="button"
                    @click="removeBlock(u.id)"
                  />
                </li>
              </ul>
              <p v-else class="text-sm text-gray-400">
                ブロック中のユーザーはいません。
              </p>
            </div>
          </section>
        </template>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: "home", middleware: "auth" });

const {
  USER_NAME_MAX,
  tabs,
  activeTab,
  tabBtn,
  controlClass,
  currentUserName,
  userNameForm,
  userNameLoading,
  userNameMessage,
  userNameError,
  userNameFieldError,
  canSubmitUserName,
  saveName,
  passwordForm,
  passwordLoading,
  passwordMessage,
  passwordError,
  passwordFieldErrors,
  isPasswordFormValid,
  savePassword,
  selectedTheme,
  blockCandidatesLoading,
  blockCandidatesError,
  selectedBlockEmail,
  blockAddLoading,
  blockFormMessage,
  blockFormError,
  blockUnblockLoadingId,
  blockedUsers,
  blockChoices,
  addBlock,
  removeBlock,
  handleShare,
  handleLogout,
} = useSettingsPage();
</script>
