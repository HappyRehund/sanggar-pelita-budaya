<script lang="ts">
  import { authStore } from '$lib/stores/auth.svelte';
  import { router } from '$lib/router.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { notifications } from '$lib/stores/notification.svelte';
  import Button from '$lib/components/Button.svelte';
  import Input from '$lib/components/Input.svelte';
  import { LogIn } from '@lucide/svelte';

  let username = $state('');
  let password = $state('');
  let error = $state('');
  let loading = $state(false);

  async function handleSubmit(e: Event): Promise<void> {
    e.preventDefault();
    error = '';
    loading = true;

    try {
      await authStore.login(username, password);
      notifications.success(t('admin_dashboard_welcome'));
      router.go('/admin');
    } catch {
      error = t('login_error');
    } finally {
      loading = false;
    }
  }
</script>

<div class="login-page">
  <div class="login-card">
    <div class="login-card__logo">
      <span class="login-card__logo-text">SPB</span>
    </div>
    <h1 class="login-card__title">{t('login_title')}</h1>
    <p class="login-card__subtitle">{t('login_subtitle')}</p>

    <form onsubmit={handleSubmit} class="login-form">
      {#if error}
        <div class="login-form__error">{error}</div>
      {/if}

      <Input
        label={t('login_username')}
        type="text"
        value={username}
        oninput={(e) => (username = (e.target as HTMLInputElement).value)}
        autocomplete="username"
        required
      />
      <Input
        label={t('login_password')}
        type="password"
        value={password}
        oninput={(e) => (password = (e.target as HTMLInputElement).value)}
        autocomplete="current-password"
        required
      />

      <Button variant="primary" size="md" full type="submit" disabled={loading}>
        {#if loading}
          {t('loading')}
        {:else}
          <LogIn size={16} />
          {t('login_submit')}
        {/if}
      </Button>
    </form>

    <a href="/" class="login-card__back">{t('login_back')}</a>
  </div>
</div>

<style>
  .login-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: var(--sp-5);
    background: var(--color-surface-alt);
  }

  .login-card {
    width: 100%;
    max-width: 24rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    padding: var(--sp-8) var(--sp-6);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-3);
  }

  .login-card__logo {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    border-radius: var(--radius-md);
    background: var(--color-accent);
    margin-bottom: var(--sp-2);
  }

  .login-card__logo-text {
    font-family: var(--font-serif);
    font-size: 1.5rem;
    font-weight: var(--fw-bold);
    color: var(--color-white);
  }

  .login-card__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h3);
    font-weight: var(--fw-semibold);
  }

  .login-card__subtitle {
    font-size: var(--fs-body-sm);
    color: var(--color-text-muted);
    margin-bottom: var(--sp-4);
  }

  .login-form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: var(--sp-4);
  }

  .login-form__error {
    background: rgba(158, 42, 43, 0.08);
    border: 1px solid var(--color-danger);
    color: var(--color-danger);
    padding: var(--sp-3) var(--sp-4);
    border-radius: var(--radius-md);
    font-size: var(--fs-body-sm);
  }

  .login-card__back {
    display: block;
    text-align: center;
    margin-top: var(--sp-4);
    font-size: var(--fs-body-sm);
    color: var(--color-text-muted);
  }

  .login-card__back:hover {
    color: var(--color-accent);
  }
</style>