import { Pipe, PipeTransform } from '@angular/core';
import { vehicleLocationLabelForDealership } from 'src/app/admin/shared/vehicle-stock/helpers/vehicle-dealership-by-user.helper';

export type VehicleDealershipStateInput = {
  name?: string;
  location?: string | null;
  state?: string | null;
} | null | undefined;

/** Estado del vehículo según sucursal (no la dirección postal). */
@Pipe({
  name: 'vehicleDealershipState',
  standalone: true,
})
export class VehicleDealershipStatePipe implements PipeTransform {
  transform(dealership: VehicleDealershipStateInput): string {
    if (!dealership?.name?.trim()) {
      return '—';
    }
    const label = vehicleLocationLabelForDealership({
      name: dealership.name,
      location: dealership.location ?? null,
      state: dealership.state ?? null,
    });
    return label || '—';
  }
}
