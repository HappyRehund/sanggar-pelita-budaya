<script lang="ts">
  import { authStore } from '$lib/stores/auth.svelte';
  import { router } from '$lib/router.svelte';
  import { t } from '$lib/i18n/index.svelte';

  async function handleLogout() {
    await authStore.logout();
    router.go('/admin/login');
  }
</script>

<div class="admin-page">
  <h2>{t('admin_title')}</h2>
  <p>{t('admin_text')}</p>
  <div class="admin-actions">
    <a href="/">{t('admin_back')}</a>
    <a href="/admin/login" onclick={(e) => { e.preventDefault(); handleLogout(); }}>
      {t('admin_logout')}
    </a>
  </div>
</div>