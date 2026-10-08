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
2. **INPO > Setup > Prepare the data.** It adds, without deleting anything:
   - singular labels and order for the publication types (Analizë, Hulumtim, Infografikë…);
   - the programmes Qeverisje e mirë and Edukim demokratik, with their main activities, and links news to them from the old programme categories;
   - two clean news filters, *Aktivitete* (new) and *Publikime* (existing); old categories stay;
   - **Team** and **Donors**, copied from the old "Anëtarët" and "Donatorët" entries (name, role, email, photo; name, link, logo).
3. Review and edit, at your own pace:
   - **INPO > Settings**: contact email, offices, phone, socials;
   - **INPO > Team** and **INPO > Donors**: order, photos, logos, the one unnamed donor logo;
   - **Publications > Programmes**: texts and main activities;
   - **Posts > Categories**: tick "News filter" on any category you want offered as a filter.

### Step 2. Go live

4. **Appearance > Themes > Add New > Upload Theme**: upload `inpo.zip`, then **Activate**.
   On the next wp-admin page the plugin goes live by itself and shows what it did:
   - Ballina, Rreth nesh and Kontakti get their new content (About gains the "Kush jemi" text, the values and the team);
   - Lajme becomes the news page; the main menu is created;
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
| `#/lajme?kategoria=prokurimi` | `/lajme/kategoria/prokurimi-i-hapur/` | Category, template `category.html` |
| `#/lajme/<id>` | `/lajme/<slug>/` | Post, template `single.html` |
| `#/publikimet` | `/publikimet/` | Publication archive |
| `#/publikimet?lloji=analiza` | `/publikimet/?lloji=analiza&viti=2023&q=…` | Same archive, filtered by type, year, search (and programme once publications have one). Works without JS, instant with JS |
| `#/publikimet/<id>` | `/publikimet/<slug>/` | Publication |
| `#/programet/qeverisje` | `/programet/qeverisje/` | Programme taxonomy page |
| `#/rreth-nesh` | `/rreth-nesh/` | Page, editable core blocks + INPO blocks |
| `#/kontakti` | `/kontakti/?tema=tender` | Page with the contact block |
| Search overlay | Overlay + `/?s=` results page | |
| 404 view | `404.html` | |

## Content model (plugin)

- **News**: core posts. Categories marked "Filtër i lajmeve" are the filters and labels (on the live site: Aktivitete, Publikime). Article photos show unless the image is a cover reused on three or more posts. Extra fields in the "Detajet e lajmit" box: short title (home card), display date (e.g. "Maj 2025" when the day is unknown), linked publication, migration note (`todo`).
- **Publications** (`publikimet`, as on the live site): title, summary (excerpt), optional body. The PDF is the file attached to the publication in Media, or the "PDF" field; the download button shows its size. Year = the "year" field or the publish year. Taxonomies: **Lloji** (`tipi_i_publikimit`: name in plural for filters, singular label, order) and **Programi**.
- **Programmes** (`inpo_program` taxonomy): name, description, full name, icon, three "Si punojmë" items, order, "show latest news" switch.
- **Donors** (`inpo_donor`, INPO > Donors): name, optional logo (featured image, shown greyscale) and link, ordered by "Order".
- **Team** (`inpo_member`, INPO > Team): name, role, email, photo (featured image), ordered by "Order". Shown on the About page by the Team block.
- **Messages** and **Subscribers**: private, under the INPO menu. Subscribers export to CSV. Both appear in Tools > Export/Erase Personal Data.

## Blocks

All in the "INPO" inserter category. Each is server-rendered with the prototype markup and edited from the sidebar.

| Block | Used on |
|---|---|
| Hero, Rastet që po ndjekim (bento), Programet, Publikimet (rrjetë), Lajme (lista), Donatorët | Home |
| Lajme (kartat) | News page and categories |
| Artikulli | Single news |
| Biblioteka e publikimeve, Detajet e publikimit | Publications |
| Faqja e programit | Programme pages |
| Transparenca | About |
| Formulari i kontaktit | Contact |
| Koka e faqes | Every page head (custom text, current page, archive, search, 404) |
| Buletini | Newsletter part, on every page |
| Koka, Fundi (theme) | Header and footer parts |

Mission, vision and history on the About page are plain core blocks with theme classes, so they are edited by typing directly on the page.

## Forms

- Contact and newsletter post to `/wp-json/inpo/v1/contact` and `/subscribe` with JS, and fall back to a normal form post without JS. Same validation messages on both sides.
- Spam: hidden honeypot field and a per-IP rate limit (5 per 10 minutes, IP hashed, never stored).
- Contact messages are stored and emailed to the address in **INPO > Cilësimet** (default: the site admin email). Configure SMTP on the host so mail is delivered.
- Mailing-list sync: hook `inpo_core_newsletter_subscribed( $email, $id )`, e.g. to push to Brevo or Mailchimp.

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `inpo_org_info` | filter | Organisation details for the header and footer (the plugin fills it from settings) |
| `inpo_has_newsletter` | filter | Shows the "Abonohu" buttons when the newsletter block exists |
| `inpo_core_contact_topics` | filter | Contact form topics |
| `inpo_core_contact_received` | action | After a contact message is stored |
| `inpo_core_newsletter_subscribed` | action | After a new subscriber is stored |

## Languages

The theme reads Polylang if it is active: the SQ/EN switch then lists the real languages. Without it, EN is a placeholder, as in the prototype.

## Design rules

The same as the prototype (see `../CLAUDE.md`): one indigo accent, Geist only, radii by hierarchy, rings only on accent panels, one orchestrated motion moment, contrast 4.5:1 in both themes. `theme.json` locks the editor to the palette and disables custom colours, gradients and font sizes so editors cannot drift from the system.

When you change `assets/css/styles.css` in the prototype, make the same change in `inpo/assets/css/inpo.css` (the first part of that file is the prototype stylesheet verbatim; WordPress rules are appended at the end).
