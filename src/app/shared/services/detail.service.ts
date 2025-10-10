import { Injectable } from '@angular/core';
import { UntypedFormGroup } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

// Interfaces
import { Lead } from '@interfaces/dashboard.interface';
import { DetailResponse , RecommendedResponse} from '@interfaces/vehicle_data.interface';

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
  
}
