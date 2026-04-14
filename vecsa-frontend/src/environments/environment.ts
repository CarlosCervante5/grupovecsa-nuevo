export const environment = {
  production: false,
  /**
   * API de vecsa-backend. Usa un puerto distinto de :8000 si en tu máquina
   * ese puerto ya lo ocupa otro Laravel (p. ej. otro proyecto).
   * Arranque: cd vecsa-backend && php artisan serve --host=127.0.0.1 --port=8010
   */
  baseUrl: 'http://127.0.0.1:8010',
  stripePublishableKey: 'pk_test_xxx',
  tidioProjectId: ''
}; 