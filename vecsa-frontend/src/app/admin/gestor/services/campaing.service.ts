import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { FormGroup } from '@angular/forms';
import { ChangeOrder } from '../../../shared/interfaces/admin.interfaces';

//prueba
import {createcampaing , GetcampaingResponse, DeleteVehicleImage, ImageOrderPromo, DeleteCampaign} from '@interfaces/admin.interfaces';

@Injectable({
    providedIn: 'root'
})
export class CampaingService {

    baseUrl = environment.baseUrl;

    constructor(
        private _http: HttpClient
    ) { }

    public setCampaing (
        form: FormGroup
    ):Observable<createcampaing>{

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<createcampaing>(`${this.baseUrl}/api/campaigns`, form, {headers: headers });
    }

    public getCampaing (){

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<GetcampaingResponse>(`${this.baseUrl}/api/campaigns/active`, {headers: headers });
    }

    public changeOrder( imagesData:  ImageOrderPromo[]):Observable<ChangeOrder>{
       
        const body = {
            "image_order": imagesData.map( (image, index) => ({
                "uuid": image.uuid,
                "sort_id": index +1
            }))
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<ChangeOrder>(`${this.baseUrl}/api/promotions/sort_update`, body, { headers });
      }
    
      public deleteImage( uuid:any ): Observable<DeleteVehicleImage>{
        let data = {uuid};

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<DeleteVehicleImage>(`${this.baseUrl}/api/promotions/delete`, data,  { headers });
      }

      public deleteCampaign( uuid:string): Observable<DeleteCampaign>{
        let data = {uuid};

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<DeleteVehicleImage>(`${this.baseUrl}/api/campaigns/delete`, data,  { headers });
      }
    
}