import { Component, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';

interface FooterLink { label: string; url: string; external: boolean; }
interface SocialLink { name: string; url: string; }

@Component({
  selector: 'app-home-footer',
  standalone: true,
  imports: [CommonModule, RouterLink],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './home-footer.component.html',
})
export class HomeFooterComponent {
  currentYear = new Date().getFullYear();

  vehicleLinks: FooterLink[] = [
    { label: 'Vehículos BMW', url: 'https://grupovecsa.com/inventory/compra-tu-auto/Nuevo/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1', external: true },
    { label: 'Vehículos MINI', url: 'https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/1000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1', external: true },
    { label: 'BMW Motorrad', url: 'https://grupovecsa.com/inventory/compra-tu-auto/Nuevo-Demo/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1', external: true },
    { label: 'Seminuevos Ejecutivos', url: 'https://grupovecsa.com/inventory/compra-tu-auto/Seminuevo/sin-marcas/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1', external: true },
    { label: 'ABCars Seminuevos', url: 'https://abcars.mx/compra-tu-auto/sin-marcas/sin-modelos/sin-anios/100000/5000000/sin-carrocerias/sin-estados/sin-busqueda/sin-transmisiones/1', external: true },
  ];

  serviceLinks: FooterLink[] = [
    { label: 'Sucursales', url: '/sucursales', external: false },
    { label: 'VECSA Boutique', url: 'https://vecsaboutique.com/', external: true },
    { label: 'VECSA Rewards', url: 'https://grupovecsa.com/inventory/auth/login', external: true },
    { label: 'VECSA Experience', url: 'https://vecsaexperience.com/', external: true },
    { label: 'Car Care', url: 'https://grupovecsa.com/inventory/carcare', external: true },
    { label: 'Promociones', url: 'https://grupovecsa.com/inventory/promotions', external: true },
  ];

  legalLinks: FooterLink[] = [
    { label: 'Aviso de Privacidad', url: '/aviso-privacidad', external: false },
    { label: 'Condiciones de Uso', url: '/condiciones-uso', external: false },
    { label: 'Políticas de Devolución', url: '/politicas-devolucion', external: false },
    { label: 'Programa de Lealtad', url: '/programa-lealtad', external: false },
    { label: 'Uso de Cookies', url: '/uso-cookies', external: false },
  ];

  socialLinks: SocialLink[] = [
    { name: 'Facebook', url: 'https://www.facebook.com/grupovecsa' },
    { name: 'Instagram', url: 'https://www.instagram.com/grupovecsa' },
    { name: 'LinkedIn', url: 'https://www.linkedin.com/company/grupovecsa' },
  ];
}
