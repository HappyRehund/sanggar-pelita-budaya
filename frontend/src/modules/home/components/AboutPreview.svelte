<script lang="ts">
  import { onMount, onDestroy } from 'svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { revealOnScroll } from '$lib/utils';
  import Button from '$lib/components/Button.svelte';
  import img1 from '$assets/images/about/about-1.jpg';
  import img2 from '$assets/images/about/about-2.jpg';
  import img3 from '$assets/images/about/about-3.jpg';

  type SlotName = 'front' | 'left' | 'right';
  type Card = { key: string; src: string; outline: string };

  const cards: Card[] = [
    { key: 'a', src: img1, outline: 'var(--color-red)' },
    { key: 'b', src: img2, outline: 'var(--color-gold-dark)' },
    { key: 'c', src: img3, outline: 'var(--color-ink)' },
  ];

  let order = $state<string[]>(cards.map((c) => c.key));

  const ROTATE_MS = 2500;
  const FRONT_SHADOW = '0 26px 46px -14px rgba(20,20,15,0.30), 0 10px 18px -10px rgba(20,20,15,0.18)';
  const SIDE_SHADOW = '0 14px 26px -12px rgba(20,20,15,0.20)';

  const slotStyles: Record<SlotName, { transform: string; z: number; scrim: number; shadow: string }> = {
    front: { transform: 'translate(-50%, 0) rotate(0deg) scale(1)',                          z: 3, scrim: 0,    shadow: FRONT_SHADOW },
    left:  { transform: 'translate(calc(-50% - 130px), 44px) rotate(-9deg) scale(0.92)',   z: 2, scrim: 0.18, shadow: SIDE_SHADOW },
    right: { transform: 'translate(calc(-50% + 130px), 44px) rotate(9deg) scale(0.92)',     z: 1, scrim: 0.18, shadow: SIDE_SHADOW },
  };

  function slotFor(key: string): SlotName {
    if (order[0] === key) return 'front';
    if (order[1] === key) return 'left';
    return 'right';
  }

  let timer: ReturnType<typeof setInterval> | null = null;
  let reduceMotion = false;

  function advance() {
    order = [order[2], order[0], order[1]];
  }

  function goTo(key: string) {
    while (order[0] !== key) {
      order = [order[2], order[0], order[1]];
    }
  }

  function startTimer() {
    if (reduceMotion) return;
    if (timer) clearInterval(timer);
    timer = setInterval(advance, ROTATE_MS);
  }

  function stopTimer() {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  function resetTimer() {
    stopTimer();
    startTimer();
  }

  function onVis() {
    if (document.hidden) stopTimer();
    else startTimer();
  }

  let sectionEl = $state<HTMLElement | null>(null);

  onMount(() => {
    if (sectionEl) revealOnScroll(sectionEl, { y: 30, duration: 0.7 });
    reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    startTimer();
    document.addEventListener('visibilitychange', onVis);
  });

  onDestroy(() => {
    stopTimer();
    if (typeof document !== 'undefined') {
      document.removeEventListener('visibilitychange', onVis);
    }
  });
</script>

<section bind:this={sectionEl} class="section about-preview">
  <div class="container about-preview__inner">
    <div
      class="about-preview__image"
      role="group"
      aria-label="About gallery"
      onmouseenter={stopTimer}
      onmouseleave={startTimer}
    >
      <div class="stack">
        {#each cards as card (card.key)}
          {@const style = slotStyles[slotFor(card.key)]}
          <button
            type="button"
            class="stack__card"
            aria-label={`Show image ${card.key}`}
            aria-current={order[0] === card.key}
            onclick={() => {
              goTo(card.key);
              resetTimer();
            }}
            style:--tx={style.transform}
            style:--z={style.z}
            style:--scrim={style.scrim}
            style:--shadow={style.shadow}
          >
            <span class="stack__frame">
              <span class="stack__scrim" aria-hidden="true"></span>
              <img src={card.src} alt="" class="stack__img" loading="lazy" />
            </span>
          </button>
        {/each}
      </div>
    </div>
    <div class="about-preview__content">
      <span class="eyebrow">{t('about_eyebrow')}</span>
      <h2 class="about-preview__title">{t('about_title')}</h2>
      <p class="about-preview__subtitle">{t('about_subtitle')}</p>
      <p class="about-preview__desc text-pretty">{t('about_description')}</p>
      <Button variant="outline-gradient" size="md" href="/about" class="about-preview__cta-button">
        {t('about_read_more')}
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
    aspect-ratio: 4 / 5;
    padding-top: var(--sp-8);
  }

  .stack {
    position: relative;
    width: 360px;
    height: 542px;
    max-width: 100%;
    margin: 0 auto;
    transform: scale(1);
    transform-origin: center center;
  }

  .stack__card {
    position: absolute;
    top: 0;
    left: 50%;
    width: 360px;
    height: 528px;
    margin-left: -180px;
    border: 0;
    padding: 2px;
    /* border-radius: var(--radius-xl); */
    overflow: hidden;
    cursor: pointer;
    background:
      linear-gradient(135deg,
        var(--color-red) 0%,
        var(--color-gold-soft) 50%,
        var(--color-red) 100%) padding-box;
    background-size: 200% 200%;
    background-position: 0% 50%;
    transform: var(--tx);
    z-index: var(--z);
    box-shadow: var(--shadow);
    transition:
      transform 0.85s cubic-bezier(0.3, 1.15, 0.4, 1),
      box-shadow 0.85s ease;
    will-change: transform;
    animation: stack-gradient-pan 6s linear infinite;
  }

  .stack__frame {
    position: relative;
    display: block;
    width: 100%;
    height: 100%;
    /* border-radius: var(--radius-lg); */
    overflow: hidden;
    z-index: 1;
  }

  @keyframes stack-gradient-pan {
    0%   { background-position:   0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position:   0% 50%; }
  }

  .stack__card:focus-visible {
    outline: 2px solid var(--color-gold, #c9a227);
    outline-offset: 4px;
  }

  .stack__scrim {
    position: absolute;
    inset: 0;
    background: #ffffff;
    opacity: var(--scrim);
    transition: opacity 0.85s ease;
    pointer-events: none;
    z-index: 2;
  }

  .stack__img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
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

  .about-preview__subtitle {
    font-family: var(--font-script);
    font-size: var(--fs-h3);
    font-weight: var(--fw-medium);
    color: var(--color-gold-soft);
    line-height: var(--lh-tight);
    letter-spacing: var(--tracking-wide);
    margin: 0;
    text-shadow:
      0 1px 0 rgba(255, 250, 231, 0.6),
      0 2px 6px rgba(122, 51, 56, 0.18);
  }

  .about-preview__desc {
    font-size: var(--fs-body);
    color: var(--color-text-muted);
    line-height: var(--lh-relaxed);
  }

  .about-preview__content :global(.about-preview__cta-button) {
    align-self: flex-start;
    margin-top: var(--sp-2);
  }

  @media (prefers-reduced-motion: reduce) {
    .stack__card {
      animation: none;
      transition: transform 0.2s linear, box-shadow 0.2s linear;
    }
    .stack__scrim {
      transition: opacity 0.2s linear;
    }
  }

  @media (max-width: 768px) {
    .about-preview__inner {
      grid-template-columns: 1fr;
      gap: var(--sp-6);
    }
    .about-preview__image {
      aspect-ratio: auto;
      min-height: 480px;
    }
    .stack {
      transform: scale(0.82);
      margin-bottom: -70px;
    }
  }
</style>
