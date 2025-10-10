import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

// Interfaces
import { DeleteVehicle, DeletedVehicle, ChangeStatusVehicle} from '@interfaces/dashboard.interface';
import { FiltersResponse,  LoadVehiclesResponse, MinMaxResponse, SearchResponse } from '@interfaces/vehicle_data.interface';

@Injectable({
  providedIn: 'root'
})

export class CompraTuAutoService {

    private baseUrl:string = environment.baseUrl;

    constructor(private _http:HttpClient) { }

    public deleteVehicle( uuid:string ):Observable<DeleteVehicle>{

        let data = { uuid }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        
        return this._http.post<DeleteVehicle>(`${ this.baseUrl }/api/vehicles/delete`, data, { headers });
    }

    public uploadCsv(    
        file: File
    ): Observable<LoadVehiclesResponse>{

        const form: FormData = new FormData();        
        
        form.append('file', file);
        
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        
        return this._http.post<LoadVehiclesResponse>(this.baseUrl+'/api/vehicles/csv_upload', form, { headers });

    }

    public deleteVehicles( uuids: string[] ): Observable<DeletedVehicle>{   

        let data = { uuids }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<DeletedVehicle>(this.baseUrl+'/api/vehicles/delete-batch', data, {headers} );

    }

    public changeStatusVehicles( uuids: string[], page_status:string ):Observable<ChangeStatusVehicle>{    

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<ChangeStatusVehicle>(this.baseUrl+'/api/vehicles/status-batch', { uuids, page_status }, { headers });
    }


    // public searchByKeyword( busqueda: string):Observable<SearchResponse>{

    //     let params = new HttpParams(); 

    //     if (busqueda.length > 0) {
    //         params = params.set('keyword', busqueda);
    //     }

    //     return this._http.get<SearchResponse>(`${ this.baseUrl }/api/vehicles/search`, {params} );

    // }

    public getFilters( 
        categories:string[],
        brandNames:string[],
        lineNames:string[],
        modelNames:string[],
        bodyNames:string[],
        versionNames:string[], 
        years:string[],
        prices:number[],
        word:string,
        page:number,
        states:string[],
        transmissions:string[],
        extColors:string[],
        intColors:string[],
        filter: boolean,
        order:string
    ):Observable<FiltersResponse>{
        let params = new HttpParams(); 
        
        if (categories.length > 0) {
            const cat = categories.map(category => category == 'Nuevo' ? 'new' : category == 'Seminuevo' ? 'pre_owned' : category);
            params = params.set('categories', cat.join(','));
        }
        
        if (brandNames.length > 0) {
            params = params.set('brand_names', brandNames.join(','));
        }

        if (lineNames.length > 0) {
            params = params.set('line_names', lineNames.join(','));
        }
        
        if (modelNames.length > 0) {
            params = params.set('model_names', modelNames.join(','));
        }

        if (bodyNames.length > 0) {
            params = params.set('body_names', bodyNames.join(','));
        }

        if (versionNames.length > 0) {
            params = params.set('version_names', versionNames.join(','));
        }

        if (years.length > 0) {
            params = params.set('years', years.join(','));
        }

        if (prices) {
            params = params.set('prices', prices.join(','));
        }

        if (word) {
            params = params.set('keyword', word);
        }

        if (page) {
            params = params.set('page', page.toString());
        }

        if (states && states.length > 0) {
            params = params.set('location_names', states.join(','));
        }

        if (transmissions && transmissions.length > 0) {
            params = params.set('transmission_names', transmissions.join(','));
        }

        if (extColors && extColors.length > 0) {
            params = params.set('exterior_colors', extColors.join(',')); /** ext_colors */
        }

        if (intColors && intColors.length > 0) {
            params = params.set('interior_colors', intColors.join(',')); /** int_colors */
        }

        if (filter !== undefined) {
            params = params.set('filters', filter.toString());
        }

        if (order != 'ninguno') {
            params = params.set('order_by', order);
        }

        return this._http.get<FiltersResponse>(`${ this.baseUrl }/api/vehicles/search`, {params} );
    }

    public getVehicles( categories:string[], brandNames:string[], lineNames:string[], modelNames:string[], bodyNames:string[], versionNames:string[], 
                            years:string[], prices:number[], word:string, page:number, states:string[], transmissions:string[], 
                            extColors:string[], intColors:string[], order:string, relationshipNames:string[] = [] ):Observable<SearchResponse>{
        
        let params = new HttpParams(); 
    
        if (categories.length > 0) {
            const cat = categories.map(category => category == 'Nuevo' ? 'new' : category == 'Seminuevo' ? 'pre_owned' : category);
            params = params.set('categories', cat.join(','));
        }
        
        if (brandNames.length > 0) {
            params = params.set('brand_names', brandNames.join(','));
        }
        
        if (relationshipNames.length > 0) {
            params = params.set('relationship_names', relationshipNames.join(','));
        }

        if (lineNames.length > 0) {
            params = params.set('line_names', lineNames.join(','));
        }
        
        if (modelNames.length > 0) {
            params = params.set('model_names', modelNames.join(','));
        }

        if (bodyNames.length > 0) {
            params = params.set('body_names', bodyNames.join(','));
        }

        if (versionNames.length > 0) {
            params = params.set('version_names', versionNames.join(','));
        }

        if (years.length > 0) {
            params = params.set('years', years.join(','));
        }

        if (prices) {
            params = params.set('prices', prices.join(','));
        }

        if (word) {
            params = params.set('keyword', word);
        }

        if (page) {
            params = params.set('page', page.toString());
        }

        if (states && states.length > 0) {
            params = params.set('location_names', states.join(','));
        }

        if (transmissions && transmissions.length > 0) {
            params = params.set('transmission_names', transmissions.join(','));
        }

        if (extColors && extColors.length > 0) {
            params = params.set('exterior_colors', extColors.join(','));
        }

        if (intColors && intColors.length > 0) {
            params = params.set('interior_colors', intColors.join(','));
        }

        if (order != 'ninguno') {
            params = params.set('order_by', order);
        }

            return this._http.get<SearchResponse>(`${ this.baseUrl }/api/vehicles/search`, {params} );
    }

    public minMax():Observable<MinMaxResponse>{
      return this._http.get<MinMaxResponse>(`${ this.baseUrl }/api/vehicles/min_max`);
    }
}
