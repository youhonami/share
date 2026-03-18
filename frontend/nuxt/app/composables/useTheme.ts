export type Theme = "dark" | "blue" | "green";

const THEME_KEY = "share-theme";
const theme = ref<Theme>("dark");
let initialized = false;

function applyTheme(t: Theme) {
  const body = process.client ? document.body : null;
  if (!body) return;

  body.classList.remove("bg-gray-700", "bg-sky-900", "bg-emerald-900");

  if (t === "blue") body.classList.add("bg-sky-900");
  else if (t === "green") body.classList.add("bg-emerald-900");
  else body.classList.add("bg-gray-700");
}

function initTheme() {
  if (initialized || !process.client) return;
  const saved = window.localStorage.getItem(THEME_KEY) as Theme | null;
  if (saved === "blue" || saved === "green" || saved === "dark") {
    theme.value = saved;
  }
  applyTheme(theme.value);
  initialized = true;
}

export function useTheme() {
  initTheme();

  function setTheme(next: Theme) {
    theme.value = next;
    if (process.client) {
      window.localStorage.setItem(THEME_KEY, next);
    }
    applyTheme(next);
  }

  return { theme, setTheme };
}
