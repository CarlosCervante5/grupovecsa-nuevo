import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';

import { Observable } from 'rxjs';

// Environment
import { environment } from '@environments/environment';

// Interfaces
import { GetDetailValuation } from '@interfaces/getDetailValuation.interface';

@Injectable({
  providedIn: 'root'
})
export class DetailValuationService {

  private baseUrl: string = environment.baseUrl;

  constructor(
    private _http: HttpClient
  ) { }

  public getDetailValuation(valuation_uuid: string): Observable<GetDetailValuation>{
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const form: FormData = new FormData();
    form.append('valuation_uuid', valuation_uuid);

    return this._http.post<GetDetailValuation>(`${this.baseUrl}/api/valuations/detail`, form, { headers });

  }

}
