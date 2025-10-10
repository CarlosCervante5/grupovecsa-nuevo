import { Component } from '@angular/core';
export interface Clase {
  image: string;
}
@Component({
    selector: 'app-home-vecsa',
    templateUrl: './home-vecsa.component.html',
    styleUrls: ['./home-vecsa.component.css'],
    standalone: false
})
export class HomeVecsaComponent {
  slides: Clase[]  = [
    {
       image: 'image1'
     },
    {
       image: 'image2'
     },
    {
       image: 'image3'
    }
  ];

  public isVisible(elm:any) {
    var rect = elm.getBoundingClientRect();
    var viewHeight = Math.max(document.documentElement.clientHeight, window.innerHeight);
    return !(rect.bottom < 0 || rect.top - viewHeight >= 0);
  }

  public home_data:Image_data[] = [
    {
      src: "assets/img/team/team-1.jpg",      
      name: "BMW",
      url: "/compra-tu-auto/Bmw/sin-modelos/sin-anios/5000000/Hidalgo/sin-busqueda/sin-transmisiones/sin-tipos/sin-colores/sin-colores/1"
    },
    {
      src: "assets/img/team/team-2.jpg",      
      name: "MINI COOPER",
      url: "/compra-tu-auto/Mini/sin-modelos/sin-anios/5000000/Hidalgo/sin-busqueda/sin-transmisiones/sin-tipos/sin-colores/sin-colores/1"
    },
    {
      src: "assets/img/team/team-3.jpg",      
      name: "BMW MOTORRAD",
      url: "/compra-tu-auto/Motorrad/sin-modelos/sin-anios/5000000/Hidalgo/sin-busqueda/sin-transmisiones/sin-tipos/sin-colores/sin-colores/1"
    }
  ];

  public home_cta:Image_data[] = [
    /*{
      src: "assets/img/cta-bg.jpg",      
      name: "VECSA PUEBLA", 
      url: "/contacto_vecsa/puebla"
    },
    {
      src: "assets/img/cta-bg2.jpg",      
      name: "VECSA VERACRUZ",
      url: "/contacto_vecsa/veracruz"
    },
    {
      src: "assets/img/cta-bg3.jpg",      
      name: "VECSA OAXACA",
      url: "/contacto_vecsa/oaxaca"
    },*/
    {
      src: "",      
      name: "VECSA HIDALGO",
      url: "/contacto_vecsa/hidalgo"
    },
    /*{
      src: "assets/img/cta-bg5.jpg",      
      name: "HUB PUEBLA",
      url: "/contacto_vecsa/hub"
    }*/
  ];
}

interface Image_data {
  src:string;  
  name:string;
  url:string;
}
