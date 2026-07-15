<script lang="ts">
  import { langStore } from '$lib/stores/lang.svelte';
  import { t } from '$lib/i18n/index.svelte';
</script>

<div class="app-shell">
  <header class="app-header">
    <h1>{t('site_name')}</h1>
    <nav>
      <a href="/admin">{t('nav_admin')}</a>
      <button class="lang-toggle" onclick={() => langStore.toggle()}>
        {t('lang_toggle')}
      </button>
    </nav>
  </header>

  <main class="app-main">
    <h2>{t('hello_title')}</h2>
    <p>{t('hello_subtitle')}</p>
    <p style="margin-top: 0.5rem; font-size: 0.875rem;">{t('hello_description')}</p>
  </main>

  <footer class="app-footer">
    {t('site_name')} · {t('site_tagline')}
  </footer>
</div>