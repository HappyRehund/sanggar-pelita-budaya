/* ============================================================
   Static site content — non-translatable values
   Hero and Footer are static frontend content (no longer CMS-managed).
   Translatable text for these sections lives in i18n ({en,id}.json).
   ============================================================ */

export const SITE_CONTENT = {
  /** Hero primary CTA target. Text comes from i18n key `hero_cta_primary`. */
  heroPrimaryCtaUrl: '/highlights',

  /** Google Maps link shown in Footer and Home ContactCta section. */
  mapsUrl: '',

  /** Social profile URLs. Falsy entries are filtered out by the Footer. */
  socials: {
    facebook: '',
    instagram: '',
    youtube: '',
  },
} as const;