# INPO website prototype

Clickable prototype of a new website for **Iniciativa për Progres (INPO)**, a Kosovar civil society organisation that monitors public procurement and local governance.

Design direction: **Quiet Indigo**. It uses INPO's own indigo, lots of white space, oversized type and data moments as the visuals. The copy is in Albanian and uses real INPO content.

- Static site: plain HTML, CSS and JavaScript
- No build step, no frameworks, no dependencies at runtime
- Works on GitHub Pages, any static host, or straight from the file system

---

## Pages

| Route | Page |
|---|---|
| `#/` | Kryefaqja: hero, data bento, programmes, publications, news, donors |
| `#/lajme` | News list with category filter |
| `#/lajme/<id>` | Article with share, related news and a contact CTA |
| `#/publikimet` | Publication library with live search, type and programme filters |
| `#/publikimet/<id>` | Publication detail |
| `#/programet/qeverisje`, `#/programet/edukim` | Programme pages |
| `#/rreth-nesh` | Mission, vision, timeline, audits, jobs, donors |
| `#/kontakti` | Contact form with validation and success state |
| anything else | 404 view |

Global features:

- Search overlay (search icon or `/` key)
- Mobile menu
- Newsletter on every page
- Light and dark theme toggle
- Accessibility basics: skip link, visible focus, reduced-motion support

---

## Deploy to GitHub Pages

### Option A: web upload (no command line)

1. Create a new **public** repository on GitHub, for example `inpo-website`.
2. Unzip the download. Open the `inpo-website` folder and drag **its contents** (not the folder itself) into the repository's *Add file → Upload files* page. Commit.
   - `index.html` must sit at the repository root.
   - `.nojekyll` is a hidden file. If your OS hides it, the site still works without it.
3. Go to **Settings → Pages**.
4. Under *Build and deployment*, choose **Deploy from a branch**, then **main** / **/(root)**, and click **Save**.
5. After about 1 minute the site is live at `https://<your-username>.github.io/inpo-website/`.

### Option B: git

```bash
cd inpo-website
git init
git add .
git commit -m "INPO website prototype"
git branch -M main
git remote add origin https://github.com/<your-username>/inpo-website.git
git push -u origin main
```

Then enable Pages as in steps 3 to 5 above.

All asset paths are relative and routing uses the URL hash. The site therefore works under a project path (`/inpo-website/`) without any configuration.

---

## Run locally

Double-click `index.html`. Or serve it:

```bash
npm run serve          # http://localhost:8000
# or
python3 -m http.server 8000
```

Smoke test (renders every route in jsdom and checks for em dashes):

```bash
npm install
npm test
```

---

## Project structure

```
inpo-website/
├── index.html            App shell: header, newsletter, footer, dialogs
├── 404.html              GitHub Pages fallback, redirects to #/404
├── favicon.svg
├── assets/
│   ├── css/styles.css    Design tokens (light and dark) and all components
│   └── js/
│       ├── data.js       All content: news, publications, programmes, donors
│       └── app.js        Router, page renderers, search, forms, theme
├── scripts/
│   ├── smoke-test.mjs
│   └── wp-zip.mjs        Builds the WordPress zips
├── wordpress/            WordPress theme (inpo) and plugin (inpo-core)
├── package.json          Dev tooling only (serve, test, wp)
├── CLAUDE.md             Instructions for Claude Code
└── README.md
```

---

## Editing content

All content lives in `assets/js/data.js`.

**Add a news item** by putting a new object at the top of `NEWS`:

```js
{ id:'slug-i-lajmit', date:'25.09.2026', iso:'2026-09-25', cat:'reagim',
  title:'Titulli i plotë',
  excerpt:'Një ose dy fjali përmbledhje.',
  body:['Paragrafi i parë.', 'Paragrafi i dytë.'],
  todo:false }
```

- `cat` must be `reagim` or `prokurimi`.
- Set `todo:true` to show the "full text imported during migration" note.

**Add a publication** by appending to `PUBS`:

```js
{ id:'slug', type:'analiza', prog:'qeverisje', short:'ABC', title:'Titulli' }
```

- `type` is one of `analiza`, `hulumtime`, `infografika`, `buletine`, `inpo`.
- `short` is the big word shown on some cover styles.

The cover style rotates automatically: rings, bars, dots, word.

---

## Design tokens

| Token | Light | Dark | Use |
|---|---|---|---|
| `--accent` | `#3D3B8E` | `#4A48A6` | INPO indigo, primary surfaces and buttons |
| `--accent-ink` | `#3D3B8E` | `#B0AEF6` | Accent text and icons |
| `--ink` | `#0E0E1A` | `#F1F1F7` | Text |
| `--bg` | `#FFFFFF` | `#0C0C16` | Page background |
| `--bg-soft` | `#F2F2F8` | `#171726` | Soft cards, pills |
| `--line` | `#E4E4EE` | `#26263A` | Borders |

Typography is **Geist** (Google Fonts), weights 300 to 800.

---

## Placeholders to replace before launch

- [ ] The INPO wordmark is typographic. Replace it with the official SVG logo.
- [ ] Donor tiles are text. Replace them with grayscale SVG logos.
- [ ] Article bodies are partial. Import the full texts from inpo-ks.org.
- [ ] Publication categories, years and summaries are assumptions. Confirm them against the CMS.
- [ ] PDF downloads, the EN version and form submission are not connected.
- [ ] Remove `<meta name="robots" content="noindex, nofollow">` for production.

---

## WordPress

The prototype is ported to a WordPress block theme and plugin in [`wordpress/`](wordpress/README.md). Same markup and CSS, verified element by element against this prototype at 390, 768, 1024, 1200 and 1440px.

```bash
npm run wp       # local WordPress with content imported, http://127.0.0.1:9500
npm run wp:zip   # dist/inpo.zip (theme) and dist/inpo-core.zip (plugin)
```

| Prototype | WordPress |
|---|---|
| Tokens in `styles.css` | `theme.json` palette + the same tokens in `inpo/assets/css/inpo.css` |
| News | Posts, categories `reagim-publik` / `prokurimi-i-hapur`, at `/lajme/<slug>/` |
| Publications | CPT `publikimet` and taxonomy `tipi_i_publikimit` (the live site's own names, `?lloji=`), plus `inpo_program` |
| Library filters | Archive query, enhanced with fetch (works without JS) |
| SQ / EN | Polylang supported in the language switch |
| Forms | Built in (INPO Core): stored, emailed, CSV export, hook for Brevo or Mailchimp |
