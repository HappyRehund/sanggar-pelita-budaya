import en from './en.json';
import id from './id.json';

type Dict = Record<string, string>;
const dictionaries: Record<'en' | 'id', Dict> = { en, id };

export type Lang = 'en' | 'id';

const STORAGE_KEY = 'sanggar_lang';

function detectInitial(): Lang {
  if (typeof window === 'undefined') return 'en';
  const stored = localStorage.getItem(STORAGE_KEY);
  if (stored === 'en' || stored === 'id') return stored;
  return 'en';
}

function interpolate(text: string, vars: Record<string, string | number>): string {
  let out = text;
  for (const [k, v] of Object.entries(vars)) {
    out = out.replaceAll(`{${k}}`, String(v));
  }
  return out;
}

function lookup(current: Lang, key: string, vars: Record<string, string | number> = {}): string {
  const dict = dictionaries[current] ?? dictionaries.en;
  const text = dict[key] ?? key;
  return interpolate(text, vars);
}

class I18nStore {
  current = $state<Lang>(detectInitial());

  constructor() {
    if (typeof window !== 'undefined') {
      document.documentElement.lang = this.current;
    }
  }

  set(lang: Lang) {
    this.current = lang;
    if (typeof window !== 'undefined') {
      localStorage.setItem(STORAGE_KEY, lang);
      document.documentElement.lang = lang;
    }
  }

  toggle() {
    this.set(this.current === 'en' ? 'id' : 'en');
  }

  t(key: string, vars: Record<string, string | number> = {}): string {
    return lookup(this.current, key, vars);
  }
}

export const i18n = new I18nStore();

export function t(key: string, vars?: Record<string, string | number>): string {
  return i18n.t(key, vars);
}