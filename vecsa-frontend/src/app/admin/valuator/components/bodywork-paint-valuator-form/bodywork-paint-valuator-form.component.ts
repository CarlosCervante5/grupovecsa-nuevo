import { Component, Inject, type OnInit } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';

import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { environment } from '@environments/environment';
import { GetDetailValuation, Repair } from '@interfaces/getDetailValuation.interface';

import { BodyworkPaintService } from '@services/bodywork-paint.service';

import Swal from 'sweetalert2';

@Component({
    selector: 'app-bodywork-paint-valuator-form',
    templateUrl: './bodywork-paint-valuator-form.component.html',
    styleUrls: ['./bodywork-paint-valuator-form.component.css'],
    standalone: false
})
export class BodyworkPaintValuatorFormComponent implements OnInit {

    public bodyworkPaintValuatorForm!: UntypedFormGroup;
    public spinner: boolean = false;
    public damage_image_file:any;
    public bodywork_paint: any[] = [];
    public imgBodyworkPaints: Repair[] = [];

    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data:any,
        private bottomsheet: MatBottomSheetRef,
        private _formBuilder: UntypedFormBuilder,
        private _bodyworkPintService: BodyworkPaintService
    ) {
        this.bodyworkPaintValuatorFormInit();
        this.getImgBodyworkPaint();
    }

    ngOnInit(): void { }

    // get nameDescriptionInvalid() {
    get descriptionInvalid() {
        return this.bodyworkPaintValuatorForm.get('description')!.invalid && (this.bodyworkPaintValuatorForm.get('description')!.dirty || this.bodyworkPaintValuatorForm.get('description')!.touched);
    }

    get imgDamageInvalid(){
        // return this.bodyworkPaintValuatorForm.get('picture')?.value === null;
        return this.bodyworkPaintValuatorForm.get('image')?.value === null;
    }

    public bodyworkPaintValuatorFormInit() {
        this.bodyworkPaintValuatorForm = this._formBuilder.group({
            // name:       ['', [ Validators.required, Validators.minLength(1), Validators.maxLength(150)]],
            description:       ['', [ Validators.required, Validators.minLength(1), Validators.maxLength(150)]],
            // picture:    [null, [Validators.required]]
            image:    [null, [Validators.required]]
        });
    }

    public getImgBodyworkPaint(){
        this._bodyworkPintService.getImgBodyworkPaint(this.data.uuid_valuation)
            .subscribe({
                next: (imgResp: GetDetailValuation) => {
                    console.log(imgResp.data.repairs);
                    if (imgResp.data.repairs.length > 0) {
                        this.imgBodyworkPaints = imgResp.data.repairs
                    }
                }
            });
    }

    public closeBottomSheet() {
        this.bottomsheet.dismiss();
    }

    public damageImage(file: any) {
        this.damage_image_file = file.target.files[0];
    }

    public onSubmit() {
        this.spinner = true;
        console.log('Hola desde el onSubmit()');
        this._bodyworkPintService.setHypRequest(
            this.bodyworkPaintValuatorForm.controls['description'].value,
            this.damage_image_file,
            this.data.uuid_valuation
        )
            .subscribe({
                next: (resp) => {
                    this.spinner = false;
                    Swal.fire({
                        icon: 'success',
                        title: 'Solicitud HyP',
                        text: String(resp.message),
                        showConfirmButton: true,
                        confirmButtonColor: '#EEB838',
                        timer: 3500
                    });
                    this.spinner = false;
                },
                error: (error: any) => {
                    this.spinner = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Ooops...',
                        text: 'Al parecer ocurrió un error' + error.error.message,
                        showConfirmButton: true,
                        confirmButtonColor: '#EEB838',
                        timer: 3500
                    });
                }
            });
    }

}
