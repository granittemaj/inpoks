// Smoke test: renders every route in jsdom and checks each page has an h1.
// Also fails on em dashes in source files, including the WordPress theme and plugin (project rule).
// Run: npm install && npm test
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join, extname } from 'node:path';
import { JSDOM } from 'jsdom';

const root = new URL('..', import.meta.url).pathname;
const read = (p) => readFileSync(join(root, p), 'utf8');

// Inline the external scripts so jsdom can run them without a server.
const html = read('index.html')
  // Function replacers, so "$" sequences in the code are not treated as patterns.
  .replace('<script src="assets/js/data.js"></script>', () => `<script>${read('assets/js/data.js')}</script>`)
  .replace('<script src="assets/js/charts.js"></script>', () => `<script>${read('assets/js/charts.js')}</script>`)
  .replace('<script src="assets/js/app.js"></script>', () => `<script>${read('assets/js/app.js')}</script>`);

const routes = [
  '', 'projektet', 'projektet?statusi=aktive', 'projektet/anulimet', 'transparenca', 'te-dhenat', 'te-dhenat/tenderet-e-anuluar', 'rreth-nesh?seksioni=ekipi', 'lajme', 'lajme?kategoria=reagime', 'lajme?q=stadiume&rendit=te-vjetrat', 'lajme/pastrimi',
  'publikimet', 'publikimet?lloji=infografika', 'publikimet?lloji=politika&institucioni=oshp', 'publikimet?fusha=prokurimi', 'publikimet?q=zzz', 'publikimet/integriteti',
  'programet/qeverisje', 'programet/edukim', 'rreth-nesh', 'kontakti', 'faqe-qe-nuk-ekziston'
];
// Filtered lists: the live count must match the data.
const counts = {
  'lajme': '6 artikuj', 'lajme?kategoria=reagime': '5 artikuj', 'lajme?q=stadiume&rendit=te-vjetrat': '2 artikuj',
  'publikimet': '13 publikime', 'publikimet?lloji=infografika': '4 publikime', 'publikimet?lloji=politika&institucioni=oshp': '1 publikim', 'publikimet?q=zzz': '0 publikime',
  'publikimet?fusha=prokurimi': '8 publikime'
};

let failed = 0;
for (const r of routes) {
  const errors = [];
  const dom = new JSDOM(html, {
    runScripts: 'dangerously',
    url: 'https://example.github.io/inpo-website/#/' + r,
    pretendToBeVisual: true,
    beforeParse(w) {
      w.matchMedia = () => ({ matches: false, addListener() {}, addEventListener() {} });
      w.scrollTo = () => {};
      w.Element.prototype.scrollIntoView = () => {};
      w.addEventListener('error', (e) => errors.push(e.message));
    }
  });
  const h1 = dom.window.document.querySelector('#app h1');
  const count = dom.window.document.querySelector('.res-count');
  if (counts[r] && (!count || count.textContent !== counts[r])) errors.push(`count "${count ? count.textContent : ''}", expected "${counts[r]}"`);
  const ok = h1 && h1.textContent.trim().length > 0 && errors.length === 0;
  if (!ok) failed++;
  console.log(`${ok ? 'PASS' : 'FAIL'}  #/${r || ''}  ${h1 ? '· ' + h1.textContent.trim().slice(0, 50) : '· no h1'}${errors.length ? '  ' + errors.join('; ') : ''}`);
}

// Em dash check across text sources.
const walk = (dir) => readdirSync(dir).flatMap((f) => {
  const p = join(dir, f);
  if (f === 'node_modules' || f === 'dist' || f.startsWith('.git')) return [];
  return statSync(p).isDirectory() ? walk(p) : [p];
});
const EM = String.fromCharCode(0x2014);
for (const f of walk(root).filter((f) => ['.html', '.css', '.js', '.md', '.mjs', '.php', '.json', '.txt'].includes(extname(f)) && !f.includes('/fonts/') && !f.includes('/vendor/') && !f.includes('/.audit'))) {
  if (readFileSync(f, 'utf8').includes(EM)) { failed++; console.log('FAIL  em dash found in ' + f.replace(root, '')); }
}

console.log(failed ? `\n${failed} check(s) failed` : '\nAll checks passed');
process.exit(failed ? 1 : 0);
