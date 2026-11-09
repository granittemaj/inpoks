// Writes wordpress/inpo-core/seed/content.json from assets/js/data.js (window.INPO_DATA).
// Run after changing data.js: npm run seed
import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import vm from 'node:vm';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const ctx = { window: {} };
vm.runInNewContext(readFileSync(join(root, 'assets/js/data.js'), 'utf8'), ctx);
const out = join(root, 'wordpress/inpo-core/seed/content.json');
writeFileSync(out, JSON.stringify(ctx.window.INPO_DATA, null, 1) + '\n');
console.log('Wrote ' + out.replace(root + '/', ''));
