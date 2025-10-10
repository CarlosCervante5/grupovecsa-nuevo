import { Component } from '@angular/core';
import { ImageService } from '../../interfaces/image_service.interface';
import { Banner } from '../../interfaces/banner.interface';

export interface Marca {
  brand: string,
  type: string
}
@Component({
    selector: 'app-mini',
    templateUrl: './mini.component.html',
    styleUrls: ['./mini.component.css'],
    standalone: false
})
export class MiniComponent {
  banner_data:Banner = {
    title1: 'EN MINI SERVICE QUEREMOS',
    title2: 'CONSENTIR A TU AUTO',
    image_class: 'mini'
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
      image_class: 'image-1',
      brand:'mini'
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
      image_class: 'image-2',
      brand:'mini'
    },
    {
      service1: "MANTENIMIENTO DE",
      service2: "PARABRISAS",
      price: "$600",
      list: [        
        "Eliminar las manchas de gota, que forman una niebla y suciedad en el parabrisas.",
        "Evita que los ácidos acumulados corroan y maximicen daños por piedras.",              
        "Aumenta la visibilidad al manejar."        
      ],
      first_image: false,
      image_class: 'image-3',
      brand:'mini'
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
      image_class: 'image-4',
      brand:'mini'
    }
  ];

  bar_message_data = {
    p1: 'TODOS NUESTROS SERVICIOS INCLUYEN',
    p2: 'UNA REVISÓN MÚLTIPUNTOS BÁSICA SIN COSTO',
    brand: 'mini'
  }

  public Marca: Marca = {
      brand : "Mini",
      type: "aftersale"
  };

}
