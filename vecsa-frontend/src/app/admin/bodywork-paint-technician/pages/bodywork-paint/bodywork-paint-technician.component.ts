import { Component, OnInit, ViewChild } from '@angular/core';
import { Router } from '@angular/router';

import { reload } from 'src/app/shared/helpers/session.helper';

import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatTableDataSource } from '@angular/material/table';

import { AppointmentService } from '@services/appointment.service';

import { ValuationAppointments, VehicleValuations } from '@interfaces/getAppointments.interface';
import { Repair } from '@interfaces/getOnHoldBodyworkPaint.interface';

import { BodyworkPaintFormComponent } from '../../components/bodywork-paint-form/bodywork-paint-form.component';


@Component({
    selector: 'app-bodywork-paint-technician',
    templateUrl: './bodywork-paint-technician.component.html',
    styleUrls: ['./bodywork-paint-technician.component.css'],
    standalone: false
})
export class BodyworkPaintTechnicianComponent implements OnInit {

  public length: number = 0;
  public page: number = 1;
  public tempUuid: string = '';
  public tempRepairs!: Repair[];

  dataSource!: MatTableDataSource<VehicleValuations>
  displayedColumns: string[] = ['id', 'name', 'lastName', 'brand', 'model', 'vin', 'year', 'status', 'actions'];

  @ViewChild(MatPaginator) paginator!: MatPaginator;

  public palabra_busqueda: string = '';

  selectedImageUrl: string | null = null;

  constructor(
    private _appointmentService: AppointmentService,
    private _bottomSheet: MatBottomSheet,
    private _router: Router
  ){}

  ngOnInit(): void {
    this.getAppointmentsHyP(this.page);
    this.scrollTop();
  }

  scrollTop() {
    let scrollElement = document.querySelector('#moveTop');
    scrollElement?.scrollIntoView();
  }

  public getAppointmentsHyP(page: number, keyword: string = ''): void {
    this._appointmentService.getAppointmentsHyP(page, keyword)
      .subscribe({
        next: ( response: ValuationAppointments) => {
          console.log(response.data.data);
          this.paginator.length = response.data.data.length;
          this.dataSource = new MatTableDataSource(response.data.data);
        },
        error: (error:any) => {
          reload(error, this._router);
        }
      });
  }

  public paginationChange(event: PageEvent) {
    this.getAppointmentsHyP(event.pageIndex + 1);
    this.scrollTop();
  }

  public openBodyworkPaint(uuid: string, repairs: Repair[]) {
    this.tempUuid = uuid;
    this.tempRepairs = repairs;
    const bottomSheetRef = this._bottomSheet.open(BodyworkPaintFormComponent, {
      data: {
        uuid: uuid,
        repairs: repairs,
        panelClass: 'custom-bottom-sheet'
      }
    });
    
    bottomSheetRef.instance.imageSelected.subscribe((imageUrl: string) => {
      this.onImageSelected(imageUrl);
    });

    bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {
        if(dataFromChild != undefined && dataFromChild.reload === true ){
            this.getAppointmentsHyP(this.page);
        }
    });
  }

  onImageSelected(imageUrl: string | null) {
    this.selectedImageUrl = imageUrl;
  }

  closeLightbox() {
    this.selectedImageUrl = null;
    const bottomSheetRef = this._bottomSheet.open(BodyworkPaintFormComponent, {
      data: {
        uuid: this.tempUuid,
        repairs: this.tempRepairs,
        panelClass: 'custom-bottom-sheet'
      }
    });
    bottomSheetRef.instance.imageSelected.subscribe((imageUrl: string) => {
      this.onImageSelected(imageUrl);
    });

    bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {
        if(dataFromChild != undefined && dataFromChild.reload === true ){
            this.getAppointmentsHyP(this.page);
        }
    });
  }

}
