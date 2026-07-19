<script lang="ts">
  import { Menu, Globe } from '@lucide/svelte';
  import { router } from '$lib/router.svelte';
  import { langStore } from '$lib/stores/lang.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import logoSvg from '$assets/logo/logo-sanggar-pelita-budaya.svg';
  import Drawer from '$lib/components/Drawer.svelte';

  let mobileOpen = $state(false);

  const navItems = [
    { path: '/', label: () => t('nav_home') },
    { path: '/about', label: () => t('nav_about') },
    { path: '/organization', label: () => t('nav_organization') },
    { path: '/portfolio', label: () => t('nav_portfolio') },
  ];

  const currentPath = $derived(router.current.path);

  function isActive(path: string): boolean {
    if (path === '/') return currentPath === '/';
    return currentPath.startsWith(path);
  }

  function navigate(path: string): void {
    mobileOpen = false;
    router.go(path);
  }
</script>

<header class="navbar">
  <div class="navbar__inner">
    <a href="/" class="navbar__logo" onclick={(e) => { e.preventDefault(); navigate('/'); }}>
      <img src={logoSvg} alt="Sanggar Pelita Budaya" class="navbar__logo-img" />
      <span class="navbar__logo-text">Sanggar Pelita Budaya</span>
    </a>

    <nav class="navbar__nav" aria-label="Primary">
      {#each navItems as item (item.path)}
        <a
          href={item.path}
          class="nav-link"
          class:nav-link--active={isActive(item.path)}
          aria-current={isActive(item.path) ? 'page' : undefined}
          onclick={(e) => { e.preventDefault(); navigate(item.path); }}
        >
          {item.label()}
        </a>
      {/each}
    </nav>

    <div class="navbar__actions">
      <div class="navbar__lang" role="group" aria-label="Language switcher">
        <button
          type="button"
          class="navbar__lang-option"
          class:navbar__lang-option--active={langStore.current === 'en'}
          onclick={() => langStore.set('en')}
          aria-pressed={langStore.current === 'en'}
        >EN</button>
        <button
          type="button"
          class="navbar__lang-option"
          class:navbar__lang-option--active={langStore.current === 'id'}
          onclick={() => langStore.set('id')}
          aria-pressed={langStore.current === 'id'}
        >ID</button>
      </div>
      <button
        class="navbar__burger"
        onclick={() => (mobileOpen = true)}
        aria-label="Open menu"
        aria-expanded={mobileOpen}
      >
        <Menu size={24} />
      </button>
    </div>
  </div>
</header>

<Drawer open={mobileOpen} side="right" title="Sanggar Pelita Budaya" onclose={() => (mobileOpen = false)}>
  <nav class="mobile-nav" aria-label="Mobile navigation">
    {#each navItems as item (item.path)}
      <a
        href={item.path}
        class="mobile-nav__link"
        class:mobile-nav__link--active={isActive(item.path)}
        onclick={(e) => { e.preventDefault(); navigate(item.path); }}
      >
        {item.label()}
      </a>
    {/each}
    <hr class="mobile-nav__divider" />
    <button class="mobile-nav__lang" onclick={() => langStore.toggle()}>
      <Globe size={18} />
      <span>{langStore.current === 'en' ? t('lang_name_id') : t('lang_name_en')}</span>
    </button>
  </nav>
</Drawer>

<style>
  .navbar {
    position: sticky;
    top: 0;
    z-index: var(--z-sticky);
    background-color: rgba(251, 247, 240, 0.95);
    backdrop-filter: blur(12px);
    box-shadow: var(--shadow-sm);
  }

  .navbar__inner {
    max-width: var(--container-max);
    margin-inline: auto;
    padding: var(--sp-4) var(--sp-5);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--sp-5);
  }

  .navbar__logo {
    display: flex;
    align-items: center;
    justify-items: center;
    gap: var(--sp-4);
    flex-shrink: 0;
  }

  .navbar__logo-img {
    height: 2.5rem;
    width: auto;
  }

  .navbar__logo-text {
    font-family: var(--font-script);
    font-size: var(--fs-body-lg);
    color: var(--color-brown);
    letter-spacing: normal;
    line-height: var(--lh-snug);
    transform: translateY(0.2rem);
  }

  .nav-link {
    font-family: var(--font-serif);
    font-size: var(--fs-body-md);
    color: var(--color-text);
    text-decoration: none;
    transform: translateY(0.025rem);
    transition: color var(--duration-fast) var(--ease-smooth);
  }

  .nav-link:hover {
    color: var(--color-accent);
  }

  .nav-link--active {
    color: var(--color-accent);
  }

  .navbar__nav {
    display: flex;
    align-items: center;
    gap: var(--sp-6);
    flex: 1;
    justify-content: center;
  }

  .navbar__actions {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    flex-shrink: 0;
  }

  .navbar__lang {
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--color-border-strong);
    border-radius: var(--radius-full);
    padding: 2px;
    background-color: var(--color-surface-alt);
    flex-shrink: 0;
  }

  .navbar__lang-option {
    padding: var(--sp-1) var(--sp-3);
    border: none;
    border-radius: var(--radius-full);
    font-family: var(--font-serif);
    font-size: var(--fs-caption);
    font-weight: var(--fw-semibold);
    letter-spacing: var(--tracking-wide);
    color: var(--color-text-muted);
    background: transparent;
    cursor: pointer;
    transition: background-color var(--duration-fast) var(--ease-smooth),
                color var(--duration-fast) var(--ease-smooth);
  }

  .navbar__lang-option:hover:not(.navbar__lang-option--active) {
    color: var(--color-text);
  }

  .navbar__lang-option--active {
    background-color: var(--color-accent);
    color: var(--color-ivory);
  }

  .navbar__burger {
    display: none;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: var(--radius-md);
    color: var(--color-text);
  }

  /* ---- Mobile ---- */
  @media (max-width: 768px) {
    .navbar__nav {
      display: none;
    }

    .navbar__burger {
      display: flex;
    }
  }

  /* ---- Mobile nav drawer ---- */
  .mobile-nav {
    display: flex;
    flex-direction: column;
    gap: var(--sp-1);
  }

  .mobile-nav__link {
    display: block;
    padding: var(--sp-3) var(--sp-4);
    font-size: var(--fs-body-lg);
    font-family: var(--font-script);
    color: var(--color-text);
    border-radius: var(--radius-md);
    transform: translateY(0.2rem);
    transition: background-color var(--duration-fast) var(--ease-smooth), color var(--duration-fast) var(--ease-smooth);
  }

  .mobile-nav__link:hover,
  .mobile-nav__link--active {
    background-color: var(--color-surface-alt);
    color: var(--color-accent);
  }

  .mobile-nav__divider {
    border: none;
    border-top: 1px solid var(--color-border);
    margin: var(--sp-3) 0;
  }

  .mobile-nav__lang {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    padding: var(--sp-3) var(--sp-4);
    font-family: var(--font-script);
    font-size: var(--fs-body);
    color: var(--color-text);
    border-radius: var(--radius-md);
  }

  .mobile-nav :global(.mobile-nav__cta-button) {
    margin-top: var(--sp-3);
  }
</style>