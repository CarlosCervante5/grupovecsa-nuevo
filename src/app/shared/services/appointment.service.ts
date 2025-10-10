import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

// Interfaces
import { ValuationAppointments, AppointmentResponse, ValuatorsResponse } from '@interfaces/getAppointments.interface';
import { GralResponse } from '@interfaces/vehicle_data.interface';


@Injectable({
  providedIn: 'root'
})
export class AppointmentService {
  baseUrl = environment.baseUrl;
  constructor(
    private _http: HttpClient
  ) { }

  public setAppointmentValuation ( form: FormGroup ):Observable<GralResponse>{

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<GralResponse>(`${this.baseUrl}/api/appointment/valuation_appointment`, form, {headers: headers});
  }

  public setExternalAppointmentValuation( form: FormGroup ): Observable<GralResponse>{
    let headers = new HttpHeaders().set('content-type', 'application/json').set('X-Requested-With', 'XMLHttpRequest');
    return this._http.post<GralResponse>(`${this.baseUrl}/api/appointment`, form, {headers: headers});
  }

  public getAppointments( page: number, keyword: string = ''): Observable<ValuationAppointments>{
    let user_token = localStorage.getItem('user_token');    
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    let params = new HttpParams();

    if (page) {
      params = params.set('page', page.toString());
    }

    if (keyword.length > 0) {
      params = params.set('keyword', keyword);
    }

    return this._http.get<ValuationAppointments>(`${ this.baseUrl }/api/valuations/search`, {headers, params}); 
  }

  public getAppointmentsHyP( page: number, keyword: string = ''): Observable<ValuationAppointments>{
    let user_token = localStorage.getItem('user_token');    
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    let params = new HttpParams();

    if (page) {
      params = params.set('page', page.toString());
    }

    if (keyword.length > 0) {
      params = params.set('keyword', keyword);
    }

    return this._http.get<ValuationAppointments>(`${ this.baseUrl }/api/valuations/search_repairs`, {headers, params});
  }

  // public searchByKeyword( busqueda: string ):Observable<ValuationAppointments> {
  //   let user_token = localStorage.getItem('user_token');    
  //   let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
  //   let params = new HttpParams

  //   if (busqueda.length > 0) {
  //     params = params.set('keyword', busqueda)
  //   }
  //   return this._http.get<ValuationAppointments>(`${ this.baseUrl }/api/valuations/search`, {headers, params} );
  // }

  public getExternalDates(page:number){
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    let params = new HttpParams(); 

    if (page) {
    params = params.set('page', page.toString());
    }
    return this._http.post<AppointmentResponse>(`${this.baseUrl}/api/appointment/search`, null ,{headers: headers, params});
  }

  public getValuators(){
    const body = {
      'role_name':    'valuator'
    }
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<ValuatorsResponse>(`${this.baseUrl}/api/users/by_role`, body ,{headers: headers});
  }

  public attatchValuator(uuid:string, appointment:string){
    const body = {
      'appointment_uuid':    appointment,
      'valuator_uuid':    uuid,
    }
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<ValuatorsResponse>(`${this.baseUrl}/api/appointment/attatch_valuator`, body ,{headers: headers});
  }
}
