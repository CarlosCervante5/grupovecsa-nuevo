import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import { ChangeOrder, DeleteVehicleImage } from '../../../shared/interfaces/admin.interfaces';

//prueba
import { UploadImages, ImageOrder , GetcampaingResponse, GetPromotionsByBrand} from '@interfaces/admin.interfaces';

@Injectable({
  providedIn: 'root'
})
export class ImagesPromoService {

  private url: string = environment.baseUrl;

  constructor( private _http: HttpClient) { }

  public setImagesPromo( brand: string, files: File[], name : string): Observable<UploadImages>{
    
    let formData: FormData = new FormData();
    
    formData.append('campaign_uuid', `${brand}`);
    formData.append('name', `${name}`);

    files.forEach((file, index) => formData.append(`images[${index}]`, file));
    
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<UploadImages>(this.url+'/api/promotions/',formData, { headers });
  }

  public getCampaing (){

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<GetcampaingResponse>(`${this.url}/api/campaigns/active`, { headers });
  }

  public getPromotionsByBrand(brand: string): Observable<GetPromotionsByBrand>{
    return this._http.get<GetPromotionsByBrand>(`${ this.url }/api/getPromotionsByBrand/${brand}`);
  }

  public changeOrderPromo(brand: string, imagesData: ImageOrder[]): Observable<ChangeOrder>{
    const body = {
      brand: `${brand}`,
      new_order: imagesData
    };
    
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<ChangeOrder>(`${this.url}/api/changeOrderPromo`, body, { headers });
  }

  public deleteImage( vehicle_image_id: number ): Observable<DeleteVehicleImage>{
    
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.delete<DeleteVehicleImage>(`${this.url}/api/promotion/${vehicle_image_id}`, { headers });
  }

  public changeOrderPrincipal(type:string, imagesData: ImageOrder[]): Observable<ChangeOrder>{
    const body = {
      type: `${type}`,
      new_order: imagesData
    };
    
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<ChangeOrder>(`${this.url}/api/changeOrderPrincipal`, body, { headers });
  }

  public deletePromoImage( vehicle_image_id: number ): Observable<DeleteVehicleImage>{

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.delete<DeleteVehicleImage>(`${this.url}/api/event/${vehicle_image_id}`, { headers });
  }

  public updateLegal( id:number, legal:string ):Observable<GetPromotionsByBrand>{     /**ImageOrder */
    const requestData = {
      legal: legal,
      status: 'active'
    };

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.put<GetPromotionsByBrand>(`${this.url}/api/promotion/${id}`, requestData, { headers });

  }

}
