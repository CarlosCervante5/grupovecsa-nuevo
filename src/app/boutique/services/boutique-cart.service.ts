import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, BehaviorSubject, tap, catchError, throwError, of } from 'rxjs';
import { Router } from '@angular/router';
import { environment } from '@environments/environment';
import { ApiResponse, BoutiqueCart, BoutiqueCartItem, BoutiqueProduct } from '../interfaces/boutique.interfaces';

const LOCAL_CART_KEY = 'boutique_local_cart';

/** Minimal shape stored locally for guest users */
interface LocalCartItem {
  uuid: string;          // item uuid (generated locally)
  product_uuid: string;
  product_snapshot: BoutiqueProduct;
  quantity: number;
  variant_uuid?: string;
}

@Injectable({ providedIn: 'root' })
export class BoutiqueCartService {

  private url = environment.baseUrl;

  private _cartCount$ = new BehaviorSubject<number>(0);
  public cartCount$ = this._cartCount$.asObservable();

  constructor(private _http: HttpClient, private router: Router) {
    // Seed count from local cart on startup (for guests)
    if (!this.isLoggedIn) {
      this._cartCount$.next(this.localItems().length);
    }
  }

  // ── Auth helpers ──────────────────────────────────────────────────────────

  private get isLoggedIn(): boolean {
    return !!localStorage.getItem('user_token');
  }

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  private handle401<T>(obs: Observable<T>): Observable<T> {
    return obs.pipe(
      catchError(err => {
        if (err.status === 401) {
          localStorage.removeItem('user_token');
          this._cartCount$.next(0);
        }
        return throwError(() => err);
      })
    );
  }

  public updateCount(cart: BoutiqueCart | null): void {
    this._cartCount$.next(cart?.items?.length ?? 0);
  }

  // ── Local cart (guest) ────────────────────────────────────────────────────

  private localItems(): LocalCartItem[] {
    try {
      return JSON.parse(localStorage.getItem(LOCAL_CART_KEY) || '[]');
    } catch { return []; }
  }

  private saveLocal(items: LocalCartItem[]): void {
    localStorage.setItem(LOCAL_CART_KEY, JSON.stringify(items));
    this._cartCount$.next(items.length);
  }

  private localToCart(items: LocalCartItem[]): BoutiqueCart {
    const cartItems: BoutiqueCartItem[] = items.map(i => ({
      uuid: i.uuid,
      product: i.product_snapshot,
      quantity: i.quantity,
      subtotal: i.product_snapshot.price * i.quantity,
    }));
    return {
      uuid: 'local',
      items: cartItems,
      total: cartItems.reduce((s, i) => s + i.subtotal, 0),
    };
  }

  private localGet(): Observable<ApiResponse<BoutiqueCart>> {
    return of({ status: 200, message: 'ok', data: this.localToCart(this.localItems()) });
  }

  private localAdd(product: BoutiqueProduct, quantity: number, variant_uuid?: string): Observable<ApiResponse<BoutiqueCart>> {
    const items = this.localItems();
    const existing = items.find(i => i.product_uuid === product.uuid && i.variant_uuid === variant_uuid);
    if (existing) {
      existing.quantity = Math.min(existing.quantity + quantity, product.stock);
    } else {
      items.push({
        uuid: crypto.randomUUID(),
        product_uuid: product.uuid,
        product_snapshot: product,
        quantity: Math.min(quantity, product.stock),
        variant_uuid,
      });
    }
    this.saveLocal(items);
    return of({ status: 200, message: 'ok', data: this.localToCart(items) });
  }

  private localUpdate(item_uuid: string, quantity: number): Observable<ApiResponse<BoutiqueCart>> {
    const items = this.localItems();
    const item = items.find(i => i.uuid === item_uuid);
    if (item) {
      item.quantity = Math.max(1, Math.min(quantity, item.product_snapshot.stock));
    }
    this.saveLocal(items);
    return of({ status: 200, message: 'ok', data: this.localToCart(items) });
  }

  private localRemove(item_uuid: string): Observable<ApiResponse<BoutiqueCart>> {
    const items = this.localItems().filter(i => i.uuid !== item_uuid);
    this.saveLocal(items);
    return of({ status: 200, message: 'ok', data: this.localToCart(items) });
  }

  /** Clear local cart (called after syncing to server on login) */
  public clearLocal(): void {
    localStorage.removeItem(LOCAL_CART_KEY);
    this._cartCount$.next(0);
  }

  /**
   * Sync local cart items to the server after login.
   * Call this from the auth service / login component.
   */
  public syncLocalCartToServer(): Observable<void> {
    const items = this.localItems();
    if (!items.length) return of(undefined as void);

    // Fire-and-forget each item sequentially via reduce
    const chain = items.reduce((prev, item) => {
      return prev.then(() =>
        this._http.post<ApiResponse<BoutiqueCart>>(
          `${this.url}/api/boutique/cart/add`,
          { product_uuid: item.product_uuid, quantity: item.quantity, ...(item.variant_uuid ? { variant_uuid: item.variant_uuid } : {}) },
          { headers: this.getHeaders() }
        ).pipe(tap(res => this.updateCount(res.data))).toPromise()
      );
    }, Promise.resolve<any>(undefined));

    return new Observable(obs => {
      chain.then(() => {
        this.clearLocal();
        obs.next(undefined as void);
        obs.complete();
      }).catch(() => {
        this.clearLocal(); // clear anyway to avoid re-sending
        obs.next(undefined as void);
        obs.complete();
      });
    });
  }

  // ── Public API (auto-routes to local or server) ───────────────────────────

  public get(): Observable<ApiResponse<BoutiqueCart>> {
    if (!this.isLoggedIn) return this.localGet();
    return this.handle401(
      this._http.post<ApiResponse<BoutiqueCart>>(
        `${this.url}/api/boutique/cart/get`, {},
        { headers: this.getHeaders() }
      ).pipe(tap(res => this.updateCount(res.data)))
    );
  }

  /**
   * Add to cart. When not logged in, product snapshot is required
   * so we can build the local cart display.
   */
  public add(product_uuid: string, quantity: number, variant_uuid?: string, productSnapshot?: BoutiqueProduct): Observable<ApiResponse<BoutiqueCart>> {
    if (!this.isLoggedIn) {
      if (!productSnapshot) {
        // Fallback: return empty success (caller should always pass snapshot for guests)
        return of({ status: 200, message: 'ok', data: this.localToCart(this.localItems()) });
      }
      return this.localAdd(productSnapshot, quantity, variant_uuid);
    }
    return this.handle401(
      this._http.post<ApiResponse<BoutiqueCart>>(
        `${this.url}/api/boutique/cart/add`,
        { product_uuid, quantity, ...(variant_uuid ? { variant_uuid } : {}) },
        { headers: this.getHeaders() }
      ).pipe(tap(res => this.updateCount(res.data)))
    );
  }

  public update(item_uuid: string, quantity: number): Observable<ApiResponse<BoutiqueCart>> {
    if (!this.isLoggedIn) return this.localUpdate(item_uuid, quantity);
    return this.handle401(
      this._http.post<ApiResponse<BoutiqueCart>>(
        `${this.url}/api/boutique/cart/update`,
        { item_uuid, quantity },
        { headers: this.getHeaders() }
      ).pipe(tap(res => this.updateCount(res.data)))
    );
  }

  public remove(item_uuid: string): Observable<ApiResponse<BoutiqueCart>> {
    if (!this.isLoggedIn) return this.localRemove(item_uuid);
    return this.handle401(
      this._http.post<ApiResponse<BoutiqueCart>>(
        `${this.url}/api/boutique/cart/remove`,
        { item_uuid },
        { headers: this.getHeaders() }
      ).pipe(tap(res => this.updateCount(res.data)))
    );
  }
}
