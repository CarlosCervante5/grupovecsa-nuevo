import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';

import { environment } from '@environments/environment';

import { Observable } from 'rxjs';

import { GralResponse } from '@interfaces/getChecklist.interface';
import { GetDetailValuation } from '@interfaces/getDetailValuation.interface';
import { GetOnHold } from '@interfaces/getOnHoldBodyworkPaint.interface';

@Injectable({
  providedIn: 'root'
})
export class BodyworkPaintService {

  private baseUrl: string = environment.baseUrl;

  constructor(
    private _http: HttpClient
  ) { }

  // public setHypRequest(form: Object): Observable<GralResponse>{
  public setHypRequest(description: string, image: File, valuation_uuid: string): Observable<GralResponse>{

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const formData = new FormData();

    formData.append('description', description);
    formData.append('image', image);
    formData.append('valuation_uuid', valuation_uuid);

    return this._http.post<GralResponse>(`${ this.baseUrl }/api/bodyworks`, formData, { headers });

  }

  public getImgBodyworkPaint(valuation_uuid: string): Observable<GetDetailValuation>{

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const formData = new FormData();

    formData.append('valuation_uuid', valuation_uuid);

    return this._http.post<GetDetailValuation>(`${ this.baseUrl }/api/valuations/detail`, formData, { headers });
  }

  public getOnHoldBodyworkPaint(): Observable<GetOnHold>{
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.get<GetOnHold>(`${ this.baseUrl }/api/valuations/search_repairs`, { headers });
  }

  public setCostHyp(repair_uuid: string, cost: string, status: string): Observable<GralResponse>{
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const formData = new FormData();

    formData.append('status', status);
    formData.append('cost', cost);
    formData.append('repair_uuid', repair_uuid);

    return this._http.post<GralResponse>(`${ this.baseUrl }/api/bodyworks/update`, formData, { headers });

  }

}
