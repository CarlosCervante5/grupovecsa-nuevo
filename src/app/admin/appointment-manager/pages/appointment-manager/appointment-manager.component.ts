import { Component } from '@angular/core';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { PageEvent } from '@angular/material/paginator';
import { MatSelectChange } from '@angular/material/select';
import { MatTableDataSource } from '@angular/material/table';
import { GralResponse } from '@interfaces/admin.interfaces';
import { AppointmentResponse,DatDates, DatoTables, ValuatorsResponse, User } from '@interfaces/getAppointments.interface';
import { AppointmentService } from '@services/appointment.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-appointment-manager',
    templateUrl: './appointment-manager.component.html',
    styleUrls: ['./appointment-manager.component.css'],
    standalone: false
})
export class AppointmentManagerComponent {
  public data !: DatDates[];
  public datostable: DatoTables[] = [];
  public displayedColumns: string[] = ['id', 'brand', 'model', 'name', 'last_name', 'phone1', 'date', 'subsidiary', 'valuator'];
  public dataSource = new MatTableDataSource(this.datostable);
  selectedValuators: { [key: number]: string } = {};

  //variables para la paginación
  public paginate = 15;
  public page: number = 1;
  public pageIndex: number = 1;
  public length: number  = 0;

  public valuators: User[] = [];


  constructor (
    private _appointmentService: AppointmentService,
  ){
    this.getData(this.page);
    this.getValuators();
  }

  public getData (page:number){
    this._appointmentService.getExternalDates(page)
    .subscribe({
      next: (response: AppointmentResponse) =>{
        this.data = response.data.appointments.data;
        this.length = response.data.appointments.total;
        this.pageIndex = response.data.appointments.current_page;
        const datosR = this.data.map((dato, index) => ({
          index:                ((this.pageIndex-1) * this.paginate)+(index+1),
          brand:                dato.vehicle_brandname,
          model:                dato.vehicle_modelname,
          name:                 dato.customer_name,
          last_name:            dato.customer_lastname,
          phone1:               dato.phone_1,
          date:                 dato.appointment_scheduled_date,
          dealership_name:      dato.dealership_name,
          appointment_type:     dato.appointment_type,
          appointment_uuid:     dato.appointment_uuid,
          valuator_name:        dato.valuator_name,
          valuator_last_name:   dato.valuator_last_name,
          id:                   dato.valuator_uuid,
        }));
        this.datostable = datosR;

        this.dataSource.data = this.datostable;
      }
    })
  }

  public paginationChange(event: PageEvent) {
    this.page = event.pageIndex + 1;
    this.getData(this.page);
  }

  public getValuators(){
    this._appointmentService.getValuators()
    .subscribe({
      next: (response:ValuatorsResponse) =>{
          this.valuators = response.data.users;
      }
    })
  }

  onValuatorChange(event: MatSelectChange, uuid:string) {
    this._appointmentService.attatchValuator(event.value, uuid)
    .subscribe({
      next:(response: GralResponse) => {
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
          }
        });
        Toast.fire({
          icon: "success",
          title: "Guardado..."
        });

      },
      error( error: any){
        console.log(error);
      }
    });
  }

}
