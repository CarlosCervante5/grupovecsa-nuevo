import { Component, Inject, ViewChild, OnInit, ElementRef } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { MatDatepickerInputEvent } from '@angular/material/datepicker';

// Service
import { SparePartsService } from '@services/spare-parts.service';

// Alert
import Swal from 'sweetalert2';

@Component({
    selector: 'app-spare-parts-edit',
    templateUrl: './spare-parts-edit.component.html',
    styleUrls: ['./spare-parts-edit.component.css'],
    standalone: false
})
export class SparePartsEditComponent implements OnInit {

    @ViewChild('timeOriginal') timeOriginal!: ElementRef<HTMLInputElement>;
    @ViewChild('timeGeneric') timeGeneric!: ElementRef<HTMLInputElement>;
    @ViewChild('timeUsed') timeUsed!: ElementRef<HTMLInputElement>

    public form!: UntypedFormGroup;
    public date = new Date();
    public spinner: boolean = false;
    public list_replacement_quote: any[] = [];
    public dateTimeOriginal: string = '';
    public dateTimeGeneric: string = '';
    public dateTimeUsed: string = '';

    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
        private _formBuilder: UntypedFormBuilder,
        private _bottomSheetRef: MatBottomSheetRef<any>,
        private _sparePartsService: SparePartsService
    ) {
        this.formInit();
    }

    ngOnInit(): void {
        this.myUuid();
    }

    get invalidPriceOriginal() {
        return this.form.get('priceOriginal')!.invalid && (this.form.get('priceOriginal')!.dirty || this.form.get('priceOriginal')!.touched);
    }

    get invalidTimeOriginal() {
        return this.form.get('timeOriginal')!.invalid && (this.form.get('timeOriginal')!.dirty || this.form.get('timeOriginal')!.touched);
    }

    get invalidSupplyOriginal() {
        return this.form.get('supplyOriginal')!.invalid && (this.form.get('supplyOriginal')!.dirty || this.form.get('supplyOriginal')!.touched);
    }
    
    public myUuid() {
        // console.log(this.data.valuation_uuid);
        console.log(this.data.partUuid);
    }

    public formInit() {
        this.form = this._formBuilder.group({
            priceOriginal: ['', [ Validators.required, Validators.min(0), Validators.max(1000000), Validators.pattern('[0-9]{1,7}')]],
            timeOriginal: ['', [Validators.required]],
            supplyOriginal: ['', [Validators.required]],
            priceGeneric: [''],
            timeGeneric: [''],
            supplyGeneric: [''],
            priceUsed: [''],
            timeUsed: [''],
            supplyUsedRepair: ['']
        });

        // Observa cambios en 'priceGeneric'
        this.form.get('priceGeneric')!.valueChanges.subscribe(value => {
            if (value) {
                this.form.get('timeGeneric')!.setValidators([Validators.required]);
                this.form.get('supplyGeneric')!.setValidators([Validators.required]);
            } else {
                this.form.get('timeGeneric')!.clearValidators();
                this.form.get('supplyGeneric')!.clearValidators();
            }
            this.form.get('timeGeneric')!.updateValueAndValidity();
            this.form.get('supplyGeneric')!.updateValueAndValidity();
        });

        // Observa cambios en 'priceUsed'
        this.form.get('priceUsed')!.valueChanges.subscribe(value => {
            if (value) {
                this.form.get('timeUsed')!.setValidators([Validators.required]);
                this.form.get('supplyUsedRepair')!.setValidators([Validators.required]);
            } else {
                this.form.get('timeUsed')!.clearValidators();
                this.form.get('supplyUsedRepair')!.clearValidators();
            }
            this.form.get('timeUsed')!.updateValueAndValidity();
            this.form.get('supplyUsedRepair')!.updateValueAndValidity();
        });
    }

    public closeSheet(): void {
        this._bottomSheetRef.dismiss();
    }

    public onSubmit() {
        if (this.form.valid) {
                const price_original = this.form.get('priceOriginal')?.value;
                const delivery_original =  this.dateTimeOriginal;
                const supplier_original = this.form.get('supplyOriginal')?.value;
                const price_generic = this.form.get('priceGeneric')?.value;
                const delivery_generic = this.dateTimeGeneric === 'NaN-NaN-NaN' ? '' : this.dateTimeGeneric;
                const supplier_generic = this.form.get('supplyGeneric')?.value;
                const price_used =  this.form.get('priceUsed')?.value;
                const delivery_used = this.dateTimeUsed === 'NaN-NaN-NaN' ? '' : this.dateTimeUsed;
                const supplier_used =  this.form.get('supplyUsedRepair')?.value;
                const part_uuid = this.data.partUuid;
            this._sparePartsService.sparePartsEdit(price_original, delivery_original, supplier_original, price_generic, delivery_generic,
                supplier_generic, price_used, delivery_used, supplier_used, part_uuid)
                .subscribe({
                    next: () => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Alta Cotización de Refacciones',
                            text: 'Cotización de refacciones almacenada exitosamente.',
                            showConfirmButton: true,
                            confirmButtonColor: '#EEB838',
                            timer: 3500
                        });
                        this.closeSheet();
                    },
                    error: (error:any) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ooopps..',
                            text: 'Al parecer ocurrio un error' + error.error.message,
                            showConfirmButton: true,
                            confirmButtonColor: '#EEB838',
                            timer: 3500
                        });
                    }
                });
        }
    }

    public timeEmmitter(event: MatDatepickerInputEvent<Date>, nameTime: string) {
        let d = new Date(`${event.value}`);
        let month = '' + (d.getMonth() + 1);
        let day = '' + d.getDate();
        let year = d.getFullYear();
        
        if (month.length < 2) {
            month = '0' + month;
        }
        if (day.length < 2) {
            day = '0' + day;
        }

        switch (nameTime) {
            case 'timeOriginal':
                this.dateTimeOriginal = [year, month, day].join('-');
                let inputValue = this.dateTimeOriginal.split("-").reverse().join("-");
                this.timeOriginal.nativeElement.value = inputValue;
                break;
            
            case 'timeGeneric':
                this.dateTimeGeneric = [year, month, day].join('-');
                let inputValueg = this.dateTimeGeneric.split("-").reverse().join("-");
                this.timeGeneric.nativeElement.value = inputValueg;
                break;

            case 'timeUsed':
                this.dateTimeUsed = [year, month, day].join('-');
                let inputValueu = this.dateTimeUsed.split("-").reverse().join("-");
                this.timeUsed.nativeElement.value = inputValueu;
                break;

            default:
                break;
        }

    }

}
