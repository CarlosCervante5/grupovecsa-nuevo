import { Component, Inject, OnInit, inject } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';

import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { MatSlideToggleChange } from '@angular/material/slide-toggle';
import { MatSelectChange } from '@angular/material/select';

import { AcquisitionsChecklistService } from '@services/acquisitions-checklist.service';


import { ChecklistItem, GetAcquisitionsChecklist } from '@interfaces/getAcquisitionsChecklist.interfaces';
import { GralResponse } from '@interfaces/getChecklist.interface';

import Swal from 'sweetalert2';

@Component({
    selector: 'app-documentation-vehicle',
    templateUrl: './documentation-vehicle.component.html',
    styleUrls: ['./documentation-vehicle.component.css'],
    standalone: false
})
export class DocumentationVehicleComponent implements OnInit {

  public url: string = '';

  public yearTenencia!: number | null;
  public inputValor = '';
  public check_uuid = '';
  public inputFocus: boolean = false;
  public btn_load: boolean = true;
  public btn_watch: boolean = false;
  
  public spinner: boolean = false;
  public spinnerEnvio: boolean = false;
  public save_documents:boolean = false;
  public tenencia12!: number;
  public tenencia11!: number;
  public tenencia10!: number;
  public tenencia9!: number;
  public tenencia8!: number;
  public tenencia7!: number;
  public tenencia6!: number;
  public tenencia5!: number;
  public tenencia4!: number;
  public tenencia3!: number;
  public tenencia2!: number;
  public tenencia1!: number;

  // References Form
  public takeInformationForm!: UntypedFormGroup;
  public documentationCarForm!: UntypedFormGroup;
  public documentsPlateProceduresForm!: UntypedFormGroup;
  public years: number[] = [];
  public year!: number;

  public acquisitions: ChecklistItem[] = [];
  public informationGathering: ChecklistItem[] = [];
  public informationGatheringT: ChecklistItem[] = [];
  public unitDocumentation: ChecklistItem[] = [];
  public plateDocuments: ChecklistItem[] = [];

  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public data:any,
    private _bottomsheet: MatBottomSheetRef,
    private _acquisitionsChecklistService: AcquisitionsChecklistService,
    private _formBuilder: UntypedFormBuilder
  ) {
    this.year = new Date().getFullYear();
    this.takeInformationForm = this._formBuilder.group({});
    this.documentationCarForm = this._formBuilder.group({});
    this.documentsPlateProceduresForm = this._formBuilder.group({});
  }

  ngOnInit(): void {
    this.getAcquisitionsChecklist(this.data.valuation_uuid);
  }

  public uploadDocuments(file: any) {
    this.spinner = true;
    const picture = file.target.files[0];
    this._acquisitionsChecklistService.uploadPdf(this.data.valuation_uuid, picture)
      .subscribe({
        next: () => {
          Swal.fire({
            icon: 'success',
            text: 'Carga de Expediente almacenado con éxito.',
            showConfirmButton: true,
            confirmButtonColor: '#EEB838',
            timer: 3000
          });
          this.spinner = false;
          setTimeout(() => {
            window.location.reload();
          }, 2500);
        },
        error: (error) => {
          this.spinner = false;
          Swal.fire({
            icon: "error",
            title: 'Ooops...',
            text: 'Al parecer ocurrio un error' + error.error.message,
            showConfirmButton: true,
            confirmButtonColor: '#EEB838',
            timer: 3500
          });
        }
      });
  }

  public closeWindow() {
    this._bottomsheet.dismiss();
  }

  public getAcquisitionsChecklist(valuation_uuid: string) {
    console.log('url acquisition_pdf:', this.data.acquisition_pdf);
    this.url = this.data.acquisition_pdf;
    this.btn_watch = this.data.acquisition_pdf == "" ? false : true;

    this._acquisitionsChecklistService.getAcquisitionsChecklist(valuation_uuid)
      .subscribe({
        next: ( acquisitionsChecklist: GetAcquisitionsChecklist) => {

          this.acquisitions = acquisitionsChecklist.data;

          this.informationGathering = this.acquisitions.filter(acquisitionschk => acquisitionschk.section_name === 'Información de la toma');
          this.informationGatheringT = this.acquisitions.filter(acquisitionschk => acquisitionschk.section_name === 'Información de la toma tenencia');
          this.unitDocumentation = this.acquisitions.filter(acquisitionschk => acquisitionschk.section_name === 'Documentación de la unidad');
          this.plateDocuments = this.acquisitions.filter(acquisitionschk => acquisitionschk.section_name === 'Fotos' || acquisitionschk.section_name === 'Documentos para tramites de placas');
          console.log(this.informationGatheringT);
          
          this.createFormControls();
        }
      });
  }

  private createFormControls() {
    this.acquisitions.forEach(acquire => {
      if (acquire.section_name === 'Información de la toma') {
        // const selectedValueInfoToma = acquire.selected_value || '';
        // this.takeInformationForm.addControl(
        //   acquire.uuid,
        //   this._formBuilder.control(selectedValueInfoToma, Validators.required)
        // );
        const selectedValueInfoToma = acquire.value_type === 'number' && acquire.selected_value
          ? acquire.selected_value
          : acquire.selected_value || '';
          const validators = acquire.value_type === 'number' ? [] : [Validators.required];
          const control = this._formBuilder.control(selectedValueInfoToma, validators);
          this.takeInformationForm.addControl(
            acquire.uuid,
            control
          );
      }
      if (acquire.section_name === 'Información de la toma tenencia' ) {
        
        const selectedValueInfoToma = acquire.selected_value ? true : false ;

        this.takeInformationForm.addControl(
          acquire.uuid,
          this._formBuilder.control(selectedValueInfoToma, Validators.required)
        );
      }
      if (acquire.section_name === 'Documentación de la unidad') {
        const selectedValueUnitDocumentation = acquire.selected_value || '';
        this.documentationCarForm.addControl(
          acquire.uuid,
          this._formBuilder.control(selectedValueUnitDocumentation, Validators.required)
        );
      }
      if (acquire.section_name === 'Fotos' || acquire.section_name === 'Documentos para tramites de placas' ) {
        // const selectedPlateDocuments = acquire.selected_value || '';
        // this.documentsPlateProceduresForm.addControl(
        //   acquire.uuid,
        //   this._formBuilder.control(selectedPlateDocuments, Validators.required)
        // );
        const selectedPlateDocuments = acquire.value_type === 'textArea' && acquire.selected_value
          ? acquire.selected_value
          : acquire.selected_value || '';
          const validators = acquire.value_type === 'textArea' ? [] : [Validators.required];
          const control = this._formBuilder.control(selectedPlateDocuments, validators);
          this.documentsPlateProceduresForm.addControl(
            acquire.uuid,
            control
          );
      }
    });

    this.spinner = false;

  }

  public onSelectChange(event: MatSelectChange, uuid_check: string) {
    const valorSeleccionado = event.value;
    this.attachCheck(this.data.valuation_uuid, uuid_check, valorSeleccionado);
  }

  public onInputChange(event: Event, uuid_check: string) {
    const valor = (event.target as HTMLInputElement).value;
    console.log('Monto ingresado:', valor, ' check_uuid:', uuid_check);
    
    this.inputValor = valor;
    this.check_uuid = uuid_check;
  }

  public onInputBlur(uuid_check: string) {
    if (this.inputValor) {
      this.attachCheck(this.data.valuation_uuid, uuid_check, Number(this.inputValor));
    } else {
      this.attachCheck(this.data.valuation_uuid, uuid_check, null);
    }
  }

  public onTextAreaBlur(uuid_check: string) {
    console.log(this.inputValor);
    
    if (this.inputValor) {
      this.attachCheck(this.data.valuation_uuid, uuid_check, this.inputValor);
    } else {
      this.attachCheck(this.data.valuation_uuid, uuid_check, null);
    }
  }

  public onTenancyChange(uuidTenancy:string, yearValue: number, event: MatSlideToggleChange) {
    console.log(uuidTenancy, yearValue, event.checked);
    this.yearTenencia = event.checked ? yearValue : null;
    this.attachCheck(this.data.valuation_uuid, uuidTenancy, this.yearTenencia);
  }

  public attachCheck(valuation_uuid: string, checkpoint_uuid: string, selected_value: number | string | null) {
    this._acquisitionsChecklistService.updateAcquisitions(valuation_uuid, checkpoint_uuid, selected_value)
      .subscribe({
        next: (resp: GralResponse) => {
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
            icon: 'success',
            title: "Guardado..."
          });
        },
        error: (error: any) => {
          Swal.fire({
            icon: "error",
            title: 'Ooops...',
            text: 'Al parecer ocurrio un error' + error.error.message,
            showConfirmButton: true,
            confirmButtonColor: '#EEB838',
            timer: 3500
          });
        }
      });
  }
}
