import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { FormGroup } from '@angular/forms';

//prueba
import { GralResponse, BodiesResponse, BrandsResponse, FullDetailResponse, LinesResponse, ModelsResponse, SearchResponse, VersionsResponse, VehicleUpdateResponse, VehicleStoreResponse } from '@interfaces/vehicle_data.interface';


@Injectable({
    providedIn: 'root'
})
export class VehicleService {

baseUrl = environment.baseUrl;

constructor(
    private _http: HttpClient
) { }

    /**
     * Login clásico guarda `user`; AuthService.login guarda `user_data` con forma { user, role, ... }.
     */
    private sessionUserEmail(): string {
        try {
            const raw = localStorage.getItem('user') ?? localStorage.getItem('user_data');
            if (!raw) {
                return '';
            }
            const parsed = JSON.parse(raw) as { user?: { email?: string }; email?: string };
            const email = parsed?.user?.email ?? parsed?.email;
            return typeof email === 'string' ? email : '';
        } catch {
            return '';
        }
    }

    public getVehicle( uuid:string ):Observable<FullDetailResponse>{

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        let data = { 
            uuid: uuid,
            relationship_names: ['brand', 'line', 'model', 'body', 'version', 'dealership', 'campaigns.promotions'],
        }

        return this._http.post<FullDetailResponse>(`${ this.baseUrl }/api/vehicles/detail`, data, { headers });

    }

    public getBrands():Observable<BrandsResponse>{
        return this._http.get<BrandsResponse>(`${ this.baseUrl }/api/vehicle_brands`);
    }

    public getLines(brand: string):Observable<LinesResponse>{
        return this._http.get<LinesResponse>(`${ this.baseUrl }/api/brand_lines/by_brand/${brand}`);
    }

    public getModels(line: string):Observable<ModelsResponse>{
        return this._http.get<ModelsResponse>(`${ this.baseUrl }/api/line_models/by_line/${line}`);
    }

    public getModelsByBrand(brand: string):Observable<ModelsResponse>{
        return this._http.get<ModelsResponse>(`${ this.baseUrl }/api/line_models/by_brand/${brand}`);
    }

    public getVersions(model: string):Observable<VersionsResponse>{
        return this._http.get<VersionsResponse>(`${ this.baseUrl }/api/model_versions/by_model/${model}`);
    }

    public getBodies():Observable<BodiesResponse>{
        return this._http.get<BodiesResponse>(`${ this.baseUrl }/api/vehicle_bodies`);
    }

    public attachVehicle(ids : string[], vehicle_id : string):Observable<GralResponse>{
       
            let data = 
            {
                'vehicle_uuid': vehicle_id,
                'campaing_uuids' : ids,
            }
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<VehicleUpdateResponse>(`${ this.baseUrl }/api/campaigns/attach_vehicle`, data, { headers });
        
    }


    public storeVehicle( form: FormGroup):Observable<VehicleStoreResponse>{    
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<VehicleStoreResponse>(`${ this.baseUrl }/api/vehicles`, form, { headers });
    }


    public updateVehicle( form: FormGroup):Observable<VehicleUpdateResponse>{    
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<VehicleUpdateResponse>(`${ this.baseUrl }/api/vehicles/update`, form, { headers });
    }
    

    public getVehicles( page:number, word:string, paginate: number, relationshipNames: string[]):Observable<SearchResponse>{
        
        const userMail = this.sessionUserEmail();

        let params = new HttpParams(); 

        if (word) {
            params = params.set('keyword', word);
        }

        if (page) {
            params = params.set('page', page.toString());
        }

        if(paginate) {
            params = params.set('paginate', paginate.toString());
        }

        if (relationshipNames) {
          params = params.set('relationship_names', relationshipNames.toString());
        }

        params = params.set('status', 'active,inactive');

        params = params.set('has_images', false);

        if (userMail === 'vecsapuebla@grupovecsa.com') {
            params = params.set('location_names', 'vecsa puebla');
        }

        return this._http.get<SearchResponse>(`${ this.baseUrl }/api/vehicles/search`, {params} );
    }
}
