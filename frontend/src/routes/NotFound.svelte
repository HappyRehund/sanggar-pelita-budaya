<script lang="ts">
  import { t } from '$lib/i18n/index.svelte';
  import Button from '$lib/components/Button.svelte';
  import { Compass } from '@lucide/svelte';
</script>

<div class="not-found">
  <div class="not-found__icon">
    <Compass size={64} strokeWidth={1} />
  </div>
  <h1 class="not-found__title">404</h1>
  <h2 class="not-found__subtitle">{t('not_found_title')}</h2>
  <p class="not-found__message">{t('not_found_message')}</p>
  <Button variant="primary" size="lg" href="/">
    {t('not_found_back')}
  </Button>
</div>

<style>
  .not-found {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: var(--sp-16) var(--sp-5);
    gap: var(--sp-4);
    min-height: 70vh;
  }

  .not-found__icon {
    color: var(--color-gold);
    margin-bottom: var(--sp-4);
  }

  .not-found__title {
    font-family: var(--font-serif);
    font-size: clamp(4rem, 12vw, 8rem);
    font-weight: var(--fw-semibold);
    color: var(--color-accent);
    line-height: 1;
  }

  .not-found__subtitle {
    font-family: var(--font-serif);
    font-size: var(--fs-h3);
    font-weight: var(--fw-medium);
    color: var(--color-text);
  }

  .not-found__message {
    font-size: var(--fs-body);
    color: var(--color-text-muted);
    max-width: 28rem;
  }
</style>