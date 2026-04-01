const puppeteer = require('puppeteer');

/**
 * Scraper de la Biblioteca de Anuncios de Facebook
 * Funciona sin API key usando la interfaz pública
 */
class AdLibraryScraper {
  constructor() {
    this.browser = null;
    this.baseUrl = 'https://www.facebook.com/ads/library/';
  }

  async init() {
    this.browser = await puppeteer.launch({
      headless: 'new',
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--lang=es-MX']
    });
  }

  async close() {
    if (this.browser) await this.browser.close();
  }

  /**
   * Busca anuncios de un competidor por nombre
   */
  async searchAds(competitorName, country = 'MX') {
    if (!this.browser) await this.init();

    const page = await this.browser.newPage();
    await page.setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36');
    
    const searchUrl = `https://www.facebook.com/ads/library/?active_status=active&ad_type=all&country=${country}&q=${encodeURIComponent(competitorName)}&search_type=keyword_unordered`;

    console.log(`Buscando anuncios de: ${competitorName}`);
    
    try {
      await page.goto(searchUrl, { waitUntil: 'networkidle2', timeout: 30000 });
      await new Promise(r => setTimeout(r, 3000));

      // Extraer datos de los anuncios visibles
      const ads = await page.evaluate(() => {
        const results = [];
        const adCards = document.querySelectorAll('[class*="xrvj5dj"]') || 
                       document.querySelectorAll('div[role="article"]') ||
                       document.querySelectorAll('._7jvw');

        adCards.forEach((card, index) => {
          if (index >= 20) return;

          const textContent = card.innerText || '';
          const images = Array.from(card.querySelectorAll('img'));
          const links = card.querySelectorAll('a[href]');
          const videos = Array.from(card.querySelectorAll('video'));

          // Extraer URLs de imágenes (filtrar iconos pequeños y miniaturas)
          const imageUrls = images
            .map(img => img.src || img.getAttribute('data-src') || '')
            .filter(src => src && !src.includes('emoji') && !src.includes('static'))
            .filter(src => !src.includes('s60x60'))
            .filter(src => {
              const img = images.find(i => (i.src || i.getAttribute('data-src')) === src);
              return img && (img.naturalWidth > 80 || img.width > 80 || !img.naturalWidth);
            });

          // Extraer URLs de videos/posters
          const videoUrls = videos
            .map(v => v.poster || v.src || '')
            .filter(Boolean);

          results.push({
            text: textContent.substring(0, 500),
            images: imageUrls,
            imageCount: imageUrls.length,
            videos: videoUrls,
            videoCount: videos.length,
            linkCount: links.length,
            hasVideo: videos.length > 0,
            timestamp: new Date().toISOString()
          });
        });

        return results;
      });

      // Contar total de anuncios activos
      const totalText = await page.evaluate(() => {
        const el = document.querySelector('[class*="x8t9es0"]') ||
                   document.querySelector('span[dir="auto"]');
        return el ? el.innerText : '';
      });

      await page.close();

      return {
        competitor: competitorName,
        country,
        totalActiveText: totalText,
        adsFound: ads.length,
        ads,
        scrapedAt: new Date().toISOString()
      };

    } catch (error) {
      console.error(`Error scraping ${competitorName}:`, error.message);
      await page.close();
      return {
        competitor: competitorName,
        country,
        error: error.message,
        adsFound: 0,
        ads: [],
        scrapedAt: new Date().toISOString()
      };
    }
  }

  /**
   * Busca anuncios de múltiples competidores
   */
  async searchMultiple(competitors, country = 'MX') {
    const results = [];
    for (const competitor of competitors) {
      const result = await this.searchAds(competitor, country);
      results.push(result);
      // Pausa entre búsquedas para no ser bloqueado
      await new Promise(r => setTimeout(r, 2000));
    }
    return results;
  }
}

module.exports = AdLibraryScraper;
