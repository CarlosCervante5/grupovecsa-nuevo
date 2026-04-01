import { Component, OnInit } from '@angular/core';
import { AppointmentsService } from '../../services/appointments.service';
import { Appointment } from '../../interfaces/appointments.interface';

@Component({
  selector: 'app-appointments-tab',
  templateUrl: './appointments-tab.component.html',
  styleUrls: ['./appointments-tab.component.css'],
  standalone: false
})
export class AppointmentsTabComponent implements OnInit {

  appointments: Appointment[] = [];
  loading = true;
  error = false;
  loaded = false;

  constructor(private appointmentsService: AppointmentsService) {}

  ngOnInit(): void {
    this.loadAppointments();
  }

  loadAppointments(): void {
    if (this.loaded) { return; }
    this.loading = true;
    this.error = false;
    this.appointmentsService.search().subscribe({
      next: (res) => {
        this.appointments = (res.data.appointments.data || []).sort((a, b) =>
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
    this.loadAppointments();
  }

  getTypeIcon(type: string): string {
    const map: { [key: string]: string } = {
      valuation: 'directions_car',
      service: 'build',
      general: 'event'
    };
    return map[type] || 'event_note';
  }

  getTypeLabel(type: string): string {
    const map: { [key: string]: string } = {
      valuation: 'Valuación',
      service: 'Servicio',
      general: 'General'
    };
    return map[type] || (type.charAt(0).toUpperCase() + type.slice(1));
  }
}
