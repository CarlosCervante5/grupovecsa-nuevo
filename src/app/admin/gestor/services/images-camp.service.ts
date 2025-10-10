import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

import { GetPromotionsByBrand, ImageOrder } from '../../../shared/interfaces/admin.interfaces';
import { ChangeOrder, DeleteVehicleImage } from '../../../shared/interfaces/admin.interfaces';

@Injectable({
    providedIn: 'root'
})
export class ImageCampService {

    private url: string = environment.baseUrl;
    constructor( private _http: HttpClient ) {

     }

    //  public setImagesCamp( camp: string, files: File[]): Observable<LoadImagesCamp>{
    //     const formData: FormData = new FormData();
    //     files.map( (file) => formData.append('pictures[]', file) );
    //     formData.append('brand', `${camp}`);
    //     let headers = new HttpHeaders().set('Authorization', JSON.stringify(localStorage.getItem('user_token'))).set('X-Requested-With', 'XMLHttpRequest');
    //     return this._http.post<LoadImagesCamp>(this.url+'/api/campaigns', formData, {headers: headers});
    //   }
    
}