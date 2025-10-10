import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';

import { Observable } from 'rxjs';

import { GetAcquisitionsChecklist } from '@interfaces/getAcquisitionsChecklist.interfaces';
import { environment } from '@environments/environment';
import { GralResponse } from '@interfaces/getChecklist.interface';

@Injectable({
  providedIn: 'root'
})
export class AcquisitionsChecklistService {

  private baseUrl: string = environment.baseUrl;

  constructor(
    private _http: HttpClient
  ) { }

  public getAcquisitionsChecklist(valuation_uuid: string): Observable<GetAcquisitionsChecklist>{

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const form: FormData = new FormData();

    form.append('valuation_uuid', valuation_uuid);

    return this._http.post<GetAcquisitionsChecklist>(`${this.baseUrl}/api/acquisitions/checklist`, form, { headers });
  }

  public updateAcquisitions(valuation_uuid: string, checkpoint_uuid: string, selected_value: number | string | null): Observable<GralResponse>{
    const body = {
      'valuation_uuid': valuation_uuid,
      'checkpoint_uuid': checkpoint_uuid,
      'selected_value': String(selected_value)
    }
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${ user_token }`);

    return this._http.post<GralResponse>(`${ this.baseUrl }/api/acquisitions/attatch`, body, { headers });
  }

  public uploadPdf(valuation_uuid: string, picture: File): Observable<GralResponse>{

    let formData: FormData = new FormData();
    formData.append('valuation_uuid', valuation_uuid);
    formData.append('documentation_pdf', picture);

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${ user_token }`);

    return this._http.post<GralResponse>(`${this.baseUrl}/api/acquisitions/upload_pdf`, formData, { headers });
  }
}
