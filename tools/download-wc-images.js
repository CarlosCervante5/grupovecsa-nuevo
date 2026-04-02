#!/usr/bin/env node
/**
 * Download WooCommerce product images from CSV export
 * 
 * Usage: node tools/download-wc-images.js <csv-file> [output-dir]
 * Example: node tools/download-wc-images.js wc-product-export.csv ./downloaded-images
 */

const fs = require('fs');
const path = require('path');
const https = require('https');
const http = require('http');

const csvFile = process.argv[2];
const outputDir = process.argv[3] || './downloaded-images';

if (!csvFile) {
  console.error('Usage: node tools/download-wc-images.js <csv-file> [output-dir]');
  process.exit(1);
}

if (!fs.existsSync(csvFile)) {
  console.error('File not found:', csvFile);
  process.exit(1);
}

// Simple CSV parser that handles quoted fields with commas
function parseCsv(filePath) {
  const content = fs.readFileSync(filePath, 'utf-8').replace(/^\uFEFF/, '');
  const rows = [];
  let current = '';
  let inQuotes = false;
  const lines = content.split('\n');
  
  for (const line of lines) {
    if (inQuotes) {
      current += '\n' + line;
      const quoteCount = (line.match(/"/g) || []).length;
      if (quoteCount % 2 !== 0) inQuotes = false;
      if (!inQuotes) { rows.push(current); current = ''; }
    } else {
      const quoteCount = (line.match(/"/g) || []).length;
      if (quoteCount % 2 !== 0) {
        inQuotes = true;
        current = line;
      } else {
        rows.push(line);
      }
    }
  }

  if (!rows.length) return [];

  const parseRow = (row) => {
    const fields = [];
    let field = '';
    let inQ = false;
    for (let i = 0; i < row.length; i++) {
      const ch = row[i];
      if (ch === '"') { inQ = !inQ; continue; }
      if (ch === ',' && !inQ) { fields.push(field.trim()); field = ''; continue; }
      field += ch;
    }
    fields.push(field.trim());
    return fields;
  };

  const headers = parseRow(rows[0]);
  const result = [];
  for (let i = 1; i < rows.length; i++) {
    if (!rows[i].trim()) continue;
    const values = parseRow(rows[i]);
    const obj = {};
    headers.forEach((h, idx) => { obj[h] = values[idx] || ''; });
    result.push(obj);
  }
  return result;
}

function downloadFile(url, destPath) {
  return new Promise((resolve, reject) => {
    const dir = path.dirname(destPath);
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });

    const client = url.startsWith('https') ? https : http;
    const request = client.get(url, { timeout: 30000 }, (response) => {
      if (response.statusCode >= 300 && response.statusCode < 400 && response.headers.location) {
        return downloadFile(response.headers.location, destPath).then(resolve).catch(reject);
      }
      if (response.statusCode !== 200) {
        return reject(new Error('HTTP ' + response.statusCode + ' for ' + url));
      }
      const file = fs.createWriteStream(destPath);
      response.pipe(file);
      file.on('finish', () => { file.close(); resolve(destPath); });
      file.on('error', (err) => { fs.unlink(destPath, () => {}); reject(err); });
    });
    request.on('error', reject);
    request.on('timeout', () => { request.destroy(); reject(new Error('Timeout: ' + url)); });
  });
}

async function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

async function main() {
  console.log('📖 Reading CSV:', csvFile);
  const rows = parseCsv(csvFile);
  console.log('   Found', rows.length, 'rows');

  // Collect all images with their SKU
  const imageJobs = [];
  for (const row of rows) {
    const sku = (row['SKU'] || '').trim();
    const images = (row['Imágenes'] || row['Images'] || '').trim();
    if (!sku || !images) continue;

    const urls = images.split(',').map(u => u.trim()).filter(u => u && u.startsWith('http'));
    urls.forEach((url, idx) => {
      const ext = path.extname(new URL(url).pathname) || '.avif';
      const filename = sku + (urls.length > 1 ? '_' + (idx + 1) : '') + ext;
      imageJobs.push({ sku, url, filename });
    });
  }

  console.log('📸 Found', imageJobs.length, 'images to download');
  if (!fs.existsSync(outputDir)) fs.mkdirSync(outputDir, { recursive: true });

  const stats = { downloaded: 0, failed: 0, skipped: 0 };
  const mapping = {}; // sku -> [filenames]
  const errors = [];
  const CONCURRENCY = 5;

  // Process in batches
  for (let i = 0; i < imageJobs.length; i += CONCURRENCY) {
    const batch = imageJobs.slice(i, i + CONCURRENCY);
    const promises = batch.map(async (job) => {
      const destPath = path.join(outputDir, job.sku, job.filename);
      
      if (fs.existsSync(destPath)) {
        stats.skipped++;
        if (!mapping[job.sku]) mapping[job.sku] = [];
        mapping[job.sku].push(job.filename);
        return;
      }

      try {
        await downloadFile(job.url, destPath);
        stats.downloaded++;
        if (!mapping[job.sku]) mapping[job.sku] = [];
        mapping[job.sku].push(job.filename);
      } catch (err) {
        stats.failed++;
        errors.push({ sku: job.sku, url: job.url, error: err.message });
      }
    });

    await Promise.all(promises);

    // Progress
    const done = Math.min(i + CONCURRENCY, imageJobs.length);
    const pct = Math.round((done / imageJobs.length) * 100);
    process.stdout.write(`\r   Progress: ${done}/${imageJobs.length} (${pct}%) | ✅ ${stats.downloaded} | ⏭ ${stats.skipped} | ❌ ${stats.failed}`);
    
    await sleep(100); // Small delay between batches
  }

  console.log('\n');
  console.log('✅ Downloaded:', stats.downloaded);
  console.log('⏭  Skipped (already exists):', stats.skipped);
  console.log('❌ Failed:', stats.failed);
  console.log('📁 Output directory:', path.resolve(outputDir));
  console.log('📦 SKUs with images:', Object.keys(mapping).length);

  // Save mapping JSON
  const mappingPath = path.join(outputDir, '_mapping.json');
  fs.writeFileSync(mappingPath, JSON.stringify(mapping, null, 2));
  console.log('📋 Mapping saved to:', mappingPath);

  // Save errors if any
  if (errors.length > 0) {
    const errPath = path.join(outputDir, '_errors.json');
    fs.writeFileSync(errPath, JSON.stringify(errors, null, 2));
    console.log('⚠️  Errors saved to:', errPath);
  }
}

main().catch(err => { console.error('Fatal error:', err); process.exit(1); });
