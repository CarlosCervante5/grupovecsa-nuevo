import { Component, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

interface Brand {
  name: string;
  logo: string;
  /** Ruta interna (`/compra-tu-auto/...`) o URL absoluta externa. */
  url: string;
  external?: boolean;
}

@Component({
  selector: 'app-brands',
  standalone: true,
  imports: [CommonModule, RouterModule],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './brands.component.html',
})
export class BrandsComponent {
  brands: Brand[] = [
    {
      name: 'BMW',
      logo: 'assets/images/home/BMW.png',
      url: '/compra-tu-auto/sin-categorias/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/9500/30000500/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
      external: false,
    },
    {
      name: 'MINI',
      logo: 'assets/images/home/MINI.png',
      url: '/compra-tu-auto/sin-categorias/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/9500/30000500/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
      external: false,
    },
    {
      name: 'Motorrad',
      logo: 'assets/images/home/MOTO.png',
      url: '/compra-tu-auto/sin-categorias/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/9500/30000500/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
      external: false,
    },
    {
      name: 'BMW Premium Selection',
      logo: 'assets/images/home/BMW_PREMIUM_SELECITION.png',
      url: '/compra-tu-auto/Seminuevo/sin-marcas/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/9500/30000500/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
      external: false,
    },
  ];
}
