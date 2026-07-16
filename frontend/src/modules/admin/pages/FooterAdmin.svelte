<script lang="ts">
  import { onMount } from 'svelte';
  import { footerApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { notifications } from '$lib/stores/notification.svelte';
  import { settingsStore } from '$lib/stores/settings.svelte';
  import { uploadUrl } from '$lib/utils';
  import Button from '$lib/components/Button.svelte';
  import Input from '$lib/components/Input.svelte';
  import Textarea from '$lib/components/Textarea.svelte';
  import FileUpload from '$lib/components/FileUpload.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import type { Footer } from '$lib/types';

  let footer = $state<Footer | null>(null);
  let loading = $state(true);
  let saving = $state(false);
  let formData = $state({
    description: '', address: '', phone: '', email: '', website: '',
    working_hours: '', facebook: '', instagram: '', youtube: '', tiktok: '',
    maps_url: '', copyright: '',
  });

  onMount(async () => {
    try {
      footer = await footerApi.get();
      formData = {
        description: footer.description, address: footer.address, phone: footer.phone,
        email: footer.email, website: footer.website, working_hours: footer.working_hours,
        facebook: footer.facebook || '', instagram: footer.instagram || '',
        youtube: footer.youtube || '', tiktok: footer.tiktok || '',
        maps_url: footer.maps_url, copyright: footer.copyright,
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
      footer = await footerApi.update(formData);
      await settingsStore.refreshFooter();
      notifications.success(t('toast_footer_updated'));
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      saving = false;
    }
  }

  async function handleLogoUpload(files: File[]): Promise<void> {
    if (files.length === 0) return;
    try {
      footer = await footerApi.uploadLogo(files[0]);
      await settingsStore.refreshFooter();
      notifications.success(t('upload_success'));
    } catch {
      notifications.error(t('upload_failed'));
    }
  }
</script>

<div class="admin-page">
  <h1 class="admin-page__title">{t('admin_footer_title')}</h1>

  {#if loading}
    <Skeleton variant="rect" height="400px" />
  {:else}
    <div class="footer-form">
      <div class="footer-form__main">
        <Textarea label={t('admin_footer_field_description')} value={formData.description} oninput={(e) => (formData.description = (e.target as HTMLTextAreaElement).value)} />
        <Textarea label={t('admin_footer_field_address')} value={formData.address} oninput={(e) => (formData.address = (e.target as HTMLTextAreaElement).value)} />
        <div class="form-row">
          <Input label={t('admin_footer_field_phone')} value={formData.phone} oninput={(e) => (formData.phone = (e.target as HTMLInputElement).value)} />
          <Input label={t('admin_footer_field_email')} value={formData.email} oninput={(e) => (formData.email = (e.target as HTMLInputElement).value)} />
        </div>
        <div class="form-row">
          <Input label={t('admin_footer_field_website')} value={formData.website} oninput={(e) => (formData.website = (e.target as HTMLInputElement).value)} />
          <Input label={t('admin_footer_field_working_hours')} value={formData.working_hours} oninput={(e) => (formData.working_hours = (e.target as HTMLInputElement).value)} />
        </div>
        <div class="form-row">
          <Input label={t('admin_footer_field_facebook')} value={formData.facebook} oninput={(e) => (formData.facebook = (e.target as HTMLInputElement).value)} />
          <Input label={t('admin_footer_field_instagram')} value={formData.instagram} oninput={(e) => (formData.instagram = (e.target as HTMLInputElement).value)} />
        </div>
        <div class="form-row">
          <Input label={t('admin_footer_field_youtube')} value={formData.youtube} oninput={(e) => (formData.youtube = (e.target as HTMLInputElement).value)} />
          <Input label={t('admin_footer_field_tiktok')} value={formData.tiktok} oninput={(e) => (formData.tiktok = (e.target as HTMLInputElement).value)} />
        </div>
        <Input label={t('admin_footer_field_maps_url')} value={formData.maps_url} oninput={(e) => (formData.maps_url = (e.target as HTMLInputElement).value)} />
        <Input label={t('admin_footer_field_copyright')} value={formData.copyright} oninput={(e) => (formData.copyright = (e.target as HTMLInputElement).value)} />
        <Button variant="primary" size="md" onclick={handleSave} disabled={saving}>{t('save')}</Button>
      </div>

      <div class="footer-form__sidebar">
        <div class="form-section">
          <h3 class="form-section__title">{t('admin_footer_field_logo')}</h3>
          {#if footer?.logo}
            <div class="media-preview">
              <img src={uploadUrl(footer.logo)} alt="Logo" />
            </div>
          {/if}
          <FileUpload label="" accept="image/jpeg,image/png,image/webp" onselect={handleLogoUpload} />
        </div>
      </div>
    </div>
  {/if}
</div>

<style>
  .admin-page__title { font-family: var(--font-serif); font-size: var(--fs-h2); font-weight: var(--fw-semibold); margin-bottom: var(--sp-6); }
  .footer-form { display: grid; grid-template-columns: 1fr 18rem; gap: var(--sp-6); }
  .footer-form__main { display: flex; flex-direction: column; gap: var(--sp-4); }
  .footer-form__sidebar { display: flex; flex-direction: column; gap: var(--sp-4); }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4); }
  .form-section { padding: var(--sp-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); }
  .form-section__title { font-size: var(--fs-body-sm); font-weight: var(--fw-semibold); margin-bottom: var(--sp-3); }
  .media-preview { margin-bottom: var(--sp-3); }
  .media-preview img { width: 100%; border-radius: var(--radius-md); max-height: 6rem; object-fit: contain; }

  @media (max-width: 880px) {
    .footer-form { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
  }
</style>