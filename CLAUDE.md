# CLAUDE.md

Guidance for Claude Code when working in this repository.

## What this is

A static, clickable prototype for the INPO (Iniciativa për Progres) website. It is hosted on GitHub Pages. It is the approved visual and UX reference for a future WordPress block theme, so **keep the design faithful**. Do not redesign unless explicitly asked.

## Hard constraints

- **No build step, no frameworks, no npm runtime dependencies.** Plain HTML, CSS and vanilla JS only. `package.json` is for dev tooling (serve, test) and must never become required to view the site.
- **Must work from `file://` and from a GitHub Pages project subpath** (`/repo-name/`):
  - Use relative asset paths only (`assets/...`, never `/assets/...`).
  - No ES modules (`type="module"` breaks on `file://`), no `fetch()` of local files.
  - Routing stays hash-based (`#/lajme`).
- The only external requests allowed are Google Fonts (Geist). No CDNs, trackers, remote images or embeds, including Google Maps iframes.
- **Never use em dashes** anywhere: copy, comments, docs. Use a colon, comma or period. `npm test` enforces this.

## Commands

```bash
npm run serve     # local server on :8000
npm install && npm test   # jsdom smoke test of every route + em dash check
```

Run `npm test` after any change to `app.js`, `data.js` or `index.html`.

## Architecture

- `index.html` is the static shell: header, `<main id="app">`, global newsletter section, footer, menu and search `<dialog>`s, toast. A small inline head script adds the `js` class and applies the saved theme before paint.
- `assets/js/data.js` defines `window.INPO_DATA` = `{ NEWS, CATS, PUBS, PTYPES, PFILTERS, PROGS, DONORS }`. It holds all content.
- `assets/js/app.js` is one IIFE:
  - **Helpers:** `cover()`, `pubCard()`, `newsRow()`, `rings()`, `pageHead()`, `donorsHTML()`.
  - **Page renderers** return HTML strings: `home`, `newsPage`, `articlePage`, `pubsPage`, `pubDetail`, `progPage`, `aboutPage`, `contactPage`, `notFound`.
  - **Binders** attach behaviour after render: `bindPubs`, `bindContact`.
  - **`render()`** is the router. It parses `location.hash` into `parts` and `q` (query), picks a renderer, sets `document.title`, updates `aria-current`, moves focus to `[data-focus]` on route change and runs the home intro.
  - **Global delegation** handles `data-noop` (placeholder links, optional `data-toast`), `data-scroll` (smooth scroll to an id), `data-copy` (copy URL) and `data-open-search`.
- `assets/css/styles.css`:
  - **Tokens** are defined 3 times: `:root` (light), `@media (prefers-color-scheme: dark) :root:not([data-theme="light"])`, and `:root[data-theme="dark"]`. When adding a token, add it to **all three**.
  - **Components** use tokens only. Do not introduce raw hex values in components, except `#FFFFFF` / `#0E0E1A` on elements that sit on the indigo accent in both themes.

### Adding a route

1. Write a renderer `function fooPage(q){ return pageHead(...) + '...'; }`. The page's main heading must carry `data-focus`.
2. Add a branch in `render()`, set `title`, and set `after` if it needs a binder.
3. If it is top-level, add a `data-nav="foo"` link in the header nav in `index.html` and in the mobile menu.
4. Add the route to `routes` in `scripts/smoke-test.mjs`.

## WordPress port (`wordpress/`)

- `wordpress/inpo/` is a block theme, `wordpress/inpo-core/` its companion plugin. Read `wordpress/README.md` first.
- Same markup and CSS as the prototype. `inpo/assets/css/inpo.css` = `assets/css/styles.css` verbatim plus a "WordPress integration" section at the end. **Any CSS change in the prototype must be mirrored there** (and vice versa for shared rules).
- Block renderers in `inpo-core/includes/render.php` mirror the renderers in `app.js` class for class. Header and footer markup lives in `inpo/inc/chrome.php`.
- Content import: `inpo-core/seed/content.json` is generated from `data.js`. Regenerate it when `data.js` changes.
- The live inpo-ks.org is WordPress (WPML, Yoast, CPT UI). The plugin reuses its names: post type `publikimet`, taxonomy `tipi_i_publikimit`. Deploy in two steps: activate the plugin first and run INPO > Setup > Prepare (no public change while the old theme is active, `inpo_core_theme_active()` gates public behaviour), then activate the theme: go-live runs automatically, backs up what it changes and is restored if the old theme is reactivated. Permalinks are never changed on the live site.
- wp-admin strings (plugin and theme) are English and wrapped in `__()`; public site copy stays Albanian.
- Still no build step: block editor UI is plain JS on `wp.*` globals (`inpo-core/assets/editor.js`).
- Run `npm run wp` to test in a real WordPress. Check every PHP file with `php -l`.

## Design system rules (approved direction: "Quiet Indigo")

- **One brand colour:** `--accent` (INPO indigo). Everything else is neutral. No gradients, no extra accent colours.
- **Type:** Geist only. Headlines are large with tight tracking (`letter-spacing: -0.045em` to `-0.055em`, weight 600).
- **Corner radii follow hierarchy:** `--r-xl` 40px for full-width panels, `--r-lg` 32px for major cards, `--r-md` 28px for covers and cards, `--r-sm`/`--r-xs` for tiles and inputs, 999px for pills and buttons.
- **Signature motif:** thin concentric rings (`rings()` helper) on indigo surfaces. Use it sparingly, only on accent panels.
- **Motion:** exactly one orchestrated moment, the home load sequence (headline reveal, ring draw, count-up). Do not add scroll-triggered fade-ins or per-section animations. User-triggered transitions are fine. Always respect `prefers-reduced-motion`.
- **Spacing:** sections use `.sec` (88 to 150px top padding). Content width is `.wrap` (max 1440, fluid side padding).

## Accessibility checklist (keep passing)

- Real `<a href>` for navigation and `<button>` for actions. Never put click handlers on divs.
- Every input has a `<label>`. Errors are inline text, `aria-invalid`, and focus moves to the first invalid field.
- Icon-only buttons have `aria-label`. Decorative SVGs have `aria-hidden="true"`.
- Text contrast is at least 4.5:1 in both themes. Check dark mode whenever you add colours.
- Touch targets are at least 44px.

## Content rules

- Copy is **Albanian**: sentence case, active voice, short.
- **Never invent facts about INPO** (numbers, dates, findings, names, donors). Everything in `data.js` comes from inpo-ks.org or public sources.
  - Unknown values go in `[square brackets]`.
  - Unknown article text is marked with `todo:true`, which renders a dashed "imported during migration" note.
- Known facts used:
  - Founded 2005 in Ferizaj.
  - Offices: Rr. "Qamil Hoxha", H32, nr. 09, kati IV, 10000 Prishtinë; Rr. "Latif Hasani", pn, 70000 Ferizaj.
  - Contact: +383 (0) 49 354 000, info@inpo-ks.org.
  - Two programmes: Qeverisje e mirë, Edukim demokratik.
  - News items and publication titles as listed in `data.js`.

## Open placeholders

- Official SVG logo and donor logos (currently text).
- Full article bodies, publication summaries, years and PDF links.
- EN translation (the switch is a `data-noop` stub).
- Form backends (contact and newsletter are client-side only).
- `noindex` meta in `index.html` must be removed for production.

## Suggested next tasks

1. Replace the text wordmark with the official SVG logo (inline in header and menu). Keep the footer giant wordmark as text.
2. Add an EN content layer: `data.en.js` plus a `lang` state, with the switch updating `<html lang>` and the hash (`#/en/...`).
3. Add a "Tender Watch" page (`#/tenderet`) listing monitored cases with status pills, reusing `.stat` and `.row` styles.
4. WordPress port: done in `wordpress/`. Next there: official SVG logo via Site Logo, Polylang EN content, PDF files on publications.
