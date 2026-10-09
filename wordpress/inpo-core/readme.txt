=== INPO Core ===
Contributors: inpo
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.2.0
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
