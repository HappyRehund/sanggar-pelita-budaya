<script lang="ts">
  import { onMount } from 'svelte';
  import { organizationApi } from '$lib/api';
  import { t } from '$lib/i18n/index.svelte';
  import { notifications } from '$lib/stores/notification.svelte';
  import { uploadUrl, imageUrl } from '$lib/utils';
  import type { OrganizationMember } from '$lib/types';
  import Button from '$lib/components/Button.svelte';
  import Input from '$lib/components/Input.svelte';
  import Textarea from '$lib/components/Textarea.svelte';
  import Select from '$lib/components/Select.svelte';
  import Checkbox from '$lib/components/Checkbox.svelte';
  import FileUpload from '$lib/components/FileUpload.svelte';
  import Skeleton from '$lib/components/Skeleton.svelte';
  import EmptyState from '$lib/components/EmptyState.svelte';
  import ConfirmDialog from '../components/ConfirmDialog.svelte';
  import { Plus, Pencil, Trash2, ArrowLeft } from '@lucide/svelte';

  let mode = $state<'list' | 'edit' | 'create'>('list');
  let members = $state<OrganizationMember[]>([]);
  let loading = $state(true);
  let saving = $state(false);
  let deleteTarget = $state<OrganizationMember | null>(null);
  let editId = $state<number | null>(null);

  let formData = $state({
    name: '', position: '', biography: '', featured_slot: '' as string | number, display_order: 0, published: true,
  });
  let photo = $state<string | null>(null);

  onMount(() => loadList());

  async function loadList(): Promise<void> {
    loading = true;
    try {
      members = await organizationApi.list();
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      loading = false;
    }
  }

  function startCreate(): void {
    mode = 'create';
    editId = null;
    formData = { name: '', position: '', biography: '', featured_slot: '', display_order: 0, published: true };
    photo = null;
  }

  async function startEdit(member: OrganizationMember): Promise<void> {
    mode = 'edit';
    editId = member.id;
    formData = {
      name: member.name, position: member.position, biography: member.biography || '',
      featured_slot: member.featured_slot ?? '', display_order: member.display_order, published: member.published,
    };
    photo = member.photo;
  }

  function backToList(): void {
    mode = 'list';
    editId = null;
  }

  async function handleSave(): Promise<void> {
    saving = true;
    const payload = {
      ...formData,
      featured_slot: formData.featured_slot === '' ? null : Number(formData.featured_slot),
    };
    try {
      if (editId !== null) {
        await organizationApi.update(editId, payload);
        notifications.success(t('toast_org_updated'));
      } else {
        const created = await organizationApi.create(payload);
        editId = created.id;
        notifications.success(t('toast_org_created'));
      }
      await loadList();
      backToList();
    } catch (e) {
      const err = e as { errors?: Record<string, string>; message?: string };
      notifications.error(err.message || t('toast_error'));
    } finally {
      saving = false;
    }
  }

  async function handleDelete(): Promise<void> {
    if (!deleteTarget) return;
    try {
      await organizationApi.delete(deleteTarget.id);
      notifications.success(t('toast_org_deleted'));
      await loadList();
    } catch {
      notifications.error(t('toast_error'));
    } finally {
      deleteTarget = null;
    }
  }

  async function handlePhotoUpload(files: File[]): Promise<void> {
    if (!editId || files.length === 0) return;
    try {
      const updated = await organizationApi.uploadPhoto(editId, files[0]);
      photo = updated.photo;
      notifications.success(t('upload_success'));
    } catch {
      notifications.error(t('upload_failed'));
    }
  }

  const slotOptions = $derived([
    { value: '', label: '— None —' },
    { value: 1, label: 'Slot 1' },
    { value: 2, label: 'Slot 2' },
    { value: 3, label: 'Slot 3' },
    { value: 4, label: 'Slot 4' },
  ]);
</script>

{#if mode === 'list'}
  <div class="admin-page">
    <div class="admin-page__header">
      <h1 class="admin-page__title">{t('admin_org_title')}</h1>
      <Button variant="primary" size="sm" onclick={startCreate}>
        <Plus size={16} />
        {t('admin_org_new')}
      </Button>
    </div>

    {#if loading}
      {#each Array(3) as _, i (i)}<Skeleton variant="rect" height="80px" />{/each}
    {:else if members.length === 0}
      <EmptyState title={t('admin_org_empty')} />
    {:else}
      <div class="member-list">
        {#each members as member (member.id)}
          <div class="member-row">
            <img src={member.photo ? uploadUrl(member.photo) : imageUrl(`m-${member.id}`, 48, 48)} alt={member.name} class="member-row__photo" />
            <div class="member-row__info">
              <span class="member-row__name">
                {member.name}
                {#if member.featured_slot}
                  <span class="member-row__badge">★ {member.featured_slot}</span>
                {/if}
              </span>
              <span class="member-row__position">{member.position}</span>
            </div>
            <div class="member-row__actions">
              <button onclick={() => startEdit(member)} aria-label={t('edit')}><Pencil size={16} /></button>
              <button onclick={() => (deleteTarget = member)} aria-label={t('delete')}><Trash2 size={16} /></button>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>
{:else}
  <div class="admin-page">
    <div class="admin-page__header">
      <button class="back-btn" onclick={backToList}><ArrowLeft size={18} /> {t('back')}</button>
      <h1 class="admin-page__title">{editId ? t('admin_org_edit') : t('admin_org_new')}</h1>
    </div>

    <div class="org-form">
      <div class="org-form__main">
        <Input label={t('admin_org_field_name')} value={formData.name} oninput={(e) => (formData.name = (e.target as HTMLInputElement).value)} />
        <Input label={t('admin_org_field_position')} value={formData.position} oninput={(e) => (formData.position = (e.target as HTMLInputElement).value)} />
        <Textarea label={t('admin_org_field_biography')} value={formData.biography} oninput={(e) => (formData.biography = (e.target as HTMLTextAreaElement).value)} />
        <div class="form-row">
          <Select label={t('admin_org_field_featured_slot')} value={formData.featured_slot} options={slotOptions} onchange={(e) => (formData.featured_slot = (e.target as HTMLSelectElement).value)} />
          <Input label={t('admin_org_field_display_order')} type="number" value={String(formData.display_order)} oninput={(e) => (formData.display_order = Number((e.target as HTMLInputElement).value))} />
        </div>
        <Checkbox label={t('admin_org_field_published')} checked={formData.published} onchange={(e) => (formData.published = (e.target as HTMLInputElement).checked)} />
      </div>

      <div class="org-form__sidebar">
        <div class="form-section">
          <h3 class="form-section__title">{t('admin_org_field_photo')}</h3>
          {#if editId === null}
            <p class="form-hint">{t('save')} first to upload photo.</p>
          {:else}
            {#if photo}
              <div class="media-preview">
                <img src={uploadUrl(photo)} alt="" />
              </div>
            {/if}
            <FileUpload label="" accept="image/jpeg,image/png,image/webp" onselect={handlePhotoUpload} />
          {/if}
        </div>

        <div class="form-actions">
          <Button variant="primary" size="md" onclick={handleSave} disabled={saving}>{t('save')}</Button>
          <Button variant="ghost" size="md" onclick={backToList}>{t('cancel')}</Button>
        </div>
      </div>
    </div>
  </div>
{/if}

<ConfirmDialog
  open={deleteTarget !== null}
  title={t('delete')}
  message={t('confirm_delete_member')}
  confirmLabel={t('delete')}
  onconfirm={handleDelete}
  oncancel={() => (deleteTarget = null)}
/>

<style>
  .admin-page__header { display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--sp-6); gap: var(--sp-4); flex-wrap: wrap; }
  .admin-page__title { font-family: var(--font-serif); font-size: var(--fs-h2); font-weight: var(--fw-semibold); }
  .back-btn { display: inline-flex; align-items: center; gap: var(--sp-1); color: var(--color-text-muted); font-size: var(--fs-body-sm); }
  .back-btn:hover { color: var(--color-accent); }

  .member-list { display: flex; flex-direction: column; gap: var(--sp-2); }
  .member-row { display: flex; align-items: center; gap: var(--sp-4); padding: var(--sp-3) var(--sp-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); }
  .member-row__photo { width: 3rem; height: 3rem; border-radius: var(--radius-full); object-fit: cover; flex-shrink: 0; }
  .member-row__info { flex: 1; display: flex; flex-direction: column; }
  .member-row__name { font-weight: var(--fw-medium); font-size: var(--fs-body-sm); display: flex; align-items: center; gap: var(--sp-2); }
  .member-row__position { font-size: var(--fs-caption); color: var(--color-text-muted); }
  .member-row__badge { display: inline-flex; align-items: center; font-size: var(--fs-caption); font-weight: var(--fw-semibold); color: var(--color-ink); background: var(--color-gold); padding: 1px var(--sp-2); border-radius: var(--radius-full); }
  .member-row__actions { display: flex; gap: var(--sp-2); }
  .member-row__actions button { color: var(--color-text-muted); padding: 4px; border-radius: var(--radius-sm); }
  .member-row__actions button:hover { color: var(--color-accent); }

  .org-form { display: grid; grid-template-columns: 1fr 18rem; gap: var(--sp-6); }
  .org-form__main { display: flex; flex-direction: column; gap: var(--sp-4); }
  .org-form__sidebar { display: flex; flex-direction: column; gap: var(--sp-4); }
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4); }
  .form-section { padding: var(--sp-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); }
  .form-section__title { font-size: var(--fs-body-sm); font-weight: var(--fw-semibold); margin-bottom: var(--sp-3); }
  .form-hint { font-size: var(--fs-caption); color: var(--color-text-subtle); }
  .form-actions { display: flex; flex-direction: column; gap: var(--sp-2); }
  .media-preview { margin-bottom: var(--sp-3); }
  .media-preview img { width: 100%; border-radius: var(--radius-md); aspect-ratio: 1; object-fit: cover; }

  @media (max-width: 880px) {
    .org-form { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
  }
</style>