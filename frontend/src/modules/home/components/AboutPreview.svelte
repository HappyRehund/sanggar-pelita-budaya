<script lang="ts">
  import { t } from '$lib/i18n/index.svelte';
  import { imageUrl } from '$lib/utils';
  import Button from '$lib/components/Button.svelte';
  import { ArrowRight } from '@lucide/svelte';
</script>

<section class="section about-preview">
  <div class="container about-preview__inner">
    <div class="about-preview__image">
      <img src={imageUrl('about-culture', 800, 1000)} alt="Cultural performance" class="about-preview__img" loading="lazy" />
    </div>
    <div class="about-preview__content">
      <span class="eyebrow">{t('about_eyebrow')}</span>
      <h2 class="about-preview__title">{t('about_title')}</h2>
      <div class="divider-gold"></div>
      <p class="about-preview__desc text-pretty">{t('about_description')}</p>
      <Button variant="ghost" size="md" href="/about" class="about-preview__cta-button">
        {t('about_read_more')}
        <ArrowRight size={16} />
      </Button>
    </div>
  </div>
</section>

<style>
  .about-preview__inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--sp-10);
    align-items: center;
  }

  .about-preview__image {
    position: relative;
    border-radius: var(--radius-3xl);
    overflow: hidden;
    aspect-ratio: 4 / 5;
    box-shadow: var(--shadow-lg);
  }

  .about-preview__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .about-preview__content {
    display: flex;
    flex-direction: column;
    gap: var(--sp-4);
  }

  .about-preview__title {
    font-size: var(--fs-h1);
    font-weight: var(--fw-semibold);
    line-height: var(--lh-tight);
  }

  .about-preview__desc {
    font-size: var(--fs-body-lg);
    color: var(--color-text-muted);
    line-height: var(--lh-relaxed);
  }

  .about-preview__cta-button {
    align-self: flex-start;
    margin-top: var(--sp-2);
  }

  @media (max-width: 768px) {
    .about-preview__inner {
      grid-template-columns: 1fr;
      gap: var(--sp-6);
    }
  }
</style>