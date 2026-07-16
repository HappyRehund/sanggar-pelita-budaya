<script lang="ts">
  import HomePage from '$modules/home/pages/HomePage.svelte';
  import { t } from '$lib/i18n/index.svelte';
</script>

<svelte:head>
  <title>{t('site_name')} — {t('site_tagline')}</title>
  <meta name="description" content={t('site_description')} />
  <link rel="canonical" href="/" />
  <meta property="og:title" content={t('site_name')} />
  <meta property="og:description" content={t('site_description')} />
  <meta property="og:type" content="website" />
  <meta name="twitter:card" content="summary" />
  <meta name="twitter:title" content={t('site_name')} />
</svelte:head>

<HomePage />