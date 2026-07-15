export interface LightboxImage {
  src: string;
  alt: string;
  caption?: string;
}

export function useLightbox() {
  let isOpen = $state(false);
  let images = $state<LightboxImage[]>([]);
  let currentIndex = $state(0);

  const current = $derived(images[currentIndex] ?? null);
  const total = $derived(images.length);
  const hasPrev = $derived(currentIndex > 0);
  const hasNext = $derived(currentIndex < total - 1);

  function open(items: LightboxImage[], startIndex = 0): void {
    images = items;
    currentIndex = startIndex;
    isOpen = true;
    document.body.style.overflow = 'hidden';
  }

  function close(): void {
    isOpen = false;
    document.body.style.overflow = '';
  }

  function next(): void {
    if (hasNext) {
      currentIndex++;
    }
  }

  function prev(): void {
    if (hasPrev) {
      currentIndex--;
    }
  }

  function goTo(index: number): void {
    if (index >= 0 && index < total) {
      currentIndex = index;
    }
  }

  function handleKeydown(e: KeyboardEvent): void {
    if (!isOpen) return;
    if (e.key === 'Escape') close();
    else if (e.key === 'ArrowRight') next();
    else if (e.key === 'ArrowLeft') prev();
  }

  return {
    get isOpen() { return isOpen; },
    get images() { return images; },
    get currentIndex() { return currentIndex; },
    get current() { return current; },
    get total() { return total; },
    get hasPrev() { return hasPrev; },
    get hasNext() { return hasNext; },
    open,
    close,
    next,
    prev,
    goTo,
    handleKeydown,
  };
}