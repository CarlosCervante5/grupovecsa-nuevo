import { Component } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { MatSnackBar } from '@angular/material/snack-bar';
import { LoadVideoComponent } from '../../components/events/load-video/load-video.component';
import { EventsService } from '../../services/events.service';
import { environment } from '@environments/environment';
import Swal from 'sweetalert2';
import { ShowEventComponent } from '../../components/show-event/show-event.component';
import { CreateEventComponent } from '../../components/create-event/create-event.component';
import { LoadPrincipalImagesComponent } from '../../components/load-principal-images/load-principal-images.component';
import { Evento } from '../../../../shared/interfaces/admin.interfaces';
import { UpdateImagesPromoComponent } from '../../components/update-images-promo/update-images-promo.component';

@Component({
    selector: 'app-schedule-events',
    templateUrl: './schedule-events.component.html',
    styleUrls: ['./schedule-events.component.css'],
    standalone: false
})
export class ScheduleEventsComponent {
  private url: string = environment.baseUrl;
  // public principal_video_path!:string;  
  public principal_video_path:string = 'https://www.4webs.es/blog/wp-content/uploads/2017/12/video-marketing-para-ecommerce.jpg';  
  public principal_calendar_path: string = 'https://static.vecteezy.com/system/resources/previews/004/698/190/non_2x/calendar-business-date-time-icon-symbol-hand-drawn-cartoon-art-illustration-vector.jpg';
  public principal_community_path:string = 'https://img.freepik.com/vector-gratis/vector-ilustracion-dibujos-animados-zona-urbana-historica_1441-257.jpg';
  public principal_image_path:string = 'https://media.istockphoto.com/id/1208816180/es/vector/fondo-espacial-paisaje-de-fantas%C3%ADa-alien%C3%ADgena-noche.jpg?s=612x612&w=0&k=20&c=fiY6zZZorAwLiRlGARlnVa_nwaNkcscpSSCa6GJwjbk=';

  public has_calendar:boolean = false;
  public has_community:boolean = false;
  public has_video:boolean = false;
  public has_principal:boolean = false;
  // public video_event_id!:number;
  public video_event_id!:string;
  public has_image: boolean = false;
  public imagesPathPrincipal: Evento[] = [];

  constructor(    
    private _bottomSheet: MatBottomSheet,
    private _snackBar: MatSnackBar,
    private _eventsService: EventsService
  ) {     
    this.init();
  }

  init(){
    // this.getEvents('video');
    // this.getEvents('calendar');
    // this.getEvents('community');
    // this.getEvents('principal');
    this.getEventsManager('video');
    this.getEventsManager('schedule');
    this.getEventsManager('community');
    this.getEventsManager('principal');
  }

  getEventsManager(type: string){
    this._eventsService.getEventsManager(type)
      .subscribe({
        next: (resp) => {
          const myTypes = resp.data.events.map( event_type => {
            switch (true) {
              case ( event_type.type === 'schedule'):
                if (event_type.image_path != undefined) {
                  this.principal_calendar_path = event_type.image_path;
                  this.has_calendar = true;
                }else{
                  this.principal_calendar_path;
                  this.has_calendar = false;
                }
              break;
              case ( event_type.type === 'community'):
                if (event_type.image_path != undefined) {
                  this.principal_community_path = event_type.image_path;
                  this.has_community = true;
                }else{
                  this.principal_community_path;
                  this.has_community = false;
                }
              break;
              case ( event_type.type === 'video'):
                if (event_type.image_path != undefined) {
                  this.principal_video_path = event_type.image_path;
                  this.has_video = true;
                }else{
                  this.principal_video_path;
                  this.has_video = false;
                }
              break;
              case ( event_type.type === 'principal'):
                if (event_type.image_path != undefined) {
                  this.principal_image_path = event_type.image_path;
                  this.has_principal = true;
                }else{
                  this.principal_image_path;
                  this.has_principal = false;
                }
              break;
            }
          });
        }
      });
  }

  getEvents( type:string ):void{
    this._eventsService.getEvents( type )
        .subscribe(
          resp => {
            if( resp.status === "success" ){
              switch (type) {
                case 'video':
                  if( resp.events[0] != undefined ){
                    this.principal_video_path = this.url + '/api/getVideo/' + resp.events[0].path;
                    // this.video_event_id = resp.events[0].id;
                  }else {
                    this.principal_video_path = '';
                  }
                  break;
                case 'calendar':
                  if( resp.events[0] != undefined ){                    
                    this.principal_calendar_path = this.url + '/api/getEventImage/' + resp.events[0].path;
                    this.has_calendar = true;
                  }else {
                    this.principal_calendar_path = 'https://static.vecteezy.com/system/resources/previews/004/698/190/non_2x/calendar-business-date-time-icon-symbol-hand-drawn-cartoon-art-illustration-vector.jpg';
                    this.has_calendar = false;
                  }                  
                  break;
                case 'community':
                  if( resp.events[0] != undefined ){                    
                    this.principal_community_path = this.url + '/api/getEventImage/' + resp.events[0].path;
                    this.has_community = true;
                  }else {
                    this.principal_community_path = 'https://img.freepik.com/vector-gratis/vector-ilustracion-dibujos-animados-zona-urbana-historica_1441-257.jpg';
                    this.has_community = false;
                  }                         
                  break;              
                case 'principal':
                  if (resp.events[0] != undefined) {
                    this.principal_image_path = this.url + '/api/getEventImage/' + resp.events[0].path;
                    this.has_image = true;
                  }else {
                    this.principal_image_path = 'https://media.istockphoto.com/id/1208816180/es/vector/fondo-espacial-paisaje-de-fantas%C3%ADa-alien%C3%ADgena-noche.jpg?s=612x612&w=0&k=20&c=fiY6zZZorAwLiRlGARlnVa_nwaNkcscpSSCa6GJwjbk=';
                    this.has_image = false;
                  }
              }
            }
          } 
        )
  }

  loadVideo( ): void {
    const bottomSheetRef = this._bottomSheet.open(LoadVideoComponent, {
      data: {
        
      }
    });   
    bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {      
      if(dataFromChild != undefined && dataFromChild.reload === true ){
        this.getEventsManager('video');
      }      
    });
  }

  // public deleteVideo(type:string){
  //   Swal.fire({
  //     title: 'Estas segur@ que quieres eliminar este video',      
  //     showCancelButton: true,
  //     confirmButtonText: 'Eliminar', 
  //     confirmButtonColor: '#008bcc',           
  //   }).then((result) => {      
  //     if (result.isConfirmed) {              
  //       this._eventsService.deleteEvents( this.video_event_id )
  //           .subscribe(
  //             ( resp ) => {       
  //               switch (type) {
  //                 case 'video':
  //                   this.getEvents('video');
  //                   break;
  //                 case 'calendar':
  //                   this.getEvents('calendar');
  //                   break;
  //                 case 'community':
  //                   this.getEvents('community');
  //                   break;              
  //               }                         
  //               Swal.fire(resp.message, '', 'success');
  //             }
  //           )        
  //     }
  //   })
  // }

  showEvents(type:string):void{  
    const bottomSheetRef = this._bottomSheet.open(ShowEventComponent, {      
      data: {
        type
      }      
    });   
    bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {                     
      // this.getEvents(type);      
      this.getEventsManager(type);      
    });
    
  }

  createEvent(type:string):void{
    const bottomSheetRef = this._bottomSheet.open(CreateEventComponent, {
      data: {
        type
      }
    });   
    bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {
      // this.getEvents(type);      
      this.getEventsManager(type);      
    });
  }

  public createEventImage(type:string): void{
    const bottomSheetRef = this._bottomSheet.open(LoadPrincipalImagesComponent, {
      data: {
        type
      }
    });
    bottomSheetRef.afterDismissed().subscribe((resp) => {
      this.getEvents('principal');
    });
  }

  public showPrincipalImages(type: string): void{
    this._eventsService.getEvents(type)
      .subscribe(
        resp => {
          this.imagesPathPrincipal = resp.events;
          const openPrincipalImage = this._bottomSheet.open(UpdateImagesPromoComponent, {
            data: {
              type,
              images: this.imagesPathPrincipal
            }
          });
          openPrincipalImage.afterDismissed().subscribe((resp) => {
            this.getEvents('principal');
          });
        }
      )
  }
}
