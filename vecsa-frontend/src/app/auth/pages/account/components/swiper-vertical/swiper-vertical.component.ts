import {Component,  ElementRef, HostListener, ViewChild } from '@angular/core';
import { linksImage } from '@interfaces/vehicle_data.interface';
import { register } from 'swiper/element/bundle';
// register Swiper custom elements

@Component({
    selector: 'app-swiper-vertical',
    templateUrl: './swiper-vertical.component.html',
    styleUrls: ['./swiper-vertical.component.css'],
    standalone: false
})
export class SwiperVerticalComponent  {
  public dataImages:linksImage[] = [
    {
      "url"  : 'assets/landings/rider/img1.jpg',
      "link" : 'https://abcars.mx'
    },
    {
      "url"  : 'assets/landings/rider/img2.jpg',
      "link" : '/compra-tu-auto/Seminuevo/sin-marcas/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/100000/5000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/1'
    },
    {
      "url"  : 'assets/landings/rider/img3.jpg',
      "link" : 'https://www.chevrolet.com.mx/'
    },
    {
      "url"  : 'assets/landings/rider/img4.jpg',
      "link" : '/compra-tu-auto/Nuevo/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/100000/5000000/Hidalgo/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/1'
    },
    {
      "url"  : 'assets/landings/rider/img5.jpg',
      "link" : '/compra-tu-auto/Nuevo/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/100000/5000000/Hidalgo/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/1'
    },
    {
      "url"  : 'assets/landings/rider/img6.jpg',
      "link" : '/compra-tu-auto/Nuevo/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/100000/5000000/Hidalgo/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/1'
    }
  ];

  constructor(){
    register();
  }

  @ViewChild('myModal') modal!: ElementRef;
  @ViewChild('myImg') imgM!: ElementRef;
  @ViewChild('img01') modalImg!: ElementRef; 
  @ViewChild('caption') caption!: ElementRef;
  execute!:string;
  public ancho!: number;
  public anchoCards!: number;
  public anchoS!: string;
  public anchoW!: number;

  @HostListener('window:resize', ['$event'])
  onResize(event: Event) {
    this.anchoW = window.innerWidth;
    this.anchoS = this.anchoW - 50+ 'px';
    if(this.anchoW < 500){
      this.ancho = 1;
      this.anchoCards = 2;
    }else{
      if(this.anchoW < 1000){
        this.ancho = 3;
        this.anchoCards = 2;
      }else{
        if(this.anchoW < 1200){
          this.anchoCards = 3
        }else{
          this.ancho = 4;
        this.anchoCards = 4;
        }
      }
    }
  }

  showModal( src: string) {   
    let imagen = src;
    let legal = "";

    this.modal.nativeElement.style.display = "grid";
    this.modalImg.nativeElement.src = imagen;  
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
