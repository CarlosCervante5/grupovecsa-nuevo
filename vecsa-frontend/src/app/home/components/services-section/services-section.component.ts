import { Component, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

interface ServiceCard {
  title: string;
  description: string;
  image: string;
  url: string;
  buttonText: string;
  span: '1x1' | '2x2';
}

@Component({
  selector: 'app-services-section',
  standalone: true,
  imports: [CommonModule, RouterModule],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './services-section.component.html',
  styleUrls: ['./services-section.component.css'],
})
export class ServicesSectionComponent {
  services: ServiceCard[] = [
    { title: 'VECSA Boutique', description: 'Accesorios y productos exclusivos BMW y MINI', image: 'assets/images/home/BOUTIQUE.jpg', url: '/boutique', buttonText: 'Ver catálogo', span: '1x1' },
    { title: 'VECSA Rewards', description: 'Programa de lealtad exclusivo con beneficios únicos', image: 'assets/images/home/REWARDS.jpg', url: '/rewards', buttonText: 'Registrarse', span: '1x1' },
    { title: 'VECSA Experience', description: 'Disfruta de eventos especiales, experiencias únicas y beneficios exclusivos.', image: 'assets/images/home/COMUNIDAD-VECSA.jpg', url: '/experience', buttonText: 'Explorar', span: '2x2' },
    { title: 'Car Care', description: 'Mantenimiento y cuidado profesional para tu vehículo', image: 'assets/images/home/CARCARE.jpg', url: '/carcare', buttonText: 'Agendar cita', span: '1x1' },
    { title: 'Promociones', description: 'Descubre las mejores ofertas y promociones especiales', image: 'assets/images/home/PROMOCIONES.jpg', url: '/promociones', buttonText: 'Ver ofertas', span: '1x1' },
  ];
}
