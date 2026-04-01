import { Component, OnInit } from '@angular/core';
import { AppointmentsService } from '../../services/appointments.service';
import { Appointment } from '../../interfaces/appointments.interface';

@Component({
  selector: 'app-valuations-tab',
  templateUrl: './valuations-tab.component.html',
  styleUrls: ['./valuations-tab.component.css'],
  standalone: false
})
export class ValuationsTabComponent implements OnInit {
  valuations: Appointment[] = [];
  loading = true;
  error = false;
  loaded = false;

  constructor(private appointmentsService: AppointmentsService) {}

  ngOnInit(): void {
    this.loadValuations();
  }

  loadValuations(): void {
    if (this.loaded) return;
    this.loading = true;
    this.error = false;
    this.appointmentsService.searchByType('valuation').subscribe({
      next: (res) => {
        const data = res.data?.appointments?.data || [];
        this.valuations = data.sort((a: Appointment, b: Appointment) =>
          new Date(b.appointment_scheduled_date).getTime() - new Date(a.appointment_scheduled_date).getTime()
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
    this.loadValuations();
  }

  getStatusLabel(status: string): string {
    const map: Record<string, string> = {
      scheduled: 'Agendada',
      confirmed: 'Confirmada',
      completed: 'Completada',
      cancelled: 'Cancelada',
      pending: 'Pendiente',
    };
    return map[status] || (status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Pendiente');
  }

  getStatusColor(status: string): string {
    const map: Record<string, string> = {
      scheduled: '#1c69d4',
      confirmed: '#059669',
      completed: '#22c55e',
      cancelled: '#ef4444',
      pending: '#94a3b8',
    };
    return map[status] || '#94a3b8';
  }
}
