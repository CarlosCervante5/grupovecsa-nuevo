import { enableProdMode } from '@angular/core';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';

import { AppModule } from './app/app.module';
import { environment } from '@environments/environment';

if (environment.production) {
  enableProdMode();
}

/** Tras un deploy, el navegador puede pedir chunks viejos; recargar una vez suele alinear index + bundles. */
const CHUNK_RELOAD_KEY = 'vecsa_chunk_reload';
window.addEventListener('unhandledrejection', (event: PromiseRejectionEvent) => {
  const reason = event.reason as { message?: string } | undefined;
  const msg = reason?.message ?? String(reason ?? '');
  if (!/Failed to fetch dynamically imported module|Loading chunk \d+ failed/i.test(msg)) {
    return;
  }
  if (sessionStorage.getItem(CHUNK_RELOAD_KEY)) {
    return;
  }
  sessionStorage.setItem(CHUNK_RELOAD_KEY, '1');
  event.preventDefault();
  window.location.reload();
});

platformBrowserDynamic().bootstrapModule(AppModule)
  .catch(err => console.error(err));
