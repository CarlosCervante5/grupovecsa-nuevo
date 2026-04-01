import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SafeUrlPipe } from '../../pipes/safe-url.pipe';

interface Location {
  id: string;
  name: string;
  address: string;
  phone: string;
  email?: string;
  lat: number;
  lng: number;
  image: string;
  filter: 'puebla' | 'otros';
  state: string;
  stateColor: string;
}

@Component({
  selector: 'app-locations',
  standalone: true,
  imports: [CommonModule, SafeUrlPipe],
  templateUrl: './locations.component.html',
  styleUrls: ['./locations.component.css'],
})
export class LocationsComponent implements OnInit {
  activeFilter: 'all' | 'puebla' | 'otros' = 'all';
  selectedLocation: Location | null = null;

  locations: Location[] = [
    { id: 'hub-serdan', name: 'HUB Serdán', address: 'Blvd. Hermanos Serdán 788, esquina Francisco Villa', phone: '222-309-0700', lat: 19.0414, lng: -98.2063, image: 'assets/images/home/hub-serdan.jpg', filter: 'puebla', state: 'Puebla', stateColor: 'blue' },
    { id: 'vecsa-puebla', name: 'VECSA Angelópolis', address: 'Blvd. Atlixcayotl No. 5316, Angelópolis', phone: '222-309-0800', lat: 19.0319, lng: -98.2442, image: 'assets/images/home/puebla-angelopolis.jpg', filter: 'puebla', state: 'Puebla', stateColor: 'blue' },
    { id: 'vecsa-pachuca', name: 'VECSA Pachuca', address: 'Vial La Paz 113, Col. Adolfo López Mateos', phone: '771-717-2554', lat: 20.1011, lng: -98.7591, image: 'assets/images/home/pachuca.jpg', filter: 'otros', state: 'Hidalgo', stateColor: 'green' },
    { id: 'vecsa-veracruz', name: 'VECSA Veracruz', address: 'Carretera Federal Boca del Río – Antón de Lizardo No. 4450', phone: '229-923-6030', lat: 19.1738, lng: -96.1342, image: 'assets/images/home/veracruz.jpg', filter: 'otros', state: 'Veracruz', stateColor: 'orange' },
    { id: 'vecsa-oaxaca', name: 'VECSA Oaxaca', address: 'Av. Universidad No. 400, Col. Ex hacienda Candiani', phone: '951-144-7955', lat: 17.0732, lng: -96.7266, image: 'assets/images/home/oaxaca.jpg', filter: 'otros', state: 'Oaxaca', stateColor: 'purple' },
    { id: 'vecsa-balderrama', name: 'Chevrolet Balderrama', address: 'Av. Hermanos Serdán No. 241, Col. Aquiles Serdán', phone: '222-303-9900', lat: 19.0326, lng: -98.2280, image: 'assets/images/home/puebla-balderrma.jpg', filter: 'puebla', state: 'Puebla', stateColor: 'blue' },
    { id: 'abcars-puebla', name: 'ABCars Puebla', address: 'Blvrd Esteban de Antuñano 1314, Obrera Textil José Abascal', phone: '222-303-9910', email: 'contacto@abcars.mx', lat: 19.0414, lng: -98.2063, image: 'assets/images/home/abcars.jpeg', filter: 'puebla', state: 'Puebla', stateColor: 'blue' },
  ];

  filters = [
    { value: 'all' as const, label: 'Todas' },
    { value: 'puebla' as const, label: 'Puebla' },
    { value: 'otros' as const, label: 'Otros Estados' },
  ];

  ngOnInit(): void {
    if (this.locations.length > 0) {
      this.selectedLocation = this.locations[0];
    }
  }

  get filteredLocations(): Location[] {
    if (this.activeFilter === 'all') return this.locations;
    return this.locations.filter(loc => loc.filter === this.activeFilter);
  }

  setFilter(filter: 'all' | 'puebla' | 'otros'): void {
    this.activeFilter = filter;
    if (this.selectedLocation && filter !== 'all' && this.selectedLocation.filter !== filter) {
      this.selectedLocation = this.filteredLocations.length > 0 ? this.filteredLocations[0] : null;
    }
  }

  selectLocation(location: Location): void {
    this.selectedLocation = location;
  }

  getStateBadgeClasses(color: string): string {
    const map: Record<string, string> = {
      blue: 'bg-blue-100 text-blue-700',
      green: 'bg-green-100 text-green-700',
      orange: 'bg-orange-100 text-orange-700',
      purple: 'bg-purple-100 text-purple-700',
    };
    return map[color] || 'bg-gray-100 text-gray-700';
  }

  getStateDotClasses(color: string): string {
    const map: Record<string, string> = {
      blue: 'bg-blue-600',
      green: 'bg-green-500',
      orange: 'bg-orange-500',
      purple: 'bg-purple-500',
    };
    return map[color] || 'bg-gray-500';
  }

  getDirectionsUrl(location: Location): string {
    return `https://maps.google.com/?q=${encodeURIComponent(location.address)}`;
  }

  getMapUrl(location: Location): string {
    return `https://maps.google.com/maps?q=${location.lat},${location.lng}&z=15&output=embed`;
  }
}
