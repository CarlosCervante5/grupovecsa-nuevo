import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

export interface ExperienceEvent {
  uuid: string;
  name: string;
  begin_date: string;
  end_date: string;
  description: string | null;
  location: string | null;
  image_path: string | null;
  type: string;
  created_at: string;
  multimedia?: ExperienceMultimedia[];
}

export interface ExperienceMultimedia {
  uuid: string;
  sort_id: number;
  name: string | null;
  description: string | null;
  multimedia_path: string;
}

export interface ExperiencePost {
  uuid: string;
  title: string;
  image_path: string | null;
  url_name: string;
  status: string;
  category: string;
  created_at: string;
}

export interface ExperienceEventsResponse {
  status: number;
  message: string;
  data: { events: ExperienceEvent[] };
}

export interface ExperiencePastEventsResponse {
  status: number;
  message: string;
  data: { gallery: { data: ExperienceEvent[]; total: number; last_page: number } };
}

export interface ExperiencePostsResponse {
  status: number;
  message: string;
  data: { posts: { data: ExperiencePost[]; total: number; last_page: number } };
}

export interface ExperienceEventDetailResponse {
  status: number;
  message: string;
  data: { event: ExperienceEvent };
}

@Injectable({ providedIn: 'root' })
export class ExperienceService {
  private baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  getUpcomingEvents(): Observable<ExperienceEventsResponse> {
    return this.http.get<ExperienceEventsResponse>(`${this.baseUrl}/api/experience/upcoming_events`);
  }

  getPastEvents(page = 1, perPage = 8): Observable<ExperiencePastEventsResponse> {
    const params = new HttpParams().set('per_page', perPage).set('page', page);
    return this.http.get<ExperiencePastEventsResponse>(`${this.baseUrl}/api/experience/past_events`, { params });
  }

  getPosts(page = 1, perPage = 6): Observable<ExperiencePostsResponse> {
    const params = new HttpParams().set('per_page', perPage).set('page', page);
    return this.http.get<ExperiencePostsResponse>(`${this.baseUrl}/api/experience/posts`, { params });
  }

  getEventDetail(uuid: string): Observable<ExperienceEventDetailResponse> {
    return this.http.post<ExperienceEventDetailResponse>(`${this.baseUrl}/api/experience/event_detail`, { uuid });
  }
}
