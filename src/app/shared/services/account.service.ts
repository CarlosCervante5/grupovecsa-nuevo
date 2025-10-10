import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

// Form
import { FormGroup } from '@angular/forms';

// HTTP Client
import { HttpClient, HttpHeaders } from '@angular/common/http';

// Enviroment
import { environment } from '@environments/environment';

// Interfaces
import { ShowProfileResponse, UpdateProfileResponse, RolData, ImageData, QuizzesData, Quiz} from '@interfaces/auth.interface';
import { GralResponse } from '@interfaces/vehicle_data.interface';

@Injectable({
  providedIn: 'root'
})

export class AccountService {

  // Global Url
  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) { }

  public validateRole(expected_role:string) {
  
    let user_token = localStorage.getItem('user_token');
    let stored_role =  localStorage.getItem('role') || '';

    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    let body = {
      stored_role: stored_role,
      expected_role: expected_role
    }

    return this._http.post<RolData>(`${ this.url }/api/auth/validate_role`, body, { headers });

  }

  /**
   * API Get information User 
   */
  public getProfile( uuid: string): Observable<ShowProfileResponse> {    

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.get<ShowProfileResponse>(`${ this.url }/api/auth/profile/${ uuid }`, { headers });
  }

  /**
   * API Update customer profile 
   */ 
  public updateProfile(form: FormGroup): Observable<UpdateProfileResponse> {  
    console.log(form.value);
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<UpdateProfileResponse>(`${ this.url }/api/auth/update_profile`, form, { headers });

  }

  public updateImage( uuid:string, fileToUploaded: File ):Observable<ImageData>{

      let user_token = localStorage.getItem('user_token');
      let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

      const form: FormData = new FormData();

      form.append('user_uuid', uuid);
      form.append('image', fileToUploaded);

      return this._http.post<ImageData>(this.url+'/api/auth/update_image', form, { headers });

  }

  public updateImageProfile( uuid:string, fileToUploaded: File ):Observable<ImageData>{

      let user_token = localStorage.getItem('user_token');
      let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

      const form: FormData = new FormData();

      form.append('user_uuid', uuid);
      form.append('image', fileToUploaded);

      return this._http.post<ImageData>(this.url+'/api/auth/update_image_profile', form, { headers });
  
  }

  public customerQuizzes( uuid:string ): Observable<QuizzesData>{

      const form: FormData = new FormData();

      form.append('customer_uuid', uuid);

      return this._http.post<QuizzesData>(this.url+'/api/quizzes/search_by_customer', form);
  
  }


  public quizzesProfile(): Observable<QuizzesData>{

    return this._http.post<QuizzesData>(this.url+'/api/quizzes/search_profile', null);

}

  public attatchQuiz( customer_uuid:string , quiz_uuid:string, selected_value:string ):  Observable<GralResponse>{

      let user_token = localStorage.getItem('user_token');
      let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

      const form: FormData = new FormData();

      form.append('customer_uuid', customer_uuid);
      form.append('quiz_uuid', quiz_uuid);
      form.append('selected_value', selected_value);

      return this._http.post<GralResponse>(this.url+'/api/quizzes/attatch', form, { headers });

  }

  public attatchQuizzes( customer_uuid:string , quiz_uuids:string[], selected_values:string[] ):  Observable<GralResponse>{

      let user_token = localStorage.getItem('user_token');
      let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

      const form: FormData = new FormData();

      form.append('customer_uuid', customer_uuid);
      quiz_uuids.forEach(quiz_uuid => form.append('quiz_uuids[]', quiz_uuid));
      selected_values.forEach(selected_value => form.append('selected_values[]', selected_value));

      return this._http.post<GralResponse>(this.url+'/api/quizzes/attatch_batch', form, { headers });

  }

}
