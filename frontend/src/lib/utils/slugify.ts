/* ============================================================
   Slug generation — converts titles to URL-safe slugs
   ============================================================ */

export function slugify(text: string): string {
  return text
    .toString()
    .toLowerCase()
    .trim()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export function ensureUniqueSlug(
  base: string,
  existing: string[] = [],
  ignore?: string
): string {
  const slug = slugify(base) || 'untitled';
  if (!existing.includes(slug) || slug === ignore) return slug;

  let counter = 1;
  let candidate = `${slug}-${counter}`;
  while (existing.includes(candidate) && candidate !== ignore) {
    counter += 1;
    candidate = `${slug}-${counter}`;
  }
  return candidate;
}