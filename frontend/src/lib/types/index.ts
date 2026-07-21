/* ============================================================
   Type definitions — Sanggar Pelita Budaya
   All API responses and domain entities are typed here.
   ============================================================ */

export type ApiResponse<T> =
  | { success: true; message: string; data: T }
  | { success: false; message: string; errors?: Record<string, string> };

/* ---- Auth ---------------------------------------------------------- */
export interface User {
  id: number;
  username: string;
  full_name: string;
}

/* ---- Language ------------------------------------------------------ */
export type Lang = 'id' | 'en';

/* ---- Highlights ---------------------------------------------------- */
export type HighlightCategory = 'achievement' | 'activity';
export type MediaKind = 'cover' | 'gallery' | 'og';

export interface HighlightMedia {
  id: number;
  highlight_id: number;
  type: MediaKind;
  filename: string;
  original_filename: string;
  mime_type: string;
  extension: string;
  width: number | null;
  height: number | null;
  size_bytes: number;
  alt_text: string | null;
  sort_order: number;
  created_at: string;
}

export interface Highlight {
  id: number;
  title: string;
  slug: string;
  category: HighlightCategory;
  short_description: string;
  content: string;
  cover_media_id: number | null;
  cover?: HighlightMedia | null;
  og_media_id: number | null;
  og_media?: HighlightMedia | null;
  gallery?: HighlightMedia[];
  event_date: string | null;
  location: string | null;
  youtube_url: string | null;
  featured: boolean;
  published: boolean;
  seo_title: string | null;
  seo_description: string | null;
  created_at: string;
  updated_at: string;
  related?: HighlightListSummary[];
}

export interface HighlightListSummary {
  id: number;
  title: string;
  slug: string;
  category: HighlightCategory;
  short_description: string;
  event_date: string | null;
  location: string | null;
  cover?: HighlightMedia | null;
  featured: boolean;
  published: boolean;
}

/* ---- Organization --------------------------------------------------- */
export interface OrganizationMember {
  id: number;
  name: string;
  position_en: string;
  position_id: string;
  biography_en: string | null;
  biography_id: string | null;
  photo: string | null;
  display_order: number;
  featured_slot: number | null;
  published: boolean;
  created_at: string;
  updated_at: string;
}

/* ---- Settings ------------------------------------------------------- */
export interface Settings {
  id: number;
  site_name: string;
  site_description: string;
  logo: string | null;
  favicon: string | null;
  default_language: Lang;
  default_og_image: string | null;
  maintenance_mode: boolean;
  updated_at: string;
}

/* ---- Dashboard ------------------------------------------------------ */
export interface DashboardStats {
  total_highlights: number;
  achievements: number;
  activities: number;
  organization_members: number;
  published_highlights: number;
  draft_highlights: number;
}

export interface RecentUpload {
  id: number;
  filename: string;
  original_filename: string;
  created_at: string;
  highlight_id: number | null;
}

export interface DashboardData {
  stats: DashboardStats;
  recent_uploads: RecentUpload[];
}

/* ---- Pagination ----------------------------------------------------- */
export interface PaginationMeta {
  current_page: number;
  total_pages: number;
  total_items: number;
  per_page: number;
  has_next: boolean;
  has_prev: boolean;
}

export interface PaginatedResponse<T> {
  items: T[];
  pagination: PaginationMeta;
}

/* ---- Validation ----------------------------------------------------- */
export interface ValidationError {
  field: string;
  message: string;
}

/* ---- Highlights queries --------------------------------------------- */
export type HighlightSort = 'newest' | 'oldest' | 'alphabetical';

export interface HighlightListQuery {
  page?: number;
  per_page?: number;
  category?: HighlightCategory | 'all';
  search?: string;
  sort?: HighlightSort;
  featured?: boolean;
  published?: boolean;
}