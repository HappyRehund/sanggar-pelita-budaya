<script lang="ts">
  import { onMount } from 'svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { revealOnScroll } from '$lib/utils';
  import Button from '$lib/components/Button.svelte';
  import { MessageCircle } from '@lucide/svelte';
  import contactCtaBg from '$assets/images/contact-cta/contact-cta-bg.webp';

  let sectionEl = $state<HTMLElement | null>(null);

  onMount(() => {
    if (sectionEl) return revealOnScroll(sectionEl, { y: 30, duration: 0.7 });
  });

  const bgImage = contactCtaBg;
</script>

<section bind:this={sectionEl} class="contact-cta" style:background-image={`linear-gradient(rgba(26,22,18,0.75), rgba(26,22,18,0.75)), url(${bgImage})`}>
  <div class="container contact-cta__inner">
    <h2 class="contact-cta__title">{t('contact_title')}</h2>
    <p class="contact-cta__desc text-pretty">{t('contact_description')}</p>
    <div class="contact-cta__actions">
      <Button radius="md" variant="soft-ink" size="md" href="https://wa.me/62819864460" target="_blank" rel="noopener noreferrer">
        <MessageCircle size={18} />
        {t('contact_cta_button')}
      </Button>
    </div>
  </div>
</section>

<style>
  .contact-cta {
    background-size: cover;
    background-position: center 40%;
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
    font-family: var(--font-script);
    font-size: var(--fs-h1);
    font-weight: var(--fw-regular);
    color: var(--color-beige);
    max-width: 36rem;
  }

  .contact-cta__desc {
    font-size: var(--fs-body-md);
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