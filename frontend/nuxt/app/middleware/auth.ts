export default defineNuxtRouteMiddleware(async () => {
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
