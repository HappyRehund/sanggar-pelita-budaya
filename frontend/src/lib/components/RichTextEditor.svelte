<script lang="ts">
  import DOMPurify from 'dompurify';
  import {
    Bold, Italic, Underline, List, ListOrdered, Quote, Link,
    Image as ImageIcon, Heading2, Heading3, Minus, Undo, Redo, Code,
  } from '@lucide/svelte';

  interface Props {
    value?: string;
    placeholder?: string;
    onchange?: (html: string) => void;
  }

  let { value = '', placeholder = '', onchange }: Props = $props();

  let editorEl = $state<HTMLDivElement | null>(null);
  let canUndo = $state(false);
  let canRedo = $state(false);

  $effect(() => {
    if (editorEl && editorEl.innerHTML !== value) {
      editorEl.innerHTML = value;
    }
  });

  function exec(command: string, val?: string): void {
    document.execCommand(command, false, val);
    emitChange();
    updateUndoRedo();
  }

  function setHeading(tag: string): void {
    document.execCommand('formatBlock', false, tag);
    emitChange();
    updateUndoRedo();
  }

  function insertLink(): void {
    const url = window.prompt('Enter URL:');
    if (url) {
      exec('createLink', url);
    }
  }

  function insertImage(): void {
    const url = window.prompt('Enter image URL:');
    if (url) {
      exec('insertImage', url);
    }
  }

  function insertHR(): void {
    exec('insertHorizontalRule');
  }

  function emitChange(): void {
    if (!editorEl) return;
    const raw = editorEl.innerHTML;
    const clean = DOMPurify.sanitize(raw, {
      ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'u', 's', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'a', 'img', 'hr', 'code', 'pre'],
      ALLOWED_ATTR: ['href', 'src', 'alt', 'title'],
    });
    onchange?.(clean);
  }

  function updateUndoRedo(): void {
    canUndo = document.queryCommandEnabled('undo');
    canRedo = document.queryCommandEnabled('redo');
  }

  function handleInput(): void {
    emitChange();
    updateUndoRedo();
  }
</script>

<div class="rte">
  <div class="rte__toolbar" role="toolbar" aria-label="Text formatting">
    <button class="rte__btn" onclick={() => setHeading('h2')} aria-label="Heading 2" title="Heading 2">
      <Heading2 size={18} />
    </button>
    <button class="rte__btn" onclick={() => setHeading('h3')} aria-label="Heading 3" title="Heading 3">
      <Heading3 size={18} />
    </button>
    <span class="rte__divider"></span>
    <button class="rte__btn" onclick={() => exec('bold')} aria-label="Bold" title="Bold (Ctrl+B)">
      <Bold size={18} />
    </button>
    <button class="rte__btn" onclick={() => exec('italic')} aria-label="Italic" title="Italic (Ctrl+I)">
      <Italic size={18} />
    </button>
    <button class="rte__btn" onclick={() => exec('underline')} aria-label="Underline" title="Underline (Ctrl+U)">
      <Underline size={18} />
    </button>
    <span class="rte__divider"></span>
    <button class="rte__btn" onclick={() => exec('insertUnorderedList')} aria-label="Bullet list" title="Bullet list">
      <List size={18} />
    </button>
    <button class="rte__btn" onclick={() => exec('insertOrderedList')} aria-label="Numbered list" title="Numbered list">
      <ListOrdered size={18} />
    </button>
    <button class="rte__btn" onclick={() => exec('formatBlock', 'blockquote')} aria-label="Blockquote" title="Blockquote">
      <Quote size={18} />
    </button>
    <span class="rte__divider"></span>
    <button class="rte__btn" onclick={insertLink} aria-label="Insert link" title="Insert link">
      <Link size={18} />
    </button>
    <button class="rte__btn" onclick={insertImage} aria-label="Insert image" title="Insert image">
      <ImageIcon size={18} />
    </button>
    <button class="rte__btn" onclick={insertHR} aria-label="Horizontal rule" title="Horizontal rule">
      <Minus size={18} />
    </button>
    <span class="rte__divider"></span>
    <button class="rte__btn" onclick={() => exec('formatBlock', 'pre')} aria-label="Code block" title="Code block">
      <Code size={18} />
    </button>
    <button class="rte__btn" disabled={!canUndo} onclick={() => exec('undo')} aria-label="Undo" title="Undo (Ctrl+Z)">
      <Undo size={18} />
    </button>
    <button class="rte__btn" disabled={!canRedo} onclick={() => exec('redo')} aria-label="Redo" title="Redo (Ctrl+Y)">
      <Redo size={18} />
    </button>
  </div>

  <div
    bind:this={editorEl}
    class="rte__editor"
    contenteditable="true"
    role="textbox"
    aria-multiline="true"
    aria-label="Rich text editor"
    data-placeholder={placeholder}
    oninput={handleInput}
    onblur={handleInput}
  ></div>
</div>

<style>
  .rte {
    border: 1px solid var(--color-border-strong);
    border-radius: var(--radius-md);
    overflow: hidden;
  }

  .rte__toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: var(--sp-1);
    padding: var(--sp-2) var(--sp-3);
    border-bottom: 1px solid var(--color-border);
    background: var(--color-surface-alt);
  }

  .rte__btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: var(--radius-sm);
    color: var(--color-text-muted);
    transition: background-color var(--duration-fast) var(--ease-smooth), color var(--duration-fast) var(--ease-smooth);
  }

  .rte__btn:hover:not(:disabled) {
    background-color: var(--color-surface);
    color: var(--color-accent);
  }

  .rte__btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
  }

  .rte__divider {
    width: 1px;
    height: 1.25rem;
    background: var(--color-border-strong);
    margin: 0 var(--sp-1);
  }

  .rte__editor {
    min-height: 12rem;
    max-height: 40rem;
    overflow-y: auto;
    padding: var(--sp-4) var(--sp-5);
    font-size: var(--fs-body);
    line-height: var(--lh-relaxed);
    color: var(--color-text);
    outline: none;
  }

  .rte__editor:empty::before {
    content: attr(data-placeholder);
    color: var(--color-text-subtle);
    pointer-events: none;
  }

  .rte__editor :global(h2) {
    font-family: var(--font-serif);
    font-size: var(--fs-h3);
    font-weight: var(--fw-semibold);
    margin-top: var(--sp-4);
    margin-bottom: var(--sp-2);
  }

  .rte__editor :global(h3) {
    font-family: var(--font-serif);
    font-size: var(--fs-h4);
    font-weight: var(--fw-medium);
    margin-top: var(--sp-4);
    margin-bottom: var(--sp-2);
  }

  .rte__editor :global(blockquote) {
    border-left: 3px solid var(--color-gold);
    padding-left: var(--sp-4);
    margin: var(--sp-3) 0;
    color: var(--color-text-muted);
    font-style: italic;
  }

  .rte__editor :global(ul),
  .rte__editor :global(ol) {
    padding-left: var(--sp-5);
    margin: var(--sp-2) 0;
  }

  .rte__editor :global(li) {
    margin: var(--sp-1) 0;
  }

  .rte__editor :global(a) {
    color: var(--color-accent);
    text-decoration: underline;
  }

  .rte__editor :global(img) {
    max-width: 100%;
    border-radius: var(--radius-md);
    margin: var(--sp-3) 0;
  }

  .rte__editor :global(hr) {
    border: none;
    border-top: 1px solid var(--color-border);
    margin: var(--sp-4) 0;
  }

  .rte__editor :global(pre) {
    background: var(--color-surface-alt);
    padding: var(--sp-3) var(--sp-4);
    border-radius: var(--radius-sm);
    overflow-x: auto;
    font-size: var(--fs-body-sm);
  }

  .rte__editor:focus {
    box-shadow: inset 0 0 0 2px rgba(158, 42, 43, 0.08);
  }
</style>