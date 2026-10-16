# INPO for WordPress

The approved prototype, rebuilt as a WordPress **block theme** (`inpo/`) and a companion **plugin** (`inpo-core/`). The markup and CSS are the prototype's, class for class, so every page looks the same at every screen size.

| Folder | What it is | Owns |
|---|---|---|
| `inpo/` | Block theme | Design tokens and CSS, `theme.json`, templates, header and footer, page patterns, UI script (menu, search overlay, dark mode, home intro), Geist font (self-hosted) |
| `inpo-core/` | Plugin | Content model (publications, programmes, donors), dynamic blocks, contact form and newsletter (with storage), REST API, organisation settings, one-click content import |

Both are required. Content lives in the plugin so it survives a theme change.

Requirements: WordPress 6.6 or newer, PHP 7.4 or newer. No build step, no npm at runtime.

---

## Install on the live inpo-ks.org

The live site already holds everything: news, publications with their PDFs, media, WPML translations and Yoast SEO data. The plugin uses the **same post type and taxonomy names** as the live site (`publikimet`, `tipi_i_publikimit`), so all of it shows up in the new design as it is. wp-admin screens of the theme and plugin are in English; the public site stays Albanian.

**Before you start:** take a full backup (files and database). Build the zips with `npm run wp:zip`.

### Step 1. Prepare, while the old theme is still live

1. **Plugins > Add New > Upload Plugin**: upload `inpo-core.zip` and activate it. Do not activate the theme yet.
   Visitors see no change: the plugin holds back everything that affects the public site until the INPO theme is active (tested: every public page is identical before and after).
2. **INPO > Setup > Prepare the data.** It maps the live content onto INPO's brief, without deleting or renaming anything:
   - the six **publication categories** (Raporte të monitorimit, Hulumtime dhe studime, Analiza të politikave publike, Analiza ligjore, Infografika, Rekomandime për politika publike) as labels on the existing types plus the two new ones; "Raporte për INPO" and job calls leave the library;
   - **thematic fields** and **institutions** on publications, read from their titles (e.g. OShP, Komunat, Prokurimi publik), for the library filters;
   - the five **news sections** (Aktivitete, Komunikata për media, Reagime dhe qëndrime publike, Opinione dhe artikuj, INPO në media), filled from the old categories, tags and titles; impact examples ("Pas reagimit të INPO-s...") are marked;
   - the programmes with their **objectives** and main activities, and programme links on news and publications;
   - **Transparency documents**: the audit reports and the 2013 work report, copied from "Raporte për INPO" (same PDFs);
   - **projects** known from the site, as drafts; **Team** and **Donors**, copied from the old "Anëtarët" and "Donatorët" entries.
   Keyword sorting runs once (on the replica: 22 reactions, 26 press releases, 6 impact examples, 113 thematic fields, 40 institutions, 6 documents). Review and correct it in wp-admin.
3. Review and edit, at your own pace:
   - **INPO > Settings**: contact email, offices, phone, socials;
   - **INPO > Team** and **INPO > Donors**: order, photos, logos, the one unnamed donor logo;
   - **Publications > Programmes**: texts and main activities;
   - **Posts > Categories**: tick "News filter" on any category you want offered as a filter.

### Step 2. Go live

4. **Appearance > Themes > Add New > Upload Theme**: upload `inpo.zip`, then **Activate**.
   On the next wp-admin page the plugin goes live by itself and shows what it did:
   - Ballina, Rreth nesh and Kontakti get their new content (About: history, mission, vision and values, team and board, strategies);
   - the **Transparenca** page is created; the projects built from facts are published;
   - Lajme becomes the news page; the main menu is built with the eight sections of the brief (an earlier INPO menu that was not edited is rebuilt);
   - old programme pages, the Video page and "Publikimet në Programin…" redirect (301) to their new equivalents.

   Permalinks are not changed, so every existing link keeps working.

**Undo:** activating the old theme again restores the old page content, titles and reading settings automatically and removes the redirects. Team, donors, programmes and filters stay prepared for the next attempt. (Also under INPO > Setup, or `wp inpo restore`.)

WP-CLI equivalents: `wp inpo prepare`, `wp inpo golive`, `wp inpo restore`.

Notes:
- CPT UI still registers `publikimet` and the old "Anëtarët"/"Donatorët" types; the plugin's own registrations win. You can leave CPT UI active.
- WPML: the language switch in the header uses WPML automatically.
- Your own wp-admin language is set per user under **Users > Profile > Language**.

## New install (staging, empty site)

Same zips, then **INPO > Konfigurimi > Importo përmbajtjen demo** (or `wp inpo setup`): imports the prototype content and sets permalinks to `/lajme/<slug>/`. This button is hidden on a site that already has content.

## Run locally

```bash
npm run wp
```

Starts WordPress Playground on http://127.0.0.1:9500 with the theme and plugin mounted from this folder, content imported and you logged in as admin. Edits to PHP, CSS and JS show on reload. Data is in memory and resets when you stop the server.

---

## URLs

| Prototype | WordPress | Source |
|---|---|---|
| `#/` | `/` | Page "Kryefaqja" (template "Faqe pa kokë"), built from INPO blocks |
| `#/lajme` | `/lajme/` | Posts page, template `home.html` |
| `#/lajme?kategoria=prokurimi` | `/lajme/?kategoria=prokurimi-i-hapur&programi=…&viti=2024&q=…&rendit=te-vjetrat` | News page, filtered. Category archives (`/lajme/kategoria/…/`) keep working and show the same filters |
| `#/lajme/<id>` | `/lajme/<slug>/` | Post, template `single.html` |
| `#/publikimet` | `/publikimet/` | Publication archive |
| `#/publikimet?lloji=analiza` | `/publikimet/?lloji=analiza&fusha=prokurimi-publik&institucioni=oshp&viti=2023&q=…&rendit=titulli` | Same archive, filtered by category, thematic field, institution, year, search, order (and programme) |
| `#/publikimet/<id>` | `/publikimet/<slug>/` | Publication |
| `#/programet/qeverisje` | `/programet/qeverisje/` | Programme taxonomy page: objectives, areas of intervention, activities, research, results, projects, news |
| `#/projektet` | `/projektet/?statusi=aktive` | Projects archive: active and completed, results and impact (`#ndikimi`), partners and donors (`#partneret`) |
| `#/projektet/<id>` | `/projektet/<slug>/` | Project: status, period, donor, partners, objectives, results |
| `#/transparenca` | `/transparenca/?viti=2018` | Page with the Documents block: categories (`#raportet-e-auditimit`...) and a year filter |
| `#/te-dhenat` | `/te-dhenat/` and `/te-dhenat/<slug>/` | Data: interactive chart, sortable table, CSV (`?shkarko=csv`) |
| `#/rreth-nesh` | `/rreth-nesh/` | Page, editable core blocks + INPO blocks |
| `#/kontakti` | `/kontakti/?tema=tender` | Page with the contact block |
| Search overlay | Overlay + `/?s=` results page | |
| 404 view | `404.html` | |

## Filters (Lajme and Publikimet)

One engine for both lists (`inpo-core/includes/filters.php`), used by the News cards and Publication library blocks:

- **Search** (`?q=`), **chips** for the category or type with live counts under the other filters (`?kategoria=`, `?lloji=`), **Programi** (shown once content carries a programme), **Viti** (shown when there is more than one year) and **Rendit**: newest (default), oldest (`te-vjetrat`), by title (`titulli`).
- Active filters show as removable tags with "Hiq të gjitha"; an empty result offers "search everywhere" and "remove filters".
- Works without JS (a plain GET form; chips are submit buttons). With JS, results, count, tags and chip counts update in place and the URL stays shareable.
- The same state drives the page's main query, so paging (`/lajme/page/2/?programi=…`) keeps the filters.
- Filtered views send `noindex, follow` (core robots and Yoast) so search engines index the plain lists only. Chip counts are cached until content changes.

## Content model (plugin)

- **News**: core posts. Categories marked "Filtër i lajmeve" are the filters and labels (on the live site: Aktivitete, Publikime). Article photos show unless the image is a cover reused on three or more posts. Extra fields in the "Detajet e lajmit" box: short title (home card), display date (e.g. "Maj 2025" when the day is unknown), linked publication, migration note (`todo`).
- **Publications** (`publikimet`, as on the live site): title, summary (excerpt), optional body. The PDF is the file attached to the publication in Media, or the "PDF" field; the download button shows its size. Year = the "year" field or the publish year. Taxonomies: **Lloji** (`tipi_i_publikimit`: name in plural for filters, singular label, order) and **Programi**.
- **Programmes** (`inpo_program` taxonomy): name, description, full name, icon, objectives, three "areas of intervention" cards, main activities, results (number | label), order, "show latest news" switch. News and publications get their programme automatically when left empty (from the linked publication, or from the thematic field).
- **Thematic fields** (`inpo_theme`) and **Institutions** (`inpo_institution`): labels on publications and filters in the library.
- **Projects** (`inpo_project`, menu Projects): name, summary (excerpt), description, status, period, donor, partners, objectives, results (`160 | aktivitete prokurimi të monitoruara` shows as a figure), programme, publication.
- **Documents** (`inpo_doc`, menu Documents): transparency documents with a year and a PDF, in six categories (Raportet vjetore, financiare, e auditimit, Dokumentet e qeverisjes organizative, Politikat dhe procedurat e brendshme, Strategjitë dhe dokumentet organizative).
- **Data** (`inpo_dataset`, menu Data): a table pasted as CSV (first line = column names), chart type (bars, lines, table only), unit, source. **INPO > Setup > Sample procurement data** adds six datasets with made-up values for demos (each labelled "Të dhëna ilustruese" on the site) and removes them again with one click (`wp inpo samples` adds them).
- **Team** members have a group: Team or Board (the About page shows them separately). Prepare adds the nine board members listed on the live About page ("Anëtarët").
- **Donors** (`inpo_donor`, INPO > Donors): name, optional logo (featured image, shown greyscale) and link, ordered by "Order".
- **Team** (`inpo_member`, INPO > Team): name, role, email, photo (featured image), ordered by "Order". Shown on the About page by the Team block.
- **Messages** and **Subscribers**: private, under the INPO menu. Subscribers export to CSV. Both appear in Tools > Export/Erase Personal Data.

## Blocks

All in the "INPO" inserter category. Each is server-rendered with the prototype markup and edited from the sidebar.

| Block | Used on |
|---|---|
| Hero (with fields of action), Key findings (bento), Publikimet (rrjetë), Results and impact, Programet, Lajme (lista), Projects (with donors) | Home |
| Section links, Team (team and board), Documents (list) | About |
| Project list, Results and impact, Impact examples, Donors | Projects archive |
| Project details | Project page |
| Documents (grouped, year filter) | Transparency |
| Data list, Dataset | Data |
| Lajme (kartat) | News page and categories |
| Artikulli | Single news |
| Biblioteka e publikimeve, Detajet e publikimit | Publications |
| Faqja e programit | Programme pages |
| Transparenca (1.x box, kept for old pages) | About |
| Formulari i kontaktit | Contact |
| Koka e faqes | Every page head (custom text, current page, archive, search, 404) |
| Buletini | Newsletter part, on every page |
| Koka, Fundi (theme) | Header and footer parts |

Mission, vision and history on the About page are plain core blocks with theme classes, so they are edited by typing directly on the page.

## Publications: covers and the PDF reader

- **Covers** are designed documents (folded corner, typeset title, type and year, INPO mark) that rotate through four styles. When a publication has a featured image, or WordPress has a preview image of its PDF, the card shows the **real first page** instead.
- **INPO > Setup > PDF covers** creates those previews for publication PDFs, 10 per click (or `wp inpo covers`). It needs Imagick with Ghostscript on the server; without it the designed covers stay, and the publication page still draws the first page in the browser.
- **Reader:** "Lexo" on a card, "Lexo online" or the cover on a publication page opens the PDF in a full-screen reader: page thumbnails (wide screens), page n/N with previous/next, zoom, download. Pages render as they scroll into view. Keyboard: arrows or Page Up/Down, + and −, Home/End, Esc. The browser's Back button closes it, and `…/publikimet/<slug>/#lexo` opens it directly (shareable). If a PDF cannot be opened in the browser, it offers download and "open in a new tab".
- pdf.js 3.11 (Mozilla, Apache-2.0) is bundled in `inpo-core/assets/vendor/pdfjs/` and only downloaded when the reader or a first-page cover is needed. No CDN.

## Forms

- Contact and newsletter post to `/wp-json/inpo/v1/contact` and `/subscribe` with JS, and fall back to a normal form post without JS. Same validation messages on both sides.
- Spam: hidden honeypot field and a per-IP rate limit (5 per 10 minutes, IP hashed, never stored).
- Contact messages are stored and emailed to the address in **INPO > Cilësimet** (default: the site admin email). Configure SMTP on the host so mail is delivered.
- Email signup: off (INPO decided against it). The newsletter block renders nothing unless the filter `inpo_core_show_newsletter` returns true; mailing-list sync then via `inpo_core_newsletter_subscribed( $email, $id )`.

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `inpo_org_info` | filter | Organisation details for the header and footer (the plugin fills it from settings) |
| `inpo_core_contact_topics` | filter | Contact form topics |
| `inpo_core_contact_received` | action | After a contact message is stored |
| `inpo_core_newsletter_subscribed` | action | After a new subscriber is stored |

## Languages

The theme reads Polylang if it is active: the SQ/EN switch then lists the real languages. Without it, EN is a placeholder, as in the prototype.

## Design rules

The same as the prototype (see `../CLAUDE.md`): one indigo accent, Geist only, radii by hierarchy, rings only on accent panels, one orchestrated motion moment, contrast 4.5:1 in both themes. `theme.json` locks the editor to the palette and disables custom colours, gradients and font sizes so editors cannot drift from the system.

When you change `assets/css/styles.css` in the prototype, make the same change in `inpo/assets/css/inpo.css` (the first part of that file is the prototype stylesheet verbatim; WordPress rules are appended at the end).
