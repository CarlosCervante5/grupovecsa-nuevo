import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

export interface FormField {
  key: string;
  label: string;
  type: 'text' | 'number' | 'email' | 'password' | 'select' | 'textarea' | 'checkbox' | 'multi-select';
  required?: boolean;
  options?: { value: string; label: string }[];
  optionsEndpoint?: string;
  optionsMethod?: 'GET' | 'POST';
  optionsDataKey?: string;
  optionsValueKey?: string;
  optionsLabelKey?: string;
}

export interface CrudSection {
  key: string;
  label: string;
  icon: string;
  endpoint: string;
  method: 'GET' | 'POST';
  dataKey: string;
  columns: { key: string; label: string }[];
  searchable?: boolean;
  paginated?: boolean;
  storeEndpoint?: string;
  updateEndpoint?: string;
  deleteEndpoint?: string;
  formFields?: FormField[];
  idKey?: string;
  useApiResourceDelete?: boolean;
}

@Injectable({ providedIn: 'root' })
export class DevCrudService {
  private baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  private get headers(): HttpHeaders {
    const token = localStorage.getItem('user_token') || '';
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      Authorization: `Bearer ${token}`,
    });
  }

  fetch(endpoint: string, method: 'GET' | 'POST', body: any = {}): Observable<any> {
    const url = `${this.baseUrl}/api/${endpoint}`;
    if (method === 'GET') {
      let params: any = {};
      if (body && typeof body === 'object') {
        Object.keys(body).forEach(k => { if (body[k] !== undefined && body[k] !== null) params[k] = body[k]; });
      }
      return this.http.get(url, { headers: this.headers, params });
    }
    return this.http.post(url, body, { headers: this.headers });
  }

  store(endpoint: string, body: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/${endpoint}`, body, { headers: this.headers });
  }

  update(endpoint: string, body: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/${endpoint}`, body, { headers: this.headers });
  }

  put(endpoint: string, body: any): Observable<any> {
    return this.http.put(`${this.baseUrl}/api/${endpoint}`, body, { headers: this.headers });
  }

  delete(endpoint: string, body: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/${endpoint}`, body, { headers: this.headers });
  }

  /** DELETE a una ruta API relativa, p. ej. `benchmark/meta-token`. */
  deleteAt(endpoint: string): Observable<any> {
    return this.http.delete(`${this.baseUrl}/api/${endpoint}`, { headers: this.headers });
  }

  deleteById(resource: string, id: number | string): Observable<any> {
    return this.http.delete(`${this.baseUrl}/api/${resource}/${id}`, { headers: this.headers });
  }
}
