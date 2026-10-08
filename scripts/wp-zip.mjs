// Builds installable zips of the WordPress theme and plugin into dist/.
// Upload them in WordPress: Appearance > Themes > Add New, and Plugins > Add New.
// Run: npm run wp:zip
import { execFileSync } from 'node:child_process';
import { mkdirSync, rmSync } from 'node:fs';
import { join } from 'node:path';

const root = new URL('..', import.meta.url).pathname;
const dist = join(root, 'dist');
mkdirSync(dist, { recursive: true });
for (const name of ['inpo', 'inpo-core']) {
  const out = join(dist, name + '.zip');
  rmSync(out, { force: true });
  execFileSync('zip', ['-rq', out, name, '-x', '*.DS_Store'], { cwd: join(root, 'wordpress'), stdio: 'inherit' });
  console.log('Built dist/' + name + '.zip');
}
