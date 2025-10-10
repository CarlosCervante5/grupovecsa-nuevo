import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { FormGroup } from '@angular/forms';
import { ChangeOrder, GralResponse } from '@interfaces/admin.interfaces';

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

    public changeOrder(uuid:number, imagesData:  ImageOrderPromo[]):Observable<ChangeOrder>{
       
        const body = {
            "image_order": imagesData.map( (image, index) => ({
            "uuid": image.uuid,
            "sort_id": index +1
        }))
        }  
        let headers = new HttpHeaders().set('Authorization', JSON.stringify(localStorage.getItem('user_token'))).set('X-Requested-With', 'XMLHttpRequest');        
        return this._http.post<ChangeOrder>(`${this.baseUrl}/api/promotions/sort_update`, body, {headers: headers });
      }
    
      public deleteImage( uuid:any ): Observable<DeleteVehicleImage>{
        let data = {uuid};
        let headers = new HttpHeaders().set('Authorization', JSON.stringify(localStorage.getItem('user_token'))).set('X-Requested-With', 'XMLHttpRequest');        
        return this._http.post<DeleteVehicleImage>(`${this.baseUrl}/api/promotions/delete`, data,  {headers: headers });
      }

      public deleteCampaign( uuid:string): Observable<DeleteCampaign>{
        let data = {uuid};
        let headers = new HttpHeaders().set('Authorization', JSON.stringify(localStorage.getItem('user_token'))).set('X-Requested-With', 'XMLHttpRequest');        
        return this._http.post<DeleteVehicleImage>(`${this.baseUrl}/api/campaigns/delete`, data,  {headers: headers });
      }

      public searchByName(){
        const body = {
            'campaign_name':   'aftersales'
        }
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.post<GetcampaingResponse>(`${this.baseUrl}/api/campaigns/active_by_name`,body, {headers: headers });
      }

      public setCarCare(name:string, email:string, phone:string, model:string, year:number, dealership:string, service:string, comments:string, brand:string, date:string, hour:string){
        const body = {
            'name':             name,
            'email_1':          email,
            'phone_1':          phone,
            'model_name':       model,
            'year':             year,
            'dealership_name':  dealership,
            'required_service': service,
            'comments':         comments,
            'brand_name':       brand,
            'appointment_date': date +''+ hour,
        }
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.post<GralResponse>(`${this.baseUrl}/api/leads/car_care`,body,{headers: headers });
      }
    
}