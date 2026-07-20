<script lang="ts">
  import type { Snippet } from 'svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { router } from '$lib/router.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { LayoutDashboard, FolderOpen, Users, Settings, LogOut, Menu, ArrowLeft } from '@lucide/svelte';

  interface Props {
    children: Snippet;
  }

  let { children }: Props = $props();
  let mobileSidebar = $state(false);

  const navItems = [
    { path: '/admin', icon: LayoutDashboard, label: () => t('admin_nav_dashboard'), exact: true },
    { path: '/admin/highlights', icon: FolderOpen, label: () => t('admin_nav_highlights') },
    { path: '/admin/organization', icon: Users, label: () => t('admin_nav_organization') },
    { path: '/admin/settings', icon: Settings, label: () => t('admin_nav_settings') },
  ];

  const currentPath = $derived(router.current.path);
  const user = $derived(authStore.user);

  function isActive(path: string, exact = false): boolean {
    if (exact) return currentPath === path;
    return currentPath === path || currentPath.startsWith(path + '/');
  }

  function navigate(path: string): void {
    mobileSidebar = false;
    router.go(path);
  }

  async function handleLogout(): Promise<void> {
    await authStore.logout();
    router.go('/admin/login');
  }
</script>

<div class="admin-layout">
  <aside class="admin-sidebar" class:admin-sidebar--open={mobileSidebar}>
    <div class="admin-sidebar__brand">
      <span class="admin-sidebar__logo">SPB</span>
      <span class="admin-sidebar__title">{t('site_name')}</span>
    </div>

    <nav class="admin-sidebar__nav" aria-label="Admin navigation">
      {#each navItems as item (item.path)}
        <button
          class="admin-nav-item"
          class:admin-nav-item--active={isActive(item.path, item.exact)}
          onclick={() => navigate(item.path)}
          aria-current={isActive(item.path, item.exact) ? 'page' : undefined}
        >
          <item.icon size={18} />
          <span>{item.label()}</span>
        </button>
      {/each}
    </nav>

    <div class="admin-sidebar__footer">
      <button class="admin-nav-item admin-nav-item--danger" onclick={handleLogout}>
        <LogOut size={18} />
        <span>{t('admin_nav_logout')}</span>
      </button>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="admin-topbar__burger" onclick={() => (mobileSidebar = true)} aria-label="Open menu">
        <Menu size={22} />
      </button>
      <div class="admin-topbar__user">
        {#if user}
          <span class="admin-topbar__user-name">{user.full_name}</span>
        {/if}
      </div>
      <a href="/" class="admin-topbar__back" onclick={(e) => { e.preventDefault(); navigate('/'); }}>
        <ArrowLeft size={16} />
        <span>{t('admin_back')}</span>
      </a>
    </header>

    <main class="admin-content">
      {@render children()}
    </main>
  </div>
</div>

{#if mobileSidebar}
  <div class="admin-overlay" onclick={() => (mobileSidebar = false)} role="presentation"></div>
{/if}

<style>
  .admin-layout {
    display: flex;
    min-height: 100vh;
    background: var(--color-gray-50);
  }

  .admin-sidebar {
    width: 16rem;
    background: var(--color-brown);
    color: var(--color-beige);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: var(--z-drawer);
    transition: transform var(--duration-short) var(--ease-smooth);
  }

  .admin-sidebar__brand {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    padding: var(--sp-5) var(--sp-6);
    border-bottom: 1px solid rgba(232, 220, 200, 0.15);
  }

  .admin-sidebar__logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: var(--radius-md);
    background: var(--color-accent);
    color: var(--color-white);
    font-family: var(--font-serif);
    font-size: 1.25rem;
    font-weight: var(--fw-bold);
  }

  .admin-sidebar__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
    color: var(--color-ivory);
  }

  .admin-sidebar__nav {
    flex: 1;
    padding: var(--sp-4) var(--sp-3);
    display: flex;
    flex-direction: column;
    gap: var(--sp-1);
  }

  .admin-nav-item {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    padding: var(--sp-3) var(--sp-4);
    border-radius: var(--radius-md);
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    color: var(--color-beige);
    width: 100%;
    text-align: left;
    transition: background-color var(--duration-fast) var(--ease-smooth), color var(--duration-fast) var(--ease-smooth);
  }

  .admin-nav-item:hover {
    background-color: rgba(255, 255, 255, 0.08);
    color: var(--color-ivory);
  }

  .admin-nav-item--active {
    background-color: var(--color-accent);
    color: var(--color-white);
  }

  .admin-nav-item--danger:hover {
    background-color: rgba(158, 42, 43, 0.25);
    color: var(--color-red-soft);
  }

  .admin-sidebar__footer {
    padding: var(--sp-3);
    border-top: 1px solid rgba(232, 220, 200, 0.15);
  }

  .admin-main {
    flex: 1;
    margin-left: 16rem;
    display: flex;
    flex-direction: column;
    min-width: 0;
  }

  .admin-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--sp-4) var(--sp-6);
    background: var(--color-surface);
    border-bottom: 1px solid var(--color-border);
    height: 3.75rem;
  }

  .admin-topbar__burger {
    display: none;
    color: var(--color-text);
  }

  .admin-topbar__user-name {
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    color: var(--color-text);
  }

  .admin-topbar__back {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--fs-body-sm);
    color: var(--color-text-muted);
  }

  .admin-topbar__back:hover {
    color: var(--color-accent);
  }

  .admin-content {
    flex: 1;
    padding: var(--sp-6);
    overflow-y: auto;
  }

  .admin-overlay {
    position: fixed;
    inset: 0;
    z-index: calc(var(--z-drawer) - 1);
    background: rgba(26, 22, 18, 0.5);
  }

  @media (max-width: 880px) {
    .admin-sidebar {
      transform: translateX(-100%);
    }
    .admin-sidebar--open {
      transform: translateX(0);
    }
    .admin-main {
      margin-left: 0;
    }
    .admin-topbar__burger {
      display: flex;
    }
  }
</style>