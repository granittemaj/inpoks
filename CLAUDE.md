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

When you change a CSS or JS file, bump the `?v=` date on its links in `index.html` (all five share one value), so browsers load the new file instead of a cached copy.

## Architecture

- `index.html` is the static shell: header in one row (INPO's logo, `assets/img/logo.png`, white in dark mode; `<nav id="nav">` centred in a pill tray with short labels from `SHORT` in app.js, language, theme, search; no "Kryefaqja" item, the logo is home; no "Abonohu" button), `<main id="app">`, footer, a floating back-to-top button (`#to-top`, shown after a screen of scrolling) (about text, offices, contact, copyright; no site map and no wordmark), menu and search `<dialog>`s, toast. Navigation and the mobile menu are built from `NAV` in data.js. A small inline head script adds the `js` class and applies the saved theme before paint.
- `assets/js/data.js` defines `window.INPO_DATA`: news (`NEWS`, `CATS` filter labels, `CAT1` card labels), publications (`PUBS`, `PTYPES`, `PFILTERS`, `THEMES`, `INSTS`; infographics carry `data`: key figures, charts and source, transcribed from INPO's own infographic PDFs), `PROGS`, `DONORS` with `DONOR_LOGOS` (files in `assets/img/donors/`, copied from the old site), `PROJECTS`, `IMPACT`, `CASES`, `TEAM`, `BOARD`, transparency documents (`DCATS`, `DOCS`), `DATASETS` (illustrative values), About texts (`ABOUT`, `MISSION`, `VISION`, `VALUES`, `HISTORY`), `AREAS`, `SOCIALS` and `NAV` (the eight sections from INPO's brief). It holds all content.
- `assets/js/slides.js` shows an infographic's images (`slides` on a publication in data.js; in WordPress the publication's "Infographic images", meta `inpo_slides`) as a row to swipe with arrows, a counter and a full-screen view. `wordpress/inpo-core/assets/slides.js` is the same file: keep them identical. Example images in `assets/img/infografika/`.
- `assets/js/charts.js` draws a dataset as an interactive SVG chart plus a sortable table and CSV download. `wordpress/inpo-core/assets/charts.js` is the same file: keep them identical.
- `assets/js/app.js` is one IIFE:
  - **Helpers:** `cover()`, `pubCard()`, `newsRow()`, `projCard()`, `docCard()`, `member()`, `impactGrid()`, `subnav()`, `rings()`, `pageHead()`, `donorsHTML()`, `buildChrome()`.
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
- **Infinite scroll** (`theme.js`, `infinite()`): any list with a `.pager` (news, publications, projects, archives, search) loads the next page as the visitor nears the end and appends its items; the pager stays in the HTML for search engines and without JS, and a "Shfaq më shumë" button does the same by hand. The filters call `window.inpoInfinite(results)` after swapping results. The prototype shows every item on one page.
- News and publication filters share one engine, `inpo-core/includes/filters.php` (state from the URL, main query, chip counts, tags, noindex). Its markup matches `filtersHTML()` in `app.js`.
- Content import: `inpo-core/seed/content.json` is generated from `data.js` with `npm run seed`. Run it when `data.js` changes.
- The live inpo-ks.org is WordPress (WPML, Yoast, CPT UI). The plugin reuses its names: post type `publikimet`, taxonomy `tipi_i_publikimit`. Deploy in two steps: activate the plugin first and run INPO > Setup > Prepare (no public change while the old theme is active, `inpo_core_theme_active()` gates public behaviour), then activate the theme: go-live runs automatically, backs up what it changes and is restored if the old theme is reactivated. Permalinks are never changed on the live site.
- wp-admin strings (plugin and theme) are English and wrapped in `__()`; public site copy stays Albanian.
- Still no build step: block editor UI is plain JS on `wp.*` globals (`inpo-core/assets/editor.js`).
- Facebook posts from the old site (`inpo-core/includes/facebook.php`): four infographics posted as a news post holding only a Facebook box become Infografika publications at go-live (images from the old media library or bundled in `inpo-core/seed/infographics/`, INPO's text from the Facebook post, old address redirects, undone on restore). INPO's own Facebook videos are bundled in `inpo-core/seed/videos/` (H.264 with sound, plus a poster frame) and replace their Facebook box at go-live (`inpo_core_facebook_videos()`). Any other Facebook box in an article renders as a link button, so the site loads no Facebook scripts. Older `[video]` posts use the browser's own player; YouTube embeds play full width from youtube-nocookie.com.
- Publication covers: markup in `cover()` (app.js) and `inpo_core_cover()` (helpers.php) must stay in step. The PDF reader (`inpo-core/assets/reader.js`, pdf.js vendored, lazy-loaded) is WordPress-only; the prototype keeps its placeholder toast.
- INPO's brief (Oct 2026) is mapped in `inpo-core/includes/setup-brief.php`: news sections, publication categories, thematic fields, institutions, transparency document categories, known projects, and the keyword rules Prepare uses once on the live content. Projects (`inpo_project`, /projektet/), documents (`inpo_doc` + `inpo_doc_cat`, Transparenca page) and data (`inpo_dataset`, /te-dhenat/) are content types in `content-model.php`.
- **Editors change everything in wp-admin, no code and no Site Editor.** Pages hold page content; the lists (publications, projects, data, news) read heading, intro and extra sections from a Page via `inpo/listing` (`includes/listing-pages.php`); INPO > Help lists where each thing is edited. Structured content types (publications, projects, documents, data, team, donors) use the classic screen with INPO's box under the title (`inpo_main` meta box context, `inpo_core_form_types()`); the Dashboard has a "Publish on the INPO website" panel. When adding a feature, give it a wp-admin field (block sidebar, meta box, term field or INPO > Settings) and a row in the Help screen. Secure Custom Fields is not needed.
- **wp-admin and login in the design system** (`inpo/inc/admin-skin.php`, `assets/css/admin.css`, `admin-bar.css`, `login.css`): Geist, an ink sidebar and toolbar with the current item in indigo, pill buttons, soft surfaces and rounded corners; the login screen has the indigo rings panel ("Llogaridhënie, jo premtime.") beside the form. WordPress 7's block editor sets its colours inline from the user's colour scheme, so `admin.css` overrides `--wp-admin-theme-color` and the `--wpds-*` brand colours with `!important` on `[style*="--wp-admin-theme-color"]`. It lives in the theme, so deactivating the theme returns wp-admin to WordPress's own look.
- Run `npm run wp` to test in a real WordPress. Check every PHP file with `php -l`.

## Design system rules (approved direction: "Quiet Indigo")

- **One brand colour:** `--accent` (INPO indigo). Everything else is neutral. No gradients, no extra accent colours.
- **Type:** Geist only, sizes from the `--fs-*` scale (at 1440px: display 76 (home hero only), page title 52, headline 40, section 34, large card 24, card 20, title 18, lead 18; article text 17, body 16, small text 12 to 15). Do not add one-off heading sizes. Headlines have tight tracking (`letter-spacing: -0.045em` to `-0.055em`, weight 600).
- **Alignment:** everything starts on the left edge of `.wrap`. A section head is the kicker stacked over the heading, both at column 1 (`.sec-head`, `.pub-head-l`); never offset a heading into column 4. Page heads put the title left and the intro right (`.page-head`). Detail pages put the text in columns 1 to 7 with a sticky `.aside` in 9 to 12.
- **Home hero** (chosen by the client, Nov 2026, after a photo hero): one indigo panel (`--r-xl`) with INPO's rings, the kicker "Iniciativa për Progres", the statement in white (second line in `--accent-soft`), the intro, a white and an outline button, and the figures (reactions, publications, years) along its bottom, counting up on load. No photo in the hero: photos start with the news cards. Real photos from INPO's own events (`assets/img/photos/`, captions from the news posts) make the rest feel like an NGO; the home "Kush jemi" section (`FIELD`) shows three of them.
- **Home stays short, like INPO's sister sites (7arte, Not For Sale, Jahjaga):** the indigo hero with the figures, latest news as photo cards (`.ncards`), latest publications, programmes, Kush jemi (three photos), partners (logos in a plain row). Add a section only by taking one away.
- **News cards** (`ncard()` / `inpo_core_news_card()`) are photo cards: featured photo with the date on it (indigo ring panel when there is none), category as a dot label, title. No excerpt. Used on the home page, the news page and under articles.
- **Section labels** (`.kicker`) are 12px uppercase with a short indigo line before them.
- **Mobile menu:** sections closed by default (the current one in indigo), names at 18 to 21px, sub-links indented under a hairline, contact and language in the footer.
- **Header and section menu on scroll:** the header shrinks from 84 to 64px (`.site-header.scrolled`, `:root.is-scrolled` sets `--hdr-h`) after 48px of scrolling and grows back under 4px. Keep that gap: the header is in the page flow, so a single threshold makes it flip back and forth (the page blinks). A page's section menu (`subnav()` / `inpo_core_subnav()`, the links only, no "Në këtë faqe" label) sticks under the header, marks the section being read and gets smaller too (36px links, 44px on touch screens). Its links slide to the section (smooth scroll, off with reduced motion), which lands 24px below the menu (`scroll-padding-top` on `html:has(.wrap>.subnav)`).
- **Filter row:** on every list (Lajme, Publikimet, Projektet, Transparenca) one `.filters-bar` sticks under the header (under the section menu on Projektet) and gets smaller on scroll: the category chips (they scroll sideways when they do not fit), "Filtrat" (a `<details>` whose panel holds the selects: programme, theme, institution, year; its badge counts the ones set), the search and "Rendit". On phones Filtrat, search and Rendit are round icon buttons and the search opens over the row. The count and active filter tags stay below the row. `form.filters` is `display:contents` so the row stays stuck along the results. The full-width background is a `border-image` outset (never a positioned pseudo-element: it widens the page).
- **Board** (Bordi) is a ruled list of names and roles, never photo tiles (`boardList()` / `.board`).
- **Corner radii follow hierarchy:** `--r-xl` 40px for full-width panels, `--r-lg` 32px for major cards, `--r-md` 28px for covers and cards, `--r-sm`/`--r-xs` for tiles and inputs, 999px for pills and buttons.
- **Photos** use real INPO event photos only, never stock. Rounded like cards (`--r-xl` in the hero, `--r-md`/`--r-lg` in the field grid), captions as text, no gradients over images.
- **Signature motif:** thin concentric rings (`rings()` helper) on indigo surfaces. Use it sparingly, only on accent panels.
- **Motion:** one orchestrated moment, the home load sequence (headline reveal, the hero's rings drawing, the figures rolling like an odometer). The hero alone also has, at the client's request (Nov 2026): a slow pulse leaving the rings' centre (monitoring), the rings drifting with a mouse pointer, and figure links that lift on hover and roll their digits again. No underline under the accent line (the client removed it). Do not add scroll-triggered fade-ins or per-section animations. User-triggered transitions are fine. Always respect `prefers-reduced-motion` (all of the above switch off).
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

## Legal pages and cookie notice

- Privacy policy, terms of use and cookie policy: `LEGAL` in data.js (keys are the slugs `politika-e-privatesise`, `kushtet-e-perdorimit`, `politika-e-cookies`), routes `#/<slug>` via `legalPage()`. In WordPress `inpo-core/includes/legal.php` creates them once as ordinary Pages (go-live, fresh setup, or tidy on sites already live), sets the privacy page as WordPress's own, and the theme's footer links to the published ones (`inpo_legal_links` filter). They describe only what the site does: contact form (stored privately, emailed to INPO), a hashed IP for 10 minutes for rate limiting, theme choice and notice dismissal in local storage, no analytics or ads. Keep them true when adding anything that collects data or loads third-party content.
- The texts are proposals for INPO's legal adviser; unknowns ([data e hyrjes në fuqi], [afati i ruajtjes], [ofruesi i hostingut]) stay in brackets.
- Cookie notice: `COOKIE_NOTE` (prototype) / INPO > Settings > Privacy (WordPress); shown once at the bottom left until "Në rregull" (`inpo-cookies` in local storage). Only necessary storage is used, so it informs; there is nothing to accept. If tracking is ever added, it must become a real consent choice first.

## Open placeholders

- Official SVG logo (the header uses a 440px PNG from inpo-ks.org; an SVG would be sharper). DANIDA and Swiss Cooperation Office logos (shown as text).
- From INPO: the recommendations figure (impact panel), project names, periods and results, strategy and policy documents, real procurement datasets. The six datasets in data.js have made-up values (`sample:true`), shown with a "Të dhëna ilustruese" notice; never present them as INPO findings.
- Full article bodies, publication summaries, years and PDF links.
- EN translation (the switch is a `data-noop` stub).
- Legal pages: review by INPO's legal adviser, the effective date, retention period and host.
- Form backend for contact (client-side only in the prototype). There is no email signup: INPO decided against it (Oct 2026).
- `noindex` meta in `index.html` must be removed for production.

## Suggested next tasks

1. Swap the header PNG logo for the official SVG once INPO supplies it.
2. Add an EN content layer: `data.en.js` plus a `lang` state, with the switch updating `<html lang>` and the hash (`#/en/...`).
3. WordPress port: done in `wordpress/`, with INPO's structure. Next there: official SVG logo via Site Logo, WPML EN content, the content INPO still has to supply (see Open placeholders).
