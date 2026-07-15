<script lang="ts">
  import { onMount } from 'svelte';
  import { settingsStore } from '$lib/stores/settings.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { MapPin, Phone, Mail, Globe, ExternalLink } from '@lucide/svelte';
  import Button from '$lib/components/Button.svelte';

  onMount(() => {
    settingsStore.init();
  });

  const footer = $derived(settingsStore.footer);

  const contactCards = $derived([
    { icon: MapPin, label: t('contact_address'), value: footer?.address ?? '', action: null as string | null },
    { icon: Phone, label: t('contact_phone'), value: footer?.phone ?? '', action: footer?.phone ? `tel:${footer.phone}` : null },
    { icon: Mail, label: t('contact_email'), value: footer?.email ?? '', action: footer?.email ? `mailto:${footer.email}` : null },
    { icon: Globe, label: t('contact_website'), value: footer?.website ?? '', action: footer?.website ?? null },
  ].filter((c) => c.value));

  const socialLinks = $derived([
    { url: footer?.facebook, label: 'Facebook' },
    { url: footer?.instagram, label: 'Instagram' },
    { url: footer?.youtube, label: 'YouTube' },
  ].filter((s) => s.url));

  function getMapsEmbed(): string {
    if (!footer?.maps_url) return '';
    const url = footer.maps_url;
    const match = url.match(/(?:maps\/place\/([^/]+))/) || url.match(/[?&]q=([^&]+)/);
    if (match) {
      return `https://maps.google.com/maps?q=${encodeURIComponent(decodeURIComponent(match[1]))}&output=embed`;
    }
    return '';
  }
</script>

<svelte:head>
  <title>{t('contact_page_title')} — {t('site_name')}</title>
  <meta name="description" content={t('contact_page_desc')} />
</svelte:head>

<section class="contact-hero">
  <div class="container contact-hero__inner">
    <span class="eyebrow eyebrow--centered">{t('contact_eyebrow')}</span>
    <h1>{t('contact_page_title')}</h1>
    <p class="text-pretty">{t('contact_page_desc')}</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="contact-grid">
      <div class="contact-info">
        {#each contactCards as card (card.label)}
          <div class="contact-card">
            <div class="contact-card__icon">
              <card.icon size={20} />
            </div>
            <div class="contact-card__content">
              <p class="contact-card__label">{card.label}</p>
              {#if card.action}
                <a href={card.action} class="contact-card__value">{card.value}</a>
              {:else}
                <p class="contact-card__value">{card.value}</p>
              {/if}
            </div>
          </div>
        {/each}

        {#if socialLinks.length > 0}
          <div class="contact-social">
            <p class="contact-social__label">{t('contact_social')}</p>
            <div class="contact-social__links">
              {#each socialLinks as social (social.label)}
                <a
                  href={social.url!}
                  target="_blank"
                  rel="noopener noreferrer"
                  class="contact-social__link"
                >
                  <ExternalLink size={16} />
                  {social.label}
                </a>
              {/each}
            </div>
          </div>
        {/if}
      </div>

      <div class="contact-map">
        {#if getMapsEmbed()}
          <iframe
            src={getMapsEmbed()}
            title="Google Maps"
            class="contact-map__iframe"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          ></iframe>
        {:else}
          <div class="contact-map__placeholder">
            <MapPin size={48} strokeWidth={1} />
            <p>{t('contact_address')}</p>
          </div>
        {/if}
      </div>
    </div>

    <div class="contact-cta">
      <Button variant="primary" size="lg" href="/">
        {t('not_found_back')}
      </Button>
    </div>
  </div>
</section>

<style>
  .contact-hero {
    padding: var(--sp-12) 0 var(--sp-8);
    text-align: center;
    background: var(--color-surface-alt);
  }

  .contact-hero__inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-3);
  }

  .contact-hero h1 {
    font-size: var(--fs-h1);
    font-weight: var(--fw-semibold);
  }

  .contact-hero p {
    font-size: var(--fs-body-lg);
    color: var(--color-text-muted);
    max-width: 32rem;
  }

  .contact-grid {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: var(--sp-8);
    align-items: start;
  }

  .contact-info {
    display: flex;
    flex-direction: column;
    gap: var(--sp-5);
  }

  .contact-card {
    display: flex;
    gap: var(--sp-4);
    padding: var(--sp-5);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
  }

  .contact-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-full);
    background: rgba(158, 42, 43, 0.08);
    color: var(--color-accent);
    flex-shrink: 0;
  }

  .contact-card__label {
    font-size: var(--fs-caption);
    font-weight: var(--fw-semibold);
    text-transform: uppercase;
    letter-spacing: var(--tracking-wide);
    color: var(--color-text-muted);
    margin-bottom: var(--sp-1);
  }

  .contact-card__value {
    font-size: var(--fs-body);
    color: var(--color-text);
  }

  .contact-card__value:hover {
    color: var(--color-accent);
  }

  .contact-social__label {
    font-size: var(--fs-caption);
    font-weight: var(--fw-semibold);
    text-transform: uppercase;
    letter-spacing: var(--tracking-wide);
    color: var(--color-text-muted);
    margin-bottom: var(--sp-3);
  }

  .contact-social__links {
    display: flex;
    gap: var(--sp-3);
    flex-wrap: wrap;
  }

  .contact-social__link {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    padding: var(--sp-2) var(--sp-4);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    font-size: var(--fs-body-sm);
    color: var(--color-text);
    transition: border-color var(--duration-fast) var(--ease-smooth), color var(--duration-fast) var(--ease-smooth);
  }

  .contact-social__link:hover {
    border-color: var(--color-accent);
    color: var(--color-accent);
  }

  .contact-map {
    border-radius: var(--radius-2xl);
    overflow: hidden;
    min-height: 24rem;
    box-shadow: var(--shadow-md);
  }

  .contact-map__iframe {
    width: 100%;
    height: 100%;
    min-height: 24rem;
    border: 0;
  }

  .contact-map__placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: var(--sp-3);
    height: 24rem;
    background: var(--color-surface-alt);
    color: var(--color-text-muted);
  }

  .contact-cta {
    display: flex;
    justify-content: center;
    margin-top: var(--sp-10);
  }

  @media (max-width: 768px) {
    .contact-grid { grid-template-columns: 1fr; }
  }
</style>