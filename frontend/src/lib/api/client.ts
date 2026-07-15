import type { ApiResponse } from '$lib/types';

const API_BASE = import.meta.env.VITE_API_BASE ?? '';

class ApiError extends Error {
  status: number;
  errors?: Record<string, string>;
  constructor(message: string, status: number, errors?: Record<string, string>) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }
}

let csrfToken: string | null = null;
let csrfPromise: Promise<string | null> | null = null;

async function ensureCsrfToken(): Promise<string | null> {
  if (csrfToken) return csrfToken;
  if (csrfPromise) return csrfPromise;

  csrfPromise = (async () => {
    try {
      const res = await fetch(`${API_BASE}/api/csrf-token`, {
        credentials: 'include',
      });
      const json = (await res.json()) as ApiResponse<{ csrf_token: string }>;
      if (json.success) {
        csrfToken = json.data.csrf_token;
        return csrfToken;
      }
      return null;
    } catch {
      return null;
    } finally {
      csrfPromise = null;
    }
  })();

  return csrfPromise;
}

export function clearCsrfToken(): void {
  csrfToken = null;
}

type RequestInitJson = Omit<RequestInit, 'body'> & { body?: unknown };

async function request<T>(path: string, init: RequestInitJson = {}): Promise<T> {
  const method = (init.method ?? 'GET').toUpperCase();
  const needsCsrf = method !== 'GET' && method !== 'OPTIONS';

  if (needsCsrf) {
    const token = await ensureCsrfToken();
    if (!token) {
      throw new ApiError('Failed to obtain CSRF token', 500);
    }
  }

  const headers = new Headers(init.headers);
  if (init.body !== undefined && !(init.body instanceof FormData) && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }
  if (needsCsrf) {
    const token = await ensureCsrfToken();
    if (token) headers.set('X-CSRF-Token', token);
  }
  headers.set('Accept', 'application/json');

  const res = await fetch(`${API_BASE}${path}`, {
    ...init,
    body: init.body !== undefined ? (init.body as BodyInit) : undefined,
    headers,
    credentials: 'include',
  });

  if (res.status === 401) {
    csrfToken = null;
  }

  let json: ApiResponse<T> | null = null;
  const contentType = res.headers.get('content-type') ?? '';
  if (contentType.includes('application/json')) {
    json = (await res.json()) as ApiResponse<T>;
  } else {
    const text = await res.text();
    if (!res.ok) {
      throw new ApiError(text || res.statusText, res.status);
    }
  }

  if (!res.ok || !json) {
    const message = json?.message ?? res.statusText ?? 'Request failed';
    const errors = json && !json.success ? json.errors : undefined;
    throw new ApiError(message, res.status, errors);
  }

  if (!json.success) {
    throw new ApiError(json.message, res.status, json.errors);
  }

  return json.data;
}

export const api = {
  get: <T>(path: string, init?: RequestInit) =>
    request<T>(path, { ...init, method: 'GET' }),

  post: <T>(path: string, body?: unknown, init?: RequestInit) => {
    const isFormData = body instanceof FormData;
    return request<T>(path, {
      ...init,
      method: 'POST',
      body: isFormData ? body : body !== undefined ? JSON.stringify(body) : undefined,
    });
  },

  put: <T>(path: string, body?: unknown, init?: RequestInit) => {
    const isFormData = body instanceof FormData;
    return request<T>(path, {
      ...init,
      method: 'PUT',
      body: isFormData ? body : body !== undefined ? JSON.stringify(body) : undefined,
    });
  },

  delete: <T>(path: string, init?: RequestInit) =>
    request<T>(path, { ...init, method: 'DELETE' }),
};

export { ApiError };