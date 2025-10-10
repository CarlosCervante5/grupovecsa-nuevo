import { Component, OnInit } from '@angular/core';
import { EventsService } from 'src/app/admin/gestor/services/events.service';
import { Events } from '@interfaces/community.interface';
@Component({
    selector: 'app-community',
    templateUrl: './community.component.html',
    styleUrls: ['./community.component.css'],
    standalone: false
})
export class CommunityComponent implements OnInit {

  // References
  public myType: string = 'schedule';
  public typeCommunity: string = 'community';
  public getPrincipalImage: string = 'principal';
  public myCalendar: Events[] = [];
  public community: Events[] = [];
  public principalImage: Events[] = [];

  constructor(
    private _eventService: EventsService
  ){}

  ngOnInit(): void {
    window.addEventListener('scroll', this.scroll, true);

    this._eventService.getEventsManager(this.myType)
      .subscribe({
        next: (resp) => {
          for (let i = 0; i < resp.data.events.length; i++) {
            this.myCalendar.push(
              {
                uuid: resp.data.events[i].uuid,
                image_path: resp.data.events[i].image_path,
                name: resp.data.events[i].name,
                description: resp.data.events[i].description,
                begin_date: resp.data.events[i].begin_date,
                end_date: resp.data.events[i].end_date,
                type: resp.data.events[i].type,
                created_at: resp.data.events[i].created_at,
                multimedia: resp.data.events[i].multimedia
              }
            );
          }
        }
      });

    this._eventService.getEventsManager(this.typeCommunity)
      .subscribe({
        next: (resp) => {
          for (let i = 0; i < resp.data.events.length; i++) {
            this.community.push(
              {
                uuid: resp.data.events[i].uuid,
                image_path: resp.data.events[i].image_path,
                name: resp.data.events[i].name,
                description: resp.data.events[i].description,
                begin_date: resp.data.events[i].begin_date,
                end_date: resp.data.events[i].end_date,
                type: resp.data.events[i].type,
                created_at: resp.data.events[i].created_at,
                multimedia: resp.data.events[i].multimedia
              }
            );
          }
        }
      });

      this._eventService.getEventsManager(this.getPrincipalImage)
      .subscribe({
        next: (resp) => {
          for (let i = 0; i < resp.data.events.length; i++) {
            this.principalImage.push(
              {
                uuid: resp.data.events[i].uuid,
                image_path: resp.data.events[i].image_path,
                name: resp.data.events[i].name,
                description: resp.data.events[i].description,
                begin_date: resp.data.events[i].begin_date,
                end_date: resp.data.events[i].end_date,
                type: resp.data.events[i].type,
                created_at: resp.data.events[i].created_at,
                multimedia: resp.data.events[i].multimedia
              }
            );
          }
        }
      });
  }

  scroll = (event:any): void => {    
    document.querySelectorAll(".paused").forEach(elm => {
      if (this.isVisible(elm))
        elm.classList.remove("paused");
    })
  };

  public isVisible(elm:any) {
    var rect = elm.getBoundingClientRect();
    var viewHeight = Math.max(document.documentElement.clientHeight, window.innerHeight);
    return !(rect.bottom < 0 || rect.top - viewHeight >= 0);
  }

}
