/* ============================================================
   Route path constants
   ============================================================ */

export const ROUTES = {
  HOME: '/',
  ABOUT: '/about',
  ORGANIZATION: '/organization',
  PORTFOLIO: '/portfolio',
  PORTFOLIO_DETAIL: '/portfolio/:slug',
  ADMIN: '/admin',
  ADMIN_LOGIN: '/admin/login',
  ADMIN_DASHBOARD: '/admin/dashboard',
  ADMIN_PORTFOLIO: '/admin/portfolio',
  ADMIN_PORTFOLIO_NEW: '/admin/portfolio/new',
  ADMIN_PORTFOLIO_EDIT: '/admin/portfolio/:id',
  ADMIN_ORGANIZATION: '/admin/organization',
  ADMIN_HERO: '/admin/hero',
  ADMIN_FOOTER: '/admin/footer',
  ADMIN_SETTINGS: '/admin/settings',
  NOT_FOUND: '*',
} as const;

export type RoutePath = (typeof ROUTES)[keyof typeof ROUTES];

export function portfolioDetailPath(slug: string): string {
  return `/portfolio/${slug}`;
}

export function portfolioEditPath(id: number): string {
  return `/admin/portfolio/${id}`;
}