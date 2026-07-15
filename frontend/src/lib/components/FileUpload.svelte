<script lang="ts">
  import { UploadCloud, X, Image as ImageIcon } from '@lucide/svelte';
  import { isImageFile } from '$lib/constants/fileTypes';
  import { formatFileSize } from '$lib/utils';
  import { t } from '$lib/i18n/index.svelte';

  interface Props {
    accept?: string;
    multiple?: boolean;
    label?: string;
    hint?: string;
    error?: string;
    onselect: (files: File[]) => void;
  }

  let {
    accept = 'image/jpeg,image/png,image/webp',
    multiple = false,
    label,
    hint,
    error,
    onselect,
  }: Props = $props();

  let isDragging = $state(false);
  let selectedFiles = $state<File[]>([]);
  let inputEl = $state<HTMLInputElement | null>(null);

  function handleDrop(e: DragEvent): void {
    e.preventDefault();
    isDragging = false;
    const files = Array.from(e.dataTransfer?.files ?? []);
    processFiles(files);
  }

  function handleDragOver(e: DragEvent): void {
    e.preventDefault();
    isDragging = true;
  }

  function handleDragLeave(): void {
    isDragging = false;
  }

  function handleChange(e: Event): void {
    const target = e.target as HTMLInputElement;
    const files = Array.from(target.files ?? []);
    processFiles(files);
  }

  function processFiles(files: File[]): void {
    const valid = files.filter(isImageFile);
    selectedFiles = multiple ? [...selectedFiles, ...valid] : valid.slice(0, 1);
    onselect(selectedFiles);
  }

  function removeFile(index: number): void {
    selectedFiles = selectedFiles.filter((_, i) => i !== index);
    onselect(selectedFiles);
  }

  function openBrowse(): void {
    inputEl?.click();
  }
</script>

<div class="upload">
  {#if label}
    <label class="upload__label">{label}</label>
  {/if}

  <div
    class="upload__zone"
    class:upload__zone--dragging={isDragging}
    class:upload__zone--error={!!error}
    role="button"
    tabindex="0"
    aria-label={t('upload_drag_drop')}
    onclick={openBrowse}
    onkeydown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openBrowse(); } }}
    ondrop={handleDrop}
    ondragover={handleDragOver}
    ondragleave={handleDragLeave}
  >
    <input
      bind:this={inputEl}
      type="file"
      {accept}
      {multiple}
      class="upload__input"
      onchange={handleChange}
    />
    <div class="upload__icon">
      <UploadCloud size={36} strokeWidth={1.5} />
    </div>
    <p class="upload__text">{t('upload_drag_drop')}</p>
    <p class="upload__hint">{t('upload_supported')} · {t('upload_max_size')}</p>
  </div>

  {#if error}
    <p class="upload__error">{error}</p>
  {:else if hint}
    <p class="upload__hint-text">{hint}</p>
  {/if}

  {#if selectedFiles.length > 0}
    <div class="upload__previews">
      {#each selectedFiles as file, i (i)}
        <div class="upload__preview">
          <div class="upload__preview-icon">
            <ImageIcon size={20} />
          </div>
          <div class="upload__preview-info">
            <p class="upload__preview-name">{file.name}</p>
            <p class="upload__preview-size">{formatFileSize(file.size)}</p>
          </div>
          <button
            class="upload__preview-remove"
            onclick={(e) => { e.stopPropagation(); removeFile(i); }}
            aria-label={t('upload_remove')}
          >
            <X size={16} />
          </button>
        </div>
      {/each}
    </div>
  {/if}
</div>

<style>
  .upload {
    display: flex;
    flex-direction: column;
    gap: var(--sp-2);
  }

  .upload__label {
    font-size: var(--fs-body-sm);
    font-weight: var(--fw-medium);
    color: var(--color-text);
  }

  .upload__zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-2);
    padding: var(--sp-8) var(--sp-5);
    border: 2px dashed var(--color-border-strong);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: border-color var(--duration-fast) var(--ease-smooth), background-color var(--duration-fast) var(--ease-smooth);
    text-align: center;
  }

  .upload__zone:hover {
    border-color: var(--color-accent);
    background-color: var(--color-surface-alt);
  }

  .upload__zone--dragging {
    border-color: var(--color-accent);
    background-color: rgba(158, 42, 43, 0.05);
  }

  .upload__zone--error {
    border-color: var(--color-danger);
  }

  .upload__input {
    position: absolute;
    width: 0;
    height: 0;
    opacity: 0;
    pointer-events: none;
  }

  .upload__icon {
    color: var(--color-text-muted);
  }

  .upload__text {
    font-size: var(--fs-body-sm);
    color: var(--color-text);
    font-weight: var(--fw-medium);
  }

  .upload__hint {
    font-size: var(--fs-caption);
    color: var(--color-text-subtle);
  }

  .upload__error {
    font-size: var(--fs-caption);
    color: var(--color-danger);
  }

  .upload__hint-text {
    font-size: var(--fs-caption);
    color: var(--color-text-subtle);
  }

  .upload__previews {
    display: flex;
    flex-direction: column;
    gap: var(--sp-2);
    margin-top: var(--sp-2);
  }

  .upload__preview {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    padding: var(--sp-3) var(--sp-4);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-surface);
  }

  .upload__preview-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: var(--radius-sm);
    background: var(--color-surface-alt);
    color: var(--color-text-muted);
    flex-shrink: 0;
  }

  .upload__preview-info {
    flex: 1;
    min-width: 0;
  }

  .upload__preview-name {
    font-size: var(--fs-body-sm);
    color: var(--color-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .upload__preview-size {
    font-size: var(--fs-caption);
    color: var(--color-text-subtle);
  }

  .upload__preview-remove {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: var(--radius-sm);
    color: var(--color-text-muted);
    transition: color var(--duration-fast) var(--ease-smooth), background-color var(--duration-fast) var(--ease-smooth);
    flex-shrink: 0;
  }

  .upload__preview-remove:hover {
    color: var(--color-danger);
    background-color: var(--color-surface-alt);
  }
</style>