/* ============================================================
   File type detection & validation
   ============================================================ */

import { UPLOAD_ALLOWED_MIME, UPLOAD_ALLOWED_EXTENSIONS } from './uploadLimits';

export function getExtension(filename: string): string {
  const parts = filename.split('.');
  return parts.length > 1 ? parts.pop()!.toLowerCase() : '';
}

export function isAllowedMime(mime: string): boolean {
  return (UPLOAD_ALLOWED_MIME as readonly string[]).includes(mime);
}

export function isAllowedExtension(ext: string): boolean {
  return (UPLOAD_ALLOWED_EXTENSIONS as readonly string[]).includes(ext.toLowerCase());
}

export function isImageFile(file: File): boolean {
  return isAllowedMime(file.type) || isAllowedExtension(getExtension(file.name));
}