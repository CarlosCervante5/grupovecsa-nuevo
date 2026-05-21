/** Lleva el scroll al inicio tras cambiar de ruta en boutique (window + ancla del layout). */
export function scrollBoutiquePageToTop(): void {
  if (typeof window === 'undefined') {
    return;
  }

  window.scrollTo({ top: 0, left: 0, behavior: 'auto' });

  const anchor = document.getElementById('moveTop');
  if (anchor) {
    anchor.scrollIntoView({ block: 'start', behavior: 'auto' });
  }
}
