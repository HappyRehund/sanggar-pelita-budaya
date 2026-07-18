<script lang="ts">
  import { onMount } from 'svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { imageUrl, revealOnScroll } from '$lib/utils';
  import { SITE_CONTENT } from '$lib/constants';
  import Button from '$lib/components/Button.svelte';
  import { MessageCircle, MapPin } from '@lucide/svelte';

  let sectionEl = $state<HTMLElement | null>(null);

  onMount(() => {
    if (sectionEl) return revealOnScroll(sectionEl, { y: 30, duration: 0.7 });
  });

  const bgImage = $derived(imageUrl('contact-cta', 1920, 800));
  const mapsUrl = $derived(SITE_CONTENT.mapsUrl);
</script>

<section bind:this={sectionEl} class="contact-cta" style:background-image={`linear-gradient(rgba(26,22,18,0.75), rgba(26,22,18,0.75)), url(${bgImage})`}>
  <div class="container contact-cta__inner">
    <h2 class="contact-cta__title">{t('contact_title')}</h2>
    <p class="contact-cta__desc text-pretty">{t('contact_description')}</p>
    <div class="contact-cta__actions">
      <Button variant="primary" size="lg" href="https://wa.me/62819864460" target="_blank" rel="noopener noreferrer">
        <MessageCircle size={18} />
        {t('contact_cta_button')}
      </Button>
      {#if mapsUrl}
        <Button variant="secondary" size="lg" href={mapsUrl}>
          <MapPin size={18} />
          {t('contact_cta_maps')}
        </Button>
      {/if}
    </div>
  </div>
</section>

<style>
  .contact-cta {
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    padding: var(--sp-16) 0;
    text-align: center;
  }

  .contact-cta__inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-4);
  }

  .contact-cta__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h1);
    font-weight: var(--fw-semibold);
    color: var(--color-ivory);
    max-width: 36rem;
  }

  .contact-cta__desc {
    font-size: var(--fs-body-lg);
    color: var(--color-beige);
    max-width: 32rem;
    line-height: var(--lh-relaxed);
  }

  .contact-cta__actions {
    display: flex;
    gap: var(--sp-4);
    flex-wrap: wrap;
    justify-content: center;
    margin-top: var(--sp-4);
  }

  @media (max-width: 768px) {
    .contact-cta { background-attachment: scroll; }
    .contact-cta__actions { flex-direction: column; align-items: stretch; }
  }
</style>