import { readFileSync, writeFileSync, existsSync, mkdirSync, rmSync, readdirSync, statSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const frontendDir = join(__dirname, '..');
const sitePublicDir = join(frontendDir, '..', 'site', 'public');

console.log('Syncing frontend build to site/public...');

const distDir = join(frontendDir, 'dist');

if (!existsSync(distDir)) {
  console.error('Build directory not found. Run `pnpm build` first.');
  process.exit(1);
}

if (!existsSync(sitePublicDir)) {
  mkdirSync(sitePublicDir, { recursive: true });
}

// 1. Sync assets/ from dist (these are the hashed JS/CSS)
const assetsSource = join(distDir, 'assets');
const assetsTarget = join(sitePublicDir, 'assets');

if (existsSync(assetsTarget)) {
  rmSync(assetsTarget, { recursive: true });
}

if (existsSync(assetsSource)) {
  copyDir(assetsSource, assetsTarget);
  console.log('Copied assets/');
}

// 2. Sync index.html
const indexSource = join(distDir, 'index.html');
const indexTarget = join(sitePublicDir, 'index.html');

if (existsSync(indexSource)) {
  writeFileSync(indexTarget, readFileSync(indexSource));
  console.log('Copied index.html');
}

console.log('Sync complete!');

function copyDir(src, dest) {
  mkdirSync(dest, { recursive: true });
  const entries = readdirSync(src);
  for (const entry of entries) {
    const srcPath = join(src, entry);
    const destPath = join(dest, entry);
    if (statSync(srcPath).isDirectory()) {
      copyDir(srcPath, destPath);
    } else {
      writeFileSync(destPath, readFileSync(srcPath));
    }
  }
}