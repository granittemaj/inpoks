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
  .replace('<script src="assets/js/app.js"></script>', () => `<script>${read('assets/js/app.js')}</script>`);

const routes = [
  '', 'lajme', 'lajme?kategoria=prokurimi', 'lajme/pastrimi',
  'publikimet', 'publikimet?lloji=analiza', 'publikimet/integriteti',
  'programet/qeverisje', 'programet/edukim', 'rreth-nesh', 'kontakti', 'faqe-qe-nuk-ekziston'
];

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
      w.addEventListener('error', (e) => errors.push(e.message));
    }
  });
  const h1 = dom.window.document.querySelector('#app h1');
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
