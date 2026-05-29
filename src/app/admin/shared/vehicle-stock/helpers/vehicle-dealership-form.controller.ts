import { FormGroup } from '@angular/forms';
import { Router } from '@angular/router';
import { Dealership, DealerShipResponse } from '@interfaces/admin.interfaces';
import { AdminService } from '@services/admin.service';
import { reload } from '@helpers/session.helper';
import {
  readSignedInUserEmail,
  readSignedInUserUuid,
  resolveAssignedDealerships,
  vehicleDealershipFallbackNamesForEmail,
  vehicleDealershipFormModeForEmail,
  vehicleDealershipNameForUserEmail,
  VehicleDealershipFormMode,
  vehicleLocationFallbackForDealershipName,
} from './vehicle-dealership-by-user.helper';

export interface VehicleDealershipFormApplyOptions {
  /** En actualizar vehículo: no sobrescribir sucursal/ubicación ya cargadas del vehículo. */
  preserveFormValues?: boolean;
}

export class VehicleDealershipFormController {
  dealershipMode: VehicleDealershipFormMode = 'manual';
  selectableDealerships: Dealership[] = [];
  dealershipsLoading = false;

  constructor(
    private readonly getForm: () => FormGroup,
    private readonly adminService: AdminService,
    private readonly router: Router,
  ) {}

  get locationFieldReadonly(): boolean {
    return this.dealershipMode === 'locked' || this.dealershipMode === 'select';
  }

  applyForSignedInUser(options?: VehicleDealershipFormApplyOptions): void {
    const email = readSignedInUserEmail();
    const preferredMode = vehicleDealershipFormModeForEmail(email);

    if (preferredMode === 'manual') {
      this.dealershipMode = 'manual';
      return;
    }

    const fallbackNames = vehicleDealershipFallbackNamesForEmail(email, preferredMode);
    this.loadAssignedDealershipsForUser(email, preferredMode, fallbackNames, options);
  }

  onDealershipSelected(event: Event): void {
    const selectedName = (event.target as HTMLSelectElement).value;
    const match = this.selectableDealerships.find((d) => d.name === selectedName);
    if (!match) {
      return;
    }
    const location =
      match.location?.trim() ||
      vehicleLocationFallbackForDealershipName(match.name);
    this.patchDealership(match.name, location);
    const form = this.getForm();
    form.get('location')?.markAsDirty();
    form.get('location')?.updateValueAndValidity();
  }

  private loadAssignedDealershipsForUser(
    email: string | null,
    preferredMode: VehicleDealershipFormMode,
    fallbackNames: readonly string[],
    options?: VehicleDealershipFormApplyOptions,
  ): void {
    const userUuid = readSignedInUserUuid();

    const finish = (
      catalog: Dealership[],
      assignedIds: number[],
      assignedNames: string[],
    ): void => {
      const assigned = resolveAssignedDealerships(
        catalog,
        assignedIds,
        assignedNames,
        fallbackNames,
      );
      this.applyDealershipUiFromAssignments(
        email,
        preferredMode,
        assigned,
        fallbackNames,
        options,
      );
    };

    if (!userUuid) {
      this.loadDealershipCatalog((catalog) => finish(catalog, [], []));
      return;
    }

    this.dealershipsLoading = true;
    this.adminService.fetchSessionUserDealershipAssignment(userUuid).subscribe({
      next: ({ ids, names }) => {
        this.loadDealershipCatalog(
          (catalog) => finish(catalog, ids, names),
          () => finish([], ids, names),
        );
      },
      error: () => {
        this.dealershipsLoading = false;
        this.loadDealershipCatalog((catalog) => finish(catalog, [], []));
      },
    });
  }

  private applyDealershipUiFromAssignments(
    email: string | null,
    preferredMode: VehicleDealershipFormMode,
    assigned: Dealership[],
    fallbackNames: readonly string[],
    options?: VehicleDealershipFormApplyOptions,
  ): void {
    const preserve = options?.preserveFormValues === true;

    if (preferredMode === 'select' || assigned.length > 1) {
      this.dealershipMode = 'select';
      this.selectableDealerships =
        assigned.length > 0
          ? assigned
          : this.buildDealershipStubs(fallbackNames);
      return;
    }

    if (assigned.length === 1) {
      this.dealershipMode = 'locked';
      if (!preserve) {
        this.lockDealershipFromRecord(assigned[0]);
      }
      return;
    }

    if (preferredMode === 'locked') {
      const fallbackName = vehicleDealershipNameForUserEmail(email);
      if (fallbackName) {
        this.dealershipMode = 'locked';
        if (!preserve) {
          this.patchDealership(
            fallbackName,
            vehicleLocationFallbackForDealershipName(fallbackName),
          );
        }
      }
      return;
    }

    this.dealershipMode = 'select';
    this.selectableDealerships = this.buildDealershipStubs(fallbackNames);
  }

  private lockDealershipFromRecord(dealership: Dealership): void {
    const location =
      dealership.location?.trim() ||
      vehicleLocationFallbackForDealershipName(dealership.name);
    this.patchDealership(dealership.name, location);
  }

  private patchDealership(dealershipName: string, location: string): void {
    const form = this.getForm();
    form.patchValue({
      dealership_name: dealershipName,
      location,
    });
    form.get('dealership_name')?.markAsDirty();
    form.get('location')?.markAsDirty();
  }

  private buildDealershipStubs(names: readonly string[]): Dealership[] {
    return names.map((name) => ({
      name,
      location: vehicleLocationFallbackForDealershipName(name),
      description: null,
      created_at: new Date(),
    }));
  }

  private loadDealershipCatalog(
    onSuccess: (dealerships: Dealership[]) => void,
    onErrorFallback?: () => void,
  ): void {
    this.dealershipsLoading = true;
    this.adminService.getDealerships().subscribe({
      next: (response: DealerShipResponse) => {
        this.dealershipsLoading = false;
        const catalog = Array.isArray(response.data) ? response.data : [];
        onSuccess(catalog);
      },
      error: (error) => {
        this.dealershipsLoading = false;
        onErrorFallback?.();
        const status =
          error && typeof error === 'object' && 'status' in error
            ? Number((error as { status: number }).status)
            : 0;
        if (status === 401) {
          reload(error, this.router);
        }
      },
    });
  }
}
