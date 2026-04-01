require('dotenv').config();

module.exports = {
  META_ACCESS_TOKEN: process.env.META_ACCESS_TOKEN || '',
  PORT: process.env.PORT || 3000,
  COMPETITORS: (process.env.COMPETITORS || '').split(',').map(c => c.trim()).filter(Boolean),
  AD_LIBRARY_API: 'https://graph.facebook.com/v21.0/ads_archive',
  COUNTRY: 'MX',
  DATA_DIR: './data',
  REPORTS_DIR: './reports'
};
