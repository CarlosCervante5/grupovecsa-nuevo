import { Component, Input } from '@angular/core';
import { environment } from 'src/environments/environment';
import Swal from 'sweetalert2'
// import { EventsService } from '../../services/events.service';
import { AdminService } from '@services/admin.service';
import { Multimedia } from '@interfaces/community.interface';
import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';
//import { Multimedia } from '../../interfaces/getEvents.interface';

// export interface EventImage {
//   id:         number;
//   path:       string;
//   event_id:   number;
//   created_at: Date;
//   updated_at: Date;
// }
export interface EventImage {
  uuid:            string;
  multimedia_path: string;
  sort_id:         number;
  name:            string;
  description:     string;
  created_at:      Date;
}

@Component({
    selector: 'app-event-card',
    templateUrl: './event-card.component.html',
    styleUrls: ['./event-card.component.css'],
    standalone: false
})
export class EventCardComponent {

  public baseUrl=environment.baseUrl;
  // @Input() images!: EventImage[];
  @Input() images!: Multimedia[];

  constructor(        
    private _eventsService:AdminService,
    private _router: Router  
  ) {}

  isVideo(filePath: string): boolean {
    const videoExtensions = ['mp4', 'mov', 'avi', 'webm', 'ogg'];
    const extension = filePath.split('.').pop()?.toLowerCase();
    return videoExtensions.includes(extension || '');
  }

  // deleteEventImage( event_image_id:number, index:number ){
  deleteEventImage( event_image_id:string, index:number ){
    Swal.fire({
      title: 'Estas segur@ que quieres eliminar este evento?',      
      showCancelButton: true,
      confirmButtonText: 'Eliminar', 
      confirmButtonColor: '#008bcc',           
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {           
        this._eventsService.deleteEventImage( event_image_id )
            .subscribe({
              next: (resp) => {
                this.images.splice(index, 1);            
                Swal.fire(resp.message, '', 'success');
              },
              error: (error:any) => {
                reload(error, this._router);
              }
            });            
      }
    })
  }
}
