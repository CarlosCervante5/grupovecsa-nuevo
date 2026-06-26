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
    {
      label: 'Vehículos BMW',
      url: '/compra-tu-auto/sin-categorias/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/9500/30000500/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
      external: false,
    },
    {
      label: 'Vehículos MINI',
      url: '/compra-tu-auto/sin-categorias/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/9500/30000500/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
      external: false,
    },
    {
      label: 'BMW Motorrad',
      url: '/compra-tu-auto/sin-categorias/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/9500/30000500/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
      external: false,
    },
    {
      label: 'Seminuevos Ejecutivos',
      url: '/compra-tu-auto/Seminuevo/sin-marcas/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/9500/30000500/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1',
      external: false,
    },
    { label: 'ABCars Seminuevos', url: 'https://www.abcars.mx/inventario', external: true },
  ];

  serviceLinks: FooterLink[] = [
    { label: 'Sucursales', url: '/sucursales', external: false },
    { label: 'VECSA Boutique', url: '/boutique', external: false },
    { label: 'VECSA Rewards', url: '/rewards', external: false },
    { label: 'VECSA Experience', url: '/experience', external: false },
    { label: 'Car Care', url: '/carcare', external: false },
    { label: 'Promociones', url: '/promociones', external: false },
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
