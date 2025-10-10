import { Component } from '@angular/core';
import { Banner } from '../../interfaces/banner.interface';
import { ImageService } from '../../interfaces/image_service.interface';
export interface Marca {
  brand: string,
  type: string
}
@Component({
    selector: 'app-bmw',
    templateUrl: './bmw.component.html',
    styleUrls: ['./bmw.component.css'],
    standalone: false
})
export class BmwComponent {
  banner_data:Banner = {
    title1: 'EN BMW SERVICE QUEREMOS',
    title2: 'CONSENTIR A TU AUTO',
    image_class: 'bmw'
  }

  image_services:ImageService[] = [
    {
      service1: "INFLADO DE LLANTAS",
      service2: "CON NITRÓGENO",
      price: "$600",
      list: [        
        "Mayor ahorro de combustible.",
        "Niveles de inflado más constantes.",
        "Menor riesgo de sobre-calentamiento en condiciones climaticas extremas.",
        "Ayuda a prolongar la vida útil del neumatico.",
        "Mejor calidad de conducción."        
      ],
      first_image: false,
      image_class: 'image-5',
      brand:'bmw'
    },
    {
      service1: "PULIDO Y MANTENIMIENTO",
      service2: "DE FAROS",
      price: "$600",
      list: [
        "Eliminar los arañazos.",
        "Restaurar los lentes nubladas y opacas, ayudando a mantener el valor del vehículo gracias a una mejor apariencia.",
        "Aumenta visibilidad como seguridad."
      ],
      first_image: true,
      image_class: 'image-6',
      brand:'bmw'
    },
    {
      service1: "MANTENIMIENTO DE ",
      service2: " PARABRISAS",
      price: "$600",
      list: [        
        "Eliminar las manchas de gota, que forman una niebla y suciedad en el parabrisas.",
        "Evita que los ácidos acumulados corroan y maximicen daños por piedras.",              
        "Aumenta la visibilidad al manejar."        
      ],
      first_image: false,
      image_class: 'image-7',
      brand:'bmw'

    },
    {
      service1: "PINTURA DE",
      service2: "CALIPER DE FRENO",
      price: "$2,800",
      list: [
        "Personaliza, realza y protege el caliper.",
        "Pintura especial que resiste el calor hasta 482°C.",
        "Reistente al polvo de frenado. Fórmula anticorrosiva."
      ],
      first_image: true,
      image_class: 'image-8',
      brand:'bmw'
    }
  ];

  bar_message_data = {
    p1: 'TODOS NUESTROS SERVICIOS INCLUYEN',
    p2: 'UNA REVISÓN MÚLTIPUNTOS BÁSICA SIN COSTO',
    brand: 'bmw'
  }

  public Marca: Marca = {
      brand : "BMW",
      type: "aftersale"
  };

}
