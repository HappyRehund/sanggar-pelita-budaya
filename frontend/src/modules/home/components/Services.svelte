<script lang="ts">
  import { onMount } from 'svelte';
  import { Tween, prefersReducedMotion } from 'svelte/motion';
  import { cubicOut } from 'svelte/easing';
  import { t } from '$lib/i18n/index.svelte';
  import { revealOnScroll } from '$lib/utils';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import seniTari from '$assets/images/service-home/seni-tari.webp';
  import kelasTari from '$assets/images/service-home/kelas-tari.webp';
  import kostum from '$assets/images/service-home/kostum.webp';
  import makeUp from '$assets/images/service-home/make-up.webp';
  import dekorasi from '$assets/images/service-home/dekorasi.webp';

  let sectionEl = $state<HTMLElement | null>(null);

  onMount(() => {
    if (sectionEl) return revealOnScroll(sectionEl, { y: 30, duration: 0.6 });
  });

  // Static service structure (non-reactive): images + i18n key suffixes.
  // The count and tween arrays derive from this so they don't read the
  // reactive `services` derived at the top level (avoids
  // state_referenced_locally warnings).
  const SERVICE_DEFS = [
    { img: seniTari, key: 'dance_performance' },
    { img: kelasTari, key: 'dance_training' },
    { img: kostum, key: 'costume_rental' },
    { img: makeUp, key: 'makeup' },
    { img: dekorasi, key: 'stage_decoration' },
  ] as const;

  const services = $derived(
    SERVICE_DEFS.map((s) => ({
      img: s.img,
      title: t(`service_${s.key}_title`),
      desc: t(`service_${s.key}_desc`),
    }))
  );
  const count = SERVICE_DEFS.length;

  let activeIndex = $state(0);

  const reduce = prefersReducedMotion.current;

  // Three-phase fully sequential transition (~520ms total, 0 with reduced motion):
  //   Phase 1 (0   – FADE_OUT  ms): old content fades opacity 1 → 0
  //   Phase 2 (FADE_OUT – FADE_OUT+WIDTH  ms): width tweens; data-active flips
  //           (writing-mode, font-size, max-height, min-height all swap while
  //            content is fully invisible, so no mid-state visibility)
  //   Phase 3 (FADE_IN_DELAY – FADE_IN_DELAY+FADE_IN ms): new content fades 0 → 1
  const FADE_OUT = reduce ? 0.001 : 140;
  const FADE_IN_DELAY = reduce ? 0 : 360;
  const FADE_IN = reduce ? 0.001 : 180;
  const WIDTH = reduce ? 0.001 : 200;
  const MODE_FLIP = reduce ? 0 : 140; // start of phase 2

  // --p drives flex-grow (width tween)
  const widthTween = SERVICE_DEFS.map(() =>
    new Tween(0, { duration: WIDTH, easing: cubicOut })
  );
  // --c drives opacity / image scale / brightness (content tween)
  const fadeTween = SERVICE_DEFS.map(() =>
    new Tween(0, { duration: FADE_IN, easing: cubicOut })
  );
  // modeTween drives data-active (writing-mode + layout flip).
  // Nearly-instant (0.001ms) so the flip is a hard cut, but delayed so it
  // only happens AFTER the content has fully faded out.
  const modeTween = SERVICE_DEFS.map(() =>
    new Tween(0, { duration: 0.001, easing: cubicOut })
  );

  $effect(() => {
    const newActive = activeIndex;
    for (let i = 0; i < count; i++) {
      const target = i === newActive ? 1 : 0;

      // Width: 200ms, delayed by FADE_OUT so it starts after fade-out finishes.
      // (Same delay for both directions — width always animates in phase 2.)
      widthTween[i].set(target, {
        duration: WIDTH,
        delay: reduce ? 0 : FADE_OUT,
        easing: cubicOut,
      });

      // Mode flip: hard cut at 140ms (start of phase 2).
      modeTween[i].set(target, {
        duration: 0.001,
        delay: MODE_FLIP,
      });

      // Fade: asymmetric — fast out (no delay), slow in (with delay).
      fadeTween[i].set(target, {
        duration: target === 1 ? FADE_IN : FADE_OUT,
        delay: target === 1 ? FADE_IN_DELAY : 0,
        easing: cubicOut,
      });
    }
  });
</script>

<section bind:this={sectionEl} class="section section--dark services">
  <div class="container">
    <SectionTitle
      eyebrow={t('services_eyebrow')}
      title={t('services_title')}
      description={t('services_description')}
      align="center"
      variant="light"
    />
  </div>

  <div class="services__wrap">
    {#each services as service, i (service.img)}
      {@const p = widthTween[i].current}
      {@const c = fadeTween[i].current}
      {@const m = modeTween[i].current}
      <button
        type="button"
        class="service-card"
        data-active={m === 1 ? 'true' : 'false'}
        style:--p={p}
        style:--c={c}
        onclick={() => (activeIndex = i)}
        aria-pressed={i === activeIndex}
        aria-label={i === activeIndex ? `Currently expanded: ${service.title}` : `Expand: ${service.title}`}
      >
        <div class="service-card__media">
          <img src={service.img} alt={service.title} loading="lazy" />
        </div>
        <div class="service-card__scrim"></div>
        <div class="service-card__body">
          <span class="service-card__index">{String(i + 1).padStart(2, '0')}</span>
          <h3 class="service-card__title">{service.title}</h3>
          <p class="service-card__desc text-pretty">{service.desc}</p>
        </div>
      </button>
    {/each}
  </div>
</section>

<style>
  .services__wrap {
    display: flex;
    flex-direction: row;
    gap: var(--sp-3);
    width: 100%;
    height: clamp(20rem, 34vw, 30rem);
    margin-top: var(--sp-10);
    padding-inline: var(--sp-5);
  }

  .service-card {
    --expanded: 3; /* flex-grow when fully active */
    appearance: none;
    -webkit-appearance: none;
    border: none;
    margin: 0;
    padding: 0;
    font: inherit;
    color: inherit;
    text-align: left;
    background: var(--color-ink);
    position: relative;
    flex-basis: 0;
    flex-shrink: 1;
    /* Width driven by --p (widthTween). Content fades via --c below. */
    flex-grow: calc(1 + var(--p) * var(--expanded));
    min-width: 0;
    border-radius: var(--radius-2xl);
    overflow: hidden;
    cursor: pointer;
    transition: box-shadow var(--duration-medium) var(--ease-smooth),
      transform var(--duration-medium) var(--ease-smooth);
  }

  .service-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
  }

  .service-card:focus-visible {
    outline: 2px solid var(--color-gold);
    outline-offset: 3px;
  }

  .service-card__media {
    position: absolute;
    inset: 0;
    overflow: hidden;
  }

  .service-card__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    /* Image "wakes up" with the content (driven by --c, not --p). */
    transform: scale(calc(1 + var(--c) * 0.05));
    filter: saturate(calc(0.85 + var(--c) * 0.15)) brightness(calc(0.7 + var(--c) * 0.3));
  }

  .service-card__scrim {
    position: absolute;
    inset: 0;
    background: linear-gradient(
      180deg,
      rgba(0, 0, 0, calc(0.15 * (1 - var(--c)))) 0%,
      rgba(10, 16, 13, calc(0.88 + var(--c) * 0.04)) 100%
    );
  }

  .service-card__body {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    padding: var(--sp-5);
    color: var(--color-ivory);
    z-index: 1;
  }

  /* Index chip — fades in with content */
  .service-card__index {
    display: inline-block;
    font-family: var(--font-sans);
    font-size: var(--fs-caption);
    letter-spacing: 0.12em;
    color: var(--color-gold);
    margin-bottom: var(--sp-2);
    opacity: var(--c);
  }

  .service-card__title {
    font-family: var(--font-serif);
    font-weight: var(--fw-semibold);
    line-height: var(--lh-snug);
    color: var(--color-ivory);
    margin: 0;
    text-shadow: 0 2px 12px rgba(0, 0, 0, 0.5);
    opacity: var(--c);
    /* Font-size + writing-mode + layout switch instantly on target flip
       (the title is invisible during the swap because opacity is 0). */
    font-size: var(--fs-h3);
  }

  /* Collapsed target → vertical title, centered. Always visible (subtle
     hierarchy vs the active card) so users can see what each panel is. */
  .service-card[data-active='false'] .service-card__title {
    writing-mode: vertical-rl;
    transform: translate(-50%, -50%) rotate(180deg);
    white-space: nowrap;
    overflow: visible;
    text-overflow: clip;
    position: absolute;
    top: 50%;
    left: 50%;
    max-width: none;
    font-size: var(--fs-body);
    opacity: 0.85;
    color: var(--color-beige);
    font-weight: var(--fw-regular);
    text-shadow: 0 1px 8px rgba(0, 0, 0, 0.45);
  }

  .service-card[data-active='false'] .service-card__body {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--sp-3);
  }

  .service-card[data-active='false'] .service-card__index {
    display: none;
  }

  .service-card__desc {
    font-size: var(--fs-body-sm);
    color: var(--color-beige);
    line-height: var(--lh-relaxed);
    margin-top: var(--sp-3);
    max-width: 38rem;
    /* Desc visibility: max-height + opacity driven by --c, font-size fixed */
    max-height: 14rem;
    opacity: var(--c);
    overflow: hidden;
  }

  /* ===== Tablet (≤1024px) ===== */
  @media (max-width: 1024px) {
    .services__wrap {
      height: clamp(18rem, 38vw, 24rem);
      gap: var(--sp-2);
    }
    .service-card__body {
      padding: var(--sp-4);
    }
    .service-card__title {
      font-size: clamp(1.3rem, 2.5vw, 1.75rem);
    }
    .service-card__desc {
      font-size: var(--fs-caption);
    }
  }

  /* ===== Mobile (<520px): vertical accordion ===== */
  @media (max-width: 520px) {
    .services__wrap {
      flex-direction: column;
      height: auto;
      min-height: 60vh;
      gap: var(--sp-2);
    }

    .service-card {
      --expanded: 7;
      flex-basis: 0;
      min-height: 4rem;
      transition: box-shadow var(--duration-medium) var(--ease-smooth);
    }

    .service-card[data-active='true'] {
      min-height: 16rem;
    }

    /* Mobile is a vertical accordion: collapsed cards are short horizontal
       bars, so titles read horizontally (not rotated). */
    .service-card[data-active='false'] .service-card__title {
      writing-mode: horizontal-tb;
      transform: none;
      position: static;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
    }

    .service-card[data-active='false'] .service-card__body {
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      top: auto;
      padding: var(--sp-3);
      display: block;
    }

    .service-card[data-active='true'] .service-card__body {
      padding: var(--sp-5);
    }
  }
</style>