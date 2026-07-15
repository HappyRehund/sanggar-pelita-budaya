/* ============================================================
   Debounce & throttle utilities
   ============================================================ */

export function debounce<T extends (...args: never[]) => void>(
  fn: T,
  delay = 300
): (...args: Parameters<T>) => void {
  let timer: ReturnType<typeof setTimeout> | null = null;
  return (...args: Parameters<T>) => {
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}

export function throttle<T extends (...args: never[]) => void>(
  fn: T,
  limit = 200
): (...args: Parameters<T>) => void {
  let inThrottle = false;
  let lastArgs: Parameters<T> | null = null;

  return (...args: Parameters<T>) => {
    if (inThrottle) {
      lastArgs = args;
      return;
    }
    fn(...args);
    inThrottle = true;
    setTimeout(() => {
      inThrottle = false;
      if (lastArgs) {
        fn(...lastArgs);
        lastArgs = null;
      }
    }, limit);
  };
}