=== INPO Core ===
Contributors: inpo
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Content model, blocks, forms and settings for the INPO website (Iniciativa për Progres).

== Description ==

Pairs with the INPO theme. Provides:

* Publications with types and programmes, at /publikimet/ with search and filters.
* Programme pages at /programet/<slug>/.
* Donors with optional logos.
* Server-rendered blocks that output the approved prototype markup: hero, bento, programmes, publications, library, news list and cards, article, publication detail, programme page, transparency, donors, newsletter, contact form, page head.
* Contact form and newsletter that work with and without JavaScript, with storage, honeypot, rate limit, CSV export and privacy export/erase.
* Organisation settings (INPO > Cilësimet) used by the theme header and footer.
* One-click import of the prototype content (INPO > Konfigurimi, or `wp inpo setup`).

== Installation ==

1. Upload and activate the plugin, then the INPO theme.
2. Go to INPO > Konfigurimi and run the import.
3. Check INPO > Cilësimet.

== Changelog ==

= 1.8.0 =
* Infographics as images: in the Publication box, "Choose images from Media" (in order). The page shows them as a row to swipe with arrows, a counter (01 / 06) and a full-screen view; the first image becomes the cover.

= 1.7.0 =
* Everything editable from wp-admin: the lists (publications, projects, data, news) take their heading, intro and the sections under them from a Page (private pages made by the plugin, marked "… list"; news uses the Posts page). INPO > Help: where to edit what. Editors can change INPO > Settings. "Choose from Media" for the hero photo and the "Kush jemi" photos.

= 1.6.0 =
* Home: the hero shows a photo from INPO's work (Hero block: photo URL and caption; empty = the report launch, February 2025). New block "Who we are (photos)" (inpo/field): who INPO is, with five photos from meetings with municipalities, roundtables and report launches; added to existing home pages after the key findings, once.
* Charts: category labels tilt when they do not fit (phones).

= 1.5.5 =
* Team photos from inpo-ks.org (bundled in seed/team/) for members without one; the executive director is listed first.

= 1.5.4 =
* No email signup: the newsletter block shows nothing (filter inpo_core_show_newsletter to bring it back) and is hidden from the inserter. The jobs box on About links to the contact page; existing About pages are updated once.

= 1.5.3 =
* Version bump with the INPO theme 1.5.3.

= 1.5.2 =
* Home hero: the badge above the headline is gone (the field is removed from the block).

= 1.5.1 =
* Infographics as charts: a publication can carry key figures and chart data (Publication box > Data). The page shows the figures and interactive charts with a table and CSV; the cover shows small bars from the data. The four infographics on the site get INPO's own numbers on update.
* Donor logos: donors without a logo get the one from the old site, bundled with the plugin (seed/donors/). DANIDA and the Swiss office have none yet.
* Charts: axis labels for large amounts read "600 mln".

= 1.5.0 =
* Board members show as a ruled list of names and roles (no photo tiles); the team keeps its cards.

= 1.4.4 =
* Filters: search and the filter selects in one row, the categories in one row (it scrolls sideways when needed), the order next to the result count. Categories without content are left out of the chips.
* Transparency: categories without documents go on one "Së shpejti" line instead of empty sections.
* Placeholders in [brackets] no longer show on project cards or as donor tiles. The DSP project title no longer starts with a placeholder.
* Donor tiles: the number of columns follows the number of donors, so the last row is not left with one tile.
* Publication cards with a generated cover show the title once (on the cover).

= 1.4.3 =
* Menu rebuild works whatever the existing menus are called (no more "The menu could not be created"); the previous menu is always kept.

= 1.4.2 =
* Update: a main menu without the brief's sections is replaced by the eight-section menu (the old one is kept as "Kryesore (e vjetër)"). INPO > Setup > Rebuild the main menu does it by hand.

= 1.4.1 =
* Updating from 1.x now applies the new structure: URL rules refresh at once (no more 404 on /projektet/, /transparenca/, /te-dhenat/), and the next wp-admin page brings sections, categories, board, projects, documents, layouts, Transparency page and menu up to date. Also under INPO > Setup > Apply the new structure.

= 1.4.0 =
* INPO's brief (October 2026): eight-section menu (Kryefaqja, Rreth nesh, Programet, Hulumtime dhe publikime, Lajme dhe qëndrime, Projektet dhe ndikimi, Transparenca, Kontakti) with sub-items.
* Projects (status, period, donor, partners, objectives, measurable results), Transparency documents by category and year, Data (CSV as interactive chart and sortable table, CSV download).
* Publications: author or research team, thematic field, institution; library filters by category, thematic field, institution, programme and year. Six publication categories from the brief.
* News: five sections (Aktivitete, Komunikata për media, Reagime dhe qëndrime publike, Opinione dhe artikuj, INPO në media), card labels.
* Programmes: objectives, areas of intervention, results; news and publications link to their programme automatically.
* Home: fields of action, key findings, results and impact figures counted from content, projects and partners, social links.
* About: history, mission, vision and values, team and board, strategies; section links.
* Board members from the About page; sample procurement datasets (made-up, labelled, removable) under INPO > Setup.
* Prepare maps the live content onto the brief once (keyword rules for sections, categories, fields, institutions, impact examples); go-live creates the Transparency page and the new menu.

= 1.3.0 =
* News and publication filters: search, category or type chips with live counts, programme, year and order (newest, oldest, by title), removable active-filter tags, "search everywhere" in empty results. One engine for both lists; works without JS, instant with JS; filtered views are noindex.
* Albanian titles for the publication archive in the browser tab and in Yoast titles.
* Calmer type scale and spacing, mirrored from the theme.

= 1.2.0 =
* Document-style publication covers, real first-page covers (featured image, WordPress PDF preview, or drawn in the browser).
* Full-screen PDF reader with thumbnails, zoom, keyboard and Back-button support; pdf.js bundled and lazy-loaded.
* PDF covers tool (INPO > Setup, wp inpo covers).
* Accessibility: 44px targets, mobile language switch, heading order, form states.

= 1.1.0 =
* Works on the live inpo-ks.org data (publikimet, tipi_i_publikimit), WPML language switch.
* Two-step launch: Prepare, then automatic go-live on theme activation, with restore.
* Team and Donors copied from the old site; programme activities; English wp-admin.

= 1.0.0 =
* First release, ported from the approved prototype.
