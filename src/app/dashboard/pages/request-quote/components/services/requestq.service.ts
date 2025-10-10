import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
//import { Sheetquote } from '../interfaces/requestq.interface';
import { Sheetquote } from '@interfaces/dashboard.interface';

@Injectable({
  providedIn: 'root'
})
export class RequestqService {

  // Global Url
  private url: string = '';

  // Headers
  private headers = new HttpHeaders().set('Content-Type', 'application/json').set('X-Requested-With', 'XMLHttpRequest');

  constructor(private _http: HttpClient) {
    this.url = environment.baseUrl;
  }

  public setQuoteRequest(
    body: string,
    brand: string,
    model: string,
    prospectorName: string,
    prospectorSurname: string,
    placeProspection: string,
    name: string,
    surname: string,
    email: string,
    phone: number,
    buyType: string,
    next: string,
    brandType: string,
    newpreowned: string,
    commentaryLead: string,
  ): Observable<Sheetquote>{
    let sendQuote = {
      "body": body,
      "brand": brand,
      "model": model,
      "prospectorName": prospectorName,
      "prospectorSurname": prospectorSurname,
      "placeProspection": placeProspection,
      "name": name,
      "surname": surname,
      "email": email,
      "phone": phone,
      "buyType": buyType,
      "next": next,
      "brandType": brandType,
      "newpreowned": newpreowned,
      "commentaryLead": commentaryLead
    }

    return this._http.post<Sheetquote>(this.url+'/api/sheet_quote', sendQuote, {headers: this.headers});
  }

}
