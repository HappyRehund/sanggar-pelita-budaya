/* ============================================================
   Highlights category constants
   ============================================================ */

import type { HighlightCategory } from '$lib/types';

export const CATEGORIES: HighlightCategory[] = ['achievement', 'activity'];

export const CATEGORY_LABELS: Record<HighlightCategory, { en: string; id: string }> = {
  achievement: { en: 'Achievement', id: 'Prestasi' },
  activity: { en: 'Activity', id: 'Kegiatan' },
};

export function categoryLabel(category: HighlightCategory, lang: 'en' | 'id'): string {
  return CATEGORY_LABELS[category][lang];
}