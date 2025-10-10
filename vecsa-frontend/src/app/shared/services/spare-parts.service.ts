import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

import { GralResponse } from '@interfaces/getChecklist.interface';
import { GetDetailValuation } from '@interfaces/getDetailValuation.interface';
import { GetSearchRepairs } from '@interfaces/getSearchRepairs.interfaces';
import { GetVehicleDetailParts } from '@interfaces/getVehicleDetailParts.interface';
import { GetSearchParts } from '@interfaces/getSearchParts.interfaces';
import { GetSparepartsLaborTime } from '@interfaces/getSparepartsLaborTime.interfaces';
import { GetUsersByRol } from '@interfaces/admin.interfaces';

@Injectable({
  providedIn: 'root'
})
export class SparePartsService {

  private baseUrl: string = environment.baseUrl;

  constructor(
    private _http: HttpClient
  ) { }

  public createSpareParts(form: Object): Observable<GralResponse>{
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<GralResponse>(`${ this.baseUrl }/api/spare_parts`, form, { headers });
  }

  public getSpareParts(valuation_uuid: string):Observable<GetDetailValuation>{

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const form: FormData = new FormData();

    form.append('valuation_uuid', valuation_uuid);

    return this._http.post<GetDetailValuation>(`${ this.baseUrl }/api/valuations/detail`, form, { headers });

  }

  public getSearchParts(page: number = 1): Observable<GetSearchParts>{
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.get<GetSearchParts>(`${ this.baseUrl }/api/valuations/search_parts?page=${ page }`, { headers });
  }

  public getSparepartsLaborTime(valuation_uuid: string):Observable<GetSparepartsLaborTime>{
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const form: FormData = new FormData();
    form.append('valuation_uuid', valuation_uuid);

    return this._http.post<GetSparepartsLaborTime>(`${ this.baseUrl }/api/valuations/detail`, form, { headers });
  }

  public getSellers() {
    const body = {
      'role_name': 'seller'
    }
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GetUsersByRol>(`${ this.baseUrl }/api/users/by_role`, body, { headers });
  }

  public getVehicleDetailParts(valuation_uuid: string):Observable<GetVehicleDetailParts>{
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const form: FormData = new FormData();
    form.append('valuation_uuid', valuation_uuid);

    return this._http.post<GetVehicleDetailParts>(`${ this.baseUrl }/api/valuations/detail_parts`, form, { headers });

  }

  public deleteSparePart(part_uuid: string):Observable<GralResponse>{
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const form: FormData = new FormData();

    form.append('part_uuid', part_uuid);

    return this._http.post<GralResponse>(`${ this.baseUrl }/api/spare_parts/delete`, form, { headers });
  }

  public sparePartsEdit(price_original: string, delivery_original: string, supplier_original: string,
                        price_generic: string, delivery_generic: string, supplier_generic: string,
                        price_used: string, delivery_used: string, supplier_used: string,
                        part_uuid: string
                      ): Observable<GralResponse>{
                          const body = {
                            'price_original': price_original,
                            'delivery_original': delivery_original,
                            'supplier_original': supplier_original,
                            'price_generic': price_generic,
                            'delivery_generic': delivery_generic,
                            'supplier_generic': supplier_generic,
                            'price_used': price_used,
                            'delivery_used': delivery_used,
                            'supplier_used': supplier_used,
                            'part_uuid': part_uuid
                          };
                          let user_token = localStorage.getItem('user_token');
                          let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
  
                          return this._http.post<GralResponse>(`${ this.baseUrl }/api/valuations/update_parts`, body, { headers });
                        }

}
