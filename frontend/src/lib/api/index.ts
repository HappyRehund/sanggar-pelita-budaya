import { api, ApiError } from './client';
import { API } from './endpoints';
import type {
  User,
  Portfolio,
  PortfolioListSummary,
  PortfolioMedia,
  OrganizationMember,
  Hero,
  Footer,
  Settings,
  DashboardData,
  PaginatedResponse,
  PortfolioListQuery,
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

/* ---- Portfolio ---------------------------------------------------- */
export const portfolioApi = {
  list: (query: PortfolioListQuery = {}) => {
    const params = new URLSearchParams();
    if (query.page) params.set('page', String(query.page));
    if (query.per_page) params.set('per_page', String(query.per_page));
    if (query.category && query.category !== 'all') params.set('category', query.category);
    if (query.search) params.set('search', query.search);
    if (query.sort) params.set('sort', query.sort);
    if (query.featured !== undefined) params.set('featured', String(query.featured));
    if (query.published !== undefined) params.set('published', String(query.published));
    const qs = params.toString();
    return api.get<PaginatedResponse<PortfolioListSummary>>(
      qs ? `${API.PORTFOLIO}?${qs}` : API.PORTFOLIO
    );
  },

  featured: (limit = 6) =>
    api.get<PortfolioListSummary[]>(`${API.PORTFOLIO_FEATURED}?limit=${limit}`),

  galleryImages: () => api.get<PortfolioMedia[]>(API.PORTFOLIO_GALLERY),

  getBySlug: (slug: string) => api.get<Portfolio>(API.PORTFOLIO_SLUG(slug)),

  getById: (id: number) => api.get<Portfolio>(API.PORTFOLIO_DETAIL(id)),

  create: (data: Partial<Portfolio>) => api.post<Portfolio>(API.PORTFOLIO, data),

  update: (id: number, data: Partial<Portfolio>) => api.put<Portfolio>(API.PORTFOLIO_DETAIL(id), data),

  delete: (id: number) => api.delete<null>(API.PORTFOLIO_DETAIL(id)),

  listMedia: (id: number) => api.get<PortfolioMedia[]>(API.PORTFOLIO_MEDIA(id)),

  uploadMedia: (id: number, file: File, type: 'cover' | 'gallery' | 'og', altText?: string) => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', type);
    if (altText) formData.append('alt_text', altText);
    return api.post<PortfolioMedia>(API.PORTFOLIO_MEDIA(id), formData);
  },

  deleteMedia: (mediaId: number) => api.delete<null>(API.PORTFOLIO_MEDIA_DETAIL(mediaId)),

  reorderMedia: (order: Record<string, number>) =>
    api.put<null>(API.PORTFOLIO_MEDIA_REORDER, { order }),
};

/* ---- Organization ------------------------------------------------- */
export const organizationApi = {
  list: () => api.get<OrganizationMember[]>(API.ORGANIZATION),

  tree: () => api.get<OrganizationMember[]>(API.ORGANIZATION_TREE),

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

/* ---- Hero --------------------------------------------------------- */
export const heroApi = {
  get: () => api.get<Hero>(API.HERO),
  update: (data: Partial<Hero>) => api.put<Hero>(API.HERO, data),
  uploadBackground: (file: File) => {
    const formData = new FormData();
    formData.append('file', file);
    return api.put<Hero>(API.HERO, formData);
  },
};

/* ---- Footer ------------------------------------------------------- */
export const footerApi = {
  get: () => api.get<Footer>(API.FOOTER),
  update: (data: Partial<Footer>) => api.put<Footer>(API.FOOTER, data),
  uploadLogo: (file: File) => {
    const formData = new FormData();
    formData.append('file', file);
    return api.put<Footer>(API.FOOTER, formData);
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