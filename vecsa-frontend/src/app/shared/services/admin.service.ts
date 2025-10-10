import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { FormGroup } from '@angular/forms';

//interfaces
import { DetailsReward, RegisterResponse } from '@interfaces/auth.interface';
import {detailsRewardResponse, createcampaing , GetcampaingResponse, DeleteVehicleImage, ImageOrderPromo, DeleteCampaign, UploadImages, ImageOrder , GetPromotionsByBrand,GralResponse, RiderResponse, ChangeOrder, rewardsResponse, UsersResponse, DetailResponsive, RolesResponse, DealerShipResponse, CustomerResponse} from '@interfaces/admin.interfaces';
import { UploadVideo,UploadEventImages, CreateEvent , DeleteEvent, GetEvents, MyEvents, DeleteEventImage} from '@interfaces/community.interface';
import { RewardResponse } from '@interfaces/rewards.interface';

@Injectable({providedIn: 'root'})
export class AdminService {
    baseUrl = environment.baseUrl;
    constructor(
        private _http: HttpClient
    ) { }
    
    public getRiders ( page:number, keyword:string, paginate: number ){

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        let params = new HttpParams(); 

        if (keyword) {
            params = params.set('keyword', keyword);
        }

        if(paginate) {
            params = params.set('paginate', paginate.toString());
        }

        if (page) {
            params = params.set('page', page.toString());
        }

        return this._http.get<RiderResponse>(`${ this.baseUrl }/api/riders/search`, {headers , params} );
    }

    public getCustomersRewards ( page:number, keyword:string, paginate: number ){

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        let params = new HttpParams(); 

        if (keyword) {
            params = params.set('keyword', keyword);
        }

        if(paginate) {
            params = params.set('paginate', paginate.toString());
        }

        if (page) {
            params = params.set('page', page.toString());
        }

        return this._http.get<CustomerResponse>(`${ this.baseUrl }/api/riders/search_customers`, {headers , params} );
    }

    public setRiders ( form: FormGroup ):Observable<RegisterResponse>{
    
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<RegisterResponse>(`${this.baseUrl}/api/auth/iternally_register`, form, { headers });
    }

    public detailReward (reward_uuid: string){
        const body = {
            'reward_uuid': reward_uuid
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<detailsRewardResponse>(`${this.baseUrl}/api/rewards/detail`, body, {headers: headers });
    }

    public getRewardByName (name: string){
    
        const body = {
            'name': name
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<RewardResponse>(`${this.baseUrl}/api/rewards/by_name`, body, { headers: headers });
    }

    public setVehicleRider (uuid: string, vin: string, milage: number, rewards:string ):Observable<GralResponse>{

        const body = {
            "customer_uuid": uuid,
            "vin" : vin,
            "mileage": milage,
            "reward_uuid": rewards
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<GralResponse>(`${this.baseUrl}/api/riders`, body, {headers: headers });
    }

    public setVehicleRiderRegister (customer_uuid: string, year: number, model: number, reward_uuid:string ):Observable<GralResponse>{

        const body = {
            "reward_uuid": reward_uuid,
            "customer_uuid": customer_uuid,
            "model" : model,
            "year" : year,
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<GralResponse>(`${this.baseUrl}/api/riders/vehicle_register`, body, {headers: headers });
    }

    public updateKm (mileage: string, files: File[], mileage_f : string, file2: File[], customer_reward_uuid: string, km:boolean){
    
        let formData: FormData = new FormData();

        // if(km){
        //     formData.append('customer_reward_uuid', `${customer_reward_uuid}`);
        //     formData.append('final_mileage', `${mileage_f}`);
        //     file2.forEach((file2, index2) => formData.append(`final_image`, file2));
        // }else{
            formData.append('customer_reward_uuid', `${customer_reward_uuid}`);
            formData.append('final_mileage', `${mileage_f}`);
            file2.forEach((file, index) => formData.append(`final_image`, file));
        // }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        
        return this._http.post<RegisterResponse>(`${this.baseUrl}/api/riders/reward_update`, formData, {headers: headers });
    }

    public rewardDetail (customer_rewards:string){

        const body = {
            'customer_reward_uuid'    : customer_rewards
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<DetailsReward>(`${this.baseUrl}/api/riders/reward_detail`, body, {headers: headers });
    }

    public updateInfoPago(customer: string, vehicle: string,reward:string, status:string, ){
        
        const body = {
            "customer_uuid" : customer,
            "vehicle_uuid"  : vehicle,
            "status"        :status,
            "reward_uuid"   :reward
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<GralResponse>(`${this.baseUrl}/api/riders/update`, body, {headers: headers });
    }

    public updateInfoVehicle(customer: string, vehicle: string, reward:string, vin:string, model:string, year:string){
        
        const body = {
            "customer_uuid" : customer,
            "vehicle_uuid"  : vehicle,
            "reward_uuid"   : reward,
            "model"         : model,
            "year"          : year,
            "vin"           :vin
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<GralResponse>(`${this.baseUrl}/api/riders/update`, body, {headers: headers });
    }

    public updateCustomerInfo(customer_uuid:string, name:string, last_name:string, phone:string, email:string, agency:string){
        
        const body = {
            customer_uuid:          customer_uuid,
            name:                   name,
            last_name:              last_name,
            phone_1:                phone,
            email_1:                email,
            origin_agency:          agency,
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<GralResponse>(`${this.baseUrl}/api/customers/update`, body, {headers: headers });
    }

    public deleteVehicleR (uuid : string){
        const body = {
            "vehicle_uuid": uuid
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<RegisterResponse>(`${this.baseUrl}/api/riders/delete`, body, {headers: headers });
    }

    public newRewards (form: FormGroup) {

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.post<RegisterResponse>(`${this.baseUrl}/api/rewards/`, form, {headers: headers });
    }

    public getRewards (){

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<rewardsResponse>(`${this.baseUrl}/api/rewards/search/`, {headers: headers });
    }

    public updateRewards (uuid: string, name: string, description:string, begin_date:string, end_date:string, type:string){
        const body = {
            reward_uuid: uuid,
            name: name,
            description: description,
            begin_date: begin_date,
            end_date: end_date,
            type: type
        }

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<rewardsResponse>(`${this.baseUrl}/api/rewards/update`,body, {headers: headers });
    }

    public updateCustomerReward (form: FormData){

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<rewardsResponse>(`${this.baseUrl}/api/riders/customer_reward_update`, form, { headers });
    }

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

    public getEvents( brand:string ):Observable<GetEvents>{
        return this._http.get<GetEvents>(`${this.baseUrl}/api/getEvents/${ brand }`);
    }
    
    public getEventsManager(type: string):Observable<MyEvents>{
        
        const formData: FormData = new FormData();
        formData.append('type', type);
    
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.post<MyEvents>(`${this.baseUrl}/api/events/search`, formData, { headers });
    }
    
    public uploadVideo(    
        video: File
    ): Observable<UploadVideo>{
        const today = new Date();
        const day = String(today.getDate()).padStart(2, '0'); // Día (con dos dígitos)
        const month = String(today.getMonth() + 1).padStart(2, '0'); // Mes (enero es 0, así que se suma 1)
        const year = today.getFullYear(); // Año
        const formattedDate = `${year}-${month}-${day}`; 
    
        const formData: FormData = new FormData();    
        formData.append('name', 'video');     
        formData.append('type', 'video');    
        formData.append('begin_date', formattedDate);         
        formData.append('end_date', formattedDate);
    
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.post<UploadVideo>(this.baseUrl+'/api/events', formData, {headers: headers });
    }
    
    public deleteEvents( event_id:string ):Observable<DeleteEvent>{
        const formData: FormData = new FormData();
        formData.append('uuid', event_id);
        
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.post<DeleteEvent>(`${this.baseUrl}/api/events/delete`, formData, { headers });
    }
    
    public createEvent( 
        title:string,    
        description:string,
        type:string,
        event_date:string,    
        picture: File
    ):Observable<CreateEvent>{
        const formData: FormData = new FormData();    
        formData.append('name', title);     
        formData.append('description', description);    
        formData.append('type', type);   
        formData.append('begin_date', event_date);         
        formData.append('end_date', event_date);         
        formData.append('image', picture);   
        
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.post<CreateEvent>(`${this.baseUrl}/api/events`, formData, { headers });
    }
    
    public setImagesEvent( event_id:string, files: File[]){
        
        const formData: FormData = new FormData();
    
        formData.append('event_uuid', `${event_id}`);
    
        files.forEach((file, index) => {
        formData.append(`multimedia[${index}]`, file)
        });
    
        formData.forEach((value, key) => {
        console.log(key + ': ' + value);
        
        });
        
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.post<UploadEventImages>(this.baseUrl+'/api/event_multimedia', formData, { headers }) 
    
    }
    
    public deleteEventImage( event_image_id:string ):Observable<DeleteEventImage>{
        const formData: FormData = new FormData();
        formData.append('uuid', event_image_id);
    
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.post<DeleteEventImage>(`${this.baseUrl}/api/event_multimedia/delete`, formData, { headers });
    }

    public setImagesPromo( brand: string, files: File[], name : string, link : string): Observable<UploadImages>{
    
        let formData: FormData = new FormData();
        
        formData.append('campaign_uuid', `${brand}`);
        formData.append('name', `${name}`);
        formData.append('spec_sheet', `${link}`);
    
        files.forEach((file, index) => formData.append(`images[${index}]`, file));
        
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.post<UploadImages>(this.baseUrl+'/api/promotions/',formData, { headers });
    }

    public getPromotionsByBrand(brand: string): Observable<GetPromotionsByBrand>{
        return this._http.get<GetPromotionsByBrand>(`${ this.baseUrl }/api/getPromotionsByBrand/${brand}`);
    }

    public changeOrderPromo(brand: string, imagesData: ImageOrder[]): Observable<ChangeOrder>{
        const body = {
        brand: `${brand}`,
        new_order: imagesData
        };
        
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.post<ChangeOrder>(`${this.baseUrl}/api/changeOrderPromo`, body, { headers });
    }
    
    public changeOrderPrincipal(type:string, imagesData: ImageOrder[]): Observable<ChangeOrder>{
        const body = {
        type: `${type}`,
        new_order: imagesData
        };
        
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.post<ChangeOrder>(`${this.baseUrl}/api/changeOrderPrincipal`, body, { headers });
    }
    
    public deletePromoImage( vehicle_image_id: number ): Observable<DeleteVehicleImage>{
    
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.delete<DeleteVehicleImage>(`${this.baseUrl}/api/event/${vehicle_image_id}`, { headers });
    }
    
    public updateLegal( id:number, legal:string ):Observable<GetPromotionsByBrand>{     /**ImageOrder */
        const requestData = {
        legal: legal,
        status: 'active'
        };
    
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    
        return this._http.put<GetPromotionsByBrand>(`${this.baseUrl}/api/promotion/${id}`, requestData, { headers });
    
    }

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
        
        return this._http.post<CreateEvent>(`${this.baseUrl}/api/uploadPrincipalImages`, formData, { headers });
    }

    public getUsers(page:number){
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        let params = new HttpParams(); 

        if (page) {
        params = params.set('page', page.toString());
        }
        return this._http.get<UsersResponse>(`${this.baseUrl}/api/users`,  {headers , params});
    }

    public addUser( name: string,last_name: string,phone_1: string,phone_2: string,gender: string,email: string,location: string,role_name: string,picture: File[],password: string,
    ): Observable<GralResponse>{
        const formData: FormData = new FormData();    
        formData.append('name', `${name}`);     
        formData.append('last_name', `${last_name}`);    
        formData.append('phone_1', `${phone_1}`);   
        formData.append('phone_2', `${phone_2}`);
        formData.append('gender', `${gender}`);         
        formData.append('email', `${email}`);         
        formData.append('location', `${location}`);
        formData.append('role_name', `${role_name}`);
        if(picture != null){
            picture.forEach((file, index) => formData.append(`image`, file));
        }
        formData.append('password', `${password}`);   
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.post<GralResponse>(`${this.baseUrl}/api/users/`,formData , { headers });
    }

    public detailUser(user_uuid:string){
        const body = {
        user_uuid: user_uuid
        }
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.post<DetailResponsive>(`${this.baseUrl}/api/users/detail`,body , { headers });
    }

    public updateUser(user_uuid:string,name: string,last_name: string,phone_1: string,phone_2: string,gender: string,email: string,location: string,role_name: string,picture: File[],password: string){
        const formData: FormData = new FormData();    
        formData.append('user_uuid', `${user_uuid}`);
        formData.append('name', `${name}`);     
        formData.append('last_name', `${last_name}`);    
        formData.append('phone_1', `${phone_1}`);   
        formData.append('phone_2', `${phone_1}`);
        formData.append('gender', `${gender}`);         
        formData.append('email', `${email}`);         
        formData.append('location', `${location}`);
        formData.append('role_name', `${role_name}`);
        console.log(picture);
        if(picture != null){
        picture.forEach((file, index) => formData.append(`image`, file));
        }
        formData.append('password', `${password}`);   
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.post<GralResponse>(`${this.baseUrl}/api/users/update`,formData , { headers });
    }

    public getRoles(){
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.get<RolesResponse[]>(`${this.baseUrl}/api/roles` , { headers });
    }

    public getDealerships(){
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.post<DealerShipResponse>(`${this.baseUrl}/api/dealerships/search` , { headers });
    }

    public deleteUser(uuid:string){
        const body = {
            user_uuid:       uuid
        }
        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
        return this._http.post<GralResponse>(`${this.baseUrl}/api/users/delete` ,body, { headers });
    }
}