/* ============================================================
   Language constants
   ============================================================ */

import type { Lang } from '$lib/types';

export const LANGUAGES: { code: Lang; label: string; native: string }[] = [
  { code: 'en', label: 'English', native: 'English' },
  { code: 'id', label: 'Indonesian', native: 'Bahasa Indonesia' },
];

export const DEFAULT_LANG: Lang = 'en';