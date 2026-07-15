import { debounce } from '$lib/utils';

export function useSearch(initialValue = '', delay = 300) {
  let value = $state(initialValue);
  let debouncedValue = $state(initialValue);

  const debouncedUpdate = debounce((v: string) => {
    debouncedValue = v;
  }, delay);

  function setValue(v: string): void {
    value = v;
    debouncedUpdate(v);
  }

  return {
    get value() { return value; },
    get debouncedValue() { return debouncedValue; },
    set: setValue,
    reset() {
      value = '';
      debouncedValue = '';
    },
  };
}