<script lang="ts">
  import { MapPin, Phone, Mail, Globe, Clock } from '@lucide/svelte';
  import { settingsStore } from '$lib/stores/settings.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { SITE_CONTENT } from '$lib/constants';
  import { router } from '$lib/router.svelte';
  import logoFooter from '$assets/logo/logo-footer-sanggar-pelita-budaya.webp';

  const siteName = $derived(settingsStore.siteName);

  const quickLinks = [
    { path: '/', label: () => t('nav_home') },
    { path: '/about', label: () => t('nav_about') },
    { path: '/organization', label: () => t('nav_organization') },
    { path: '/highlights', label: () => t('nav_highlights') },
  ];

  type SocialEntry = { url: string; label: string; glyph: 'instagram' | 'youtube' | 'tiktok' };

  const socials = $derived<SocialEntry[]>(
    (
      [
        { url: SITE_CONTENT.socials.instagram, label: 'Instagram', glyph: 'instagram' },
        { url: SITE_CONTENT.socials.tiktok, label: 'TikTok', glyph: 'tiktok' },
        { url: SITE_CONTENT.socials.youtube, label: 'YouTube', glyph: 'youtube' },
      ] satisfies SocialEntry[]
    ).filter((s) => Boolean(s.url))
  );

  const year = new Date().getFullYear();
  const copyright = $derived(t('footer_copyright_template').replace('{year}', String(year)));

  const address = $derived(t('footer_address_value'));
  const phone = $derived(t('footer_phone_value'));
  const email = $derived(t('footer_email_value'));
  const website = $derived(t('footer_website_value'));
  const workingHours = $derived(t('footer_working_hours_value'));
  const mapsUrl = $derived(SITE_CONTENT.mapsUrl);

  function navigate(path: string): void {
    router.go(path);
  }
</script>

{#snippet instagramGlyph()}
  <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
    <path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.43.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.43.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 0 1-1.38-.9 3.7 3.7 0 0 1-.9-1.38c-.16-.43-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.43-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16Zm0 1.62c-3.15 0-3.52.01-4.76.07-1.15.05-1.77.24-2.19.4-.55.22-.94.47-1.35.88-.41.41-.66.8-.88 1.35-.16.42-.35 1.04-.4 2.19-.06 1.24-.07 1.61-.07 4.76s.01 3.52.07 4.76c.05 1.15.24 1.77.4 2.19.22.55.47.94.88 1.35.41.41.8.66 1.35.88.42.16 1.04.35 2.19.4 1.24.06 1.61.07 4.76.07s3.52-.01 4.76-.07c1.15-.05 1.77-.24 2.19-.4.55-.22.94-.47 1.35-.88.41-.41.66-.8.88-1.35.16-.42.35-1.04.4-2.19.06-1.24.07-1.61.07-4.76s-.01-3.52-.07-4.76c-.05-1.15-.24-1.77-.4-2.19a3.6 3.6 0 0 0-.88-1.35 3.6 3.6 0 0 0-1.35-.88c-.42-.16-1.04-.35-2.19-.4-1.24-.06-1.61-.07-4.76-.07Zm0 2.76a5.46 5.46 0 1 1 0 10.92 5.46 5.46 0 0 1 0-10.92Zm0 9a3.54 3.54 0 1 0 0-7.08 3.54 3.54 0 0 0 0 7.08Zm5.68-9.22a1.28 1.28 0 1 1-2.55 0 1.28 1.28 0 0 1 2.55 0Z" />
  </svg>
{/snippet}

{#snippet tiktokGlyph()}
  <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
    <path d="M16.5 2c.34 2.15 1.7 3.9 3.5 4.6v3.05a8.9 8.9 0 0 1-3.5-.7v6.1c0 3.5-2.83 6.35-6.32 6.35A6.34 6.34 0 0 1 3.84 15.6c0-3.5 2.83-6.35 6.32-6.35.32 0 .64.03.95.08v3.2a3.18 3.18 0 1 0 2.23 3.04V2h3.16Z" />
  </svg>
{/snippet}

{#snippet youtubeGlyph()}
  <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
    <path d="M23.5 7.2a3 3 0 0 0-2.12-2.12C19.5 4.5 12 4.5 12 4.5s-7.5 0-9.38.58A3 3 0 0 0 .5 7.2 31.4 31.4 0 0 0 0 12a31.4 31.4 0 0 0 .5 4.8 3 3 0 0 0 2.12 2.12C4.5 19.5 12 19.5 12 19.5s7.5 0 9.38-.58a3 3 0 0 0 2.12-2.12A31.4 31.4 0 0 0 24 12a31.4 31.4 0 0 0-.5-4.8ZM9.6 15.6V8.4l6.2 3.6-6.2 3.6Z" />
  </svg>
{/snippet}

<footer class="footer">
    <div class="footer__inner">
      <div class="footer__col footer__col--brand">
        <img src={logoFooter} alt={siteName} class="footer__logo" />
        <p class="footer__desc">{t('footer_desc')}</p>
        {#if socials.length > 0}
          <div class="footer__socials">
            {#each socials as social (social.label)}
              <a
                href={social.url}
                target="_blank"
                rel="noopener noreferrer"
                class="footer__social"
                aria-label={social.label}
              >
                {#if social.glyph === 'instagram'}
                  {@render instagramGlyph()}
                {:else if social.glyph === 'tiktok'}
                  {@render tiktokGlyph()}
                {:else if social.glyph === 'youtube'}
                  {@render youtubeGlyph()}
                {/if}
              </a>
            {/each}
          </div>
        {/if}
      </div>

      <div class="footer__col">
        <h3 class="footer__heading">{t('footer_quick_links')}</h3>
        <ul class="footer__links">
          {#each quickLinks as link (link.path)}
            <li>
              <a
                href={link.path}
                class="footer__link"
                onclick={(e) => { e.preventDefault(); navigate(link.path); }}
              >
                {link.label()}
              </a>
            </li>
          {/each}
        </ul>
      </div>

      <div class="footer__col">
        <h3 class="footer__heading">{t('footer_address')}</h3>
        <ul class="footer__contact">
          {#if address}
            <li>
              <MapPin size={16} />
              <span>{address}</span>
            </li>
          {/if}
          {#if phone}
            <li>
              <Phone size={16} />
              <a href={`tel:${phone}`}>{phone}</a>
            </li>
          {/if}
          {#if email}
            <li>
              <Mail size={16} />
              <a href={`mailto:${email}`}>{email}</a>
            </li>
          {/if}
          {#if website}
            <li>
              <Globe size={16} />
              <a href={website} target="_blank" rel="noopener noreferrer">{website}</a>
            </li>
          {/if}
          {#if workingHours}
            <li>
              <Clock size={16} />
              <span>{workingHours}</span>
            </li>
          {/if}
        </ul>
        {#if mapsUrl}
          <a href={mapsUrl} target="_blank" rel="noopener noreferrer" class="footer__maps">
            <MapPin size={14} />
            {t('view_on_map')}
          </a>
        {/if}
      </div>
    </div>

    <div class="footer__bottom">
      <p class="footer__copyright">{copyright}</p>
    </div>
  </footer>

<style>
  .footer {
    background-color: var(--color-brown);
    color: var(--color-beige);
    padding-top: var(--sp-12);
  }

  .footer__inner {
    max-width: var(--container-max);
    margin-inline: auto;
    padding: 0 var(--sp-5) var(--sp-10);
    display: grid;
    grid-template-columns: 2fr 1fr 1.5fr;
    gap: var(--sp-8);
  }

  .footer__col {
    display: flex;
    flex-direction: column;
    gap: var(--sp-3);
  }

  .footer__logo {
    display: block;
    align-self: flex-start;
    max-width: min(100%, 26rem);
    max-height: 6.5rem;
    width: auto;
    height: auto;
    object-fit: contain;
    margin-bottom: var(--sp-2);
  }

  .footer__desc {
    font-size: var(--fs-body-sm);
    line-height: var(--lh-relaxed);
    color: var(--color-beige);
    max-width: 24rem;
  }

  .footer__heading {
    font-family: var(--font-serif);
    font-size: var(--fs-body-lg);
    font-weight: var(--fw-medium);
    color: var(--color-ivory);
    margin-bottom: var(--sp-2);
  }

  .footer__links {
    display: flex;
    flex-direction: column;
    gap: var(--sp-2);
  }

  .footer__link {
    font-size: var(--fs-body-sm);
    color: var(--color-beige);
    transition: color var(--duration-fast) var(--ease-smooth);
  }

  .footer__link:hover {
    color: var(--color-gold);
  }

  .footer__contact {
    display: flex;
    flex-direction: column;
    gap: var(--sp-3);
  }

  .footer__contact li {
    display: flex;
    align-items: flex-start;
    gap: var(--sp-2);
    font-size: var(--fs-body-sm);
    color: var(--color-beige);
  }

  .footer__contact a {
    color: var(--color-beige);
    transition: color var(--duration-fast) var(--ease-smooth);
  }

  .footer__contact a:hover {
    color: var(--color-gold);
  }

  .footer__maps {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-1);
    font-size: var(--fs-body-sm);
    color: var(--color-gold);
    margin-top: var(--sp-2);
  }

  .footer__socials {
    display: flex;
    gap: var(--sp-2);
    margin-top: var(--sp-3);
  }

  .footer__social {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: var(--radius-full);
    border: 1px solid rgba(232, 220, 200, 0.3);
    color: var(--color-beige);
    transition: border-color var(--duration-fast) var(--ease-smooth), color var(--duration-fast) var(--ease-smooth);
  }

  .footer__social:hover {
    border-color: var(--color-gold);
    color: var(--color-gold);
  }

  .footer__bottom {
    border-top: 1px solid rgba(232, 220, 200, 0.15);
    padding: var(--sp-5) var(--sp-5);
    max-width: var(--container-max);
    margin-inline: auto;
  }

  .footer__copyright {
    font-size: var(--fs-caption);
    color: var(--color-gray-400);
    text-align: center;
  }

  /* ---- Responsive ---- */
  @media (max-width: 880px) {
    .footer__inner {
      grid-template-columns: 1fr;
      gap: var(--sp-6);
    }
  }

  @media (max-width: 480px) {
    .footer__inner {
      gap: var(--sp-5);
      padding-bottom: var(--sp-8);
    }
  }
</style>