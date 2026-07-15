<script lang="ts">
  import { onMount } from 'svelte';
  import { gsap } from 'gsap';
  import { router, defineRoute } from '$lib/router.svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { settingsStore } from '$lib/stores/settings.svelte';
  import { langStore } from '$lib/stores/lang.svelte';
  import { t } from '$lib/i18n/index.svelte';

  import './assets/styles/tokens.css';
  import './assets/styles/global.css';

  import PublicLayout from './layouts/PublicLayout.svelte';
  import Toast from '$lib/components/Toast.svelte';

  import Home from './routes/Home.svelte';
  import AboutPage from '$modules/about/pages/AboutPage.svelte';
  import OrganizationPage from '$modules/organization/pages/OrganizationPage.svelte';
  import PortfolioListPage from '$modules/portfolio/pages/PortfolioListPage.svelte';
  import PortfolioDetailPage from '$modules/portfolio/pages/PortfolioDetailPage.svelte';
  import ContactPage from '$modules/contact/pages/ContactPage.svelte';
  import Login from './routes/admin/Login.svelte';
  import Admin from './routes/admin/Admin.svelte';
  import NotFound from './routes/NotFound.svelte';

  defineRoute('/');
  defineRoute('/about');
  defineRoute('/organization');
  defineRoute('/portfolio');
  defineRoute('/portfolio/:slug');
  defineRoute('/contact');
  defineRoute('/admin/login');
  defineRoute('/admin');
  defineRoute('*');

  let appContainer: HTMLElement;
  let pageContainer = $state<HTMLElement | null>(null);

  onMount(() => {
    router.init();
    document.documentElement.lang = langStore.current;

    settingsStore.init();

    const isAdmin = router.current.path.startsWith('/admin');
    if (isAdmin) {
      authStore.initialized = true;
      authStore.init().then(() => {
        const path = router.current.path;
        if (path !== '/admin/login' && !authStore.isAuthenticated) {
          router.go('/admin/login');
        }
      });
    }

    gsap.fromTo(
      appContainer,
      { opacity: 0, y: 10 },
      { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }
    );
  });

  function getPageFromPath() {
    const m = router.current;
    if (m.path === '/') return { component: Home, key: 'home' };
    if (m.path === '/about') return { component: AboutPage, key: 'about' };
    if (m.path === '/organization') return { component: OrganizationPage, key: 'organization' };
    if (m.path === '/portfolio') return { component: PortfolioListPage, key: 'portfolio' };
    if (m.path === '/portfolio/:slug') return { component: PortfolioDetailPage, key: 'portfolio-detail' };
    if (m.path === '/contact') return { component: ContactPage, key: 'contact' };
    if (m.path === '/admin/login') return { component: Login, key: 'admin-login' };
    if (m.path === '/admin') return { component: Admin, key: 'admin' };
    return { component: NotFound, key: 'not-found' };
  }

  const page = $derived(getPageFromPath());

  const isAdminProtected = $derived(
    router.current.path.startsWith('/admin') && router.current.path !== '/admin/login'
  );
  const showPage = $derived(
    !isAdminProtected || (authStore.initialized && authStore.isAuthenticated)
  );

  const isAdminRoute = $derived(router.current.path.startsWith('/admin'));
  const usePublicLayout = $derived(!isAdminRoute && showPage);

  $effect(() => {
    if (router.current.path.startsWith('/admin') && !authStore.initialized) {
      authStore.init().then(() => {
        if (
          router.current.path !== '/admin/login' &&
          !authStore.isAuthenticated
        ) {
          router.go('/admin/login');
        }
      });
    }
  });

  $effect(() => {
    if (!pageContainer) return;
    gsap.fromTo(
      pageContainer,
      { opacity: 0, y: 8 },
      { opacity: 1, y: 0, duration: 0.35, ease: 'power2.out' }
    );
  });
</script>

<svelte:head>
  <title>{t('site_name')}</title>
</svelte:head>

<div bind:this={appContainer}>
  {#if showPage}
    {#if usePublicLayout}
      <PublicLayout>
        {#key page.key}
          <div bind:this={pageContainer}>
            <page.component />
          </div>
        {/key}
      </PublicLayout>
    {:else}
      {#key page.key}
        <div bind:this={pageContainer}>
          <page.component />
        </div>
      {/key}
    {/if}
  {/if}
</div>

<Toast />