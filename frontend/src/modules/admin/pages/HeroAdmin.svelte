<script lang="ts">
  import { onMount } from 'svelte';
  import { heroApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { notifications } from '$lib/stores/notification.svelte';
  import { settingsStore } from '$lib/stores/settings.svelte';
  import { uploadUrl, imageUrl } from '$lib/utils';
  import Button from '$lib/components/Button.svelte';
  import Input from '$lib/components/Input.svelte';
  import Textarea from '$lib/components/Textarea.svelte';
  import FileUpload from '$lib/components/FileUpload.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import type { Hero } from '$lib/types';

  let hero = $state<Hero | null>(null);
  let loading = $state(true);
  let saving = $state(false);
  let formData = $state({
    headline: '', subtitle: '', description: '',
    primary_button_text: '', primary_button_url: '',
    secondary_button_text: '', secondary_button_url: '',
  });

  onMount(async () => {
    try {
      hero = await heroApi.get();
      formData = {
        headline: hero.headline, subtitle: hero.subtitle, description: hero.description,
        primary_button_text: hero.primary_button_text, primary_button_url: hero.primary_button_url,
        secondary_button_text: hero.secondary_button_text, secondary_button_url: hero.secondary_button_url,
      };
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      loading = false;
    }
  });

  async function handleSave(): Promise<void> {
    saving = true;
    try {
      hero = await heroApi.update(formData);
      await settingsStore.refreshHero();
      notifications.success(t('toast_hero_updated'));
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      saving = false;
    }
  }

  async function handleBgUpload(files: File[]): Promise<void> {
    if (files.length === 0) return;
    try {
      hero = await heroApi.uploadBackground(files[0]);
      await settingsStore.refreshHero();
      notifications.success(t('upload_success'));
    } catch {
      notifications.error(t('upload_failed'));
    }
  }

  const bgImage = $derived(hero?.background_image ? uploadUrl(hero.background_image) : imageUrl('hero-admin', 800, 400));
</script>

<div class="admin-page">
  <h1 class="admin-page__title">{t('admin_hero_title')}</h1>

  {#if loading}
    <Skeleton variant="rect" height="400px" />
  {:else}
    <div class="hero-form">
      <div class="hero-form__main">
        <Input label={t('admin_hero_field_headline')} value={formData.headline} oninput={(e) => (formData.headline = (e.target as HTMLInputElement).value)} />
        <Input label={t('admin_hero_field_subtitle')} value={formData.subtitle} oninput={(e) => (formData.subtitle = (e.target as HTMLInputElement).value)} />
        <Textarea label={t('admin_hero_field_description')} value={formData.description} oninput={(e) => (formData.description = (e.target as HTMLTextAreaElement).value)} />
        <div class="form-row">
          <Input label={t('admin_hero_field_primary_text')} value={formData.primary_button_text} oninput={(e) => (formData.primary_button_text = (e.target as HTMLInputElement).value)} />
          <Input label={t('admin_hero_field_primary_url')} value={formData.primary_button_url} oninput={(e) => (formData.primary_button_url = (e.target as HTMLInputElement).value)} />
        </div>
        <div class="form-row">
          <Input label={t('admin_hero_field_secondary_text')} value={formData.secondary_button_text} oninput={(e) => (formData.secondary_button_text = (e.target as HTMLInputElement).value)} />
          <Input label={t('admin_hero_field_secondary_url')} value={formData.secondary_button_url} oninput={(e) => (formData.secondary_button_url = (e.target as HTMLInputElement).value)} />
        </div>
        <Button variant="primary" size="md" onclick={handleSave} disabled={saving}>{t('save')}</Button>
      </div>

      <div class="hero-form__sidebar">
        <div class="form-section">
          <h3 class="form-section__title">{t('admin_hero_field_background')}</h3>
          {#if hero?.background_image}
            <div class="media-preview">
              <img src={bgImage} alt="Background" />
            </div>
          {/if}
          <FileUpload label="" accept="image/jpeg,image/png,image/webp" onselect={handleBgUpload} />
        </div>
      </div>
    </div>
  {/if}
</div>

<style>
  .admin-page__title { font-family: var(--font-serif); font-size: var(--fs-h2); font-weight: var(--fw-semibold); margin-bottom: var(--sp-6); }
  .hero-form { display: grid; grid-template-columns: 1fr 18rem; gap: var(--sp-6); }
  .hero-form__main { display: flex; flex-direction: column; gap: var(--sp-4); }
  .hero-form__sidebar { display: flex; flex-direction: column; gap: var(--sp-4); }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4); }
  .form-section { padding: var(--sp-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); }
  .form-section__title { font-size: var(--fs-body-sm); font-weight: var(--fw-semibold); margin-bottom: var(--sp-3); }
  .media-preview { margin-bottom: var(--sp-3); }
  .media-preview img { width: 100%; border-radius: var(--radius-md); aspect-ratio: 4/3; object-fit: cover; }

  @media (max-width: 880px) {
    .hero-form { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
  }
</style>