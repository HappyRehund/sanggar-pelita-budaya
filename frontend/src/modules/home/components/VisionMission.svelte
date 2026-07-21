<script lang="ts">
  import { onMount } from 'svelte';
  import { gsap } from 'gsap';
  import { ChevronDown, Eye, Target } from '@lucide/svelte';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import Button from '$lib/components/Button.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { revealOnScroll } from '$lib/utils';

  import visionImg from '$assets/images/vision-mission/vision.webp';
  import missionImg from '$assets/images/vision-mission/mission.webp';

  let sectionEl = $state<HTMLElement | null>(null);
  let desktopEl = $state<HTMLElement | null>(null);

  onMount(() => {
    if (!sectionEl) return;
    const cleanup = revealOnScroll(sectionEl, { y: 24, duration: 0.6 });

    let tileObserver: IntersectionObserver | null = null;
    if (desktopEl) {
      const tiles = desktopEl.querySelectorAll<HTMLElement>('.vm-tile');
      gsap.set(tiles, { opacity: 0, y: 28 });

      tileObserver = new IntersectionObserver(
        (entries) => {
          for (const entry of entries) {
            if (entry.isIntersecting) {
              gsap.to(tiles, {
                opacity: 1,
                y: 0,
                duration: 0.7,
                stagger: 0.12,
                ease: 'power3.out',
              });
              tileObserver?.disconnect();
              break;
            }
          }
        },
        { threshold: 0.18 }
      );
      tileObserver.observe(desktopEl);
    }

    return () => {
      cleanup();
      tileObserver?.disconnect();
    };
  });
</script>

<section
  bind:this={sectionEl}
  class="vision-mission"
  aria-label={t('vision_mission_title')}
>
  <div bind:this={desktopEl} class="vm-desktop">
    <div class="vm-tile vm-tile--image" role="img" aria-label={t('vision_title')}>
      <img src={visionImg} alt="" loading="lazy" />
      <span class="vm-tile__scrim" aria-hidden="true"></span>
    </div>

    <div class="vm-tile vm-tile--text vm-tile--vision">
      <div class="vm-tile__inner">
        <h2 class="vm-tile__title__vision">{t('vision_title')}</h2>
        <span class="vm-tile__rule__vision" aria-hidden="true"></span>
        <p class="vm-tile__desc__vision text-pretty">{t('vision_description')}</p>
        <div class="vm-tile__cta-wrap">
          <Button variant="outline-gold" size="md" href="/about">
            {t('learn_more')}
          </Button>
        </div>
      </div>
    </div>

    <div class="vm-tile vm-tile--text vm-tile--mission">
      <div class="vm-tile__inner">
        <h2 class="vm-tile__title__mission">{t('mission_title')}</h2>
        <span class="vm-tile__rule__mission" aria-hidden="true"></span>
        <p class="vm-tile__desc__mission text-pretty">{t('mission_description')}</p>
        <div class="vm-tile__cta-wrap">
          <Button variant="outline-red" size="md" href="/about">
            {t('read_more')}
          </Button>
        </div>
      </div>
    </div>

    <div class="vm-tile vm-tile--image" role="img" aria-label={t('mission_title')}>
      <img src={missionImg} alt="" loading="lazy" />
      <span class="vm-tile__scrim" aria-hidden="true"></span>
    </div>
  </div>

  <div class="vm-mobile container">
    <SectionTitle
      eyebrow={t('vision_mission_eyebrow')}
      title={t('vision_mission_title')}
      align="center"
    />

    <div class="vm-accordion">
      <details class="vm-accordion__item vm-accordion__item--vision" name="vm-accordion">
        <summary class="vm-accordion__summary">
          <span class="vm-accordion__summary-left">
            <Eye size={20} strokeWidth={1.5} />
            <span>{t('vision_title')}</span>
          </span>
          <span class="vm-accordion__icon" aria-hidden="true">
            <ChevronDown size={20} />
          </span>
        </summary>
        <div class="vm-accordion__body">
          <p>{t('vision_description')}</p>
        </div>
      </details>

      <details class="vm-accordion__item vm-accordion__item--mission" name="vm-accordion">
        <summary class="vm-accordion__summary">
          <span class="vm-accordion__summary-left">
            <Target size={20} strokeWidth={1.5} />
            <span>{t('mission_title')}</span>
          </span>
          <span class="vm-accordion__icon" aria-hidden="true">
            <ChevronDown size={20} />
          </span>
        </summary>
        <div class="vm-accordion__body">
          <p>{t('mission_description')}</p>
        </div>
      </details>
    </div>
  </div>
</section>

<style>
  .vision-mission {
    position: relative;
    background: var(--color-surface-alt);
  }

  /* ============================================================
     DESKTOP — 100vh split-screen, 50/50
     ============================================================ */
  .vm-desktop {
    display: none;
  }

  .vm-tile {
    position: relative;
  }

  /* ---- Image tiles ---- */
  .vm-tile--image {
    overflow: hidden;
    isolation: isolate;
  }

  .vm-tile--image img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 1.2s var(--ease-out);
    will-change: transform;
  }

  .vm-tile--image:hover img {
    transform: scale(1.06);
  }

  .vm-tile__scrim {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 35%, rgba(36, 0, 3, 0.55) 100%);
    pointer-events: none;
    z-index: 1;
  }

  /* ---- Text tiles ---- */
  .vm-tile--text {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--sp-10) var(--sp-6);
  }

  .vm-tile--vision {
    background: var(--color-ink-soft);
    color: var(--color-ivory);
  }

  .vm-tile--mission {
    background: var(--color-gold-soft);
    color: var(--color-ink);
  }

  .vm-tile__inner {
    position: relative;
    z-index: 2;
    max-width: 32rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--sp-3);
  }

  .vm-tile__title__mission {
    font-family: var(--font-script);
    font-size: var(--fs-h1);
    font-weight: var(--fw-regular);
    line-height: var(--lh-tight);
    letter-spacing: var(--tracking-normal);
    color: var(--color-ink-soft);
  }

  .vm-tile__title__vision {
    font-family: var(--font-script);
    font-size: var(--fs-h1);
    font-weight: var(--fw-regular);
    line-height: var(--lh-tight);
    letter-spacing: var(--tracking-normal);
    color: var(--color-gold-soft);
  }

  .vm-tile__rule__vision {
    display: block;
    width: var(--sp-12);
    height: 1px;
    background-color: var(--color-gold-soft);
    opacity: 0.4;
    margin-block: var(--sp-1);
  }

  .vm-tile__rule__mission {
    display: block;
    width: var(--sp-12);
    height: 1px;
    background-color: var(--color-ink-soft);
    opacity: 0.4;
    margin-block: var(--sp-1);
  }

  .vm-tile__desc__vision {
    font-size: var(--fs-body);
    color: var(--color-ink-soft);
    line-height: var(--lh-relaxed);
    opacity: 0.92;
  }

  .vm-tile__desc__vision {
    font-size: var(--fs-body);
    color: var(--color-gold-soft);
    line-height: var(--lh-relaxed);
    opacity: 0.92;
  }

  .vm-tile__cta-wrap {
    margin-top: var(--sp-4);
  }

  /* ============================================================
     MOBILE — accordion
     ============================================================ */
  .vm-mobile {
    display: block;
    padding-block: var(--section-padding-y);
  }

  .vm-accordion {
    display: flex;
    flex-direction: column;
    gap: var(--sp-3);
    margin-top: var(--sp-8);
    max-width: 36rem;
    margin-inline: auto;
  }

  .vm-accordion__item {
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--color-border);
    transition:
      box-shadow var(--duration-medium) var(--ease-out),
      transform var(--duration-medium) var(--ease-out);
  }

  .vm-accordion__item[open] {
    box-shadow: var(--shadow-md);
  }

  .vm-accordion__summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--sp-3);
    padding: var(--sp-4) var(--sp-5);
    cursor: pointer;
    list-style: none;
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
    transition: filter var(--duration-fast) var(--ease-smooth);
  }

  .vm-accordion__summary::-webkit-details-marker {
    display: none;
  }

  .vm-accordion__summary:hover {
    filter: brightness(1.05);
  }

  .vm-accordion__summary-left {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-3);
  }

  .vm-accordion__item--vision .vm-accordion__summary {
    background: var(--color-ink-soft);
    color: var(--color-ivory);
  }

  .vm-accordion__item--mission .vm-accordion__summary {
    background: var(--color-cream);
    color: var(--color-ink);
  }

  .vm-accordion__icon {
    flex-shrink: 0;
    transition: transform var(--duration-medium) var(--ease-out);
  }

  .vm-accordion__item[open] .vm-accordion__icon {
    transform: rotate(180deg);
  }

  .vm-accordion__body {
    background: var(--color-surface);
    color: var(--color-text-muted);
    font-size: var(--fs-body);
    line-height: var(--lh-relaxed);
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    transition:
      max-height var(--duration-medium) var(--ease-out),
      opacity var(--duration-short) var(--ease-out),
      padding var(--duration-medium) var(--ease-out);
    padding: 0 var(--sp-5);
  }

  .vm-accordion__item[open] .vm-accordion__body {
    max-height: 32rem;
    opacity: 1;
    padding: var(--sp-5);
  }

  /* ============================================================
     Breakpoints
     ============================================================ */
  @media (min-width: 901px) {
    .vision-mission {
      min-height: 100vh;
    }

    .vm-desktop {
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-template-rows: 50vh 50vh;
      width: 100%;
    }

    .vm-mobile {
      display: none;
    }
  }

  @media (max-width: 900px) {
    .vm-desktop {
      display: none;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .vm-tile--image img {
      transition: none;
    }
    .vm-tile--image:hover img {
      transform: none;
    }
    .vm-accordion__item,
    .vm-accordion__icon,
    .vm-accordion__body {
      transition: none;
    }
  }
</style>
