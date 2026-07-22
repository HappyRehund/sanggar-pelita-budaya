# Highlights — Admin Guide

This page explains every field in the **Highlights** admin form (`/admin/highlights`) and how to fill it correctly so a highlight renders well on the public site.

---

## 1. Two modes in one screen

The page has two views:

- **List view** — table of all highlights, with search, edit and delete actions, and a `+` button to create a new one.
- **Form view** — opens when you click `+` (create) or the pencil icon (edit). It has a main column and a sidebar.

The form is split visually into:

- **Main column (left)** — text/content fields
- **Sidebar (right)** — cover, gallery, status flags, and action buttons

---

## 2. Main column fields

### 2.1 Judul (Title)

- **What it is:** The headline of the highlight. Shown in the list, on the public card, and as the page `<h1>`.
- **How to fill:** Use a short, descriptive sentence. Prefer Title Case in Indonesian, e.g. *“Festival Tari Tradisional 2026”*.
- **Tip:** Keep it under ~80 characters so it doesn't wrap awkwardly in card grids.
- **Side effect:** Changing the title auto-regenerates the **Slug** below (only until you edit the slug manually).

### 2.2 Slug

- **What it is:** The URL identifier for the public highlight page (e.g. `/sorotan/traditional-dance-festival-2026`).
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
- **How to fill:** Native HTML date picker (`mm/dd/yyyy` placeholder). Leave blank if the highlight isn't tied to a specific date.
- **Used for:** Sorting and “happened on …” labels on the public site.

### 2.5 Lokasi (Location)

- **What it is:** Free-text venue/place, e.g. *“Jakarta, Indonesia”*.
- **How to fill:** City + country is usually enough. Optional — leave blank if not applicable (the list shows `—`).

### 2.6 Deskripsi Singkat (Short Description)

- **What it is:** A 1–2 sentence summary used for cards, previews, and social share snippets.
- **How to fill:** Plain text only (no rich text). Aim for ~120–160 characters. Don't repeat the title.

### 2.7 Deskripsi Lengkap (Full Description)

- **What it is:** The body of the highlight, rendered as rich text.
- **How to fill:** The toolbar offers: H2, H3, **B**, *I*, U, bullet list, numbered list, blockquote, link, image, horizontal rule, code, undo, redo.
- **Tip:**
  - Use H2/H3 to break the story into sections.
  - Embed images with the image button — they get inserted as URLs in the HTML, so upload them to the gallery first.
  - Don't paste raw HTML from random sources; the editor sanitises it.

### 2.8 URL YouTube

- **What it is:** Optional YouTube link to embed a video recap.
- **How to fill:** Full YouTube URL, e.g. `https://youtube.com/watch?v=...` or the short `https://youtu.be/...` form. Leave blank to skip video.
- **Format check:** The backend validates that it is a real YouTube URL.

### 2.9 Judul SEO (SEO Title)

- **What it is:** Custom `<title>` tag for the public page. Falls back to the regular title if empty.
- **How to fill:** ≤ 60 characters, include the highlight name and (optionally) the site name.

### 2.10 Deskripsi SEO (SEO Description)

- **What it is:** Custom `<meta name="description">`. Falls back to `short_description` if empty.
- **How to fill:** 120–160 characters, action-oriented, includes a few keywords from the body.

---

## 3. Sidebar — Media

Both media sections show a hint **“Simpan first to upload images.”** when the highlight has never been saved. You must save a draft once (any button) before uploads are accepted, because the server needs the highlight `id` to attach media to it.

### 3.1 Gambar Sampul (Cover)

- **What it is:** The hero image used on cards and the top of the public page.
- **How to fill:** Single image upload. Accepted: `image/jpeg`, `image/png`, `image/webp`. Recommended aspect ratio **4:3**, minimum 800×600.
- **Manage:** Click the `×` overlay to remove the current cover. Removing it doesn't delete the highlight; you can re-upload right away.

### 3.2 Gambar Galeri (Gallery)

- **What it is:** A set of additional photos shown below the body on the public page.
- **How to fill:** Multi-file upload (same accepted types as cover). Add as many as you like. Each tile has a `×` to delete just that photo.
- **Tip:** Square (1:1) crops look best in the 3-column mini grid; the component uses `object-fit: cover`.

---

## 4. Sidebar — Status flags

### 4.1 Unggulan (Featured)

- Marks this highlight as a **featured** item. On the public site this usually pins it to the top of the list, makes it eligible for the homepage hero, or both (depending on the public page template).
- Default: off. Toggle freely without affecting `published`.

### 4.2 Terbit (Published)

- Whether the highlight is **publicly visible**. Off = draft, only visible inside the admin.
- See the action buttons below for the relationship between this checkbox and the `Simpan Draf` / `Terbitkan` buttons.

---

## 5. Action buttons (sidebar bottom)

Three buttons, in this order:

| Button | What it does |
| --- | --- |
| **Simpan Draf** (outlined) | Save the form **without** forcing publish. Respects whatever `Terbit` checkbox state is currently set. Use while you're still editing. |
| **Terbitkan** (solid red) | Save **and** force `published = true` regardless of the checkbox. Use when the highlight is ready to go live. |
| **Batal** (ghost) | Discard changes and return to the list. Does **not** save. |

All three are disabled while a save is in flight (`saving = true`).

---

## 6. Save behaviour — what each path does

- **Create flow:** `+` → fill form → *Simpan Draf* or *Terbitkan* → record is created → list refreshes → you return to the list view. The first save assigns an `id`, after which you can upload cover and gallery.
- **Edit flow:** pencil icon → form pre-fills from the API → edit → save → list refreshes → return to list.
- **Errors:** If the backend returns validation errors, the first error message is surfaced as a toast (see `handleSave`). Fix the offending field and re-save.

---

## 7. Quick checklist before publishing

- [ ] Title is clear and in the right language
- [ ] Slug is clean and unique
- [ ] Category matches the story
- [ ] Event date and location are correct (or intentionally blank)
- [ ] Short description is ≤ 160 chars and doesn't duplicate the title
- [ ] Body is formatted with H2/H3, no orphan images
- [ ] Cover image is at least 800×600, 4:3
- [ ] Gallery has 3–8 photos if applicable
- [ ] YouTube link (if any) plays in an incognito tab
- [ ] SEO title and description are filled
- [ ] `Terbit` is on (or click `Terbitkan` to flip it automatically)
