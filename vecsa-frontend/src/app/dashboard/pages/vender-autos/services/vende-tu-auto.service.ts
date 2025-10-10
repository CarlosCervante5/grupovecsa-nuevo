import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { Brands, DataModels } from '@interfaces/dashboard.interface';
//import { Brands } from '../Interfaces/vende-tu-auto.interface';
//import { DataModels } from '../../comprar-autos/interfaces/compra-tu-auto/data_models.interface';

@Injectable({
  providedIn: 'root'
})
export class VendeTuAutoService {

  // Global Url
  private url: string = '';

  constructor(private _http: HttpClient) { 
    this.url = environment.baseUrl;
  }

  /**
   * Get Brands
   */
  public brands(): Observable<Brands>{
    return this._http.get<Brands>(`${this.url}/api/brands`);
  }

  /**
   * Get models by brand
   */
  public getModels(brand_id: number): Observable<DataModels>{
    return this._http.get<DataModels>(`${this.url}/api/vehicle/carmodels/${brand_id}`);
  }

}
