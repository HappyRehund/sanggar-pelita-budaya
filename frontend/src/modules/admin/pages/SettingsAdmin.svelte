<script lang="ts">
  import { onMount } from 'svelte';
  import { settingsApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { notifications } from '$lib/stores/notification.svelte';
  import { settingsStore } from '$lib/stores/settings.svelte';
  import { uploadUrl } from '$lib/utils';
  import Button from '$lib/components/Button.svelte';
  import Input from '$lib/components/Input.svelte';
  import Textarea from '$lib/components/Textarea.svelte';
  import Select from '$lib/components/Select.svelte';
  import Checkbox from '$lib/components/Checkbox.svelte';
  import FileUpload from '$lib/components/FileUpload.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import type { Settings } from '$lib/types';

  let settings = $state<Settings | null>(null);
  let loading = $state(true);
  let saving = $state(false);
  let formData = $state({
    site_name: '', site_description: '', default_language: 'en' as 'en' | 'id', maintenance_mode: false,
  });

  onMount(async () => {
    try {
      settings = await settingsApi.get();
      formData = {
        site_name: settings.site_name, site_description: settings.site_description,
        default_language: settings.default_language, maintenance_mode: settings.maintenance_mode,
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
      settings = await settingsApi.update(formData);
      await settingsStore.refreshSettings();
      notifications.success(t('toast_settings_updated'));
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      saving = false;
    }
  }

  async function handleImageUpload(field: 'logo' | 'favicon' | 'default_og_image', files: File[]): Promise<void> {
    if (files.length === 0) return;
    try {
      settings = await settingsApi.uploadImage(field, files[0]);
      await settingsStore.refreshSettings();
      notifications.success(t('upload_success'));
    } catch {
      notifications.error(t('upload_failed'));
    }
  }
</script>

<div class="admin-page">
  <h1 class="admin-page__title">{t('admin_settings_title')}</h1>

  {#if loading}
    <Skeleton variant="rect" height="400px" />
  {:else}
    <div class="settings-form">
      <div class="settings-form__main">
        <Input label={t('admin_settings_field_site_name')} value={formData.site_name} oninput={(e) => (formData.site_name = (e.target as HTMLInputElement).value)} />
        <Textarea label={t('admin_settings_field_site_description')} value={formData.site_description} oninput={(e) => (formData.site_description = (e.target as HTMLTextAreaElement).value)} />
        <Select label={t('admin_settings_field_default_language')} value={formData.default_language} options={[{value:'en',label:'English'},{value:'id',label:'Bahasa Indonesia'}]} onchange={(e) => (formData.default_language = (e.target as HTMLSelectElement).value as 'en' | 'id')} />
        <Checkbox label={t('admin_settings_field_maintenance')} checked={formData.maintenance_mode} onchange={(e) => (formData.maintenance_mode = (e.target as HTMLInputElement).checked)} />
        <Button variant="primary" size="md" onclick={handleSave} disabled={saving}>{t('save')}</Button>
      </div>

      <div class="settings-form__sidebar">
        <div class="form-section">
          <h3 class="form-section__title">{t('admin_settings_field_logo')}</h3>
          {#if settings?.logo}
            <div class="media-preview"><img src={uploadUrl(settings.logo)} alt="Logo" /></div>
          {/if}
          <FileUpload label="" accept="image/jpeg,image/png,image/webp" onselect={(files) => handleImageUpload('logo', files)} />
        </div>
        <div class="form-section">
          <h3 class="form-section__title">{t('admin_settings_field_favicon')}</h3>
          {#if settings?.favicon}
            <div class="media-preview"><img src={uploadUrl(settings.favicon)} alt="Favicon" /></div>
          {/if}
          <FileUpload label="" accept="image/jpeg,image/png,image/webp" onselect={(files) => handleImageUpload('favicon', files)} />
        </div>
        <div class="form-section">
          <h3 class="form-section__title">{t('admin_settings_field_og_image')}</h3>
          {#if settings?.default_og_image}
            <div class="media-preview"><img src={uploadUrl(settings.default_og_image)} alt="OG Image" /></div>
          {/if}
          <FileUpload label="" accept="image/jpeg,image/png,image/webp" onselect={(files) => handleImageUpload('default_og_image', files)} />
        </div>
      </div>
    </div>
  {/if}
</div>

<style>
  .admin-page__title { font-family: var(--font-serif); font-size: var(--fs-h2); font-weight: var(--fw-semibold); margin-bottom: var(--sp-6); }
  .settings-form { display: grid; grid-template-columns: 1fr 18rem; gap: var(--sp-6); }
  .settings-form__main { display: flex; flex-direction: column; gap: var(--sp-4); }
  .settings-form__sidebar { display: flex; flex-direction: column; gap: var(--sp-4); }
  .form-section { padding: var(--sp-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); }
  .form-section__title { font-size: var(--fs-body-sm); font-weight: var(--fw-semibold); margin-bottom: var(--sp-3); }
  .media-preview { margin-bottom: var(--sp-3); }
  .media-preview img { width: 100%; border-radius: var(--radius-md); max-height: 6rem; object-fit: contain; }

  @media (max-width: 880px) {
    .settings-form { grid-template-columns: 1fr; }
  }
</style>