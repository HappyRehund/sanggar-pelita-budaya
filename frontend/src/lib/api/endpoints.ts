/* ============================================================
   Centralized API endpoint paths
   Never hardcode URLs — import from here.
   ============================================================ */

export const API = {
  // Auth
  LOGIN: '/api/login',
  LOGOUT: '/api/logout',
  SESSION: '/api/session',
  CSRF_TOKEN: '/api/csrf-token',

  // Health
  HEALTH: '/api/health',
  INFO: '/api/info',

  // Highlights
  HIGHLIGHTS: '/api/highlights',
  HIGHLIGHTS_FEATURED: '/api/highlights/featured',
  HIGHLIGHTS_GALLERY: '/api/highlights/gallery',
  HIGHLIGHTS_SLUG: (slug: string) => `/api/highlights/slug/${slug}`,
  HIGHLIGHTS_DETAIL: (id: number) => `/api/highlights/${id}`,
  HIGHLIGHTS_MEDIA: (id: number) => `/api/highlights/${id}/media`,
  HIGHLIGHTS_MEDIA_DETAIL: (id: number) => `/api/highlights/media/${id}`,
  HIGHLIGHTS_MEDIA_REORDER: '/api/highlights/media/reorder',

  // Organization
  ORGANIZATION: '/api/organization',
  ORGANIZATION_FEATURED: '/api/organization/featured',
  ORGANIZATION_DETAIL: (id: number) => `/api/organization/${id}`,
  ORGANIZATION_REORDER: '/api/organization/reorder',
  ORGANIZATION_PHOTO: (id: number) => `/api/organization/${id}/photo`,

  // Settings
  SETTINGS: '/api/settings',

  // Dashboard
  DASHBOARD: '/api/dashboard',
} as const;