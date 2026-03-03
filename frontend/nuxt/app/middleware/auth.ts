export default defineNuxtRouteMiddleware(async () => {
  // SSR（サーバーサイド）ではブラウザの Cookie がないため、認証チェックはクライアント側のみで行う
  if (process.server) {
    return
  }

  const config = useRuntimeConfig()

  try {
    const apiBase = config.public.apiBase
    await $fetch('/api/user', {
      baseURL: apiBase,
      credentials: 'include',
    })
  } catch {
    return navigateTo('/login')
  }
})
