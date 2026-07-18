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

  // Portfolio
  PORTFOLIO: '/api/portfolio',
  PORTFOLIO_FEATURED: '/api/portfolio/featured',
  PORTFOLIO_GALLERY: '/api/portfolio/gallery',
  PORTFOLIO_SLUG: (slug: string) => `/api/portfolio/slug/${slug}`,
  PORTFOLIO_DETAIL: (id: number) => `/api/portfolio/${id}`,
  PORTFOLIO_MEDIA: (id: number) => `/api/portfolio/${id}/media`,
  PORTFOLIO_MEDIA_DETAIL: (id: number) => `/api/portfolio/media/${id}`,
  PORTFOLIO_MEDIA_REORDER: '/api/portfolio/media/reorder',

  // Organization
  ORGANIZATION: '/api/organization',
  ORGANIZATION_TREE: '/api/organization/tree',
  ORGANIZATION_DETAIL: (id: number) => `/api/organization/${id}`,
  ORGANIZATION_REORDER: '/api/organization/reorder',
  ORGANIZATION_PHOTO: (id: number) => `/api/organization/${id}/photo`,

  // Settings
  SETTINGS: '/api/settings',

  // Dashboard
  DASHBOARD: '/api/dashboard',
} as const;