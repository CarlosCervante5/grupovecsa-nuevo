import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '@environments/environment';
import { HomeSlide, HomeTestimonial, HomeSlidesResponse, HomeTestimonialsResponse } from '@interfaces/admin.interfaces';

@Injectable({ providedIn: 'root' })
export class HomePublicService {

  private baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  getSlides(): Observable<HomeSlide[]> {
    return this.http
      .post<HomeSlidesResponse>(`${this.baseUrl}/api/home/slides`, {})
      .pipe(map(res => res.data.slides));
  }

  getTestimonials(): Observable<HomeTestimonial[]> {
    return this.http
      .post<HomeTestimonialsResponse>(`${this.baseUrl}/api/home/testimonials`, {})
      .pipe(map(res => res.data.testimonials));
  }
}
