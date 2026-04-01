import { Component, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';

interface Brand {
  name: string;
  logo: string;
  url: string;
}

@Component({
  selector: 'app-brands',
  standalone: true,
  imports: [CommonModule],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './brands.component.html',
})
export class BrandsComponent {
  brands: Brand[] = [
    { name: 'BMW', logo: 'assets/images/home/BMW.png', url: 'https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1' },
    { name: 'MINI', logo: 'assets/images/home/MINI.png', url: 'https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/1000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1' },
    { name: 'Motorrad', logo: 'assets/images/home/MOTO.png', url: 'https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1' },
    { name: 'BMW Premium Selection', logo: 'assets/images/home/BMW_PREMIUM_SELECITION.png', url: 'https://grupovecsa.com/inventory/compra-tu-auto/Seminuevo/sin-marcas/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1' },
    { name: 'ABCars', logo: 'assets/images/home/ABCARS_new.png', url: 'https://abcars.mx/compra-tu-auto/sin-marcas/sin-modelos/sin-anios/100000/5000000/sin-carrocerias/sin-estados/sin-busqueda/sin-transmisiones/1' },
    { name: 'Chevrolet Balderrama', logo: 'assets/images/home/CHEVROLET_new.png', url: 'https://www.chevroletbalderrama.com.mx/' },
  ];
}
