import { Component, Inject, OnInit } from '@angular/core';
import { FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { SpareParts } from '../../../../shared/interfaces/getChecklist.interface';
import { SparePartsService } from '@services/spare-parts.service';
import Swal from 'sweetalert2';
import { GralResponse } from '@interfaces/admin.interfaces';
import { GetDetailValuation } from '@interfaces/getDetailValuation.interface';

@Component({
    selector: 'app-spare-parts-form',
    templateUrl: './spare-parts-form.component.html',
    styleUrls: ['./spare-parts-form.component.css'],
    standalone: false
})
export class SparePartsFormComponent implements OnInit {
    public uuid_valuation = '';
    public form !: FormGroup;
    public list_spare_parts : SpareParts[] = [];
    public spinner: boolean = false;

    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
        private _formBuilder: UntypedFormBuilder,
        private _bottomSheetRef: MatBottomSheetRef<any>,
        private _sparePartsService: SparePartsService
    ){
        this.uuid_valuation = data.uuid_valuation;
        this.createForm();
    }

    ngOnInit(): void {
        //Called after the constructor, initializing input properties, and the first call to ngOnChanges.
        //Add 'implements OnInit' to the class.
        this.getSpareParts(this.uuid_valuation);
    }

    private createForm() {
        this.form = this._formBuilder.group({
            name:                   ['', [Validators.required]],
            amount:                 ['', [Validators.required]],
            hours:                  ['', [Validators.required]],
        });
    }

    public get nameInvalid() {
        return this.form.get('name')?.invalid && (this.form.get('name')?.dirty || this.form.get('name')?.touched);
    }
    public get amountInvalid() {
        return this.form.get('amount')?.invalid && (this.form.get('amount')?.dirty || this.form.get('amount')?.touched);
    }
    public get hoursInvalid() {
        return this.form.get('hours')?.invalid && (this.form.get('hours')?.dirty || this.form.get('hours')?.touched);
    }
    public maxLengthCheck(object: any) {
        if (object.value.length > object.maxLength) {
          object.value = object.value.slice(0, object.maxLength);
        }
    }
    public close():void {
        this._bottomSheetRef.dismiss();
    }

    public getSpareParts(valuation_uuid: string){
        this._sparePartsService.getSpareParts(valuation_uuid)
            .subscribe({
                next: ( spareParts: GetDetailValuation) => {
                    for (let i = 0; i < spareParts.data.spare_parts.length; i++) {
                        const refaccion = {
                            name:       spareParts.data.spare_parts[i].name,
                            quantity:   spareParts.data.spare_parts[i].quantity,
                            labor_time: spareParts.data.spare_parts[i].labor_time,
                            valuation_uuid: spareParts.data.spare_parts[i].uuid
                        }
                        this.list_spare_parts.push(refaccion);
                    }
                }
            });
            console.log(this.list_spare_parts);
            
    }

    public deletePart(part_uuid: string, index: number) {
        this.list_spare_parts.splice(index, 1);
        this._sparePartsService.deleteSparePart(part_uuid)
            .subscribe({
                next: ({ message }: GralResponse ) => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Refacción eliminada exitosamente.',
                        text: String(message),
                        showConfirmButton: true,
                        confirmButtonColor: '#EEB838',
                        timer: 3500
                    });
                },
                error: (error:any) => {
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

    public onSubmit(){
        if(this.form.valid){
            const refaccion = {
                name:               this.form.get('name')?.value,
                quantity:           this.form.get('amount')?.value,
                labor_time:         this.form.get('hours')?.value,
                valuation_uuid:     this.uuid_valuation     
            };
            this._sparePartsService.createSpareParts(refaccion)
                .subscribe({
                    next: ({ message }: GralResponse ) => {
                        this.spinner = false;
                        Swal.fire({
                            icon: 'success',
                            title: 'Inserción de refacción exitosa.',
                            text: String(message),
                            showConfirmButton: true,
                            confirmButtonColor: '#EEB838',
                            timer: 3500
                        });
                    },
                    error: (error) => {
                        this.spinner = false;
                    }
                });

            this.list_spare_parts.push(refaccion);
            console.log(this.list_spare_parts);
        }
    }
   

}
