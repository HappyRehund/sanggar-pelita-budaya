/* ============================================================
   Image URL utilities
   Centralizes construction of upload URLs and dev placeholders.
   ============================================================ */

const API_BASE = import.meta.env.VITE_API_BASE ?? '';

export function uploadUrl(filename: string | null | undefined): string {
  if (!filename) return '/assets/images/placeholder.svg';
  if (filename.startsWith('http') || filename.startsWith('/')) return filename;
  return `${API_BASE}/uploads/${filename}`;
}

export function imageUrl(
  seed: string | number,
  width = 800,
  height = 600
): string {
  return `https://picsum.photos/seed/${seed}/${width}/${height}`;
}

export function galleryImageUrl(seed: string | number): string {
  const seeds = [
    'culture1', 'dance2', 'tradition3', 'festival4', 'art5',
    'heritage6', 'costume7', 'music8', 'batik9', 'stage10',
  ];
  const safe = String(seed).replace(/[^a-z0-9]/gi, '') || seeds[Math.floor(Math.random() * seeds.length)];
  return imageUrl(safe, 1200, 800);
}