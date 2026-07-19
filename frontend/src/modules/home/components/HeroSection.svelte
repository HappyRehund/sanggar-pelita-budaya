<script lang="ts">
  import { onMount } from 'svelte';
  import { gsap } from 'gsap';
  import { t } from '$lib/i18n/index.svelte';
  import { SITE_CONTENT } from '$lib/constants';
  import heroBg from '$assets/images/hero/hero-bg.webp';
  import Button from '$lib/components/Button.svelte';
  import { ArrowRight } from '@lucide/svelte';

  let heroEl: HTMLElement;
  let bgEl: HTMLElement;

  const bgImage = heroBg;

  onMount(() => {
    const ctx = gsap.context(() => {
      gsap.fromTo('.hero__eyebrow', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.6, delay: 0.2, ease: 'power2.out' });
      gsap.fromTo('.hero__title', { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.8, delay: 0.4, ease: 'power2.out' });
      gsap.fromTo('.hero__subtitle', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.6, delay: 0.6, ease: 'power2.out' });
      gsap.fromTo('.hero__desc', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.6, delay: 0.7, ease: 'power2.out' });
      gsap.fromTo('.hero__actions > *', { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.5, delay: 0.9, stagger: 0.1, ease: 'power2.out' });
    }, heroEl);

    const handleScroll = () => {
      if (!bgEl) return;
      const offset = window.scrollY * 0.4;
      bgEl.style.transform = `translateY(${offset}px)`;
    };
    window.addEventListener('scroll', handleScroll, { passive: true });

    return () => {
      ctx.revert();
      window.removeEventListener('scroll', handleScroll);
    };
  });
</script>

<section bind:this={heroEl} class="hero">
  <div bind:this={bgEl} class="hero__bg" style:background-image={`url(${bgImage})`}></div>
  <div class="hero__overlay"></div>
  <div class="hero__content container">
    <span class="hero__eyebrow eyebrow">{t('hero_eyebrow')}</span>
    <h1 class="hero__title">{t('hero_title')}</h1>
    <p class="hero__subtitle script">{t('hero_subtitle')}</p>
    <p class="hero__desc">{t('hero_description')}</p>
    <div class="hero__actions">
      <Button variant="primary" size="lg" href={SITE_CONTENT.heroPrimaryCtaUrl}>
        {t('hero_cta_primary')}
        <ArrowRight size={18} />
      </Button>
    </div>
  </div>
</section>

<style>
  .hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
  }

  .hero__bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    will-change: transform;
    z-index: 0;
  }

  .hero__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(26, 22, 18, 0.7) 0%, rgba(26, 22, 18, 0.4) 60%, rgba(26, 22, 18, 0.7) 100%);
    z-index: 1;
  }

  .hero__content {
    position: relative;
    z-index: 2;
    max-width: var(--container-max);
    padding-block: var(--sp-16);
  }

  .hero__eyebrow {
    color: var(--color-gold-soft);
    margin-bottom: var(--sp-4);
    opacity: 0;
  }

  .hero__eyebrow::before {
    background-color: var(--color-gold-soft);
  }

  .hero__title {
    font-size: var(--fs-display);
    font-weight: var(--fw-semibold);
    color: var(--color-ivory);
    line-height: var(--lh-tight);
    max-width: 32rem;
    margin-bottom: var(--sp-3);
    opacity: 0;
  }

  .hero__subtitle {
    font-size: var(--fs-h3);
    color: var(--color-gold-soft);
    margin-bottom: var(--sp-5);
    opacity: 0;
  }

  .hero__desc {
    font-size: var(--fs-body-lg);
    color: var(--color-beige);
    max-width: 36rem;
    line-height: var(--lh-relaxed);
    margin-bottom: var(--sp-8);
    opacity: 0;
  }

  .hero__actions {
    display: flex;
    gap: var(--sp-4);
    flex-wrap: wrap;
    opacity: 0;
  }

  @media (max-width: 768px) {
    .hero { min-height: 90vh; }
    .hero__title { font-size: clamp(2.5rem, 8vw, 4rem); }
    .hero__actions { flex-direction: column; align-items: stretch; }
  }

  @media (prefers-reduced-motion: reduce) {
    .hero__bg { transform: none !important; }
  }
</style>