# Highlights — Admin Guide

This page explains every field in the **Highlights** admin form (`/admin/highlights`) and how to fill it so a highlight renders well on the public site.

Highlights are **auto-published** the moment you click **Save** — there is no draft state and no separate publish button. Fill the form, pick an image, save, done. Each highlight has exactly **one image** (no gallery).

---

## 1. Two modes in one screen

The page has two views:

- **List view** — table of all highlights, with search, edit and delete actions, and a `+` button to create a new one.
- **Form view** — opens when you click `+` (create) or the pencil icon (edit). It has a main column and a sidebar.

The form is split visually into:

- **Main column (left)** — text fields
- **Sidebar (right)** — the single image, and the Save / Cancel buttons

---

## 2. Main column fields

### 2.1 Judul (Title)

- **What it is:** The headline of the highlight. Shown in the list, on the public card, and as the page `<h1>`.
- **How to fill:** Use a short, descriptive sentence. Prefer Title Case in Indonesian, e.g. *"Festival Tari Tradisional 2026"*.
- **Tip:** Keep it under ~80 characters so it doesn't wrap awkwardly in card grids.
- **Side effect:** Changing the title auto-regenerates the **Slug** below (only until you edit the slug manually).

### 2.2 Slug

- **What it is:** The URL identifier for the public highlight page (e.g. `/highlights/traditional-dance-festival-2026`).
- **How to fill:** Lowercase, dash-separated, no spaces or special characters. The form will auto-slugify whatever you type.
- **Rule:** Must be unique. Once you touch this field, the title no longer overwrites it (`slugTouched = true`).
- **Caution:** Changing the slug after publishing will break any external links pointing to the old URL.

### 2.3 Kategori (Category)

- **What it is:** Two-value dropdown that affects how the highlight is grouped on the public site.
- **Options:**
  - `achievement` — Pencapaian (e.g. awards, certifications, milestones)
  - `activity` — Kegiatan (e.g. performances, workshops, events)
- **How to fill:** Pick the one that best matches the story. If unsure, default to `activity`.

### 2.4 Tanggal Acara (Event Date)

- **What it is:** When the event actually happened (or will happen).
- **How to fill:** Native HTML date picker (`yyyy-mm-dd`). Leave blank if the highlight isn't tied to a specific date.
- **Used for:** Sorting and "happened on …" labels on the public site.

### 2.5 Lokasi (Location)

- **What it is:** Free-text venue/place, e.g. *"Jakarta, Indonesia"*.
- **How to fill:** City + country is usually enough. Optional — leave blank if not applicable (the list shows `—`).

### 2.6 Deskripsi Singkat (Short Description)

- **What it is:** A 1–2 sentence summary used for cards, previews, social share snippets, and as the lead paragraph on the detail page.
- **How to fill:** Plain text only (no rich text). Aim for ~120–160 characters. Don't repeat the title.
- **Note:** This is the only body text on the public detail page — there is no separate rich-text description anymore.

### 2.7 URL YouTube

- **What it is:** Optional YouTube link to embed a video recap on the public detail page.
- **How to fill:** Full YouTube URL, e.g. `https://youtube.com/watch?v=...` or the short `https://youtu.be/...` form. Leave blank to skip the video.
- **Format check:** The backend validates that it is a real URL.

### 2.8 Judul SEO (SEO Title)

- **What it is:** Custom `<title>` tag for the public page. Falls back to the regular title if empty.
- **How to fill:** ≤ 60 characters, include the highlight name and (optionally) the site name.

### 2.9 Deskripsi SEO (SEO Description)

- **What it is:** Custom `<meta name="description">`. Falls back to `short_description` if empty.
- **How to fill:** 120–160 characters, action-oriented, includes a few keywords from the body.

---

## 3. Sidebar — Image

A highlight has **one image**. It is used as the card thumbnail, the hero on the public detail page, the social-share (`og:image`) fallback, and in the homepage Highlights collage. Clicking the hero on the detail page opens it in a lightbox.

### 3.1 Picking the image

- **In create mode:** pick the image before saving — it is staged client-side and uploaded together with the highlight when you click **Save**. A preview shows the selected file.
- **In edit mode:** the current image is shown; picking a new file uploads it immediately to the existing highlight.
- **Accepted types:** `image/jpeg`, `image/png`, `image/webp`. Recommended aspect ratio **4:3**, minimum 800×600.
- **Remove:** click the `×` overlay on the preview. In create mode this just clears the staged file; in edit mode it deletes the saved image from the server.

---

## 4. Action buttons (sidebar bottom)

Two buttons, in this order:

| Button | What it does |
| --- | --- |
| **Simpan** (solid red) | Save the form. Creates the highlight (if new) or updates it (if editing); if a cover image was staged during create, it is uploaded in the same action. Then returns to the list. The highlight is immediately public. |
| **Batal** (ghost) | Discard changes and return to the list. Does **not** save. |

Both are disabled while a save is in flight (`saving = true`).

---

## 5. Save behaviour — what each path does

- **Create flow (one shot):** `+` → fill form → pick image → *Simpan* → the highlight is created, the staged image is uploaded, and you return to the list. No second step needed.
- **Edit flow:** pencil icon → form pre-fills from the API (image preview shows the saved cover) → edit text and/or replace the image → save → list refreshes → return to list.
- **Errors:** If the backend returns validation errors, the first error message is surfaced as a toast (see `handleSave`). Fix the offending field and re-save.

---

## 6. Quick checklist before saving

- [ ] Title is clear and in the right language
- [ ] Slug is clean and unique
- [ ] Category matches the story
- [ ] Event date and location are correct (or intentionally blank)
- [ ] Short description is ≤ 160 chars, doesn't duplicate the title, and reads well as the only body text
- [ ] Image is at least 800×600, 4:3 (or intentionally left blank — the public site falls back to a placeholder)
- [ ] YouTube link (if any) plays in an incognito tab
- [ ] SEO title and description are filled (or intentionally left blank for fallbacks)