import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { FormGroup } from '@angular/forms';
import { ChangeOrder } from '@interfaces/admin.interfaces';
import { CustomerPositionResponse, PointsResponse } from '@interfaces/auth.interface';

//prueba

@Injectable({
    providedIn: 'root'
})
export class AuthService {

    baseUrl = environment.baseUrl;

    constructor(
        private _http: HttpClient
    ) { }

    public getPointsRewards (){
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.post<PointsResponse>(`${this.baseUrl}/api/riders/points`, {headers: headers });
    }

    public getPositionCustomer (customer_uuid: string){
        const body = {
            'customer_uuid': customer_uuid
        }
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.post<CustomerPositionResponse>(`${this.baseUrl}/api/riders/customer_position`,body, {headers: headers });
    }
    
}