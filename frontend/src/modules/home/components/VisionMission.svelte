<script lang="ts">
  import { Eye, Target } from '@lucide/svelte';
  import SectionTitle from '$lib/components/SectionTitle.svelte';
  import { t } from '$lib/i18n/index.svelte';
</script>

<section class="section section--alt vision-mission">
  <div class="container">
    <SectionTitle eyebrow={t('vision_mission_eyebrow')} title={t('vision_mission_title')} align="center" />

    <div class="vision-mission__cards">
      <div class="vm-card">
        <div class="vm-card__icon">
          <Eye size={32} strokeWidth={1.5} />
        </div>
        <h3 class="vm-card__title">{t('vision_title')}</h3>
        <p class="vm-card__desc text-pretty">{t('vision_description')}</p>
      </div>

      <div class="vm-card__divider">
        <span class="vm-card__divider-line"></span>
        <span class="vm-card__divider-dot"></span>
        <span class="vm-card__divider-line"></span>
      </div>

      <div class="vm-card">
        <div class="vm-card__icon">
          <Target size={32} strokeWidth={1.5} />
        </div>
        <h3 class="vm-card__title">{t('mission_title')}</h3>
        <p class="vm-card__desc text-pretty">{t('mission_description')}</p>
      </div>
    </div>

    <div class="vision-mission__mobile">
      <div class="vm-accordion-item">
        <details>
          <summary>{t('vision_title')}</summary>
          <div class="vm-accordion-content">
            <p>{t('vision_description')}</p>
          </div>
        </details>
        <details>
          <summary>{t('mission_title')}</summary>
          <div class="vm-accordion-content">
            <p>{t('mission_description')}</p>
          </div>
        </details>
      </div>
    </div>
  </div>
</section>

<style>
  .vision-mission__cards {
    display: none;
    grid-template-columns: 1fr auto 1fr;
    gap: var(--sp-6);
    align-items: stretch;
    margin-top: var(--sp-10);
  }

  .vm-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--sp-4);
    padding: var(--sp-8) var(--sp-6);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
  }

  .vm-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 4rem;
    height: 4rem;
    border-radius: var(--radius-full);
    background: rgba(201, 162, 39, 0.12);
    color: var(--color-gold-dark);
  }

  .vm-card__title {
    font-family: var(--font-serif);
    font-size: var(--fs-h3);
    font-weight: var(--fw-semibold);
  }

  .vm-card__desc {
    font-size: var(--fs-body);
    color: var(--color-text-muted);
    line-height: var(--lh-relaxed);
    max-width: 24rem;
  }

  .vm-card__divider {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: var(--sp-2);
  }

  .vm-card__divider-line {
    width: 1px;
    flex: 1;
    min-height: 3rem;
    background: linear-gradient(to bottom, transparent, var(--color-gold), transparent);
  }

  .vm-card__divider-dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: var(--radius-full);
    background: var(--color-gold);
  }

  .vision-mission__mobile {
    display: block;
    margin-top: var(--sp-6);
  }

  .vm-accordion-item details {
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    margin-bottom: var(--sp-2);
    overflow: hidden;
  }

  .vm-accordion-item summary {
    padding: var(--sp-4) var(--sp-5);
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-medium);
    cursor: pointer;
    list-style: none;
  }

  .vm-accordion-item summary::-webkit-details-marker { display: none; }

  .vm-accordion-content {
    padding: 0 var(--sp-5) var(--sp-5);
    color: var(--color-text-muted);
    line-height: var(--lh-relaxed);
  }

  @media (min-width: 769px) {
    .vision-mission__cards { display: grid; }
    .vision-mission__mobile { display: none; }
  }
</style>