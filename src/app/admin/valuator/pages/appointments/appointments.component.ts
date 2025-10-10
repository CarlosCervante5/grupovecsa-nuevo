import { Component, inject, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';

// Angular Material
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator, PageEvent } from '@angular/material/paginator';

// Componentes
import { AppointmentFormComponent } from '../../components/appointment-form/appointment-form.component';
import { DocumentationVehicleComponent } from '../../components/documentation-vehicle/documentation-vehicle.component';

// Servicios
import { AppointmentService } from '@services/appointment.service';
import { ValuationAppointments, VehicleValuations } from '@interfaces/getAppointments.interface';
import { Router } from '@angular/router';
import {reload} from '../../../../shared/helpers/session.helper';
import { MatIconRegistry } from '@angular/material/icon';
import { DomSanitizer } from '@angular/platform-browser';

const THUMBUP_ICON =
  `
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"/></svg>
`;

@Component({
    selector: 'app-appointments',
    templateUrl: './appointments.component.html',
    styleUrls: ['./appointments.component.css'],
    encapsulation: ViewEncapsulation.None,
    standalone: false
})
export class AppointmentsComponent implements OnInit {

  public length: number = 0;
  public page: number = 1;

  dataSource!: MatTableDataSource<VehicleValuations>;
  displayedColumns: string[] = ['id', 'name', 'lastName', 'brand', 'model', 'vin', 'year', 'status', 'statusParts', 'statusRepairs', 'actions'];

  public palabra_busqueda: string = '';
  private timer: any;

  @ViewChild(MatPaginator) paginator!: MatPaginator;

  constructor(
    private _bottomSheet: MatBottomSheet,
    private _appointmentService: AppointmentService,
    private _router: Router
  ) {
    const iconRegistry = inject(MatIconRegistry);
    const sanitizer = inject(DomSanitizer)
    iconRegistry.addSvgIconLiteral('thumbs-up', sanitizer.bypassSecurityTrustHtml(THUMBUP_ICON));
  }

  ngOnInit(): void {
    this.getAppointments(this.page);
    this.scrollTop();
  }

  scrollTop() {
    var scrollElem = document.querySelector('#moveTop');
    scrollElem!.scrollIntoView();  
  }

  public newAppointment(){
    this._bottomSheet.open(AppointmentFormComponent);  
  }

  public getAppointments(page: number, keyword: string = ''): void {
    this._appointmentService.getAppointments(page, keyword)
      .subscribe({
        next: ( response: ValuationAppointments) => {
          console.log(response.data.data);
          this.paginator.length = response.data.total;
          this.dataSource = new MatTableDataSource(response.data.data);
        },
        error: (error:any) => {
          reload(error, this._router);
        }
      });
  }

  public openBottomSheetDocumentation(valuation_uuid: string, acquisition_pdf: string) {
    this._bottomSheet.open(DocumentationVehicleComponent, {
      data: {
        valuation_uuid,
        acquisition_pdf
      }
    });
  }

  public searchByKeyword(){
    let busqueda = this.palabra_busqueda.length > 0 ? this.palabra_busqueda : '';
    // this._appointmentService.searchByKeyword(busqueda)
    //   .subscribe({
    //     next: ( response: ValuationAppointments) => {
    //       this.paginator.length = response.data.total;
    //       this.dataSource = new MatTableDataSource(response.data.data);
    //     }
    //   });
    this.getAppointments(this.page, busqueda);
  }

  public searchKeyboard(){
    if (this.timer) {
      clearTimeout(this.timer);
    }
    this.timer = setTimeout(() => {
      this.searchByKeyword();
    }, 630);
  }

  public paginationChange(event: PageEvent) {
    this.getAppointments(event.pageIndex + 1);
    this.scrollTop();
  }
}
