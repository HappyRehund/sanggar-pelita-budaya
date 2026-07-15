import { i18n, type Lang } from '$lib/i18n/index.svelte';

export const langStore = {
  get current(): Lang {
    return i18n.current;
  },
  set(lang: Lang) {
    i18n.set(lang);
  },
  toggle() {
    i18n.toggle();
  },
};