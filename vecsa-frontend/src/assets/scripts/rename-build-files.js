/**
 * Postbuild: renombra chunks legacy `src_app_*` y copia serve.json al output `browser/`
 * (Angular 19 + serve -s: index en caché + chunks viejos → MIME text/html en .js).
 */
const fs = require('fs');
const path = require('path');

const distRoot = path.join(__dirname, '../../../dist/vecsa-frontend');
const serveJsonSrc = path.join(__dirname, '../../../serve.json');

function renameSrcAppChunks(directory) {
  if (!fs.existsSync(directory)) return;
  for (const file of fs.readdirSync(directory)) {
    if (!file.startsWith('src_app_')) continue;
    const oldPath = path.join(directory, file);
    const newPath = path.join(directory, file.replace('src_app_', ''));
    try {
      fs.renameSync(oldPath, newPath);
      console.log(`Rename: ${file} -> ${path.basename(newPath)}`);
    } catch (e) {
      console.error(`Error rename file: ${file}:`, e);
    }
  }
}

renameSrcAppChunks(distRoot);
renameSrcAppChunks(path.join(distRoot, 'browser'));

const browserDir = path.join(distRoot, 'browser');
const serveDest = path.join(browserDir, 'serve.json');
if (fs.existsSync(browserDir) && fs.existsSync(serveJsonSrc)) {
  fs.copyFileSync(serveJsonSrc, serveDest);
  console.log('Copied serve.json ->', serveDest);
} else if (!fs.existsSync(browserDir)) {
  console.warn('postbuild: no dist/vecsa-frontend/browser (¿build incompleto?)');
}
