/* ============================================================
   File size formatting
   ============================================================ */

export function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 B';
  if (bytes < 0) return '—';

  const units = ['B', 'KB', 'MB', 'GB'];
  const exponent = Math.min(
    units.length - 1,
    Math.floor(Math.log(bytes) / Math.log(1024))
  );
  const value = bytes / Math.pow(1024, exponent);
  const rounded = exponent === 0 ? value : value.toFixed(1);

  return `${rounded} ${units[exponent]}`;
}