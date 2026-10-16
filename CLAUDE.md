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

- `index.html` is the static shell: header in one row (INPO's logo, `assets/img/logo.png`, white in dark mode; `<nav id="nav">` centred in a pill tray with short labels from `SHORT` in app.js, language, theme, search; no "Kryefaqja" item, the logo is home; no "Abonohu" button), `<main id="app">`, footer, a floating back-to-top button (`#to-top`, shown after a screen of scrolling) (about text, offices, contact, copyright; no site map and no wordmark), menu and search `<dialog>`s, toast. Navigation and the mobile menu are built from `NAV` in data.js. A small inline head script adds the `js` class and applies the saved theme before paint.
- `assets/js/data.js` defines `window.INPO_DATA`: news (`NEWS`, `CATS` filter labels, `CAT1` card labels), publications (`PUBS`, `PTYPES`, `PFILTERS`, `THEMES`, `INSTS`; infographics carry `data`: key figures, charts and source, transcribed from INPO's own infographic PDFs), `PROGS`, `DONORS` with `DONOR_LOGOS` (files in `assets/img/donors/`, copied from the old site), `PROJECTS`, `IMPACT`, `CASES`, `TEAM`, `BOARD`, transparency documents (`DCATS`, `DOCS`), `DATASETS` (illustrative values), About texts (`ABOUT`, `MISSION`, `VISION`, `VALUES`, `HISTORY`), `AREAS`, `SOCIALS` and `NAV` (the eight sections from INPO's brief). It holds all content.
- `assets/js/slides.js` shows an infographic's images (`slides` on a publication in data.js; in WordPress the publication's "Infographic images", meta `inpo_slides`) as a row to swipe with arrows, a counter and a full-screen view. `wordpress/inpo-core/assets/slides.js` is the same file: keep them identical. Example images in `assets/img/infografika/`.
- `assets/js/charts.js` draws a dataset as an interactive SVG chart plus a sortable table and CSV download. `wordpress/inpo-core/assets/charts.js` is the same file: keep them identical.
- `assets/js/app.js` is one IIFE:
  - **Helpers:** `cover()`, `pubCard()`, `newsRow()`, `projCard()`, `docRow()`, `member()`, `impactGrid()`, `subnav()`, `rings()`, `pageHead()`, `donorsHTML()`, `buildChrome()`.
  - **Page renderers** return HTML strings: `home`, `newsPage`, `articlePage`, `pubsPage`, `pubDetail`, `progPage`, `aboutPage`, `projectsPage`, `projectPage`, `transparencyPage`, `dataPage`, `datasetPage`, `contactPage`, `notFound`.
  - **Binders** attach behaviour after render: `bindFilters(kind, q)` (Lajme and Publikimet: search, chips with counts, selects for programme, thematic field, institution, year and order, active-filter tags, state in the hash via `replaceState`), `bindTransparency` (year filter), `bindContact`.
  - **`render()`** is the router. It parses `location.hash` into `parts` and `q` (query), picks a renderer, sets `document.title`, updates `aria-current`, moves focus to `[data-focus]` on route change and runs the home intro. `?seksioni=<id>` scrolls to a section of the page and focuses its heading (the menu's sub-items, e.g. `#/rreth-nesh?seksioni=ekipi`).
  - **Global delegation** handles `data-noop` (placeholder links, optional `data-toast`), `data-scroll` (smooth scroll to an id), `data-copy` (copy URL) and `data-open-search`.
- `assets/css/styles.css`:
  - **Tokens** are defined 3 times: `:root` (light), `@media (prefers-color-scheme: dark) :root:not([data-theme="light"])`, and `:root[data-theme="dark"]`. When adding a colour token, add it to **all three**. The type scale (`--fs-*`) does not change with the theme, so it lives only in the first `:root`.
  - **Components** use tokens only. Do not introduce raw hex values in components, except `#FFFFFF` / `#0E0E1A` on elements that sit on the indigo accent in both themes.

### Adding a route

1. Write a renderer `function fooPage(q){ return pageHead(...) + '...'; }`. The page's main heading must carry `data-focus`.
2. Add a branch in `render()`, set `title`, and set `after` if it needs a binder.
3. If it belongs in the menu, add it to `NAV` in data.js (header and mobile menu follow).
4. Add the route to `routes` in `scripts/smoke-test.mjs`.

## WordPress port (`wordpress/`)

- `wordpress/inpo/` is a block theme, `wordpress/inpo-core/` its companion plugin. Read `wordpress/README.md` first.
- Same markup and CSS as the prototype. `inpo/assets/css/inpo.css` = `assets/css/styles.css` verbatim plus a "WordPress integration" section at the end. **Any CSS change in the prototype must be mirrored there** (and vice versa for shared rules).
- Block renderers in `inpo-core/includes/render.php` mirror the renderers in `app.js` class for class. Header and footer markup lives in `inpo/inc/chrome.php`.
- News and publication filters share one engine, `inpo-core/includes/filters.php` (state from the URL, main query, chip counts, tags, noindex). Its markup matches `filtersHTML()` in `app.js`.
- Content import: `inpo-core/seed/content.json` is generated from `data.js`. Regenerate it when `data.js` changes.
- The live inpo-ks.org is WordPress (WPML, Yoast, CPT UI). The plugin reuses its names: post type `publikimet`, taxonomy `tipi_i_publikimit`. Deploy in two steps: activate the plugin first and run INPO > Setup > Prepare (no public change while the old theme is active, `inpo_core_theme_active()` gates public behaviour), then activate the theme: go-live runs automatically, backs up what it changes and is restored if the old theme is reactivated. Permalinks are never changed on the live site.
- wp-admin strings (plugin and theme) are English and wrapped in `__()`; public site copy stays Albanian.
- Still no build step: block editor UI is plain JS on `wp.*` globals (`inpo-core/assets/editor.js`).
- Publication covers: markup in `cover()` (app.js) and `inpo_core_cover()` (helpers.php) must stay in step. The PDF reader (`inpo-core/assets/reader.js`, pdf.js vendored, lazy-loaded) is WordPress-only; the prototype keeps its placeholder toast.
- INPO's brief (Oct 2026) is mapped in `inpo-core/includes/setup-brief.php`: news sections, publication categories, thematic fields, institutions, transparency document categories, known projects, and the keyword rules Prepare uses once on the live content. Projects (`inpo_project`, /projektet/), documents (`inpo_doc` + `inpo_doc_cat`, Transparenca page) and data (`inpo_dataset`, /te-dhenat/) are content types in `content-model.php`.
- **Editors change everything in wp-admin, no code and no Site Editor.** Pages hold page content; the lists (publications, projects, data, news) read heading, intro and extra sections from a Page via `inpo/listing` (`includes/listing-pages.php`); INPO > Help lists where each thing is edited. Structured content types (publications, projects, documents, data, team, donors) use the classic screen with INPO's box under the title (`inpo_main` meta box context, `inpo_core_form_types()`); the Dashboard has a "Publish on the INPO website" panel. When adding a feature, give it a wp-admin field (block sidebar, meta box, term field or INPO > Settings) and a row in the Help screen. Secure Custom Fields is not needed.
- Run `npm run wp` to test in a real WordPress. Check every PHP file with `php -l`.

## Design system rules (approved direction: "Quiet Indigo")

- **One brand colour:** `--accent` (INPO indigo). Everything else is neutral. No gradients, no extra accent colours.
- **Type:** Geist only, sizes from the `--fs-*` scale (at 1440px: display 108 (home hero only), page title 64, headline 48, section 40, large card 28, card 22, title 20, lead 18; body 16, small text 12 to 15). Do not add one-off heading sizes. Headlines have tight tracking (`letter-spacing: -0.045em` to `-0.055em`, weight 600). Page heads and section heads align on the last baseline.
- **Home hero**: statement, lead and actions on the left, a real photo of INPO's work on the right (`HERO_PHOTO`). No badge or pill above the headline: the client does not want generic "AI landing page" tropes. Real photos from INPO's own events (`assets/img/photos/`, captions from the news posts) make it feel like an NGO; the home "Kush jemi" section (`FIELD`) shows five of them.
- **Board** (Bordi) is a ruled list of names and roles, never photo tiles (`boardList()` / `.board`).
- **Corner radii follow hierarchy:** `--r-xl` 40px for full-width panels, `--r-lg` 32px for major cards, `--r-md` 28px for covers and cards, `--r-sm`/`--r-xs` for tiles and inputs, 999px for pills and buttons.
- **Photos** use real INPO event photos only, never stock. Rounded like cards (`--r-xl` in the hero, `--r-md`/`--r-lg` in the field grid), captions as text, no gradients over images.
- **Signature motif:** thin concentric rings (`rings()` helper) on indigo surfaces. Use it sparingly, only on accent panels.
- **Motion:** exactly one orchestrated moment, the home load sequence (headline reveal, ring draw, count-up). Do not add scroll-triggered fade-ins or per-section animations. User-triggered transitions are fine. Always respect `prefers-reduced-motion`.
- **Spacing:** sections use `.sec` (56 to 96px top padding). Content width is `.wrap` (max 1440, fluid side padding).

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
  - Cards and lists hide bracketed values (`known()` in app.js, `inpo_core_is_placeholder()` in PHP); detail pages still show them so the gaps stay visible.
  - Unknown article text is marked with `todo:true`, which renders a dashed "imported during migration" note.
- Known facts used:
  - Founded 2005 in Ferizaj.
  - Offices: Rr. "Qamil Hoxha", H32, nr. 09, kati IV, 10000 Prishtinë; Rr. "Latif Hasani", pn, 70000 Ferizaj.
  - Contact: +383 (0) 49 354 000, info@inpo-ks.org.
  - Two programmes: Qeverisje e mirë, Edukim demokratik.
  - News items and publication titles as listed in `data.js`.

## Open placeholders

- Official SVG logo (the header uses a 440px PNG from inpo-ks.org; an SVG would be sharper). DANIDA and Swiss Cooperation Office logos (shown as text).
- From INPO: the recommendations figure (impact panel), project names, periods and results, strategy and policy documents, real procurement datasets. The six datasets in data.js have made-up values (`sample:true`), shown with a "Të dhëna ilustruese" notice; never present them as INPO findings.
- Full article bodies, publication summaries, years and PDF links.
- EN translation (the switch is a `data-noop` stub).
- Form backend for contact (client-side only in the prototype). There is no email signup: INPO decided against it (Oct 2026).
- `noindex` meta in `index.html` must be removed for production.

## Suggested next tasks

1. Swap the header PNG logo for the official SVG once INPO supplies it.
2. Add an EN content layer: `data.en.js` plus a `lang` state, with the switch updating `<html lang>` and the hash (`#/en/...`).
3. WordPress port: done in `wordpress/`, with INPO's structure. Next there: official SVG logo via Site Logo, WPML EN content, the content INPO still has to supply (see Open placeholders).
