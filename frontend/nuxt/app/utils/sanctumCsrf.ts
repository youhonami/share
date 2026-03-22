/** Cookie から XSRF トークン（無ければ null） */
export function xsrfFromCookie(): string | null {
  if (!process.client) return null;
  const hit = decodeURIComponent(document.cookie ?? "")
    .split("; ")
    .find((c) => c.startsWith("XSRF-TOKEN="));
  return hit ? hit.slice("XSRF-TOKEN=".length) : null;
}

/**
 * /sanctum/csrf-cookie 取得後、ブラウザが XSRF-TOKEN を即座に document.cookie に載せないことがあるため短く待つ
 */
export async function xsrfAfterCsrfCookie(base: string): Promise<string | null> {
  await $fetch("/sanctum/csrf-cookie", { baseURL: base, credentials: "include" });
  await nextTick();
  for (let i = 0; i < 15; i++) {
    const t = xsrfFromCookie();
    if (t) return t;
    await new Promise((r) => setTimeout(r, 20));
  }
  return xsrfFromCookie();
}

export function fetchStatus(err: unknown): number | undefined {
  const e = err as { statusCode?: number; status?: number; response?: { status?: number } };
  return e.statusCode ?? e.status ?? e.response?.status;
}
