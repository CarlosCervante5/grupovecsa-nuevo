import { Component, EventEmitter, Inject, Output, OnInit } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup } from '@angular/forms';

import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';

import { Repair } from '@interfaces/getOnHoldBodyworkPaint.interface';

import { BodyworkPaintService } from '@services/bodywork-paint.service';

import Swal from 'sweetalert2';

@Component({
    selector: 'app-bodywork-paint-form',
    templateUrl: './bodywork-paint-form.component.html',
    styleUrls: ['./bodywork-paint-form.component.css'],
    standalone: false
})
export class BodyworkPaintFormComponent implements OnInit {

    @Output() imageSelected = new EventEmitter<string | null>();

    public bodyworkPaintForm!: UntypedFormGroup;
    public spinner: boolean = false;
    public toggle: boolean = false;
    public onHoldBodyworksPaints: Repair[] = [];

    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data:any,
        private bottomsheet: MatBottomSheetRef,
        private _formBuilder: UntypedFormBuilder,
        private _bodyworkPaintService: BodyworkPaintService
    ) {
        this.bodyworkPaintFormInit();
    }

    ngOnInit(): void { 
        // this.myData();
        this.getOnHoldBodyworkPaint();
     }

    public getOnHoldBodyworkPaint() {
        console.log(this.data.uuid, this.data.repairs);
        this.onHoldBodyworksPaints = this.data.repairs;
    }

    public bodyworkPaintFormInit() {
        this.bodyworkPaintForm = this._formBuilder.group({
            cost: [0]
        });
    }

    public closeBottomSheet() {
        this.bottomsheet.dismiss({reload: true});
    }

    public showModal(src: string | null) {
        this.imageSelected.emit(src);
        this.bottomsheet.dismiss();
    }

    public onSubmit(uuid: string, status: string) {
        this.toggle = true;
        this.spinner = true;
        let costo = this.bodyworkPaintForm.controls['cost'].value;

        if (status === 'rejected') {
            costo = 0;
            this.toggle = false;
        }

        this._bodyworkPaintService.setCostHyp(uuid, costo, status)
            .subscribe({
                next: (resp) => {
                    this.spinner = false;
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    });
                    if (this.toggle) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Costo aceptado',
                            text: String(resp.message)
                        });
                    }else{
                        Toast.fire({
                            icon: 'error',
                            title: 'Reparación Rechazada',
                            text: String(resp.message)
                        });
                    }
                }
            });
        
    }

}
