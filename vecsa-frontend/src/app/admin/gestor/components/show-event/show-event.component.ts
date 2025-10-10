import { Component, Inject } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
// import { EventsService } from '../../services/events.service';
import { AdminService } from '@services/admin.service';
import { Events } from '@interfaces/community.interface';
// import { Evento, Events } from '../../interfaces/getEvents.interface';
import { environment } from 'src/environments/environment';
import Swal from 'sweetalert2';

import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

@Component({
    selector: 'app-show-event',
    templateUrl: './show-event.component.html',
    styleUrls: ['./show-event.component.css'],
    standalone: false
})
export class ShowEventComponent {

  public url: string = environment.baseUrl;
  // events!:Evento[];
  events!:Events[];

  files: File[] = [];
  disabled:boolean[] = [];
  loading:boolean = false;

  constructor(
    @Inject (MAT_BOTTOM_SHEET_DATA) public data: any,
    private _bottomSheetRef: MatBottomSheetRef<any>,
    private _eventsService:AdminService,
    private _router: Router    
  ) {
    // this.getEvents();
    this.getEventsManager();
  }


  getEvents():void {
    this._eventsService.getEvents( this.data.type )
        .subscribe(
          resp => {
            // this.events = resp.events;
            // this.events.map( file => this.disabled.push(true) );
          }
        )
  }

  getEventsManager(){
    this._eventsService.getEventsManager(this.data.type)
      .subscribe({
        next: (resp) => {
          this.events = resp.data.events;
          this.events.map( file => this.disabled.push(true) );
        }
      });
  }

  close() {
    this._bottomSheetRef.dismiss('data');
  }

  // deleteEvent( event_id:number ):void {
  deleteEvent( event_id:string ):void {
    Swal.fire({
      title: 'Estas segur@ que quieres eliminar este evento?',      
      showCancelButton: true,
      confirmButtonText: 'Eliminar', 
      confirmButtonColor: '#008bcc',           
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {     
        this._eventsService.deleteEvents( event_id )
            .subscribe({
              next: (resp) => {
                this.getEventsManager(); 
                Swal.fire(resp.message, '', 'success');
              },
              error: (error:any) => {
                reload(error, this._router);
              }
            });                   
      }
    })
  }  

  assignImagePromo( event: Event, index:number){
    const element = event.currentTarget as HTMLInputElement;
    let fileList: FileList | null = element.files;
    if (fileList) {
      this.files = Array.from(fileList);
      if (this.files.length > 0) {        
        this.disabled.map( (v, i) => i != index ? this.disabled[i] = true : this.disabled[i] = false );
      }else{
        this.disabled[index] = true; 
      }
    }
  }

  // uploadImagesPromo( event_id:number, index:number ){
  uploadImagesPromo( event_id:string, index:number ){
    this.disabled[index] = true;
    this.loading = true;
    
    this._eventsService.setImagesEvent( event_id, this.files )
      .subscribe({
        next: (resp) => {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Carga correcta de imágenes',
            showConfirmButton: true,
            confirmButtonColor: '#008bcc',
            timer: 3500
          });
          this.loading = false;            
          this._bottomSheetRef.dismiss();
        },
        error: (err) => {
          this.loading = false;
          this._bottomSheetRef.dismiss();
          reload(err, this._router);
        }
      });
    
  }
}
