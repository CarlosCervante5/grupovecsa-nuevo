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

export function removePostLoginOverlayElement(): void {
  document.getElementById('vecsa-post-login-overlay')?.remove();
}
