---
name: i18n
description: "Manage translations for this Svelte SPA's custom i18n system. Use whenever the user mentions translations, i18n, langStore, t(), adding/translating keys, hardcoded strings, missing translations, language toggle, or the frontend/src/lib/i18n/{en,id}.json files. Triggers on: i18n, translation, translate, t(), langStore, hardcoded string, missing translation, language, bahasa, en.json, id.json, language key, Add [language] translation, is this string translated?"
---

# i18n for Sanggar Pelita Budaya

This project uses a **custom i18n system** (NOT Paraglide — see AGENTS.md decision #6 for why). All operations should preserve this architecture: flat JSON files + `t(key, vars?)` function. Do NOT migrate to Paraglide/inlang or introduce compile-time extraction without explicit user request.

## Project conventions (DO NOT BREAK)

### File layout
- Translation files: `frontend/src/lib/i18n/{en,id}.json`
- Loader: `frontend/src/lib/i18n/index.svelte.ts`
- **Base language**: `en` (English) — reference structure for integrity/validation checks
- **Second language**: `id` (Indonesian)
- Keep both files in sync — any add/delete must update BOTH

### Key style — FLAT `snake_case`
Keys are **flat dotted strings using underscores**, grouped by prefix. Examples:
```
site_name, site_tagline
nav_home, nav_admin
hello_title, hello_subtitle, hello_description
admin_title, admin_text
login_title, login_username, login_password
```
**Never use nested objects** (`{"nav": {"home": "..."}}`) — this project uses flat keys only. When using the MCP, tools may suggest `nested` style — override to `flat` or reject nested key suggestions.

### Interpolation
Uses `{var}` placeholder syntax:
```json
"items_count": "{count} items"
```
```svelte
t('items_count', { count: 3 })
```
When adding translations with placeholders, ensure the **same `{var}` names appear in both `en` and `id`** versions. The interpolator does not validate missing vars — output will show the literal `{var}` text.

### Usage in components
```svelte
<script>
  import { t, i18n } from '$lib/i18n/index.svelte';
</script>

<h1>{t('site_name')}</h1>
<button onclick={() => i18n.toggle()}>{t('lang_toggle')}</button>
```
**Never call `t()` outside Svelte reactive context** expecting reactivity — `t()` reads `i18n.current` which is `$state`, so it only updates inside Svelte components.

## MCP tool mapping

The `i18n-mcp` server is connected (configured in `.opencode/opencode.json`). Use these tools based on user intent:

### User asks "what's missing / is everything translated?"
1. `i18n_check_translation_integrity` with `baseLanguage: "en"` — fastest sanity check
2. If issues: `i18n_search_missing_translations` with `srcDir: "frontend/src"`, `frameworks: ["svelte"]` for code-vs-files diff
3. Report using a markdown table: | key | status | notes |

### User asks "add a new translation key" or "translate [string]"
1. Check if similar key exists first: `i18n_search_translation` with the English text or proposed key prefix
2. If new key: `i18n_add_translations` with:
   - `keyPath`: flat snake_case with appropriate prefix (`nav_`, `hero_`, `section_`, `about_`, `admin_`, `settings_`, etc.)
   - `translations`: `{"en": "English text", "id": "Indonesian text"}`
   - `validateStructure`: true
   - `conflictResolution`: "error" (refuse to overwrite — ask user first)
3. After adding, run `i18n_check_translation_integrity` to confirm both files stayed in sync.
4. **Remind the user**: "Restart `pnpm dev` or refresh the page — Svelte doesn't hot-reload JSON imports."

### User asks "extract hardcoded strings" / "find untranslated text in component"
1. `i18n_analyze_codebase` with `srcDir: "frontend/src"`, `frameworks: ["svelte"]`, `minConfidence: 0.6`
2. Report findings with proposed keys in the snake_case style above
3. **Do NOT auto-extract** — show user the proposed texts → keys → EN/ID translations and ask for confirmation
4. After confirmation, use `i18n_extract_to_translation` per file with `frameworks: ["svelte"]` and `replaceInFile: true`.

### User asks "rename / delete a key"
1. Use `i18n_delete_translations` with `checkDependencies: true`, `dryRun: true` first
2. Show user all the places in `frontend/src/` that reference the key (use `grep` tool with pattern `\bt\(['"]old_key['"]`)
3. After user confirms, delete for real, then update all `.svelte` files calling `t('old_key', ...)`.

### User asks "show structure / stats"
1. `i18n_get_stats` for quick counts
2. `i18n_explore_translation_structure` with `language: "en"`, `maxDepth: 1` for a flat key listing

### User asks "validate / organize files"
1. `i18n_validate_structure` with `baseLanguage: "en"`
2. If files drifted out of sync: `i18n_reorganize_translation_files` with `baseLanguage: "en"`, `sortKeys: true`, `backupFiles: true`
3. `i18n_cleanup_unused_translations` with `dryRun: true` first; ask before deleting.

## Common pitfalls

### 1. Keys with mixed casing
The project enforces lowercase snake_case. If the MCP suggests `nav.Home` or `nav_home_page_title_v2`, **reject silently and propose the project-style** equivalent (`nav_home`, `home_title`).

### 2. Plurals / count
There is **no pluralization system**. Keys like `items_count` accept `{count}` and render as `"{count} items"`. Do not introduce ICU MessageFormat or new plural libraries.

### 3. Component rewiring after key rename
After renaming any key, ALL `.svelte` files using `t('old_key', ...)` must be updated. Use `grep`:
```
pattern: \bt\(['"]old_key['"]
include: *.svelte
```

### 4. Adding a third language (e.g., `zh`)
- Create `frontend/src/lib/i18n/zh.json` with all keys (copy from `en.json` as starting point)
- Update `index.svelte.ts`: extend `Lang` type + adding to `dictionaries`
- Update `i18n.toggle()` — currently only flips en ↔ id, won't rotate to third language
- Update MCP config if needed; MCP will auto-detect the new file
- Verify with `i18n_check_translation_integrity`

### 5. Spaces and special chars in keys
Keys must match `[a-z][a-z0-9_]*`. Indonesian text with `/`, `:`, spaces in value is fine — only keys are constrained.

## Important: do NOT touch

- `frontend/src/lib/i18n/index.svelte.ts` — Svelte store mechanics are stable. Only modify when adding a third language.
- IDs of existing translations: renaming breaks runtime silently (`t('old_key')` returns the raw literal). Always grep first.
- The `STORAGE_KEY = 'sanggar_lang'` — used by existing users; changing it forgets their language preference.

## Verification after any change

Run this as the final sanity check before declaring "done":
```
i18n_check_translation_integrity(baseLanguage="en")
```
Expected output: `isValid: true`, `totalMissingKeys: 0` for all languages.