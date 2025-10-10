import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

// Interfaces
import { GenerateLead } from '@interfaces/dashboard.interface';
import { UntypedFormGroup } from '@angular/forms';
import { RecommendedResponse } from '@interfaces/vehicle_data.interface'; 

@Injectable({
  providedIn: 'root'
})

export class HomeService {
  private baseUrl:string = environment.baseUrl;

  // Headers
  private headers = new HttpHeaders().set('Content-Type', 'application/json').set('X-Requested-With', 'XMLHttpRequest');

  constructor(private _http:HttpClient) { }

  public getRecommendedVehicles(): Observable<RecommendedResponse>{

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<RecommendedResponse>(`${ this.baseUrl }/api/vehicles/random`, { headers });
  }

  // public generateLead(data: UntypedFormGroup):Observable<GenerateLead>{

  //   let user_token = localStorage.getItem('user_token');
  //   let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

  //   return this._http.post<GenerateLead>(`${ this.baseUrl }/api/lead`, data, { headers });
  // }

  public generateRider(data: UntypedFormGroup):Observable<GenerateLead>{
    return this._http.post<GenerateLead>(`${ this.baseUrl }/api/leads/riders_quiz`, data, {headers: this.headers});
  }

}