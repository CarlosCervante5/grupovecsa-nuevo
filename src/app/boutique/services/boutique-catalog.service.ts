import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import {
  ApiResponse,
  PaginatedData,
  BoutiqueProduct,
  BoutiqueCategory
} from '../interfaces/boutique.interfaces';

@Injectable({
  providedIn: 'root'
})
export class BoutiqueCatalogService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  public search(params: {
    category_uuid?: string;
    search?: string;
    min_price?: number;
    max_price?: number;
    page?: number;
    per_page?: number;
  }): Observable<ApiResponse<PaginatedData<BoutiqueProduct>>> {
    return this._http.post<ApiResponse<PaginatedData<BoutiqueProduct>>>(
      `${this.url}/api/boutique/catalog/search`,
      params
    );
  }

  public detail(uuid: string): Observable<ApiResponse<{ product: BoutiqueProduct; related: BoutiqueProduct[] }>> {
    return this._http.post<ApiResponse<{ product: BoutiqueProduct; related: BoutiqueProduct[] }>>(
      `${this.url}/api/boutique/catalog/detail`,
      { uuid }
    );
  }

  public categories(): Observable<ApiResponse<{ categories: BoutiqueCategory[] }>> {
    return this._http.post<ApiResponse<{ categories: BoutiqueCategory[] }>>(
      `${this.url}/api/boutique/catalog/categories`,
      {}
    );
  }
}
