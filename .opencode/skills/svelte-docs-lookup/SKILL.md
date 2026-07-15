---
name: svelte-docs-lookup
description: "Cara lookup Svelte 5 / SvelteKit docs via MCP. Triggers on: ragu Svelte 5 API (runes, $bindable, $derived.by, $state, snippets, lifecycle), mau cek perilaku runtime Svelte, betulkan istilah teknis sebelum jawab. SEKARANG JUGA cek svelte version terlebih dahulu di frontend/package.json (biasanya ^5.16). Aturan: list-sections dulu, cocokkan use_cases, baru get-documentation per batch. Jangan dok heart-of-the-system bila autofixer sudah cukup."
---

# Skill: Svelte docs lookup via MCP

## When to consult docs

Pattern: don't know the API. Examples:
- "Apakah `$derived.by` beda dengan `$derived(() => ...)`?"
- "How do I mark a prop as bindable in Svelte 5?"
- "Can I use `await` at the top of a Svelte component?"
- "Does `<svelte:head>` still work in Svelte 5?"
- "Mount lifecycle changes in runes mode?"

If you already know the answer or the autofixer can fix it, **don't** consult docs — it costs tokens.

## Two-step rule (mandatory order)

### Step 1: `svelte_list-sections`

Always call this first. It returns:

```
* title: $state, use_cases: component state, reactive..., path: docs/state.md
* title: $bindable, use_cases: forms, controlled inputs..., path: docs/bind.md
...
```

Analyze the `use_cases` field. Match the user's task against these. E.g., if building forms, look for `forms`, `controlled inputs`, `event handlers`. If you guess wrong, you waste a follow-up call.

Batch the candidates — choose ALL sections that might be relevant before doing Step 2.

### Step 2: `svelte_get-documentation`

Pass an array of section names/paths, e.g.:

```
svelte_get-documentation(section: ["$state", "$derived", "$bindable"])
```

This fetches the full text in one round-trip. Combine related sections to avoid follow-up clarification.

## Version note

This repo runs Svelte ^5.16 (see `frontend/package.json`). Sections relevant to:
- **Svelte 5**: `$state`, `$derived`, `$derived.by`, `$props`, `$bindable`, `$effect`, snippets, event handlers (`onclick=`), lifecycle (`onMount` still works in runes mode)
- **SvelteKit**: not used here (this is a SPA, not SvelteKit — see AGENTS.md decision #1). Skip any SvelteKit routing / load function docs.

## Cost-saving heuristics

| Symptom | Cheapest tool |
|---|---|
| Verifying my own Svelte code | `svelte-autofixer` (cheaper, more specific) |
| Quick type sanity check | `svelte-check` |
| Confirming a Svelte 5 API behavior | `svelte_get-documentation` (this skill) |
| "What does X do?" open question | `svelte_list-sections` then `svelte_get-documentation` |
| Visual confirmation | Playwright (separate skill) |

When in doubt between autofixer and docs: try the autofixer first. If it can't fix the issue, escalate to docs.

## Verification: cross-reference AGENTS.md first

This repo's `.opencode/AGENTS.md` documents overrides and known bugs (notably bug #4: `bind:value` forbidden on form components because of historical `props_invalid_value` failure). If docs say a feature is idiomatic, but AGENTS.md forbids it for this repo, **AGENTS.md wins**.

So the lookup path is:

```
1. Check AGENTS.md for relevant "Critical Patterns DO NOT BREAK" section
2. Consult docs to understand the API / behavior
3. If docs conflict with AGENTS.md → escalate to user, do not silently apply docs advice
```

## Pitfalls

- `$derived(() => expr)` vs `$derived.by(() => expr)`:
  The former is shorthand that returns the arrow function itself (anti-pattern). The latter is the explicit function form. Most of the time you want `$derived(expr)` (no arrow) or `$derived.by(() => { ... return value; })`.
- `bind:value` with `$bindable()`:
  Default `$bindable('')` does NOT make the prop optional in TS, and does NOT catch parent `undefined` values. See bug #4 in AGENTS.md.
- Svelte 4 vs Svelte 5 syntax:
  `on:click={...}` (Svelte 4) is replaced by `onclick={...}`. Same for `on:input`, `on:change`, etc. The autofixer flags these.
- SvelteKit features are not available (SPA): `+page.ts`, `+layout.ts`, `load`, `form actions`, etc. — all absent. Don't pull SvelteKit sections unless the question is genuinely about SvelteKit migration.