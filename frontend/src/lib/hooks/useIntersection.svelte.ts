export function useIntersection(
  callback: () => void,
  options: IntersectionObserverInit = { threshold: 0.15, rootMargin: '0px 0px -10% 0px' }
) {
  let target = $state<HTMLElement | null>(null);
  let observer: IntersectionObserver | null = null;
  let hasIntersected = false;

  $effect(() => {
    if (!target || hasIntersected) return;

    if (observer) {
      observer.disconnect();
    }

    observer = new IntersectionObserver((entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting && !hasIntersected) {
          hasIntersected = true;
          callback();
          observer?.disconnect();
        }
      }
    }, options);

    observer.observe(target);

    return () => {
      observer?.disconnect();
      observer = null;
    };
  });

  return {
    get target() { return target; },
    set target(el: HTMLElement | null) { target = el; },
  };
}