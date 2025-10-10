import { Component, ElementRef, ViewChild, type OnInit } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { UserTechnicians } from '@interfaces/admin.interfaces';

import { SparePartsService } from '@services/spare-parts.service';
import { UpdateQuoteValuationService } from '@services/update-quote-valuation.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-quote-sell-car-request',
    templateUrl: './quote-sell-car-request.component.html',
    styleUrls: ['./quote-sell-car-request.component.css'],
    standalone: false
})
export class QuoteSellCarRequestComponent implements OnInit {

    @ViewChild('workforce') workforce!:ElementRef<HTMLInputElement>;
    @ViewChild('spare_parts') spare_parts!:ElementRef<HTMLInputElement>;
    @ViewChild('hyp') hyp!:ElementRef<HTMLInputElement>;

    public quotationForm!: UntypedFormGroup;

    public workforceInactive = true;
    public sparePartsInactive = true;

    public checkedp  = false;
    public checkedt  = false;
    public checkedpt = false;

    public sellers: UserTechnicians[] = [];

    constructor(
        private _formBuilder: UntypedFormBuilder,
        private _activatedRoute: ActivatedRoute,
        private _sparePartsService: SparePartsService,
        private _updateQuoteValuationService: UpdateQuoteValuationService,
        private _router: Router
    ) {
        this.quotationFormInit();
    }

    // ngAfterViewInit() {
    //     this.getPartsLaborTime(this._activatedRoute.snapshot.params.uuid_valuation);
    // }

    ngOnInit(): void {
        this.getPartsLaborTime(this._activatedRoute.snapshot.params.uuid_valuation);
        this.getSellers();
    }

    get takeInvalid() {
        return this.quotationForm.get('take')!.invalid && (this.quotationForm.get('take')!.dirty || this.quotationForm.get('take')!.touched);
    }

    get saleInvalid() {
        return this.quotationForm.get('sale')!.invalid && (this.quotationForm.get('sale')!.dirty || this.quotationForm.get('sale')!.touched);
    }

    get takeIntelimotorsInvalid() {
        return this.quotationForm.get('take_intelimotors')!.invalid && (this.quotationForm.get('take_intelimotors')!.dirty || this.quotationForm.get('take_intelimotors')!.touched);
    }

    get saleIntelimotorsInvalid() {
        return this.quotationForm.get('sale_intelimotors')!.invalid && (this.quotationForm.get('sale_intelimotors')!.dirty || this.quotationForm.get('sale_intelimotors')!.touched);
    }

    get workforceInvalid() {
        return this.quotationForm.get('workforce')!.invalid && (this.quotationForm.get('workforce')!.dirty || this.quotationForm.get('workforce')!.touched);
    }

    get spare_partsInvalid() {
        return this.quotationForm.get('spare_parts')!.invalid && (this.quotationForm.get('spare_parts')!.dirty || this.quotationForm.get('spare_parts')!.touched);
    }

    get hypInvalid() {
        return this.quotationForm.get('hyp')!.invalid && (this.quotationForm.get('hyp')!.dirty || this.quotationForm.get('hyp')!.touched);
    }

    get take_valueInvalid() {
        return this.quotationForm.get('take_value')!.invalid && (this.quotationForm.get('take_value')!.dirty || this.quotationForm.get('take_value')!.touched);
    }

    get final_offerInvalid() {
        return this.quotationForm.get('final_offer')!.invalid && (this.quotationForm.get('final_offer')!.dirty || this.quotationForm.get('final_offer')!.touched);
    }

    get sellerInvalid() {
        return this.quotationForm.get('seller')!.invalid && (this.quotationForm.get('seller')!.dirty || this.quotationForm.get('seller')!.touched);
    }

    private quotationFormInit(){
        this.quotationForm = this._formBuilder.group({
            take: [0, Validators.required],
            sale: [0, Validators.required],
            take_intelimotors: [0, Validators.required],
            sale_intelimotors: [0, Validators.required],
            workforce: [0, [Validators.required, Validators.pattern("[0-9\.]{1,7}")]],
            spare_parts: [0, [Validators.required, Validators.pattern("[0-9\.]{1,9}")]],
            hyp: [0, [Validators.required, Validators.pattern("[0-9\.]{1,7}")]],
            total: [0],
            take_value: [0, Validators.required],
            final_offer: [0, Validators.required],
            direct_purchase: [],
            take_into_account: [],
            direct_purchase_take_account: [],
            seller: ['', Validators.required],
            comments: [''],
        });
    }

    public getPartsLaborTime(uuid: string) {
        this._sparePartsService.getSparepartsLaborTime(uuid)
            .subscribe({
                next: (resp) => {
                    console.log(resp.data);
                    let sum = 0;
                    let sumCost = 0;
                    let sumRepairs = 0;
                    resp.data.spare_parts.map(splt => {
                        sum += splt.labor_time;
                        sumCost += (splt.part_supplier_original!.cost * splt.quantity);
                    });
                    // console.log(sumCost);
                    resp.data.repairs.map(repairs => {
                        sumRepairs += repairs.cost;
                    });
                    // console.log(sumRepairs);
                    this.quotationForm.controls['workforce'].setValue(Number(sum * 45).toFixed(2));
                    this.quotationForm.controls['spare_parts'].setValue(Number(sumCost).toFixed(2));
                    this.quotationForm.controls['hyp'].setValue(Number(sumRepairs).toFixed(2));
                    this.onCotizacion();
                    if (resp.data.status === 'valuated') {
                        this.quotationForm.patchValue({
                            take: resp.data.book_trade_in_offer,
                            sale: resp.data.book_sale_price,
                            take_intelimotors: resp.data.intellimotors_trade_in_offer,
                            sale_intelimotors: resp.data.intellimotors_sale_price,
                            take_value: resp.data.trade_in_final,
                            final_offer: resp.data.final_offer,
                            seller: resp.data.seller[0].uuid,
                            comments: resp.data.comments
                        });
                        if (resp.data.take_type === 'Compra directa') {
                            this.checkedp = true;
                            this.quotationForm.controls['direct_purchase'].setValue(this.checkedp);
                        } else {
                            this.checkedp = false;
                            this.quotationForm.controls['direct_purchase'].setValue(this.checkedp);
                        }
                        if (resp.data.take_type === 'Toma a cuenta') {
                            this.checkedt = true;
                            this.quotationForm.controls['take_into_account'].setValue(this.checkedt);
                        } else {
                            this.checkedt = false;
                            this.quotationForm.controls['take_into_account'].setValue(this.checkedt);
                        }
                        if (resp.data.take_type === 'Compra directa/Toma a cuenta') {
                            this.checkedpt = true;
                            this.quotationForm.controls['direct_purchase_take_account'].setValue(this.checkedpt);
                        } else {
                            this.checkedpt = false;
                            this.quotationForm.controls['direct_purchase_take_account'].setValue(this.checkedpt);
                        }
                    }
                }
            });
    }

    public onCotizacion() {
        const input = this.quotationForm.controls['workforce'].value;
        const input2 = this.quotationForm.controls['spare_parts'].value;
        const input3 = this.quotationForm.controls['hyp'].value;
        const totalReacondition = (parseFloat(input) || 0) + (parseFloat(input2) || 0) + (parseFloat(input3) || 0);
        console.log(totalReacondition);
        
        this.quotationForm.controls['total'].setValue(Number(totalReacondition).toFixed(2));
    }

    public getSellers() {
        this._sparePartsService.getSellers()
            .subscribe({
                next: (resp) => {
                    console.log(resp.data.users);
                    this.sellers = resp.data.users;
                }
            });
    }

    public onSubmit() {
        
        const valuation_uuid = this._activatedRoute.snapshot.params.uuid_valuation;
        const seller_uuid = this.quotationForm.controls['seller'].value;
        const book_trade_in_offer = this.quotationForm.controls['take'].value;
        const book_sale_price = this.quotationForm.controls['sale'].value;
        const intellimotors_trade_in_offer = this.quotationForm.controls['take_intelimotors'].value;
        const intellimotors_sale_price = this.quotationForm.controls['sale_intelimotors'].value;
        const labor_cost = this.quotationForm.controls['workforce'].value;
        const spare_parts_cost = this.quotationForm.controls['spare_parts'].value;
        const body_work_painting_cost = this.quotationForm.controls['hyp'].value;
        const estimated_total = this.quotationForm.controls['total'].value;
        const trade_in_final = this.quotationForm.controls['take_value'].value;
        const final_offer = this.quotationForm.controls['final_offer'].value;
        const status = 'valuated';
        const comments = this.quotationForm.controls['comments'].value;

        const take_type = this.quotationForm.controls['direct_purchase'].value ? 'Compra directa' :
                          this.quotationForm.controls['take_into_account'].value ? 'Toma a cuenta' :
                          this.quotationForm.controls['direct_purchase_take_account'].value ? 'Compra directa/Toma a cuenta' : 'No hay tipo de toma';

        this._updateQuoteValuationService.updateQuoteValuation(valuation_uuid, seller_uuid, book_trade_in_offer, book_sale_price, intellimotors_trade_in_offer, intellimotors_sale_price, labor_cost, spare_parts_cost,
            body_work_painting_cost, estimated_total, trade_in_final, final_offer, status, comments, take_type)
                .subscribe({
                    next: () => {
                        this._router.navigateByUrl('/admin/valuator/appointment');
                        Swal.fire({
                            icon: 'success',
                            title: 'Alta registro',
                            text: 'Alta de registro exitoso',
                            showConfirmButton: true,
                            confirmButtonColor: '#EEB838',
                            timer: 3500
                        });
                    },
                    error: (error) => {
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
        
        // console.log(valuation_uuid, seller_uuid, book_trade_in_offer, book_sale_price, intellimotors_trade_in_offer, intellimotors_sale_price, labor_cost, spare_parts_cost,
        //             body_work_painting_cost, estimated_total, trade_in_final, final_offer, status, comments, take_type);
    }

}
