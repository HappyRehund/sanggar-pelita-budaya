/* ============================================================
   Route path constants
   ============================================================ */

export const ROUTES = {
  HOME: '/',
  ABOUT: '/about',
  ORGANIZATION: '/organization',
  HIGHLIGHTS: '/highlights',
  HIGHLIGHTS_DETAIL: '/highlights/:slug',
  ADMIN: '/admin',
  ADMIN_LOGIN: '/admin/login',
  ADMIN_DASHBOARD: '/admin/dashboard',
  ADMIN_HIGHLIGHTS: '/admin/highlights',
  ADMIN_HIGHLIGHTS_NEW: '/admin/highlights/new',
  ADMIN_HIGHLIGHTS_EDIT: '/admin/highlights/:id',
  ADMIN_ORGANIZATION: '/admin/organization',
  ADMIN_SETTINGS: '/admin/settings',
  NOT_FOUND: '*',
} as const;

export type RoutePath = (typeof ROUTES)[keyof typeof ROUTES];

export function highlightDetailPath(slug: string): string {
  return `/highlights/${slug}`;
}

export function highlightEditPath(id: number): string {
  return `/admin/highlights/${id}`;
}