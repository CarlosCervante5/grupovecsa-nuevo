export const POST_LOGIN_LOADING_KEY = 'vecsa_post_login';

export function markPostLoginLoading(): void {
  try {
    sessionStorage.setItem(POST_LOGIN_LOADING_KEY, '1');
  } catch {
    /* ignore */
  }
}

export function clearPostLoginLoading(): void {
  try {
    sessionStorage.removeItem(POST_LOGIN_LOADING_KEY);
  } catch {
    /* ignore */
  }
}

export function isPostLoginLoading(): boolean {
  try {
    return sessionStorage.getItem(POST_LOGIN_LOADING_KEY) === '1';
  } catch {
    return false;
  }
}

export function showPostLoginOverlayElement(): void {
  const el = document.getElementById('vecsa-post-login-overlay');
  if (el) {
    el.hidden = false;
  }
}

export function removePostLoginOverlayElement(): void {
  document.getElementById('vecsa-post-login-overlay')?.remove();
}

/** Marca sesión en tránsito y muestra cubo hasta recarga/navegación al panel. */
export function beginPostLoginTransition(): void {
  markPostLoginLoading();
  showPostLoginOverlayElement();
}
