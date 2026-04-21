import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import {
  ApiResponse,
  PaginatedData,
  BoutiqueCategory
} from '../../../../../boutique/interfaces/boutique.interfaces';

@Injectable({
  providedIn: 'root'
})
export class BoutiqueAdminCategoryService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  public search(params: {
    search?: string;
    page?: number;
    per_page?: number;
  }): Observable<ApiResponse<PaginatedData<BoutiqueCategory>>> {
    return this._http.post<ApiResponse<PaginatedData<BoutiqueCategory>>>(
      `${this.url}/api/boutique/admin/categories/search`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public store(params: {
    name: string;
    description?: string;
    active?: boolean;
    parent_uuid?: string | null;
  }): Observable<ApiResponse<BoutiqueCategory>> {
    return this._http.post<ApiResponse<BoutiqueCategory>>(
      `${this.url}/api/boutique/admin/categories/store`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public update(params: {
    uuid: string;
    name?: string;
    description?: string;
    active?: boolean;
    parent_uuid?: string | null;
  }): Observable<ApiResponse<BoutiqueCategory>> {
    return this._http.post<ApiResponse<BoutiqueCategory>>(
      `${this.url}/api/boutique/admin/categories/update`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public delete(uuid: string): Observable<ApiResponse<any>> {
    return this._http.post<ApiResponse<any>>(
      `${this.url}/api/boutique/admin/categories/delete`,
      { uuid },
      { headers: this.getHeaders() }
    );
  }
}
