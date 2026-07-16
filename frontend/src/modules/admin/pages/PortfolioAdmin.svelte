<script lang="ts">
  import { onMount } from 'svelte';
  import { portfolioApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { notifications } from '$lib/stores/notification.svelte';
  import { categoryLabel } from '$lib/constants/categories';
  import { slugify } from '$lib/utils';
  import { formatDateInput, uploadUrl, imageUrl } from '$lib/utils';
  import { langStore } from '$lib/stores/lang.svelte';
  import type { PortfolioListSummary, PortfolioCategory, PortfolioMedia } from '$lib/types';
  import Button from '$lib/components/Button.svelte';
  import Input from '$lib/components/Input.svelte';
  import Textarea from '$lib/components/Textarea.svelte';
  import Select from '$lib/components/Select.svelte';
  import Checkbox from '$lib/components/Checkbox.svelte';
  import RichTextEditor from '$lib/components/RichTextEditor.svelte';
  import FileUpload from '$lib/components/FileUpload.svelte';
  import Badge from '$lib/components/Badge.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import ConfirmDialog from '../components/ConfirmDialog.svelte';
  import { Plus, Search, Pencil, Trash2, ArrowLeft, X } from '@lucide/svelte';

  let mode = $state<'list' | 'edit' | 'create'>('list');
  let items = $state<PortfolioListSummary[]>([]);
  let loading = $state(true);
  let search = $state('');
  let saving = $state(false);
  let deleteTarget = $state<PortfolioListSummary | null>(null);

  let formData = $state({
    title: '', slug: '', category: 'activity' as PortfolioCategory,
    short_description: '', content: '', event_date: '', location: '',
    youtube_url: '', featured: false, published: false,
    seo_title: '', seo_description: '',
  });
  let coverMedia = $state<PortfolioMedia | null>(null);
  let galleryMedia = $state<PortfolioMedia[]>([]);
  let slugTouched = $state(false);

  let editId = $state<number | null>(null);

  onMount(() => {
    loadList();
  });

  async function loadList(): Promise<void> {
    loading = true;
    try {
      const result = await portfolioApi.list({});
      items = result.items;
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      loading = false;
    }
  }

  function startCreate(): void {
    mode = 'create';
    editId = null;
    formData = { title: '', slug: '', category: 'activity', short_description: '', content: '', event_date: '', location: '', youtube_url: '', featured: false, published: false, seo_title: '', seo_description: '' };
    coverMedia = null;
    galleryMedia = [];
    slugTouched = false;
  }

  async function startEdit(item: PortfolioListSummary): Promise<void> {
    mode = 'edit';
    editId = item.id;
    loading = true;
    try {
      const full = await portfolioApi.getById(item.id);
      coverMedia = full.cover ?? null;
      galleryMedia = full.gallery ?? [];
      formData = {
        title: full.title, slug: full.slug, category: full.category,
        short_description: full.short_description, content: full.content,
        event_date: formatDateInput(full.event_date), location: full.location || '',
        youtube_url: full.youtube_url || '', featured: full.featured, published: full.published,
        seo_title: full.seo_title || '', seo_description: full.seo_description || '',
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
  }

  function onTitleInput(e: Event): void {
    formData.title = (e.target as HTMLInputElement).value;
    if (!slugTouched) formData.slug = slugify(formData.title);
  }

  function onSlugInput(e: Event): void {
    formData.slug = slugify((e.target as HTMLInputElement).value);
    slugTouched = true;
  }

  async function handleSave(publish = false): Promise<void> {
    saving = true;
    const payload = { ...formData, published: publish ? true : formData.published };
    try {
      if (editId !== null) {
        await portfolioApi.update(editId, payload);
        notifications.success(t('toast_portfolio_updated'));
      } else {
        const created = await portfolioApi.create(payload);
        editId = created.id;
        notifications.success(t('toast_portfolio_created'));
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
      await portfolioApi.delete(deleteTarget.id);
      notifications.success(t('toast_portfolio_deleted'));
      await loadList();
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      deleteTarget = null;
    }
  }

  async function handleCoverUpload(files: File[]): Promise<void> {
    if (!editId || files.length === 0) return;
    try {
      const media = await portfolioApi.uploadMedia(editId, files[0], 'cover');
      coverMedia = media;
      notifications.success(t('upload_success'));
    } catch {
      notifications.error(t('upload_failed'));
    }
  }

  async function handleGalleryUpload(files: File[]): Promise<void> {
    if (!editId || files.length === 0) return;
    try {
      for (const file of files) {
        const media = await portfolioApi.uploadMedia(editId, file, 'gallery');
        galleryMedia = [...galleryMedia, media];
      }
      notifications.success(t('upload_success'));
    } catch {
      notifications.error(t('upload_failed'));
    }
  }

  async function deleteMedia(media: PortfolioMedia): Promise<void> {
    try {
      await portfolioApi.deleteMedia(media.id);
      galleryMedia = galleryMedia.filter((m) => m.id !== media.id);
      if (coverMedia?.id === media.id) coverMedia = null;
      notifications.success(t('toast_deleted'));
    } catch {
      notifications.error(t('toast_error'));
    }
  }

  async function deleteCover(): Promise<void> {
    if (!coverMedia) return;
    try {
      await portfolioApi.deleteMedia(coverMedia.id);
      coverMedia = null;
      notifications.success(t('toast_deleted'));
    } catch {
      notifications.error(t('toast_error'));
    }
  }

  const filteredItems = $derived(search ? items.filter((i) => i.title.toLowerCase().includes(search.toLowerCase())) : items);
</script>

{#if mode === 'list'}
  <div class="admin-page">
    <div class="admin-page__header">
      <h1 class="admin-page__title">{t('admin_portfolio_title')}</h1>
      <Button variant="primary" size="sm" onclick={startCreate}>
        <Plus size={16} />
        {t('admin_portfolio_new')}
      </Button>
    </div>

    <div class="admin-search">
      <Search size={16} class="admin-search__icon" />
      <input type="search" placeholder={t('search_placeholder')} value={search} oninput={(e) => (search = (e.target as HTMLInputElement).value)} />
    </div>

    {#if loading}
      {#each Array(5) as _, i (i)}<Skeleton variant="rect" height="60px" />{/each}
    {:else if filteredItems.length === 0}
      <EmptyState title={t('admin_portfolio_empty')} />
    {:else}
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>{t('admin_portfolio_col_cover')}</th>
              <th>{t('admin_portfolio_col_title')}</th>
              <th>{t('admin_portfolio_col_category')}</th>
              <th>{t('admin_portfolio_col_location')}</th>
              <th>{t('admin_portfolio_col_status')}</th>
              <th>{t('admin_portfolio_col_actions')}</th>
            </tr>
          </thead>
          <tbody>
            {#each filteredItems as item (item.id)}
              <tr>
                <td>
                  <img src={item.cover ? uploadUrl(item.cover.filename) : imageUrl(`p-${item.slug}`, 80, 60)} alt="" class="table-thumb" />
                </td>
                <td>{item.title}</td>
                <td><Badge variant={item.category}>{categoryLabel(item.category, langStore.current)}</Badge></td>
                <td>{item.location || '—'}</td>
                <td>
                  <span class="status-badge" class:status-badge--published={item.published} class:status-badge--draft={!item.published}>
                    {item.published ? t('admin_portfolio_status_published') : t('admin_portfolio_status_draft')}
                  </span>
                </td>
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
      <h1 class="admin-page__title">{editId ? t('admin_portfolio_edit') : t('admin_portfolio_new')}</h1>
    </div>

    {#if loading}
      <Skeleton variant="rect" height="600px" />
    {:else}
      <div class="form-grid">
        <div class="form-main">
          <Input label={t('admin_portfolio_field_title')} value={formData.title} oninput={onTitleInput} placeholder="Traditional Dance Festival 2026" />
          <Input label={t('admin_portfolio_field_slug')} value={formData.slug} oninput={onSlugInput} placeholder="traditional-dance-festival-2026" hint={t('admin_portfolio_field_slug')} />
          <div class="form-row">
            <Select label={t('admin_portfolio_field_category')} value={formData.category} options={[{value:'achievement',label:t('category_achievement')},{value:'activity',label:t('category_activity')}]} onchange={(e) => (formData.category = (e.target as HTMLSelectElement).value as PortfolioCategory)} />
            <Input label={t('admin_portfolio_field_event_date')} type="date" value={formData.event_date} oninput={(e) => (formData.event_date = (e.target as HTMLInputElement).value)} />
          </div>
          <Input label={t('admin_portfolio_field_location')} value={formData.location} oninput={(e) => (formData.location = (e.target as HTMLInputElement).value)} placeholder="Jakarta, Indonesia" />
          <Textarea label={t('admin_portfolio_field_short_description')} value={formData.short_description} oninput={(e) => (formData.short_description = (e.target as HTMLTextAreaElement).value)} />
          <div class="form-field">
            <label class="form-label">{t('admin_portfolio_field_content')}</label>
            <RichTextEditor value={formData.content} placeholder="Write the full description..." onchange={(html) => (formData.content = html)} />
          </div>
          <Input label={t('admin_portfolio_field_youtube')} value={formData.youtube_url} oninput={(e) => (formData.youtube_url = (e.target as HTMLInputElement).value)} placeholder="https://youtube.com/watch?v=..." />
          <div class="form-row">
            <Input label={t('admin_portfolio_field_seo_title')} value={formData.seo_title} oninput={(e) => (formData.seo_title = (e.target as HTMLInputElement).value)} />
            <Input label={t('admin_portfolio_field_seo_description')} value={formData.seo_description} oninput={(e) => (formData.seo_description = (e.target as HTMLInputElement).value)} />
          </div>
        </div>

        <div class="form-sidebar">
          <div class="form-section">
            <h3 class="form-section__title">{t('admin_portfolio_field_cover')}</h3>
            {#if editId === null}
              <p class="form-hint">{t('save')} first to upload images.</p>
            {:else}
              {#if coverMedia}
                <div class="media-preview">
                  <img src={uploadUrl(coverMedia.filename)} alt="Cover" />
                  <button onclick={deleteCover} aria-label={t('upload_remove')}><X size={14} /></button>
                </div>
              {/if}
              <FileUpload label="" accept="image/jpeg,image/png,image/webp" onselect={handleCoverUpload} />
            {/if}
          </div>

          <div class="form-section">
            <h3 class="form-section__title">{t('admin_portfolio_field_gallery')}</h3>
            {#if editId === null}
              <p class="form-hint">{t('save')} first to upload images.</p>
            {:else}
              {#if galleryMedia.length > 0}
                <div class="gallery-mini">
                  {#each galleryMedia as media (media.id)}
                    <div class="gallery-mini__item">
                      <img src={uploadUrl(media.filename)} alt={media.alt_text || 'Gallery'} loading="lazy" />
                      <button onclick={() => deleteMedia(media)} aria-label={t('upload_remove')}><X size={12} /></button>
                    </div>
                  {/each}
                </div>
              {/if}
              <FileUpload label="" multiple accept="image/jpeg,image/png,image/webp" onselect={handleGalleryUpload} />
            {/if}
          </div>

          <div class="form-section">
            <Checkbox label={t('admin_portfolio_field_featured')} checked={formData.featured} onchange={(e) => (formData.featured = (e.target as HTMLInputElement).checked)} />
            <Checkbox label={t('admin_portfolio_field_published')} checked={formData.published} onchange={(e) => (formData.published = (e.target as HTMLInputElement).checked)} />
          </div>

          <div class="form-actions">
            <Button variant="secondary" size="md" onclick={() => handleSave(false)} disabled={saving}>{t('save_draft')}</Button>
            <Button variant="primary" size="md" onclick={() => handleSave(true)} disabled={saving}>{t('publish')}</Button>
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
  message={t('confirm_delete_portfolio')}
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
  .admin-search__icon { position: absolute; left: var(--sp-3); top: 50%; transform: translateY(-50%); color: var(--color-text-subtle); }
  .admin-search input { width: 100%; padding: var(--sp-2) var(--sp-4) var(--sp-2) var(--sp-8); border: 1px solid var(--color-border-strong); border-radius: var(--radius-md); font-size: var(--fs-body-sm); }
  .admin-search input:focus { outline: none; border-color: var(--color-accent); }

  .table-wrap { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--color-border); background: var(--color-surface); }
  .admin-table { width: 100%; border-collapse: collapse; }
  .admin-table th { text-align: left; padding: var(--sp-3) var(--sp-4); font-size: var(--fs-caption); font-weight: var(--fw-semibold); text-transform: uppercase; letter-spacing: var(--tracking-wide); color: var(--color-text-muted); border-bottom: 1px solid var(--color-border); }
  .admin-table td { padding: var(--sp-3) var(--sp-4); font-size: var(--fs-body-sm); border-bottom: 1px solid var(--color-border); }
  .table-thumb { width: 3rem; height: 2.25rem; object-fit: cover; border-radius: var(--radius-sm); }

  .status-badge { font-size: var(--fs-caption); padding: 2px 8px; border-radius: var(--radius-full); font-weight: var(--fw-medium); }
  .status-badge--published { background: rgba(74,124,78,0.12); color: var(--color-success); }
  .status-badge--draft { background: var(--color-surface-alt); color: var(--color-text-muted); }

  .table-actions { display: flex; gap: var(--sp-2); }
  .table-actions button { color: var(--color-text-muted); padding: 4px; border-radius: var(--radius-sm); transition: color var(--duration-fast) var(--ease-smooth); }
  .table-actions button:hover { color: var(--color-accent); }

  .form-grid { display: grid; grid-template-columns: 1fr 20rem; gap: var(--sp-6); }
  .form-main { display: flex; flex-direction: column; gap: var(--sp-4); }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4); }
  .form-sidebar { display: flex; flex-direction: column; gap: var(--sp-6); }
  .form-section { padding: var(--sp-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); }
  .form-section__title { font-size: var(--fs-body-sm); font-weight: var(--fw-semibold); margin-bottom: var(--sp-3); }
  .form-label { display: block; font-size: var(--fs-body-sm); font-weight: var(--fw-medium); color: var(--color-text); margin-bottom: var(--sp-2); }
  .form-hint { font-size: var(--fs-caption); color: var(--color-text-subtle); }
  .form-actions { display: flex; flex-direction: column; gap: var(--sp-2); }
  .form-field { display: flex; flex-direction: column; gap: var(--sp-2); }

  .media-preview { position: relative; margin-bottom: var(--sp-3); }
  .media-preview img { width: 100%; border-radius: var(--radius-md); aspect-ratio: 4/3; object-fit: cover; }
  .media-preview button { position: absolute; top: 4px; right: 4px; display: flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem; border-radius: var(--radius-full); background: rgba(26,22,18,0.7); color: var(--color-white); }

  .gallery-mini { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--sp-2); margin-bottom: var(--sp-3); }
  .gallery-mini__item { position: relative; }
  .gallery-mini__item img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: var(--radius-sm); }
  .gallery-mini__item button { position: absolute; top: 2px; right: 2px; display: flex; align-items: center; justify-content: center; width: 1.25rem; height: 1.25rem; border-radius: var(--radius-full); background: rgba(26,22,18,0.7); color: var(--color-white); }

  @media (max-width: 880px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
  }
</style>