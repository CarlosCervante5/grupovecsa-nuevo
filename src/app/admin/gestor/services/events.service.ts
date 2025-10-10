import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { UploadVideo,UploadEventImages, CreateEvent , DeleteEvent, GetEvents, MyEvents } from '@interfaces/community.interface';

export interface DeleteEventImage {
  status:  string;
  code:    number;
  message: string;
}

@Injectable({
  providedIn: 'root'
})
export class EventsService {

  private url: string = environment.baseUrl;
  // Headers
  private headers = new HttpHeaders().set('Content-Type', 'application/json').set('X-Requested-With', 'XMLHttpRequest');        

  constructor(private _http:HttpClient) { }

  public getEvents( brand:string ):Observable<GetEvents>{
    return this._http.get<GetEvents>(`${this.url}/api/getEvents/${ brand }`);
  }

  public getEventsManager(type: string):Observable<MyEvents>{
    
    const formData: FormData = new FormData();
    formData.append('type', type);

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<MyEvents>(`${this.url}/api/events/search`, formData, { headers });
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

    return this._http.post<UploadVideo>(this.url+'/api/events', formData, {headers: headers });
  }

  // public deleteEvents( event_id:number ):Observable<DeleteEvent>{
  public deleteEvents( event_id:string ):Observable<DeleteEvent>{
    const formData: FormData = new FormData();
    formData.append('uuid', event_id);
    
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<DeleteEvent>(`${this.url}/api/events/delete`, formData, { headers });
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

    return this._http.post<CreateEvent>(`${this.url}/api/events`, formData, { headers });
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

    return this._http.post<UploadEventImages>(this.url+'/api/event_multimedia', formData, { headers }) 

  }

  public deleteEventImage( event_image_id:string ):Observable<DeleteEventImage>{
    const formData: FormData = new FormData();
    formData.append('uuid', event_image_id);

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<DeleteEventImage>(`${this.url}/api/event_multimedia/delete`, formData, { headers });
  }
}
