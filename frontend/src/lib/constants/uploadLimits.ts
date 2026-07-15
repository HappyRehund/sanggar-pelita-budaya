/* ============================================================
   Upload limits & file type constants
   ============================================================ */

export const UPLOAD_MAX_SIZE = 5 * 1024 * 1024;

export const UPLOAD_ALLOWED_MIME = [
  'image/jpeg',
  'image/png',
  'image/webp',
] as const;

export const UPLOAD_ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'] as const;

export const UPLOAD_FOLDERS = {
  HERO: 'hero',
  PORTFOLIO: 'portfolio',
  ORGANIZATION: 'organization',
  SETTINGS: 'settings',
  DOCUMENTS: 'documents',
} as const;

export const PORTFOLIO_PER_PAGE = 12;
export const FEATURED_PORTFOLIO_LIMIT = 6;
export const RELATED_PORTFOLIO_LIMIT = 4;
export const GALLERY_IMAGE_LIMIT = 10;