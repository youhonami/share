/** 422 の errors オブジェクトを取り出す（ofetch / $fetch の形の差を吸収） */
function extractErrorsRecord(err: unknown): Record<string, unknown> | null {
  if (!err || typeof err !== "object") return null;
  const o = err as Record<string, unknown>;

  const fromPayload = (payload: unknown): Record<string, unknown> | null => {
    if (!payload || typeof payload !== "object") return null;
    const p = payload as { errors?: unknown };
    if (p.errors && typeof p.errors === "object" && !Array.isArray(p.errors)) {
      return p.errors as Record<string, unknown>;
    }
    return null;
  };

  // Nuxt $fetch / ofetch: 本体が data に入る
  const d = fromPayload(o.data);
  if (d) return d;

  // 一部環境: response._data
  const res = o.response as Record<string, unknown> | undefined;
  const d2 = fromPayload(res?._data);
  if (d2) return d2;

  // cause にラップされている場合
  const d3 = fromPayload(o.cause);
  if (d3) return d3;

  if (o.errors && typeof o.errors === "object" && !Array.isArray(o.errors)) {
    return o.errors as Record<string, unknown>;
  }

  return null;
}

function firstMessage(v: unknown): string | null {
  if (typeof v === "string" && v.trim()) return v;
  if (Array.isArray(v) && v.length > 0 && typeof v[0] === "string") return v[0];
  return null;
}

/** Laravel の 422 レスポンス `errors` をフィールドごとの先頭メッセージに整形 */
export function pickFieldErrors(err: unknown): Record<string, string> {
  const raw = extractErrorsRecord(err);
  if (!raw) return {};
  const out: Record<string, string> = {};
  for (const key of Object.keys(raw)) {
    const msg = firstMessage(raw[key]);
    if (msg) out[key] = msg;
  }
  return out;
}
