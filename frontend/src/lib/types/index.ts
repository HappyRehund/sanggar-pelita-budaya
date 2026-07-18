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

/* ---- Portfolio ----------------------------------------------------- */
export type PortfolioCategory = 'achievement' | 'activity';
export type MediaKind = 'cover' | 'gallery' | 'og';

export interface PortfolioMedia {
  id: number;
  portfolio_id: number;
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

export interface Portfolio {
  id: number;
  title: string;
  slug: string;
  category: PortfolioCategory;
  short_description: string;
  content: string;
  cover_image_id: number | null;
  cover?: PortfolioMedia | null;
  og_image_id: number | null;
  og_image?: PortfolioMedia | null;
  gallery?: PortfolioMedia[];
  event_date: string | null;
  location: string | null;
  youtube_url: string | null;
  featured: boolean;
  published: boolean;
  seo_title: string | null;
  seo_description: string | null;
  created_at: string;
  updated_at: string;
  related?: PortfolioListSummary[];
}

export interface PortfolioListSummary {
  id: number;
  title: string;
  slug: string;
  category: PortfolioCategory;
  short_description: string;
  event_date: string | null;
  location: string | null;
  cover?: PortfolioMedia | null;
  featured: boolean;
  published: boolean;
}

/* ---- Organization --------------------------------------------------- */
export interface OrganizationMember {
  id: number;
  parent_id: number | null;
  name: string;
  position: string;
  photo: string | null;
  biography: string | null;
  display_order: number;
  published: boolean;
  children?: OrganizationMember[];
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
  total_portfolio: number;
  achievements: number;
  activities: number;
  organization_members: number;
  published_portfolio: number;
  draft_portfolio: number;
}

export interface RecentUpload {
  id: number;
  filename: string;
  original_filename: string;
  created_at: string;
  portfolio_id: number | null;
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

/* ---- Portfolio queries ---------------------------------------------- */
export type PortfolioSort = 'newest' | 'oldest' | 'alphabetical';

export interface PortfolioListQuery {
  page?: number;
  per_page?: number;
  category?: PortfolioCategory | 'all';
  search?: string;
  sort?: PortfolioSort;
  featured?: boolean;
  published?: boolean;
}