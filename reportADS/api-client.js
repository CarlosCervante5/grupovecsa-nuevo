const axios = require('axios');
const config = require('./config');

/**
 * Cliente para la API oficial de Meta Ad Library
 * Requiere access token de Meta Developer
 */
class MetaAdLibraryClient {
  constructor(accessToken) {
    this.accessToken = accessToken || config.META_ACCESS_TOKEN;
    this.baseUrl = config.AD_LIBRARY_API;
  }

  hasToken() {
    return this.accessToken && this.accessToken !== 'TU_ACCESS_TOKEN_AQUI';
  }

  /**
   * Busca anuncios por término de búsqueda
   */
  async searchAds(searchTerm, options = {}) {
    if (!this.hasToken()) {
      throw new Error('No se ha configurado el META_ACCESS_TOKEN en .env');
    }

    const params = {
      access_token: this.accessToken,
      search_terms: searchTerm,
      ad_reached_countries: options.country || config.COUNTRY,
      ad_active_status: options.status || 'ACTIVE',
      ad_type: 'ALL',
      fields: [
        'id', 'ad_creation_time', 'ad_creative_bodies',
        'ad_creative_link_captions', 'ad_creative_link_titles',
        'ad_delivery_start_time', 'ad_delivery_stop_time',
        'page_id', 'page_name', 'publisher_platforms',
        'estimated_audience_size', 'impressions',
        'spend', 'currency', 'languages'
      ].join(','),
      limit: options.limit || 50
    };

    try {
      const response = await axios.get(this.baseUrl, { params });
      return {
        competitor: searchTerm,
        data: response.data.data || [],
        paging: response.data.paging || null,
        fetchedAt: new Date().toISOString()
      };
    } catch (error) {
      const msg = error.response?.data?.error?.message || error.message;
      console.error(`Error API para "${searchTerm}": ${msg}`);
      return {
        competitor: searchTerm,
        data: [],
        error: msg,
        fetchedAt: new Date().toISOString()
      };
    }
  }

  /**
   * Busca anuncios de múltiples competidores
   */
  async searchMultiple(competitors, options = {}) {
    const results = [];
    for (const competitor of competitors) {
      const result = await this.searchAds(competitor, options);
      results.push(result);
      await new Promise(r => setTimeout(r, 1000));
    }
    return results;
  }
}

module.exports = MetaAdLibraryClient;
