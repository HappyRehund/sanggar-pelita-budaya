/* ============================================================
   Portfolio category constants
   ============================================================ */

import type { PortfolioCategory } from '$lib/types';

export const CATEGORIES: PortfolioCategory[] = ['achievement', 'activity'];

export const CATEGORY_LABELS: Record<PortfolioCategory, { en: string; id: string }> = {
  achievement: { en: 'Achievement', id: 'Prestasi' },
  activity: { en: 'Activity', id: 'Kegiatan' },
};

export function categoryLabel(category: PortfolioCategory, lang: 'en' | 'id'): string {
  return CATEGORY_LABELS[category][lang];
}