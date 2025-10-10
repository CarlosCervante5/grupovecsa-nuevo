import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '@environments/environment';
import { GralResponse } from '@interfaces/getChecklist.interface';
import { GetExternalImages } from '@interfaces/getExternalImages.interface';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class InternalImagesService {

  private baseUrl: string = environment.baseUrl;

  constructor(
    private _http: HttpClient
  ) { }

  public setInternalImage(formItem: any): Observable<GralResponse>{
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    const form: FormData = new FormData();

    Object.keys(formItem).forEach(key => {
      const value = formItem[key];
      if (Array.isArray(value)) {
        value.forEach((item) => form.append(`${key}[]`, item));
      } else {
        form.append(key, value);
      }
    });
    return this._http.post<GralResponse>(`${this.baseUrl}/api/valuations/update_images`, form, {headers});
  }

  public getInternalImage(valuation_uuid: number): Observable<GetExternalImages>{

    const body = {
      'valuation_uuid': valuation_uuid,
      'group_name': 'Interior'
    }

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<GetExternalImages>(`${ this.baseUrl }/api/valuations/search_images`, body, { headers });
  }

  public getOthersInternalImages(valuation_uuid: number): Observable<GetExternalImages>{
    const body = {
      'valuation_uuid': valuation_uuid,
      'group_name': 'Interior_others'
    }

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GetExternalImages>(`${ this.baseUrl }/api/valuations/search_images`, body, {headers});
  }

}
