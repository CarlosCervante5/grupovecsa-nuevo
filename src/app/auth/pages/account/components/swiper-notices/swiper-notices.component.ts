import { Component } from '@angular/core';

import { Events } from '@interfaces/community.interface';
import { EventsService } from 'src/app/admin/gestor/services/events.service';

@Component({
    selector: 'app-swiper-notices',
    templateUrl: './swiper-notices.component.html',
    styleUrls: ['./swiper-notices.component.css'],
    standalone: false
})

export class SwiperNoticesComponent {

  public images: Events[] = [];

  constructor(private _eventService: EventsService) { 
    // this.imagenes();
  }

  public showModal(imagen: any) { }

  public imagenes() {
    this._eventService.getEventsManager('schedule').subscribe({
      next: (response) => {
        response.data.events.map((item) => {
          this.images.push(item);
        });
      }
    });
  }

}
