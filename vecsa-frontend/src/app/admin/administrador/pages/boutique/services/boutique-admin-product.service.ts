import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import {
  ApiResponse,
  PaginatedData,
  BoutiqueProduct,
  BoutiqueProductImage
} from '../../../../../boutique/interfaces/boutique.interfaces';

@Injectable({
  providedIn: 'root'
})
export class BoutiqueAdminProductService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  public search(params: {
    category_uuid?: string;
    search?: string;
    active?: boolean;
    page?: number;
    per_page?: number;
  }): Observable<ApiResponse<PaginatedData<BoutiqueProduct>>> {
    return this._http.post<ApiResponse<PaginatedData<BoutiqueProduct>>>(
      `${this.url}/api/boutique/admin/products/search`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public detail(uuid: string): Observable<ApiResponse<{ product: BoutiqueProduct }>> {
    return this._http.post<ApiResponse<{ product: BoutiqueProduct }>>(
      `${this.url}/api/boutique/admin/products/detail`,
      { uuid },
      { headers: this.getHeaders() }
    );
  }

  public store(params: {
    category_uuid: string;
    name: string;
    description?: string;
    price: number;
    sku: string;
    stock: number;
    active?: boolean;
    /** Obligatorio vía API si el usuario tiene varias sucursales asignadas */
    dealership_id?: number;
  }): Observable<ApiResponse<BoutiqueProduct>> {
    return this._http.post<ApiResponse<BoutiqueProduct>>(
      `${this.url}/api/boutique/admin/products/store`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public update(params: {
    uuid: string;
    category_uuid?: string;
    name?: string;
    description?: string;
    price?: number;
    sku?: string;
    stock?: number;
    active?: boolean;
    dealership_id?: number | null;
  }): Observable<ApiResponse<BoutiqueProduct>> {
    return this._http.post<ApiResponse<BoutiqueProduct>>(
      `${this.url}/api/boutique/admin/products/update`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public delete(uuid: string): Observable<ApiResponse<any>> {
    return this._http.post<ApiResponse<any>>(
      `${this.url}/api/boutique/admin/products/delete`,
      { uuid },
      { headers: this.getHeaders() }
    );
  }

  public storeImage(productUuid: string, file: File): Observable<ApiResponse<BoutiqueProductImage>> {
    const formData = new FormData();
    formData.append('product_uuid', productUuid);
    formData.append('image', file);
    const token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    return this._http.post<ApiResponse<BoutiqueProductImage>>(
      `${this.url}/api/boutique/admin/product_images/store`,
      formData,
      { headers }
    );
  }

  public sortImages(params: {
    product_uuid: string;
    images: { uuid: string; sort_id: number }[];
  }): Observable<ApiResponse<any>> {
    return this._http.post<ApiResponse<any>>(
      `${this.url}/api/boutique/admin/product_images/sort`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public deleteImage(uuid: string): Observable<ApiResponse<any>> {
    return this._http.post<ApiResponse<any>>(
      `${this.url}/api/boutique/admin/product_images/delete`,
      { uuid },
      { headers: this.getHeaders() }
    );
  }
}
