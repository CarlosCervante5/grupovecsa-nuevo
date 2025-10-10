import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { FormGroup } from '@angular/forms';

//interfaces
import { RegisterResponse } from '@interfaces/auth.interface';

@Injectable({providedIn: 'root'})
export class StregaService {
    baseUrl = environment.baseUrl;
    constructor(
        private _http: HttpClient
    ) { }


    public createLead ( form: FormGroup ):Observable<RegisterResponse>{
    
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<RegisterResponse>(`${this.baseUrl}/api/strega/public_create`, form, { headers });
    }

}