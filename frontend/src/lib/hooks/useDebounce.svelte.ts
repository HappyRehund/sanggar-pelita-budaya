export function useDebounce<T>(value: () => T, delay = 300): () => T {
  let debounced = $state<T>(value());

  $effect(() => {
    const current = value();
    const timer = setTimeout(() => {
      debounced = current;
    }, delay);
    return () => clearTimeout(timer);
  });

  return () => debounced;
}