import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { UpdateQuoteValuation } from '@interfaces/updateQuoteValuation.interfaces';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';

@Injectable({
  providedIn: 'root'
})
export class UpdateQuoteValuationService {

  private baseUrl: string = environment.baseUrl;

  constructor(
    private _http: HttpClient
  ) { }

  public updateQuoteValuation(
    valuation_uuid: string,
    seller_uuid: string,
    book_trade_in_offer: number,
    book_sale_price: number,
    intellimotors_trade_in_offer: number,
    intellimotors_sale_price: number,
    labor_cost: number,
    spare_parts_cost: number,
    body_work_painting_cost: number,
    estimated_total: number,
    trade_in_final: number,
    final_offer: number,
    status: string,
    comments: string,
    take_type: string
  ): Observable<UpdateQuoteValuation> {
    const body = {
      'valuation_uuid': valuation_uuid,
      'seller_uuid': seller_uuid,
      'book_trade_in_offer': book_trade_in_offer,
      'book_sale_price': book_sale_price,
      'intellimotors_trade_in_offer': intellimotors_trade_in_offer,
      'intellimotors_sale_price': intellimotors_sale_price,
      'labor_cost': labor_cost,
      'spare_parts_cost': spare_parts_cost,
      'body_work_painting_cost': body_work_painting_cost,
      'estimated_total': estimated_total,
      'trade_in_final': trade_in_final,
      'final_offer': final_offer,
      'status': status,
      'comments': comments,
      'take_type': take_type
    }

    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

    return this._http.post<UpdateQuoteValuation>(`${ this.baseUrl }/api/valuations/update`, body, { headers });
  }

}
