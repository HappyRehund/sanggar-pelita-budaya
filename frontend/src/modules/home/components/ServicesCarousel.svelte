<script lang="ts">
  import { onMount } from 'svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { imageUrl, revealOnScroll } from '$lib/utils';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import { ChevronLeft, ChevronRight } from '@lucide/svelte';

  let sectionEl = $state<HTMLElement | null>(null);

  onMount(() => {
    if (sectionEl) return revealOnScroll(sectionEl, { y: 30, duration: 0.6 });
  });

  const services = [
    { img: 'dance-performance', title: t('service_dance_performance_title'), desc: t('service_dance_performance_desc') },
    { img: 'dance-training', title: t('service_dance_training_title'), desc: t('service_dance_training_desc') },
    { img: 'costume-rental', title: t('service_costume_rental_title'), desc: t('service_costume_rental_desc') },
    { img: 'traditional-makeup', title: t('service_makeup_title'), desc: t('service_makeup_desc') },
    { img: 'cultural-workshops', title: t('service_workshops_title'), desc: t('service_workshops_desc') },
    { img: 'stage-decoration', title: t('service_stage_decoration_title'), desc: t('service_stage_decoration_desc') },
  ];

  let scrollEl = $state<HTMLElement | null>(null);
  let current = $state(0);

  function scrollBy(dir: number): void {
    if (!scrollEl) return;
    const cardWidth = scrollEl.querySelector('.service-card')?.getBoundingClientRect().width ?? 400;
    const gap = 24;
    scrollEl.scrollBy({ left: dir * (cardWidth + gap), behavior: 'smooth' });
    current = Math.max(0, Math.min(current + dir, services.length - 1));
  }
</script>

<section bind:this={sectionEl} class="section section--dark services">
  <div class="container">
    <SectionTitle eyebrow={t('services_eyebrow')} title={t('services_title')} description={t('services_description')} align="center" variant="light" />
  </div>

  <div class="services__wrap">
    <button class="services__nav services__nav--prev" onclick={() => scrollBy(-1)} aria-label="Previous">
      <ChevronLeft size={24} />
    </button>
    <div class="services__scroll" bind:this={scrollEl}>
      {#each services as service (service.title)}
        <div class="service-card">
          <div class="service-card__image">
            <img src={imageUrl(service.img, 800, 500)} alt={service.title} loading="lazy" />
          </div>
          <h3 class="service-card__title">{service.title}</h3>
          <p class="service-card__desc text-pretty">{service.desc}</p>
        </div>
      {/each}
    </div>
    <button class="services__nav services__nav--next" onclick={() => scrollBy(1)} aria-label="Next">
      <ChevronRight size={24} />
    </button>
  </div>
</section>

<style>
  .services__wrap {
    position: relative;
    margin-top: var(--sp-10);
    max-width: var(--container-max);
    margin-inline: auto;
    padding-inline: var(--sp-5);
  }

  .services__scroll {
    display: flex;
    gap: var(--sp-6);
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scrollbar-width: none;
    padding-bottom: var(--sp-4);
  }

  .services__scroll::-webkit-scrollbar { display: none; }

  .service-card {
    flex: 0 0 400px;
    scroll-snap-align: center;
  }

  .service-card__image {
    border-radius: var(--radius-2xl);
    overflow: hidden;
    aspect-ratio: 16 / 10;
    margin-bottom: var(--sp-4);
  }

  .service-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-medium) var(--ease-out);
  }

  .service-card:hover .service-card__image img {
    transform: scale(1.05);
  }

  .service-card__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h3);
    font-weight: var(--fw-semibold);
    color: var(--color-ivory);
    margin-bottom: var(--sp-2);
  }

  .service-card__desc {
    font-size: var(--fs-body);
    color: var(--color-beige);
    line-height: var(--lh-relaxed);
  }

  .services__nav {
    position: absolute;
    top: 35%;
    transform: translateY(-50%);
    z-index: var(--z-base);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-full);
    background: rgba(255, 255, 255, 0.15);
    color: var(--color-ivory);
    transition: background-color var(--duration-fast) var(--ease-smooth);
  }

  .services__nav:hover {
    background: rgba(255, 255, 255, 0.3);
  }

  .services__nav--prev { left: var(--sp-2); }
  .services__nav--next { right: var(--sp-2); }

  @media (max-width: 768px) {
    .service-card { flex: 0 0 85%; }
    .services__nav { display: none; }
  }
</style>