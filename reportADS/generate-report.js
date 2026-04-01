#!/usr/bin/env node
require('dotenv').config();
const config = require('./config');
const MetaAdLibraryClient = require('./api-client');
const AdLibraryScraper = require('./scraper');
const ReportGenerator = require('./report-generator');

async function main() {
  const method = process.argv[2] || 'scraper';
  const reportGen = new ReportGenerator();

  console.log('📊 Report ADS - Generador de Reportes BMW México');
  console.log(`   Método: ${method === 'api' ? 'Meta API' : 'Web Scraper'}`);
  console.log(`   Competidores: ${config.COMPETITORS.length}\n`);

  let results;

  if (method === 'api') {
    const client = new MetaAdLibraryClient();
    if (!client.hasToken()) {
      console.error('❌ Configura META_ACCESS_TOKEN en .env');
      process.exit(1);
    }
    results = await client.searchMultiple(config.COMPETITORS);
  } else {
    const scraper = new AdLibraryScraper();
    results = await scraper.searchMultiple(config.COMPETITORS, config.COUNTRY);
    await scraper.close();
  }

  reportGen.saveRawData(results);
  const htmlPath = reportGen.generateHTML(results, method);
  const csvPath = reportGen.generateCSV(results, method);

  console.log('\n✅ Reporte generado exitosamente');
  console.log(`   HTML: ${htmlPath}`);
  console.log(`   CSV: ${csvPath}`);
}

main().catch(err => {
  console.error('Error:', err.message);
  process.exit(1);
});
