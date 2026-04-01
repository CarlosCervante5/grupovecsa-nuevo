import { Component, OnInit } from '@angular/core';
import { QuotationsService } from '../../services/quotations.service';
import { Quotation } from '../../interfaces/quotations.interface';

@Component({
  selector: 'app-quotations-tab',
  templateUrl: './quotations-tab.component.html',
  styleUrls: ['./quotations-tab.component.css'],
  standalone: false
})
export class QuotationsTabComponent implements OnInit {

  quotations: Quotation[] = [];
  loading = true;
  error = false;
  loaded = false;

  constructor(private quotationsService: QuotationsService) {}

  ngOnInit(): void {
    this.loadQuotations();
  }

  loadQuotations(): void {
    if (this.loaded) { return; }
    this.loading = true;
    this.error = false;
    this.quotationsService.search().subscribe({
      next: (res) => {
        this.quotations = (res.data.data || []).sort((a, b) =>
          new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
        );
        this.loading = false;
        this.loaded = true;
      },
      error: () => {
        this.loading = false;
        this.error = true;
      }
    });
  }

  retry(): void {
    this.loaded = false;
    this.loadQuotations();
  }

  getVehicleInfo(q: Quotation): string {
    if (q.appointment && q.appointment.vehicle) {
      const v = q.appointment.vehicle;
      return [v.brand_name, v.model_name].filter(Boolean).join(' ');
    }
    if (q.vehicle) {
      const parts: string[] = [];
      if (q.vehicle.brand) { parts.push(q.vehicle.brand.name); }
      if (q.vehicle.line) { parts.push(q.vehicle.line.name); }
      if (q.vehicle.model) { parts.push(q.vehicle.model.name); }
      return parts.join(' ');
    }
    return 'Vehículo no especificado';
  }

  getVehicleYear(q: Quotation): string {
    if (q.appointment && q.appointment.vehicle && q.appointment.vehicle.year) {
      return q.appointment.vehicle.year;
    }
    if (q.vehicle && q.vehicle.year) {
      return q.vehicle.year;
    }
    return '';
  }

  getVehicleMileage(q: Quotation): string {
    if (q.appointment && q.appointment.vehicle && q.appointment.vehicle.mileage) {
      return q.appointment.vehicle.mileage;
    }
    return '';
  }

  getStatusColor(status: string): string {
    const map: { [key: string]: string } = {
      pending: '#94a3b8',
      pendiente: '#94a3b8',
      in_progress: '#eab308',
      en_proceso: '#eab308',
      completed: '#22c55e',
      completada: '#22c55e',
      cancelled: '#ef4444',
      cancelada: '#ef4444'
    };
    return map[status] || '#94a3b8';
  }

  getStatusLabel(status: string): string {
    const map: { [key: string]: string } = {
      pending: 'Pendiente',
      pendiente: 'Pendiente',
      in_progress: 'En proceso',
      en_proceso: 'En proceso',
      completed: 'Completada',
      completada: 'Completada',
      cancelled: 'Cancelada',
      cancelada: 'Cancelada'
    };
    return map[status] || (status.charAt(0).toUpperCase() + status.slice(1));
  }
}
