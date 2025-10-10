import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import { ReceptionForm } from '@interfaces/dashboard.interface';

@Injectable({
  providedIn: 'root'
})
export class ReceptionFormService {

  // Global Url
  private url: string = '';

  // Headers
  private headers = new HttpHeaders().set('Content-Type', 'application/json').set('X-Requested-With', 'XMLHttpRequest');

  constructor(private _http: HttpClient) {
    this.url = environment.baseUrl;
  }

  public receptionForm(form : FormData): Observable<ReceptionForm>{

    return this._http.post<ReceptionForm>(this.url+'/api/leads/reception_form', form, {headers: this.headers});
  }

  public sendMailFandI(): Observable<any>{
    return this._http.post<any>(`${this.url}/api/leads/reception_notification`, {headers: this.headers});
  }

}
