require('dotenv').config();
const express = require('express');
const config = require('./config');
const MetaAdLibraryClient = require('./api-client');
const AdLibraryScraper = require('./scraper');
const ReportGenerator = require('./report-generator');

const path = require('path');
const app = express();
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));
app.use('/reports', express.static(path.join(__dirname, 'reports')));

const apiClient = new MetaAdLibraryClient();
const reportGen = new ReportGenerator();

// Dashboard principal
app.get('/', (req, res) => {
  res.sendFile(__dirname + '/public/index.html');
});

// API: Escanear competidores
app.post('/api/scan', async (req, res) => {
  const { competitors, method, includeResults } = req.body;
  const targets = competitors || config.COMPETITORS;

  try {
    let results;

    if (method === 'scraper') {
      const scraper = new AdLibraryScraper();
      results = await scraper.searchMultiple(targets, config.COUNTRY);
      await scraper.close();
    } else {
      if (!apiClient.hasToken()) {
        return res.status(400).json({ error: 'No se ha configurado META_ACCESS_TOKEN en .env' });
      }
      results = await apiClient.searchMultiple(targets);
    }

    const dataFile = reportGen.saveRawData(results);
    const htmlFile = reportGen.generateHTML(results, method === 'scraper' ? 'scraper' : 'api');
    const csvFile = reportGen.generateCSV(results, method === 'scraper' ? 'scraper' : 'api');

    // Siempre incluir `results` (Laravel benchmark y clientes legacy dependen de ello).
    // `includeResults: false` solo omite el cuerpo si se pide explícitamente (ahorro de ancho de banda).
    const payload = {
      success: true,
      results,
      summary: results.map(r => ({
        competitor: r.competitor,
        adsCount: method === 'scraper' ? (r.adsFound || 0) : (r.data?.length || 0),
        error: r.error || null
      })),
      files: { data: dataFile, html: htmlFile, csv: csvFile }
    };
    if (includeResults === false) {
      delete payload.results;
    }
    res.json(payload);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// API: Buscar un competidor específico
app.get('/api/search', async (req, res) => {
  const { q, method } = req.query;
  if (!q) return res.status(400).json({ error: 'Parámetro q requerido' });

  try {
    let result;
    if (method === 'scraper') {
      const scraper = new AdLibraryScraper();
      result = await scraper.searchAds(q, config.COUNTRY);
      await scraper.close();
    } else {
      result = await apiClient.searchAds(q);
    }
    res.json(result);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// API: Historial de escaneos
app.get('/api/history', (req, res) => {
  const history = reportGen.getHistory();
  res.json(history.map(h => ({ file: h.file, date: h.date })));
});

// API: Obtener datos de un escaneo previo
app.get('/api/history/:file', (req, res) => {
  const history = reportGen.getHistory();
  const found = history.find(h => h.file === req.params.file);
  if (!found) return res.status(404).json({ error: 'No encontrado' });
  res.json(found.data);
});

// API: Lista de competidores configurados
app.get('/api/competitors', (req, res) => {
  res.json(config.COMPETITORS);
});

// API: Agregar competidor
app.post('/api/competitors', (req, res) => {
  const { name } = req.body;
  if (!name || !name.trim()) return res.status(400).json({ error: 'Nombre requerido' });
  const trimmed = name.trim();
  if (config.COMPETITORS.includes(trimmed)) return res.status(400).json({ error: 'Ya existe' });
  config.COMPETITORS.push(trimmed);
  updateEnvFile();
  res.json({ success: true, competitors: config.COMPETITORS });
});

// API: Eliminar competidor
app.delete('/api/competitors/:name', (req, res) => {
  const name = decodeURIComponent(req.params.name);
  const index = config.COMPETITORS.indexOf(name);
  if (index === -1) return res.status(404).json({ error: 'No encontrado' });
  config.COMPETITORS.splice(index, 1);
  updateEnvFile();
  res.json({ success: true, competitors: config.COMPETITORS });
});

function updateEnvFile() {
  const fs = require('fs');
  const envPath = require('path').join(__dirname, '.env');
  let content = fs.readFileSync(envPath, 'utf8');
  content = content.replace(/^COMPETITORS=.*/m, `COMPETITORS=${config.COMPETITORS.join(',')}`);
  fs.writeFileSync(envPath, content);
}

// API: Listar reportes generados
app.get('/api/reports', (req, res) => {
  const fs = require('fs');
  const path = require('path');
  if (!fs.existsSync(config.REPORTS_DIR)) return res.json([]);
  const files = fs.readdirSync(config.REPORTS_DIR)
    .filter(f => f.endsWith('.html') || f.endsWith('.csv'))
    .sort().reverse();
  res.json(files);
});

app.listen(config.PORT, () => {
  console.log(`\n🚀 Report ADS - Monitor BMW México`);
  console.log(`   Dashboard: http://localhost:${config.PORT}`);
  console.log(`   API Token: ${apiClient.hasToken() ? '✅ Configurado' : '❌ No configurado (usa scraper)'}`);
  console.log(`   Competidores: ${config.COMPETITORS.length}`);
  console.log(`\n   Competidores monitoreados:`);
  config.COMPETITORS.forEach(c => console.log(`   - ${c}`));
  console.log('');
});
