import { Component } from '@angular/core';
import { Router } from '@angular/router';

// Interfaces
import { Vehicle } from '@interfaces/vehicle_data.interface';

// Services
import { HomeService } from 'src/app/dashboard/services/home.service';

@Component({
    selector: 'app-footer',
    templateUrl: './footer.component.html',
    styleUrls: ['./footer.component.css'],
    standalone: false
})

export class FooterComponent {
  
  // References
  public date: number = 0;  
  public recommended_vehicles: Vehicle[] = [];

  /** Rutas legales del sitio (mismas que home-footer y módulo Legales). */
  readonly legalLinks: { label: string; url: string }[] = [
    { label: 'Aviso de Privacidad', url: '/aviso-privacidad' },
    { label: 'Condiciones de Uso', url: '/condiciones-uso' },
    { label: 'Políticas de Devolución', url: '/politicas-devolucion' },
    { label: 'Programa de Lealtad', url: '/programa-lealtad' },
    { label: 'Uso de Cookies', url: '/uso-cookies' },
  ];

  constructor(private _homeService: HomeService, private _router: Router) {
    const d = new Date();
    this.date = d.getFullYear();
  }

  /**
   * Search vehicle in Stock Redirect
   * @param brand String
   * @param carmodel String
   */
  public redirectStockVehicle(brand: string, carmodel: string) {
    window.location.href = `/compra-tu-auto/${ brand.toLocaleLowerCase() }/${ carmodel.toLocaleLowerCase() }/sin-anios/3000000/sin-carrocerias/sin-estados/sin-busqueda/sin-transmisiones/1`;
  }

}