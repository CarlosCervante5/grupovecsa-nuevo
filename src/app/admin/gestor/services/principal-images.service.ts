import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { CreateEvent } from '../../../shared/interfaces/admin.interfaces';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class PrincipalImagesService {

  private url: string = environment.baseUrl;

  constructor( private _http: HttpClient) { }

  public setPrincipalImages( type: string, files: File[]): Observable<CreateEvent>{
    let d = new Date();
    let month = '' + (d.getMonth() + 1);
    let day = '' + d.getDate();
    let year = d.getFullYear();

    if (month.length < 2)
      month = '0' + month;
    if (day.length < 2)
      day = '0' + day;

    let todayDate = [year, month, day].join('-');

    const formData: FormData = new FormData();
    files.map( (file) => formData.append('pictures[]', file) );
    formData.append('title', 'IMAGEN PRINCIPAL');
    formData.append('description', 'Imagen principal');
    formData.append('brand', 'bmw');
    formData.append('type', `${type}`);
    formData.append('event_date', todayDate);

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
    return this._http.post<CreateEvent>(`${this.url}/api/uploadPrincipalImages`, formData, { headers });
  }

}
