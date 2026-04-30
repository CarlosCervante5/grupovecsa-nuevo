import { Component, OnDestroy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Subscription } from 'rxjs';

import { environment } from '@environments/environment';
import { SafeUrlPipe } from '../../pipes/safe-url.pipe';

const PLACEHOLDER_IMAGE = 'assets/images/placeholder-product.svg';

interface DealershipApiRow {
  id: number;
  name: string;
  location: string;
  state?: string | null;
  description?: string | null;
  phone?: string | null;
  email?: string | null;
  whatsapp_phone?: string | null;
  latitude?: number | null;
  longitude?: number | null;
  image_url?: string | null;
  opening_hours?: string | null;
}

interface Location {
  id: string;
  name: string;
  address: string;
  phone: string;
  email?: string;
  lat: number | null;
  lng: number | null;
  image: string;
  filter: 'puebla' | 'otros';
  state: string;
  stateColor: string;
  whatsappPhone?: string | null;
  openingHours?: string | null;
}

@Component({
  selector: 'app-locations',
  standalone: true,
  imports: [CommonModule, SafeUrlPipe],
  templateUrl: './locations.component.html',
  styleUrls: ['./locations.component.css'],
})
export class LocationsComponent implements OnInit, OnDestroy {
  activeFilter: 'all' | 'puebla' | 'otros' = 'all';
  selectedLocation: Location | null = null;
  locations: Location[] = [];
  loading = true;
  loadError = false;

  private sub: Subscription | null = null;

  filters = [
    { value: 'all' as const, label: 'Todas' },
    { value: 'puebla' as const, label: 'Puebla' },
    { value: 'otros' as const, label: 'Otros Estados' },
  ];

  constructor(private readonly http: HttpClient) {}

  ngOnInit(): void {
    this.sub = this.http
      .post<{ status: number; message: string; data: DealershipApiRow[] }>(
        `${environment.baseUrl}/api/dealerships/search`,
        {},
      )
      .subscribe({
        next: (res) => {
          const rows = Array.isArray(res?.data) ? res.data : [];
          this.locations = rows.map((d) => this.mapDealershipToLocation(d)).sort((a, b) => a.name.localeCompare(b.name));
          this.loading = false;
          this.loadError = false;
          if (this.locations.length > 0) {
            const prevId = this.selectedLocation?.id;
            const next =
              prevId != null ? this.filteredLocations.find((l) => l.id === prevId) : undefined;
            this.selectedLocation = next ?? this.filteredLocations[0] ?? this.locations[0] ?? null;
          } else {
            this.selectedLocation = null;
          }
        },
        error: () => {
          this.locations = [];
          this.selectedLocation = null;
          this.loading = false;
          this.loadError = true;
        },
      });
  }

  ngOnDestroy(): void {
    this.sub?.unsubscribe();
  }

  get filteredLocations(): Location[] {
    if (this.activeFilter === 'all') {
      return this.locations;
    }
    return this.locations.filter((loc) => loc.filter === this.activeFilter);
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

  onMobileSelect(event: Event): void {
    const id = (event.target as HTMLSelectElement).value;
    const loc = this.filteredLocations.find((l) => l.id === id);
    if (loc) {
      this.selectLocation(loc);
    }
  }

  getStateBadgeClasses(color: string): string {
    const map: Record<string, string> = {
      blue: 'bg-blue-100 text-blue-700',
      green: 'bg-green-100 text-green-700',
      orange: 'bg-orange-100 text-orange-700',
      purple: 'bg-purple-100 text-purple-700',
      gray: 'bg-gray-100 text-gray-700',
    };
    return map[color] || 'bg-gray-100 text-gray-700';
  }

  getStateDotClasses(color: string): string {
    const map: Record<string, string> = {
      blue: 'bg-blue-600',
      green: 'bg-green-500',
      orange: 'bg-orange-500',
      purple: 'bg-purple-500',
      gray: 'bg-gray-500',
    };
    return map[color] || 'bg-gray-500';
  }

  getDirectionsUrl(location: Location): string {
    return `https://maps.google.com/?q=${encodeURIComponent(location.address)}`;
  }

  getMapUrl(location: Location): string {
    if (location.lat != null && location.lng != null && !Number.isNaN(location.lat) && !Number.isNaN(location.lng)) {
      return `https://maps.google.com/maps?q=${location.lat},${location.lng}&z=15&output=embed`;
    }
    return `https://maps.google.com/maps?q=${encodeURIComponent(location.address)}&z=15&output=embed`;
  }

  hoursLine(location: Location): string {
    const h = location.openingHours?.trim();
    if (h) {
      return `Abierto · ${h}`;
    }
    return 'Abierto · Consulta horario en sucursal';
  }

  telHref(location: Location): string {
    const raw = (location.phone || '').replace(/\s/g, '').replace(/-/g, '');
    return raw ? `tel:${raw}` : '#';
  }

  whatsappHref(location: Location): string | null {
    const raw = location.whatsappPhone?.trim() || null;
    if (!raw) {
      return null;
    }
    const digits = raw.replace(/\D/g, '');
    if (digits.length < 10) {
      return null;
    }
    let n = digits;
    if (n.length === 10) {
      n = '52' + n;
    }
    if (n.startsWith('00')) {
      n = n.slice(2);
    }
    return `https://wa.me/${n}`;
  }

  trackById(_index: number, loc: Location): string {
    return loc.id;
  }

  private mapDealershipToLocation(d: DealershipApiRow): Location {
    const stateLabel = this.resolveStateLabel(d);
    const filter = this.resolveFilter(d, stateLabel);
    const lat = d.latitude != null ? Number(d.latitude) : NaN;
    const lng = d.longitude != null ? Number(d.longitude) : NaN;
    const latOk = Number.isFinite(lat);
    const lngOk = Number.isFinite(lng);

    return {
      id: String(d.id),
      name: d.name || 'Sucursal',
      address: (d.location || '').trim() || '—',
      phone: (d.phone || '').trim() || '—',
      email: d.email?.trim() || undefined,
      lat: latOk ? (lat as number) : null,
      lng: lngOk ? (lng as number) : null,
      image: this.resolveImageUrl(d.image_url),
      filter,
      state: stateLabel,
      stateColor: this.stateColorFor(stateLabel),
      whatsappPhone: d.whatsapp_phone,
      openingHours: d.opening_hours,
    };
  }

  private resolveStateLabel(d: DealershipApiRow): string {
    const s = d.state?.trim();
    if (s) {
      return s;
    }
    return this.inferStateFromText(`${d.location || ''} ${d.name || ''}`);
  }

  private resolveFilter(d: DealershipApiRow, stateLabel: string): 'puebla' | 'otros' {
    const explicit = d.state?.trim().toLowerCase();
    if (explicit === 'puebla') {
      return 'puebla';
    }
    if (explicit && explicit !== 'puebla') {
      return 'otros';
    }
    return this.inferPueblaFromText(`${d.location || ''} ${d.name || ''}`) ? 'puebla' : 'otros';
  }

  private inferPueblaFromText(text: string): boolean {
    const t = text.toLowerCase();
    return (
      t.includes('puebla') ||
      t.includes('atlixcayotl') ||
      t.includes('serdán') ||
      t.includes('serdan') ||
      t.includes('cholula') ||
      t.includes('tehuacán') ||
      t.includes('tehuacan') ||
      t.includes('angelópolis') ||
      t.includes('angelopolis') ||
      t.includes('hub serdán') ||
      t.includes('hub serdan')
    );
  }

  private inferStateFromText(text: string): string {
    const t = text.toLowerCase();
    const pairs: [string, string][] = [
      ['puebla', 'Puebla'],
      ['hidalgo', 'Hidalgo'],
      ['veracruz', 'Veracruz'],
      ['oaxaca', 'Oaxaca'],
      ['tlaxcala', 'Tlaxcala'],
      ['cdmx', 'Ciudad de México'],
      ['ciudad de méxico', 'Ciudad de México'],
    ];
    for (const [needle, label] of pairs) {
      if (t.includes(needle)) {
        return label;
      }
    }
    if (this.inferPueblaFromText(text)) {
      return 'Puebla';
    }
    return 'México';
  }

  private stateColorFor(state: string): string {
    const s = state.toLowerCase();
    if (s.includes('puebla')) {
      return 'blue';
    }
    if (s.includes('hidalgo')) {
      return 'green';
    }
    if (s.includes('veracruz')) {
      return 'orange';
    }
    if (s.includes('oaxaca')) {
      return 'purple';
    }
    return 'gray';
  }

  private resolveImageUrl(url: string | null | undefined): string {
    const u = url?.trim();
    if (!u) {
      return PLACEHOLDER_IMAGE;
    }
    if (u.startsWith('http://') || u.startsWith('https://')) {
      return u;
    }
    if (u.startsWith('/')) {
      const base = environment.baseUrl.replace(/\/$/, '');
      return `${base}${u}`;
    }
    if (u.startsWith('assets/')) {
      return u;
    }
    return u;
  }
}
