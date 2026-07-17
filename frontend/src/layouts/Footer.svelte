<script lang="ts">
  import { MapPin, Phone, Mail, Globe, Clock, ExternalLink } from '@lucide/svelte';
  import { settingsStore } from '$lib/stores/settings.svelte';
  import { t } from '$lib/i18n/index.svelte';
  import { uploadUrl } from '$lib/utils';
  import { router } from '$lib/router.svelte';

  const footer = $derived(settingsStore.footer);
  const siteName = $derived(settingsStore.siteName);
  const logo = $derived(footer?.logo ?? null);

  const quickLinks = [
    { path: '/', label: () => t('nav_home') },
    { path: '/about', label: () => t('nav_about') },
    { path: '/organization', label: () => t('nav_organization') },
    { path: '/portfolio', label: () => t('nav_portfolio') },
  ];

  const socials = $derived([
    { url: footer?.facebook, icon: ExternalLink, label: 'Facebook' },
    { url: footer?.instagram, icon: ExternalLink, label: 'Instagram' },
    { url: footer?.youtube, icon: ExternalLink, label: 'YouTube' },
  ].filter((s) => s.url));

  const year = new Date().getFullYear();
  const copyright = $derived(
    (footer?.copyright ?? '© {year} Sanggar Pelita Budaya. All rights reserved.')
      .replace('{year}', String(year))
  );

  function navigate(path: string): void {
    router.go(path);
  }
</script>

{#if footer}
  <footer class="footer">
    <div class="footer__inner">
      <div class="footer__col footer__col--brand">
        {#if logo}
          <img src={uploadUrl(logo)} alt={siteName} class="footer__logo" />
        {:else}
          <span class="footer__logo-text">{siteName}</span>
        {/if}
        <p class="footer__desc">{footer.description}</p>
        {#if socials.length > 0}
          <div class="footer__socials">
            {#each socials as social (social.label)}
              <a
                href={social.url!}
                target="_blank"
                rel="noopener noreferrer"
                class="footer__social"
                aria-label={social.label}
              >
                <social.icon size={18} />
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
          {#if footer.address}
            <li>
              <MapPin size={16} />
              <span>{footer.address}</span>
            </li>
          {/if}
          {#if footer.phone}
            <li>
              <Phone size={16} />
              <a href={`tel:${footer.phone}`}>{footer.phone}</a>
            </li>
          {/if}
          {#if footer.email}
            <li>
              <Mail size={16} />
              <a href={`mailto:${footer.email}`}>{footer.email}</a>
            </li>
          {/if}
          {#if footer.website}
            <li>
              <Globe size={16} />
              <a href={footer.website} target="_blank" rel="noopener noreferrer">{footer.website}</a>
            </li>
          {/if}
          {#if footer.working_hours}
            <li>
              <Clock size={16} />
              <span>{footer.working_hours}</span>
            </li>
          {/if}
        </ul>
        {#if footer.maps_url}
          <a href={footer.maps_url} target="_blank" rel="noopener noreferrer" class="footer__maps">
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
{:else}
  <footer class="footer footer--minimal">
    <div class="footer__bottom">
      <p class="footer__copyright">© {year} {siteName}. {t('footer_copyright')}</p>
    </div>
  </footer>
{/if}

<style>
  .footer {
    background-color: var(--color-brown);
    color: var(--color-beige);
    padding-top: var(--sp-12);
  }

  .footer--minimal {
    padding-top: var(--sp-6);
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
    height: 3rem;
    width: auto;
    margin-bottom: var(--sp-2);
  }

  .footer__logo-text {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-semibold);
    color: var(--color-ivory);
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