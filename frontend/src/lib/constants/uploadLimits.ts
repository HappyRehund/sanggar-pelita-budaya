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
  HIGHLIGHTS: 'highlights',
  ORGANIZATION: 'organization',
  SETTINGS: 'settings',
  DOCUMENTS: 'documents',
} as const;

export const HIGHLIGHTS_PER_PAGE = 12;
export const RELATED_HIGHLIGHTS_LIMIT = 4;