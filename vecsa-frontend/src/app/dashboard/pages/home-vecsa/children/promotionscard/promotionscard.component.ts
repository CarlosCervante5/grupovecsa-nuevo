import { Component, ElementRef, HostListener, Input, ViewChild } from '@angular/core';
import { Camp } from '@interfaces/admin.interfaces';
import { environment } from '@environments/environment';
import { register } from 'swiper/element/bundle';
// register Swiper custom elements
register();

@Component({
    selector: 'app-promotionscard',
    templateUrl: './promotionscard.component.html',
    styleUrls: ['./promotionscard.component.css'],
    standalone: false
})
export class PromotionscardComponent  {
  @Input() images!: Camp[];
  @ViewChild('myModal') modal!: ElementRef;
  @ViewChild('myImg') img!: ElementRef;
  @ViewChild('img01') modalImg!: ElementRef; 
  @ViewChild('caption') caption!: ElementRef;  
 

  public ancho!: number;
  public anchoW!: number;
  @HostListener('window:resize', ['$event'])
    onResize(event: Event) {
      this.anchoW = window.innerWidth;
      if(this.anchoW < 500){
        this.ancho = 1;
      }else{
        if(this.anchoW < 1000){
          this.ancho = 3;
        }else{
          this.ancho = 4;
        }
      }
    }
  
  
  public baseUrl: string = environment.baseUrl;


  execute!:string;
 
  specificationsLink!: string|null;
  showModal( src: string, legal: string, link:string ) {   
    let imagen = src;
    if(legal == null){
      legal =""
    }
    this.modal.nativeElement.style.display = "grid";
    this.modalImg.nativeElement.src = imagen;  
    this.caption.nativeElement.innerHTML = legal ;
    this.specificationsLink = link; 
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
