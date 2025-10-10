import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { FormGroup } from '@angular/forms';

import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

// Interfaces
import { GetChecklist, GralResponse } from '@interfaces/getChecklist.interface';
import { GetUsersByRol } from '@interfaces/admin.interfaces';

@Injectable({
  providedIn: 'root'
})
export class ChecklistService {

  private baseUrl: string = environment.baseUrl;

  constructor(
    private _http: HttpClient
  ) { }

  public getChecklist(valuation_uuid: string):Observable<GetChecklist>{

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const form: FormData = new FormData();

    form.append('valuation_uuid', valuation_uuid);

    return this._http.post<GetChecklist>(`${this.baseUrl}/api/valuations/checklist`, form, { headers });

  }

  public updateCustomerInformation(form: FormGroup): Observable<GetChecklist>{ /** */
    // console.log(form);
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<GetChecklist>(`${ this.baseUrl }/api/valuations/update_vehicle`, form, { headers });
  }

  public getTechnicians(): Observable<GetUsersByRol>{
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const form: FormData = new FormData();

    form.append('role_name', 'technician');

    return this._http.post<GetUsersByRol>(`${ this.baseUrl }/api/users/by_role`, form, { headers } );
  }

  public updateValuation(valuation_uuid:string , check_uuid:string, selected_value:string):  Observable<GralResponse>{
    console.log('valuación:', valuation_uuid);
    console.log('check uuid:', check_uuid);
    console.log('seleccion:', selected_value);
    const body = {
      'valuation_uuid':       valuation_uuid,
      'checkpoint_uuid':      check_uuid,
      'selected_value':       selected_value
    }
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<GralResponse>(`${ this.baseUrl }/api/valuations/attatch`, body, { headers });
  }

  public updateStatus(valuation_uuid: string): Observable<GralResponse>{
    console.log(valuation_uuid);
    
    
    const body = {
      'valuation_uuid': valuation_uuid,
      'status': 'on_progress'
    }
    
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<GralResponse>(`${ this.baseUrl }/api/valuations/update`, body, { headers });
  }

}
