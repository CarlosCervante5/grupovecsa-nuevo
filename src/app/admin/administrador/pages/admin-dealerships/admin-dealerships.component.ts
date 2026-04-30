import { Component, OnInit } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';
import { MatSnackBar } from '@angular/material/snack-bar';
import { DevCrudService } from '../../../developer/services/dev-crud.service';
import Swal from 'sweetalert2';

export interface AdminDealershipRow {
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
  vehicles_count?: number;
  users_count?: number;
}

@Component({
  selector: 'app-admin-dealerships',
  templateUrl: './admin-dealerships.component.html',
  styleUrls: ['./admin-dealerships.component.css'],
  standalone: false,
})
export class AdminDealershipsComponent implements OnInit {
  dealerships: AdminDealershipRow[] = [];
  filtered: AdminDealershipRow[] = [];
  loading = false;
  searchTerm = '';

  formOpen = false;
  editingId: number | null = null;
  form = {
    name: '',
    location: '',
    state: '',
    description: '',
    phone: '',
    email: '',
    whatsapp_phone: '',
    latitude: '',
    longitude: '',
    image_url: '',
    opening_hours: '',
  };
  saving = false;

  constructor(
    private readonly crud: DevCrudService,
    private readonly snackBar: MatSnackBar,
  ) {}

  ngOnInit(): void {
    this.load();
  }

  load(): void {
    this.loading = true;
    this.crud.fetch('dealerships/search', 'POST', {}).subscribe({
      next: (res: any) => {
        const d = res?.data;
        this.dealerships = Array.isArray(d) ? d : [];
        this.applyFilter();
        this.loading = false;
      },
      error: () => {
        this.dealerships = [];
        this.filtered = [];
        this.loading = false;
        this.toast('No se pudieron cargar las sucursales', true);
      },
    });
  }

  applyFilter(): void {
    const q = this.searchTerm.trim().toLowerCase();
    if (!q) {
      this.filtered = [...this.dealerships];
      return;
    }
    this.filtered = this.dealerships.filter(
      (r) =>
        (r.name || '').toLowerCase().includes(q) ||
        (r.location || '').toLowerCase().includes(q) ||
        (r.email || '').toLowerCase().includes(q),
    );
  }

  onSearch(): void {
    this.applyFilter();
  }

  clearSearch(): void {
    this.searchTerm = '';
    this.applyFilter();
  }

  openCreate(): void {
    this.editingId = null;
    this.form = {
      name: '',
      location: '',
      state: '',
      description: '',
      phone: '',
      email: '',
      whatsapp_phone: '',
      latitude: '',
      longitude: '',
      image_url: '',
      opening_hours: '',
    };
    this.formOpen = true;
  }

  openEdit(row: AdminDealershipRow): void {
    this.editingId = row.id;
    this.form = {
      name: row.name || '',
      location: row.location || '',
      state: row.state || '',
      description: row.description || '',
      phone: row.phone || '',
      email: row.email || '',
      whatsapp_phone: row.whatsapp_phone || '',
      latitude: row.latitude != null ? String(row.latitude) : '',
      longitude: row.longitude != null ? String(row.longitude) : '',
      image_url: row.image_url || '',
      opening_hours: row.opening_hours || '',
    };
    this.formOpen = true;
  }

  closeForm(): void {
    this.formOpen = false;
    this.editingId = null;
    this.saving = false;
  }

  submitForm(): void {
    const name = this.form.name.trim();
    const location = this.form.location.trim();
    if (!name || !location) {
      this.toast('Nombre y ubicación son obligatorios', true);
      return;
    }

    const latStr = this.form.latitude.trim();
    const lngStr = this.form.longitude.trim();
    let latitude: number | null = null;
    let longitude: number | null = null;
    if (latStr !== '') {
      latitude = Number(latStr);
      if (Number.isNaN(latitude)) {
        this.toast('Latitud no válida', true);
        return;
      }
    }
    if (lngStr !== '') {
      longitude = Number(lngStr);
      if (Number.isNaN(longitude)) {
        this.toast('Longitud no válida', true);
        return;
      }
    }

    const body: Record<string, unknown> = {
      name,
      location,
      state: this.form.state.trim() || null,
      description: this.form.description.trim() || null,
      phone: this.form.phone.trim() || null,
      email: this.form.email.trim() || null,
      whatsapp_phone: this.form.whatsapp_phone.trim() || null,
      latitude,
      longitude,
      image_url: this.form.image_url.trim() || null,
      opening_hours: this.form.opening_hours.trim() || null,
    };

    this.saving = true;
    if (this.editingId != null) {
      body['id'] = this.editingId;
      this.crud.update('dealerships/update', body).subscribe({
        next: () => {
          this.toast('Sucursal actualizada');
          this.closeForm();
          this.load();
        },
        error: (err) => this.handleSaveError(err),
      });
    } else {
      this.crud.store('dealerships/store', body).subscribe({
        next: () => {
          this.toast('Sucursal creada');
          this.closeForm();
          this.load();
        },
        error: (err) => this.handleSaveError(err),
      });
    }
  }

  private handleSaveError(err: unknown): void {
    this.saving = false;
    this.toast(this.httpErrorMessage(err), true);
  }

  deleteRow(row: AdminDealershipRow): void {
    Swal.fire({
      title: '¿Eliminar esta sucursal?',
      html: 'Los vehículos y usuarios asociados pueden quedar sin sucursal según la configuración del sistema.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#991b1b',
      cancelButtonText: 'Cancelar',
    }).then((result) => {
      if (!result.isConfirmed) return;
      this.crud.delete('dealerships/delete', { id: row.id }).subscribe({
        next: (res: any) => {
          Swal.fire(res?.message || 'Eliminada', '', 'success');
          this.load();
        },
        error: (err) => this.toast(this.httpErrorMessage(err), true),
      });
    });
  }

  private toast(msg: string, isError = false): void {
    this.snackBar.open(msg, 'Cerrar', { duration: isError ? 7000 : 4000 });
  }

  private httpErrorMessage(err: unknown): string {
    if (err instanceof HttpErrorResponse) {
      const body = err.error;
      if (body?.message && typeof body.message === 'string') return body.message;
      if (body?.errors) return JSON.stringify(body.errors);
      return err.message || `Error HTTP ${err.status}`;
    }
    return 'Error de red';
  }
}
