import { Injectable } from '@angular/core';
import { Form, UntypedFormGroup } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

// Interfaces
// import { Notification } from './../../interfaces/detail/vehicle_data.interface';
// import { RecommendedCarsData } from '../../interfaces/detail/recommended_cars_data.interface';
import { Lead } from '@interfaces/dashboard.interface';
import { DetailResponse , RecommendedResponse} from '@interfaces/vehicle_data.interface';
//import { Lead } from '../../interfaces/detail/lead.interface';
//import { DetailResponse, RecommendedResponse } from './../../interfaces/detail/vehicle_data.interface'; 

@Injectable({
  providedIn: 'root'
})

export class DetailService {

  private baseUrl:string = environment.baseUrl;
  private headers = new HttpHeaders().set('content-type', 'application/json').set('X-Requested-With', 'XMLHttpRequest');        

  constructor(private _http:HttpClient) { }

  public getVehicleDetail( uuid: string ): Observable<DetailResponse>{        
    const body = { uuid: uuid };
    return this._http.post<DetailResponse>(`${this.baseUrl}/api/vehicles/detail`, body, { headers: this.headers });
  }

  public getRecommendedVehicles( priceMin: number, priceMax: number): Observable<RecommendedResponse>{

    const body = { 
      prices: [priceMin, priceMax]
    };
    return this._http.post<RecommendedResponse>(`${ this.baseUrl }/api/vehicles/random`, body, {headers: this.headers});
    
  }

  public generateLead( form: UntypedFormGroup ):Observable<Lead>{
    return this._http.post<Lead>(`${ this.baseUrl }/api/leads/ask_information`, form);
  }

  /**
   * Generate notification for vehicle reserved
   */
  // public notificationReserved(notification: UntypedFormGroup): Observable<Notification> {    
  //   return this._http.post<Notification>(`${ this.baseUrl }/api/notification`, notification);
  // }

  // /**
  //  * Ask Information Vehicle
  //  * @param form FormGroup
  //  */
  // public sendAskInformationLead(form: UntypedFormGroup) {
  //   return this._http.post(`${ this.baseUrl }/api/askInformationVehicle`, form);
  // }

  // public generateOffer( form: UntypedFormGroup ):Observable<any>{
  //   return this._http.post<any>(`${ this.baseUrl }/api/offerClient`, form);
  // }

  // public getLocationvehiclesId(id:number):Observable<LocationData>{ 
  //   return this._http.get<LocationData>(`${ this.baseUrl}/api/getLocationvehiclesId/${ id }`, {headers: this.headers})
  // }

  // public getChoiceByVin ( vin: string):Observable<any>{
  //   return this._http.get(`${ this.baseUrl }/api/apartado/${ vin }`, {headers: this.headers});
  // }
  
}
