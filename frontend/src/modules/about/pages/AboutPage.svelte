<script lang="ts">
  import { t } from '$lib/i18n/index.svelte';
  import { imageUrl } from '$lib/utils';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import { Eye, Target, Heart, Users, Award, Sparkles } from '@lucide/svelte';

  const coreValues = [
    { icon: Heart, key: 'about_core_value_heritage', label: 'Heritage', desc: 'Preserving the wisdom of generations past.' },
    { icon: Users, key: 'about_core_value_community', label: 'Community', desc: 'Building bonds through shared cultural experiences.' },
    { icon: Award, key: 'about_core_value_excellence', label: 'Excellence', desc: 'Striving for artistic quality in every performance.' },
    { icon: Sparkles, key: 'about_core_value_inspiration', label: 'Inspiration', desc: 'Igniting passion for tradition in future generations.' },
  ];
</script>

<svelte:head>
  <title>{t('about_page_hero_title')} — {t('site_name')}</title>
  <meta name="description" content={t('about_page_hero_desc')} />
</svelte:head>

<section class="about-hero">
  <div class="container about-hero__inner">
    <span class="eyebrow eyebrow--centered">{t('about_eyebrow')}</span>
    <h1 class="about-hero__title">{t('about_page_hero_title')}</h1>
    <p class="about-hero__desc text-pretty">{t('about_page_hero_desc')}</p>
  </div>
</section>

<section class="section">
  <div class="container container--reading">
    <h2 class="about-section__title">{t('about_history_title')}</h2>
    <div class="about-editorial">
      <p class="lead">{t('about_description')}</p>
      <p>Sanggar Pelita Budaya was founded with a vision to preserve and celebrate the rich tapestry of Indonesian traditional art. What began as a small gathering of passionate artists has grown into a recognized cultural organization, dedicated to sharing the beauty of heritage through performance, education, and community engagement.</p>
    </div>
    <div class="about-image">
      <img src={imageUrl('about-history', 1000, 500)} alt="Cultural heritage" loading="lazy" />
    </div>
  </div>
</section>

<section class="section section--alt">
  <div class="container container--reading">
    <h2 class="about-section__title">{t('about_background_title')}</h2>
    <div class="about-editorial">
      <p>Rooted in the traditions of Indonesian dance, music, and visual arts, the organization serves as a bridge between generations. Our programs encompass performance training, cultural workshops, costume preservation, and community outreach — all aimed at keeping traditional artistry alive and relevant in the modern era.</p>
      <p>We believe that cultural heritage is not a relic of the past, but a living tradition that evolves while honoring its roots. Every performance, every workshop, and every collaboration is an opportunity to celebrate the depth and beauty of Indonesian culture.</p>
    </div>
    <div class="about-image">
      <img src={imageUrl('about-background', 1000, 500)} alt="Cultural background" loading="lazy" />
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <SectionTitle eyebrow={t('vision_mission_eyebrow')} title={t('vision_mission_title')} align="center" />
    <div class="about-vm-cards">
      <div class="about-vm-card">
        <div class="about-vm-card__icon"><Eye size={28} strokeWidth={1.5} /></div>
        <h3>{t('vision_title')}</h3>
        <p class="text-pretty">{t('vision_description')}</p>
      </div>
      <div class="about-vm-card">
        <div class="about-vm-card__icon"><Target size={28} strokeWidth={1.5} /></div>
        <h3>{t('mission_title')}</h3>
        <p class="text-pretty">{t('mission_description')}</p>
      </div>
    </div>
  </div>
</section>

<section class="section section--alt">
  <div class="container">
    <SectionTitle eyebrow="Our Principles" title={t('about_core_values_title')} align="center" />
    <div class="about-values">
      {#each coreValues as value (value.key)}
        <div class="about-value-card">
          <div class="about-value-card__icon">
            <value.icon size={24} strokeWidth={1.5} />
          </div>
          <h3 class="about-value-card__title">{value.label}</h3>
          <p class="about-value-card__desc">{value.desc}</p>
        </div>
      {/each}
    </div>
  </div>
</section>

<style>
  .about-hero {
    padding: var(--sp-16) 0 var(--sp-12);
    text-align: center;
    background: var(--color-surface-alt);
  }

  .about-hero__inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-4);
  }

  .about-hero__title {
    font-size: var(--fs-h1);
    font-weight: var(--fw-semibold);
    max-width: 36rem;
  }

  .about-hero__desc {
    font-size: var(--fs-body-lg);
    color: var(--color-text-muted);
    max-width: 32rem;
  }

  .about-section__title {
    font-size: var(--fs-h2);
    font-weight: var(--fw-semibold);
    margin-bottom: var(--sp-6);
  }

  .about-editorial {
    display: flex;
    flex-direction: column;
    gap: var(--sp-4);
  }

  .about-editorial p {
    font-size: var(--fs-body-lg);
    line-height: var(--lh-relaxed);
    color: var(--color-text);
  }

  .about-image {
    margin-top: var(--sp-6);
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    aspect-ratio: 2 / 1;
  }

  .about-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .about-vm-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--sp-6);
    margin-top: var(--sp-8);
  }

  .about-vm-card {
    display: flex;
    flex-direction: column;
    gap: var(--sp-3);
    padding: var(--sp-6);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    text-align: center;
  }

  .about-vm-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    border-radius: var(--radius-full);
    background: rgba(201, 162, 39, 0.12);
    color: var(--color-gold-dark);
    margin: 0 auto;
  }

  .about-vm-card h3 {
    font-family: var(--font-serif);
    font-size: var(--fs-h3);
  }

  .about-vm-card p {
    color: var(--color-text-muted);
    line-height: var(--lh-relaxed);
  }

  .about-values {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--sp-5);
    margin-top: var(--sp-8);
  }

  .about-value-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--sp-3);
    padding: var(--sp-5);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
  }

  .about-value-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-full);
    background: rgba(158, 42, 43, 0.08);
    color: var(--color-accent);
  }

  .about-value-card__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
  }

  .about-value-card__desc {
    font-size: var(--fs-body-sm);
    color: var(--color-text-muted);
    line-height: var(--lh-relaxed);
  }

  @media (max-width: 768px) {
    .about-vm-cards { grid-template-columns: 1fr; }
    .about-values { grid-template-columns: repeat(2, 1fr); }
  }

  @media (max-width: 480px) {
    .about-values { grid-template-columns: 1fr; }
  }
</style>