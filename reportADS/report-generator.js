const fs = require('fs');
const path = require('path');
const config = require('./config');

class ReportGenerator {
  constructor() {
    this.ensureDirs();
  }

  ensureDirs() {
    [config.DATA_DIR, config.REPORTS_DIR].forEach(dir => {
      if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    });
  }

  /**
   * Guarda datos crudos con timestamp
   */
  saveRawData(results) {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const filePath = path.join(config.DATA_DIR, `scan-${timestamp}.json`);
    fs.writeFileSync(filePath, JSON.stringify(results, null, 2));
    console.log(`Datos guardados en: ${filePath}`);
    return filePath;
  }

  /**
   * Genera reporte HTML de seguimiento
   */
  generateHTML(results, source = 'api') {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const date = new Date().toLocaleDateString('es-MX', {
      year: 'numeric', month: 'long', day: 'numeric'
    });

    let competitorRows = '';
    let detailSections = '';

    results.forEach(result => {
      const name = result.competitor || result.page_name || 'Desconocido';
      const adsCount = source === 'api' ? (result.data?.length || 0) : (result.adsFound || 0);
      const hasError = !!result.error;

      competitorRows += `
        <tr>
          <td>${name}</td>
          <td>${hasError ? '<span class="error">Error</span>' : adsCount}</td>
          <td>${hasError ? result.error : (adsCount > 0 ? 'Activo' : 'Sin anuncios')}</td>
          <td>${result.fetchedAt || result.scrapedAt || '-'}</td>
        </tr>`;

      if (!hasError && adsCount > 0) {
        const ads = source === 'api' ? result.data : result.ads;
        detailSections += `
          <div class="competitor-detail">
            <h3>${name} (${adsCount} anuncios)</h3>
            <div class="ads-grid">
              ${ads.slice(0, 10).map((ad, i) => {
                if (source === 'api') {
                  return `
                    <div class="ad-card">
                      <div class="ad-header">Anuncio #${i + 1} - ${ad.page_name || name}</div>
                      <div class="ad-body">${(ad.ad_creative_bodies || []).join(' ').substring(0, 200)}...</div>
                      <div class="ad-meta">
                        <span>Inicio: ${ad.ad_delivery_start_time || '-'}</span>
                        <span>Plataformas: ${(ad.publisher_platforms || []).join(', ')}</span>
                        ${ad.spend ? `<span>Gasto: ${JSON.stringify(ad.spend)}</span>` : ''}
                        ${ad.impressions ? `<span>Impresiones: ${JSON.stringify(ad.impressions)}</span>` : ''}
                      </div>
                    </div>`;
                } else {
                  const imgHtml = (ad.images || []).slice(0, 3).map(src => 
                    `<img src="${src}" style="max-width:100%;border-radius:6px;margin-bottom:8px" loading="lazy" onerror="this.style.display='none'">`
                  ).join('');
                  return `
                    <div class="ad-card">
                      <div class="ad-header">Anuncio #${i + 1}</div>
                      ${imgHtml}
                      <div class="ad-body">${(ad.text || '').substring(0, 200)}</div>
                      <div class="ad-meta">
                        <span>Imágenes: ${ad.imageCount || 0}</span>
                        <span>Videos: ${ad.videoCount || 0}</span>
                      </div>
                    </div>`;
                }
              }).join('')}
            </div>
          </div>`;
      }
    });

    const html = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte ADS BMW México - ${date}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0a0a0a; color: #e0e0e0; padding: 20px; }
    .container { max-width: 1200px; margin: 0 auto; }
    h1 { color: #1877f2; margin-bottom: 5px; font-size: 28px; }
    h2 { color: #ccc; margin: 30px 0 15px; font-size: 20px; border-bottom: 1px solid #333; padding-bottom: 8px; }
    h3 { color: #1877f2; margin-bottom: 10px; }
    .subtitle { color: #888; margin-bottom: 30px; }
    .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
    .stat-card { background: #1a1a1a; border: 1px solid #333; border-radius: 8px; padding: 20px; text-align: center; }
    .stat-card .number { font-size: 36px; font-weight: bold; color: #1877f2; }
    .stat-card .label { color: #888; margin-top: 5px; }
    table { width: 100%; border-collapse: collapse; background: #1a1a1a; border-radius: 8px; overflow: hidden; }
    th { background: #1877f2; color: white; padding: 12px 15px; text-align: left; }
    td { padding: 12px 15px; border-bottom: 1px solid #333; }
    tr:hover { background: #222; }
    .error { color: #ff4444; }
    .ads-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin-bottom: 30px; }
    .ad-card { background: #1a1a1a; border: 1px solid #333; border-radius: 8px; padding: 15px; }
    .ad-header { font-weight: bold; color: #1877f2; margin-bottom: 8px; }
    .ad-body { color: #ccc; font-size: 14px; margin-bottom: 10px; line-height: 1.4; }
    .ad-meta { display: flex; flex-wrap: wrap; gap: 10px; font-size: 12px; color: #888; }
    .ad-meta span { background: #222; padding: 3px 8px; border-radius: 4px; }
    .competitor-detail { margin-bottom: 30px; }
    .footer { text-align: center; color: #555; margin-top: 40px; padding-top: 20px; border-top: 1px solid #333; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Reporte de Anuncios - Competencia BMW México</h1>
    <p class="subtitle">Generado: ${date} | Fuente: ${source === 'api' ? 'Meta Ad Library API' : 'Web Scraping'}</p>
    
    <div class="summary">
      <div class="stat-card">
        <div class="number">${results.length}</div>
        <div class="label">Competidores monitoreados</div>
      </div>
      <div class="stat-card">
        <div class="number">${results.reduce((sum, r) => sum + (source === 'api' ? (r.data?.length || 0) : (r.adsFound || 0)), 0)}</div>
        <div class="label">Anuncios encontrados</div>
      </div>
      <div class="stat-card">
        <div class="number">${results.filter(r => !r.error).length}</div>
        <div class="label">Búsquedas exitosas</div>
      </div>
    </div>

    <h2>Resumen por Competidor</h2>
    <table>
      <thead>
        <tr><th>Competidor</th><th>Anuncios Activos</th><th>Estado</th><th>Última consulta</th></tr>
      </thead>
      <tbody>${competitorRows}</tbody>
    </table>

    <h2>Detalle de Anuncios</h2>
    ${detailSections || '<p style="color:#888">No se encontraron anuncios para mostrar en detalle.</p>'}

    <div class="footer">
      <p>Report ADS - Monitor de Competencia BMW México</p>
    </div>
  </div>
</body>
</html>`;

    const filePath = path.join(config.REPORTS_DIR, `reporte-${timestamp}.html`);
    fs.writeFileSync(filePath, html);
    console.log(`Reporte generado: ${filePath}`);
    return filePath;
  }

  /**
   * Genera CSV con datos
   */
  generateCSV(results, source = 'api') {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const rows = ['Competidor,Anuncios Activos,Estado,Fecha'];

    results.forEach(r => {
      const name = r.competitor || 'Desconocido';
      const count = source === 'api' ? (r.data?.length || 0) : (r.adsFound || 0);
      const status = r.error ? 'Error' : (count > 0 ? 'Activo' : 'Sin anuncios');
      const date = r.fetchedAt || r.scrapedAt || '-';
      rows.push(`"${name}",${count},"${status}","${date}"`);
    });

    const filePath = path.join(config.REPORTS_DIR, `reporte-${timestamp}.csv`);
    fs.writeFileSync(filePath, rows.join('\n'));
    console.log(`CSV generado: ${filePath}`);
    return filePath;
  }

  /**
   * Obtiene historial de escaneos previos
   */
  getHistory() {
    if (!fs.existsSync(config.DATA_DIR)) return [];
    return fs.readdirSync(config.DATA_DIR)
      .filter(f => f.endsWith('.json'))
      .map(f => {
        const data = JSON.parse(fs.readFileSync(path.join(config.DATA_DIR, f)));
        return { file: f, date: f.replace('scan-', '').replace('.json', ''), data };
      })
      .sort((a, b) => b.file.localeCompare(a.file));
  }
}

module.exports = ReportGenerator;
