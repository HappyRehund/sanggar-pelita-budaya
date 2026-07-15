<script lang="ts">
  import { authStore } from '$lib/stores/auth.svelte';
  import { router } from '$lib/router.svelte';
  import { t } from '$lib/i18n/index.svelte';

  let username = $state('');
  let password = $state('');
  let error = $state('');
  let loading = $state(false);

  async function handleSubmit(e: Event) {
    e.preventDefault();
    error = '';
    loading = true;

    try {
      await authStore.login(username, password);
      router.go('/admin');
    } catch (err) {
      error = t('login_error');
    } finally {
      loading = false;
    }
  }
</script>

<div class="admin-login">
  <form class="login-card" onsubmit={handleSubmit}>
    <h2>{t('login_title')}</h2>
    <p class="subtitle">{t('login_subtitle')}</p>

    {#if error}
      <div class="login-error">{error}</div>
    {/if}

    <div class="form-field">
      <label for="username">{t('login_username')}</label>
      <input
        id="username"
        type="text"
        value={username}
        oninput={(e) => (username = (e.target as HTMLInputElement).value)}
        autocomplete="username"
        required
      />
    </div>

    <div class="form-field">
      <label for="password">{t('login_password')}</label>
      <input
        id="password"
        type="password"
        value={password}
        oninput={(e) => (password = (e.target as HTMLInputElement).value)}
        autocomplete="current-password"
        required
      />
    </div>

    <button type="submit" class="btn-primary" disabled={loading}>
      {loading ? t('loading') : t('login_submit')}
    </button>

    <a href="/" class="login-back">{t('login_back')}</a>
  </form>
</div>