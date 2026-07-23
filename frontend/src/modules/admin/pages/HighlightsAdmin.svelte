<script lang="ts">
  import { onMount, onDestroy } from 'svelte';
  import { highlightsApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { notifications } from '$lib/stores/notification.svelte';
  import { categoryLabel } from '$lib/constants/categories';
  import { slugify } from '$lib/utils';
  import { formatDateInput, uploadUrl, imageUrl } from '$lib/utils';
  import { langStore } from '$lib/stores/lang.svelte';
  import type { HighlightListSummary, HighlightCategory, HighlightMedia } from '$lib/types';
  import Button from '$lib/components/Button.svelte';
  import Input from '$lib/components/Input.svelte';
  import Textarea from '$lib/components/Textarea.svelte';
  import Select from '$lib/components/Select.svelte';
  import FileUpload from '$lib/components/FileUpload.svelte';
  import Badge from '$lib/components/Badge.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import ConfirmDialog from '../components/ConfirmDialog.svelte';
  import { Plus, Search, Pencil, Trash2, ArrowLeft, X } from '@lucide/svelte';

  let mode = $state<'list' | 'edit' | 'create'>('list');
  let items = $state<HighlightListSummary[]>([]);
  let loading = $state(true);
  let search = $state('');
  let searchInput = $state<HTMLInputElement | null>(null);
  let saving = $state(false);
  let deleteTarget = $state<HighlightListSummary | null>(null);

  let formData = $state({
    title_en: '', title_id: '', slug: '', category: 'activity' as HighlightCategory,
    short_description_en: '', short_description_id: '', event_date: '', location: '',
    youtube_url: '', seo_title_en: '', seo_title_id: '', seo_description_en: '', seo_description_id: '',
  });
  let coverMedia = $state<HighlightMedia | null>(null);
  let pendingCover = $state<File | null>(null);
  let coverPreviewUrl = $state<string | null>(null);
  let slugTouched = $state(false);

  let editId = $state<number | null>(null);

  onMount(() => {
    loadList();
  });

  onDestroy(() => {
    if (coverPreviewUrl) URL.revokeObjectURL(coverPreviewUrl);
  });

  async function loadList(): Promise<void> {
    loading = true;
    try {
      const result = await highlightsApi.list({});
      items = result.items;
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      loading = false;
    }
  }

  function clearCoverPreview(): void {
    if (coverPreviewUrl) URL.revokeObjectURL(coverPreviewUrl);
    coverPreviewUrl = null;
    pendingCover = null;
  }

  function startCreate(): void {
    mode = 'create';
    editId = null;
    formData = { title_en: '', title_id: '', slug: '', category: 'activity', short_description_en: '', short_description_id: '', event_date: '', location: '', youtube_url: '', seo_title_en: '', seo_title_id: '', seo_description_en: '', seo_description_id: '' };
    coverMedia = null;
    clearCoverPreview();
    slugTouched = false;
  }

  async function startEdit(item: HighlightListSummary): Promise<void> {
    mode = 'edit';
    editId = item.id;
    loading = true;
    try {
      const full = await highlightsApi.getById(item.id);
      coverMedia = full.cover ?? null;
      clearCoverPreview();
      formData = {
        title_en: full.title_en, title_id: full.title_id, slug: full.slug, category: full.category,
        short_description_en: full.short_description_en, short_description_id: full.short_description_id,
        event_date: formatDateInput(full.event_date), location: full.location || '',
        youtube_url: full.youtube_url || '',
        seo_title_en: full.seo_title_en || '', seo_title_id: full.seo_title_id || '',
        seo_description_en: full.seo_description_en || '', seo_description_id: full.seo_description_id || '',
      };
      slugTouched = true;
    } catch {
      notifications.error(t('toast_error'));
      mode = 'list';
    } finally {
      loading = false;
    }
  }

  function backToList(): void {
    mode = 'list';
    editId = null;
    clearCoverPreview();
  }

  function onTitleInput(e: Event): void {
    formData.title_en = (e.target as HTMLInputElement).value;
    if (!slugTouched) formData.slug = slugify(formData.title_en);
  }

  function onSlugInput(e: Event): void {
    formData.slug = slugify((e.target as HTMLInputElement).value);
    slugTouched = true;
  }

  function handleCoverSelect(files: File[]): void {
    if (files.length === 0) return;
    const file = files[0];
    if (editId !== null) {
      uploadCoverNow(file);
    } else {
      if (coverPreviewUrl) URL.revokeObjectURL(coverPreviewUrl);
      pendingCover = file;
      coverPreviewUrl = URL.createObjectURL(file);
    }
  }

  async function uploadCoverNow(file: File): Promise<void> {
    if (editId === null) return;
    try {
      const media = await highlightsApi.uploadMedia(editId, file, 'cover');
      coverMedia = media;
      notifications.success(t('upload_success'));
    } catch {
      notifications.error(t('upload_failed'));
    }
  }

  async function handleSave(): Promise<void> {
    saving = true;
    try {
      if (editId !== null) {
        await highlightsApi.update(editId, formData);
        notifications.success(t('toast_highlights_updated'));
      } else {
        const created = await highlightsApi.create(formData);
        editId = created.id;
        if (pendingCover) {
          await highlightsApi.uploadMedia(editId, pendingCover, 'cover');
        }
        notifications.success(t('toast_highlights_created'));
      }
      await loadList();
      backToList();
    } catch (e) {
      const err = e as { errors?: Record<string, string>; message?: string };
      if (err.errors) {
        notifications.error(Object.values(err.errors)[0] || t('toast_error'));
      } else {
        notifications.error(err.message || t('toast_error'));
      }
    } finally {
      saving = false;
    }
  }

  async function handleDelete(): Promise<void> {
    if (!deleteTarget) return;
    try {
      await highlightsApi.delete(deleteTarget.id);
      notifications.success(t('toast_highlights_deleted'));
      await loadList();
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      deleteTarget = null;
    }
  }

  async function deleteCover(): Promise<void> {
    if (editId === null) {
      clearCoverPreview();
      return;
    }
    if (!coverMedia) return;
    try {
      await highlightsApi.deleteMedia(coverMedia.id);
      coverMedia = null;
      notifications.success(t('toast_deleted'));
    } catch {
      notifications.error(t('toast_error'));
    }
  }

  const filteredItems = $derived(search ? items.filter((i) => i.title_en.toLowerCase().includes(search.toLowerCase()) || i.title_id.toLowerCase().includes(search.toLowerCase())) : items);
  const coverSrc = $derived(coverPreviewUrl ?? (coverMedia ? uploadUrl(coverMedia.filename) : null));
</script>

{#if mode === 'list'}
  <div class="admin-page">
    <div class="admin-page__header">
      <h1 class="admin-page__title">{t('admin_highlights_title')}</h1>
      <Button variant="primary" size="sm" onclick={startCreate}>
        <Plus size={16} />
        {t('admin_highlights_new')}
      </Button>
    </div>

    <div class="admin-search">
      <button type="button" class="admin-search__icon" aria-label={t('search')} onclick={() => searchInput?.focus()}>
        <Search size={16} />
      </button>
      <input bind:this={searchInput} type="search" placeholder={t('search_placeholder')} value={search} oninput={(e) => (search = (e.target as HTMLInputElement).value)} />
    </div>

    {#if loading}
      {#each Array(5) as _, i (i)}<Skeleton variant="rect" height="60px" />{/each}
    {:else if filteredItems.length === 0}
      <EmptyState title={t('admin_highlights_empty')} />
    {:else}
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>{t('admin_highlights_col_cover')}</th>
              <th>{t('admin_highlights_col_title')}</th>
              <th>{t('admin_highlights_col_category')}</th>
              <th>{t('admin_highlights_col_location')}</th>
              <th>{t('admin_highlights_col_actions')}</th>
            </tr>
          </thead>
          <tbody>
            {#each filteredItems as item (item.id)}
              <tr>
                <td>
                  <img src={item.cover ? uploadUrl(item.cover.filename) : imageUrl(`h-${item.slug}`, 80, 60)} alt="" class="table-thumb" />
                </td>
                <td>{item.title_id}</td>
                <td><Badge variant={item.category}>{categoryLabel(item.category, langStore.current)}</Badge></td>
                <td>{item.location || '—'}</td>
                <td>
                  <div class="table-actions">
                    <button onclick={() => startEdit(item)} aria-label={t('edit')}><Pencil size={16} /></button>
                    <button onclick={() => (deleteTarget = item)} aria-label={t('delete')}><Trash2 size={16} /></button>
                  </div>
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    {/if}
  </div>
{:else}
  <div class="admin-page">
    <div class="admin-page__header">
      <button class="back-btn" onclick={backToList}><ArrowLeft size={18} /> {t('back')}</button>
      <h1 class="admin-page__title">{editId ? t('admin_highlights_edit') : t('admin_highlights_new')}</h1>
    </div>

    {#if loading}
      <Skeleton variant="rect" height="600px" />
    {:else}
      <div class="form-grid">
        <div class="form-main">
          <div class="lang-block">
            <span class="lang-block__label">EN</span>
            <Input label={t('admin_highlights_field_title_en')} value={formData.title_en} oninput={onTitleInput} placeholder="Traditional Dance Festival 2026" />
            <Textarea label={t('admin_highlights_field_short_description_en')} value={formData.short_description_en} oninput={(e) => (formData.short_description_en = (e.target as HTMLTextAreaElement).value)} />
            <Input label={t('admin_highlights_field_seo_title_en')} value={formData.seo_title_en} oninput={(e) => (formData.seo_title_en = (e.target as HTMLInputElement).value)} />
            <Input label={t('admin_highlights_field_seo_description_en')} value={formData.seo_description_en} oninput={(e) => (formData.seo_description_en = (e.target as HTMLInputElement).value)} />
          </div>

          <div class="lang-block">
            <span class="lang-block__label">ID</span>
            <Input label={t('admin_highlights_field_title_id')} value={formData.title_id} oninput={(e) => (formData.title_id = (e.target as HTMLInputElement).value)} placeholder="Festival Tari Tradisional 2026" />
            <Textarea label={t('admin_highlights_field_short_description_id')} value={formData.short_description_id} oninput={(e) => (formData.short_description_id = (e.target as HTMLTextAreaElement).value)} />
            <Input label={t('admin_highlights_field_seo_title_id')} value={formData.seo_title_id} oninput={(e) => (formData.seo_title_id = (e.target as HTMLInputElement).value)} />
            <Input label={t('admin_highlights_field_seo_description_id')} value={formData.seo_description_id} oninput={(e) => (formData.seo_description_id = (e.target as HTMLInputElement).value)} />
          </div>

          <Input label={t('admin_highlights_field_slug')} value={formData.slug} oninput={onSlugInput} placeholder="traditional-dance-festival-2026" hint={t('admin_highlights_field_slug')} />
          <div class="form-row">
            <Select label={t('admin_highlights_field_category')} value={formData.category} options={[{value:'achievement',label:t('category_achievement')},{value:'activity',label:t('category_activity')}]} onchange={(e) => (formData.category = (e.target as HTMLSelectElement).value as HighlightCategory)} />
            <Input label={t('admin_highlights_field_event_date')} type="date" value={formData.event_date} oninput={(e) => (formData.event_date = (e.target as HTMLInputElement).value)} />
          </div>
          <Input label={t('admin_highlights_field_location')} value={formData.location} oninput={(e) => (formData.location = (e.target as HTMLInputElement).value)} placeholder="Jakarta, Indonesia" />
          <Input label={t('admin_highlights_field_youtube')} value={formData.youtube_url} oninput={(e) => (formData.youtube_url = (e.target as HTMLInputElement).value)} placeholder="https://youtube.com/watch?v=..." />
        </div>

        <div class="form-sidebar">
          <div class="form-section">
            <h3 class="form-section__title">{t('admin_highlights_field_cover')}</h3>
            {#if coverSrc}
              <div class="media-preview">
                <img src={coverSrc} alt="Cover" />
                <button onclick={deleteCover} aria-label={t('upload_remove')}><X size={14} /></button>
              </div>
            {/if}
            <FileUpload label="" accept="image/jpeg,image/png,image/webp" onselect={handleCoverSelect} />
          </div>

          <div class="form-actions">
            <Button variant="primary" size="md" onclick={handleSave} disabled={saving}>{t('save')}</Button>
            <Button variant="ghost" size="md" onclick={backToList}>{t('cancel')}</Button>
          </div>
        </div>
      </div>
    {/if}
  </div>
{/if}

<ConfirmDialog
  open={deleteTarget !== null}
  title={t('delete')}
  message={t('confirm_delete_highlights')}
  confirmLabel={t('delete')}
  onconfirm={handleDelete}
  oncancel={() => (deleteTarget = null)}
/>

<style>
  .admin-page__header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--sp-6); gap: var(--sp-4); flex-wrap: wrap; }
  .admin-page__title { font-family: var(--font-serif); font-size: var(--fs-h2); font-weight: var(--fw-semibold); }
  .back-btn { display: inline-flex; align-items: center; gap: var(--sp-1); color: var(--color-text-muted); font-size: var(--fs-body-sm); }
  .back-btn:hover { color: var(--color-accent); }

  .admin-search { position: relative; margin-bottom: var(--sp-4); }
  .admin-search__icon {
    position: absolute;
    left: var(--sp-3);
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: none;
    background: transparent;
    color: var(--color-text-subtle);
    cursor: pointer;
  }
  .admin-search__icon:hover { color: var(--color-accent); }
  .admin-search input { width: 100%; padding: var(--sp-2) var(--sp-4) var(--sp-2) var(--sp-8); border: 1px solid var(--color-border-strong); border-radius: var(--radius-md); font-size: var(--fs-body-sm); }
  .admin-search input:focus { outline: none; border-color: var(--color-accent); }

  .table-wrap { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--color-border); background: var(--color-surface); }
  .admin-table { width: 100%; border-collapse: collapse; }
  .admin-table th { text-align: left; padding: var(--sp-3) var(--sp-4); font-size: var(--fs-caption); font-weight: var(--fw-semibold); text-transform: uppercase; letter-spacing: var(--tracking-wide); color: var(--color-text-muted); border-bottom: 1px solid var(--color-border); }
  .admin-table td { padding: var(--sp-3) var(--sp-4); font-size: var(--fs-body-sm); border-bottom: 1px solid var(--color-border); }
  .table-thumb { width: 3rem; height: 2.25rem; object-fit: cover; border-radius: var(--radius-sm); }

  .table-actions { display: flex; gap: var(--sp-2); }
  .table-actions button { color: var(--color-text-muted); padding: 4px; border-radius: var(--radius-sm); transition: color var(--duration-fast) var(--ease-smooth); }
  .table-actions button:hover { color: var(--color-accent); }

  .form-grid { display: grid; grid-template-columns: 1fr 20rem; gap: var(--sp-6); }
  .form-main { display: flex; flex-direction: column; gap: var(--sp-4); }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4); }
  .lang-block { display: flex; flex-direction: column; gap: var(--sp-3); padding: var(--sp-4); background: var(--color-surface-alt); border: 1px solid var(--color-border); border-radius: var(--radius-lg); }
  .lang-block__label { font-size: var(--fs-caption); font-weight: var(--fw-semibold); color: var(--color-text-muted); letter-spacing: var(--tracking-widest); }
  .form-sidebar { display: flex; flex-direction: column; gap: var(--sp-6); }
  .form-section { padding: var(--sp-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); }
  .form-section__title { font-size: var(--fs-body-sm); font-weight: var(--fw-semibold); margin-bottom: var(--sp-3); }
  .form-actions { display: flex; flex-direction: column; gap: var(--sp-2); }

  .media-preview { position: relative; margin-bottom: var(--sp-3); }
  .media-preview img { width: 100%; border-radius: var(--radius-md); aspect-ratio: 4/3; object-fit: cover; }
  .media-preview button { position: absolute; top: 4px; right: 4px; display: flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem; border-radius: var(--radius-full); background: rgba(26,22,18,0.7); color: var(--color-white); }

  @media (max-width: 880px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
  }
</style>