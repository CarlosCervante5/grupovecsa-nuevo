import { Component, ElementRef, Inject, ViewChild } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA } from '@angular/material/bottom-sheet';

@Component({
    selector: 'app-m-swiper',
    templateUrl: './m-swiper.component.html',
    styleUrls: ['./m-swiper.component.css'],
    standalone: false
})
export class MSwiperComponent {

  constructor(){
    this.src = '';
  }

  @ViewChild('myModal') modal!: ElementRef;
  @ViewChild('myImg') imgM!: ElementRef;
  @ViewChild('img01') modalImg!: ElementRef; 
  @ViewChild('caption') caption!: ElementRef;
  execute!:string;
  public src!: string;
  showModal( ) {   
    let legal = "";

    this.modal.nativeElement.style.display = "grid";
    this.modalImg.nativeElement.src = this.src;  
    this.caption.nativeElement.innerHTML = legal ;
  }
  
  closeModal( message:string ) {    
    if( message == "no" ) {
      this.execute = 'no';
    }else if ( message == "yes" && this.execute == 'no' ){
      this.execute = 'processing';
    }else {
      this.execute = 'yes';
    }
    if( this.execute == 'yes' ){
      this.modal.nativeElement.style.display = "none";
    }    
  }
}
