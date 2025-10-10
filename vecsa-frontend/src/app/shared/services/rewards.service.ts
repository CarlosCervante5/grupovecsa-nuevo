import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { environment } from '@environments/environment';
import { PointsResponse, RedeemPointsResponse, RewardsResponse } from '@interfaces/rewards.interface';
import { GralResponse } from '@interfaces/vehicle_data.interface';
import { Observable } from 'rxjs';


@Injectable({
    providedIn: 'root'
})
export class RewardsService {

baseUrl = environment.baseUrl;

constructor(
    private _http: HttpClient
) { }

    public getRewardsByCategory( category: string ):Observable<RewardsResponse>{

        const body = {
            "category": category
        };
     
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<RewardsResponse>(`${ this.baseUrl }/api/rewards/by_category`, body, { headers });
    }

    public updatePoints( form: FormGroup ):Observable<RewardsResponse>{
     
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<RewardsResponse>(`${ this.baseUrl }/api/rewards/update_sale`, form, { headers });
    }

    public customerPoints( customer_uuid: string ):Observable<PointsResponse>{
     
        const body = {
            "customer_uuid": customer_uuid
        };

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<PointsResponse>(`${ this.baseUrl }/api/rewards/customer_points`, body, { headers });
    }

    public redeemPoints( customer_uuid: string ):Observable<RedeemPointsResponse>{
        
        const body = {
            "customer_uuid": customer_uuid
        };

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<RedeemPointsResponse>(`${ this.baseUrl }/api/rewards/redeem_customer_points`, body, { headers });
    }
}
