import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import { UntypedFormGroup } from '@angular/forms';
import { Lead } from '../interfaces/lead_service.interface';

 


@Injectable({
  providedIn: 'root'
})
export class LeadsService {

  private baseUrl:string = environment.baseUrl;
  private headers = new HttpHeaders().set('Content-Type', 'application/json').set('X-Requested-With', 'XMLHttpRequest');        

  constructor(private _http:HttpClient) { }

  public generateQuote(data: UntypedFormGroup):Observable<Lead>{
    return this._http.post<Lead>(`${ this.baseUrl }/api/landing_lead`, data, {
      headers: this.headers.set('Authorization', JSON.stringify(localStorage.getItem('user_token')))
    });
  }
}
