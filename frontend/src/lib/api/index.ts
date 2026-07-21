import { api, ApiError } from './client';
import { API } from './endpoints';
import type {
  User,
  Highlight,
  HighlightListSummary,
  HighlightMedia,
  OrganizationMember,
  Settings,
  DashboardData,
  PaginatedResponse,
  HighlightListQuery,
} from '$lib/types';

export { ApiError };
export { API };

/* ---- Auth --------------------------------------------------------- */
export const authApi = {
  login: (username: string, password: string) =>
    api.post<{ user: User }>(API.LOGIN, { username, password }),
  logout: () => api.post<null>(API.LOGOUT),
  session: () => api.get<User>(API.SESSION),
};

/* ---- Health ------------------------------------------------------- */
export const healthApi = {
  check: () => api.get<{ status: string; time: string; env: string }>(API.HEALTH),
};

/* ---- Highlights --------------------------------------------------- */
export const highlightsApi = {
  list: (query: HighlightListQuery = {}) => {
    const params = new URLSearchParams();
    if (query.page) params.set('page', String(query.page));
    if (query.per_page) params.set('per_page', String(query.per_page));
    if (query.category && query.category !== 'all') params.set('category', query.category);
    if (query.search) params.set('search', query.search);
    if (query.sort) params.set('sort', query.sort);
    if (query.featured !== undefined) params.set('featured', String(query.featured));
    if (query.published !== undefined) params.set('published', String(query.published));
    const qs = params.toString();
    return api.get<PaginatedResponse<HighlightListSummary>>(
      qs ? `${API.HIGHLIGHTS}?${qs}` : API.HIGHLIGHTS
    );
  },

  featured: (limit = 6) =>
    api.get<HighlightListSummary[]>(`${API.HIGHLIGHTS_FEATURED}?limit=${limit}`),

  galleryImages: () => api.get<HighlightMedia[]>(API.HIGHLIGHTS_GALLERY),

  getBySlug: (slug: string) => api.get<Highlight>(API.HIGHLIGHTS_SLUG(slug)),

  getById: (id: number) => api.get<Highlight>(API.HIGHLIGHTS_DETAIL(id)),

  create: (data: Partial<Highlight>) => api.post<Highlight>(API.HIGHLIGHTS, data),

  update: (id: number, data: Partial<Highlight>) => api.put<Highlight>(API.HIGHLIGHTS_DETAIL(id), data),

  delete: (id: number) => api.delete<null>(API.HIGHLIGHTS_DETAIL(id)),

  listMedia: (id: number) => api.get<HighlightMedia[]>(API.HIGHLIGHTS_MEDIA(id)),

  uploadMedia: (id: number, file: File, type: 'cover' | 'gallery' | 'og', altText?: string) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', type);
    if (altText) formData.append('alt_text', altText);
    return api.post<HighlightMedia>(API.HIGHLIGHTS_MEDIA(id), formData);
  },

  deleteMedia: (mediaId: number) => api.delete<null>(API.HIGHLIGHTS_MEDIA_DETAIL(mediaId)),

  reorderMedia: (order: Record<string, number>) =>
    api.put<null>(API.HIGHLIGHTS_MEDIA_REORDER, { order }),
};

/* ---- Organization ------------------------------------------------- */
export const organizationApi = {
  list: () => api.get<OrganizationMember[]>(API.ORGANIZATION),

  featured: () => api.get<OrganizationMember[]>(API.ORGANIZATION_FEATURED),

  getById: (id: number) => api.get<OrganizationMember>(API.ORGANIZATION_DETAIL(id)),

  create: (data: Partial<OrganizationMember>) => api.post<OrganizationMember>(API.ORGANIZATION, data),

  update: (id: number, data: Partial<OrganizationMember>) =>
    api.put<OrganizationMember>(API.ORGANIZATION_DETAIL(id), data),

  delete: (id: number) => api.delete<null>(API.ORGANIZATION_DETAIL(id)),

  reorder: (order: Record<string, number>) =>
    api.put<null>(API.ORGANIZATION_REORDER, { order }),

  uploadPhoto: (id: number, file: File) => {
    const formData = new FormData();
    formData.append('file', file);
    return api.post<OrganizationMember>(API.ORGANIZATION_PHOTO(id), formData);
  },
};

/* ---- Settings ----------------------------------------------------- */
export const settingsApi = {
  get: () => api.get<Settings>(API.SETTINGS),
  update: (data: Partial<Settings>) => api.put<Settings>(API.SETTINGS, data),
  uploadImage: (field: 'logo' | 'favicon' | 'default_og_image', file: File) => {
    const formData = new FormData();
    formData.append(field, file);
    return api.put<Settings>(API.SETTINGS, formData);
  },
};

/* ---- Dashboard ---------------------------------------------------- */
export const dashboardApi = {
  getData: () => api.get<DashboardData>(API.DASHBOARD),
};