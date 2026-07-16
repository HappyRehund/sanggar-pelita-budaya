export type RouteMatch = {
  path: string;
  params: Record<string, string>;
  query: URLSearchParams;
};

type RoutePattern = {
  pattern: string;
  regex: RegExp;
  keys: string[];
};

function compile(path: string): RoutePattern {
  const keys: string[] = [];
  if (path === '*') {
    return { pattern: path, regex: /^.*$/, keys };
  }
  const regex = new RegExp(
    '^' +
      path
        .replace(/\/$/, '')
        .replace(/:([a-zA-Z_]+)/g, (_, key) => {
          keys.push(key);
          return '([^/]+)';
        }) +
      '/?$'
  );
  return { pattern: path, regex, keys };
}

const routes: { raw: string; compiled: RoutePattern }[] = [];

export function defineRoute(path: string) {
  routes.push({ raw: path, compiled: compile(path) });
  return path;
}

export function matchRoute(pathname: string): RouteMatch | null {
  const cleanPath = pathname.split('?')[0] || '/';
  for (const route of routes) {
    const m = route.compiled.regex.exec(cleanPath);
    if (m) {
      const params: Record<string, string> = {};
      route.compiled.keys.forEach((key, i) => {
        params[key] = decodeURIComponent(m[i + 1]);
      });
      const query = new URLSearchParams(pathname.split('?')[1] ?? '');
      return { path: route.raw, params, query };
    }
  }
  return null;
}

export function navigate(path: string, replace = false) {
  if (typeof window === 'undefined') return;
  if (window.location.pathname + window.location.search === path && !replace) return;
  if (replace) {
    window.history.replaceState({}, '', path);
  } else {
    window.history.pushState({}, '', path);
  }
  window.dispatchEvent(new PopStateEvent('popstate'));
}

class Router {
  current = $state<RouteMatch>({
    path: '/',
    params: {},
    query: new URLSearchParams(),
  });

  init() {
    if (typeof window === 'undefined') return;
    this.current = resolveCurrent();

    window.addEventListener('popstate', () => {
      this.current = resolveCurrent();
    });

    document.addEventListener('click', (e) => this.handleLinkClick(e));
  }

  go(path: string, replace = false) {
    navigate(path, replace);
    this.current = resolveCurrent();
  }

  private handleLinkClick(e: MouseEvent) {
    if (e.defaultPrevented) return;
    if (e.button !== 0) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    let target: HTMLElement | null = e.target as HTMLElement;
    while (target && target.tagName !== 'A') {
      target = target.parentElement;
    }
    if (!target) return;

    const anchor = target as HTMLAnchorElement;
    if (anchor.target && anchor.target !== '_self') return;
    if (anchor.hasAttribute('download')) return;
    if (anchor.hasAttribute('data-external')) return;

    const href = anchor.getAttribute('href');
    if (!href || !href.startsWith('/')) return;
    if (href.startsWith('//')) return;
    if (anchor.origin && anchor.origin !== window.location.origin) return;

    e.preventDefault();
    this.go(href);
  }
}

function resolveCurrent(): RouteMatch {
  if (typeof window === 'undefined') {
    return { path: '/', params: {}, query: new URLSearchParams() };
  }
  return matchRoute(window.location.pathname + window.location.search) ?? {
    path: '*',
    params: {},
    query: new URLSearchParams(window.location.search),
  };
}

export const router = new Router();