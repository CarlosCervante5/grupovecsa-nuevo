import { Injectable } from '@angular/core';
import { environment } from '@environments/environment';
import { HttpHeaders, HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { ImageOrder } from '@interfaces/vehicle_data.interface';
import { ChangeOrder, DeleteVehicleImage, LoadImages } from '@interfaces/admin.interfaces';

@Injectable({
  providedIn: 'root'
})
export class ImagesService {

  private url: string = environment.baseUrl;

  constructor(private _http:HttpClient) { }

  public setImage(
    vehicle_id:string,
    files: File[]
  ): Observable<LoadImages>{

    const formData: FormData = new FormData();

    formData.append('vehicle_uuid', `${vehicle_id}`);  
    files.map( (file) => formData.append('images[]', file));

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<LoadImages>(this.url+'/api/vehicle_images', formData, { headers });

  }

  public changeOrder(imagesData: ImageOrder[]){

    const body = {
        "image_order": imagesData.map( (image, index) => ({
          "uuid": image.id, 
          "sort_id": index + 1
        }))
    };
 
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<ChangeOrder>(`${this.url}/api/vehicle_images/sort_update`, body, { headers });

  }

  public deleteImage( vehicle_image_uuid:string ): Observable<DeleteVehicleImage>{

    let data = { uuid: vehicle_image_uuid };

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<DeleteVehicleImage>(`${this.url}/api/vehicle_images/delete`, data, { headers });

  }
}
