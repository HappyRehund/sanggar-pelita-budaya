---
name: verify-svelte-check
description: "Run svelte-check (or `pnpm check`) after EVERY edit to any .svelte or .ts file under frontend/src. Triggers on: edit/save .svelte / .ts, refactoring Svelte 5 components, after extracting helpers, after changing $state/$derived/$props, after migration, after converting handlers (oninput/onchange/bind), before reporting work as done, before git commit. Use to: verify types, catch pre-existing vs newly introduced errors, confirm refactor safety, validate AGENTS.md policy compliance. Always read output END-TO-END before reporting."
---

# Skill: Always verify with svelte-check

## When to run

Run `svelte-check` (or `pnpm check`) EVERY TIME after ANY of these:
- Edit a `.svelte` or `.ts` file under `frontend/src`
- Refactor `$state` / `$derived` / `$props` / `$bindable` declarations
- Add/remove/change imports
- Convert inline event handlers (`oninput`, `onchange`, `onclick`) to `bind:` or vice versa
- Extract/dead-code removal in components
- After running `svelte-autofixer` and applying its suggestions
- BEFORE reporting work as done to the user
- BEFORE `git commit`

## Inline LSP errors are often STALE

When using the `edit` or `write` tools, the editor LSP may report `ERROR [N:M] Cannot find name 'X'` immediately after the change blob — this is frequently a false positive based on cached state. **Do not treat the inline LSP diagnostics as ground truth.** Always confirm by running svelte-check from the shell.

## Command

```bash
cd frontend && npx svelte-check --threshold error 2>&1 | tail -30
```

For full diagnostics (including warnings):

```bash
cd frontend && npx svelte-check 2>&1 | tail -30
```

Equivalently (npm script defined in `frontend/package.json`):

```bash
cd frontend && pnpm check
```

Note: `pnpm check` runs the same thing without `--threshold`. Prefer the explicit `npx svelte-check --threshold error` form when you want to filter to errors only.

## Reading the output

Sample output:
```
/home/rehundy/.../frontend/src/routes/admin/GalleryList.svelte:13:7
Error: 'categories' is declared but its value is never read. (ts)
  let categories = $state<CategoryMeta[]>([]);
```

Each finding lists:
- File path
- Line:column
- Severity (`Error` / `Warning`)
- Rule (e.g., `(ts)` for TypeScript, slint, svelte)
- The offending line

Tail of output gives a summary: `svelte-check found N errors and M warnings in K files`.

## Triaging findings

For each diagnostic, classify it:

1. **Introduced by this edit** — fix immediately. The work isn't done.
2. **Pre-existing (touched file unrelated to current edit)** — note in the report but don't fix unless the user asked for a tidying pass. Fixing them mid-task muddies diffs and may break unrelated code.
3. **Stale LSP false positive** — confirm by re-running: often goes away after the next full svelte-check pass. Never "fix" something by changing code you don't understand to silence a diagnostic you can't reproduce in svelte-check.

## AGENTS.md policy check

When the refactor is in a domain covered by `.opencode/AGENTS.md` "Critical Patterns (DO NOT BREAK)" section — e.g. form components (`value` + `oninput`, NOT `bind:value`), admin route gating, image path handling — verify the change complies BEFORE running svelte-check. Type-check passing doesn't equal semantic correctness (see AGENTS.md bug #4: `bind:value` silently fails when parent value is `undefined`).

If the user's task conflicts with AGENTS.md, **STOP**, surface the conflict to the user, and ask for a decision. Do not silently proceed.

## Report format

When reporting back to the user after edits:

```
Verified with: `svelte-check --threshold error`
Result: 0 errors, 0 warnings (or: N errors, M warnings — all pre-existing in unrelated files)
```

If findings exist, group them as:
- New (introduced by this edit) — listed with proposed fix
- Pre-existing — listed with file:line, no action taken

## Failure modes to avoid

- Skipping svelte-check because the change "looks small". Even single-line edits can introduce type errors with strict TS settings.
- Trusting inline `<diagnostics>` block from the `edit` tool — frequently stale.
- Fixing pre-existing errors mid-task without user consent — creates unprompted diff noise.
- Editing `.svelte` files without re-running the check before reporting "done".
- Treating warnings as ignorable. `unused-import` / `unused-var` warnings are real cleanup debt. Address at minimum when finishing a task.