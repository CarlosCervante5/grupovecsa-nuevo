import { Component } from '@angular/core';
import { LocationsComponent } from 'src/app/home/components/locations/locations.component';

@Component({
  selector: 'app-gerente-sucursales',
  standalone: true,
  imports: [LocationsComponent],
  template: `
    <div class="gerente-sucursales">
      <app-locations />
    </div>
  `,
  styles: [
    `
      .gerente-sucursales {
        max-width: 100%;
        overflow-x: hidden;
      }
      :host ::ng-deep .py-20 {
        padding-top: 1.5rem !important;
        padding-bottom: 2rem !important;
      }
    `,
  ],
})
export class GerenteSucursalesComponent {}
