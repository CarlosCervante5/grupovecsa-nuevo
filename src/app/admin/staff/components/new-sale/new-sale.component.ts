import { Component, Inject } from '@angular/core';
import { FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';


import Swal from 'sweetalert2';
import { Router } from '@angular/router';
import {reload} from '@helpers/session.helper';
import { RewardsService } from '@services/rewards.service';
import { RewardsResponse } from '@interfaces/rewards.interface';
import { Reward } from '@interfaces/rewards.interface';
import { GralResponse } from '@interfaces/vehicle_data.interface';

@Component({
    selector: 'app-new-sale',
    templateUrl: './new-sale.component.html',
    styleUrls: ['./new-sale.component.css'],
    standalone: false
})

export class NewSaleComponent{

    public spinner = false;
    public form!: FormGroup;
    public button: boolean = false;

    public rewards:Reward[] = [];
    public customer_uuid !: string;

    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
        private _bottomSheetRef: MatBottomSheetRef<any>,
        private _formBuilder: UntypedFormBuilder,
        private _router: Router,
        private _rewards: RewardsService

    ){
        this.customer_uuid = data.customer_uuid;

        this.getRewards();

        this.createForm();
    }

    private createForm() {
        this.form = this._formBuilder.group({
            quantity: ['', [Validators.required]],
            origin: ['', [Validators.required]],
            sale_id: ['', [Validators.required]],
            reward_uuid: ['', [Validators.required]],
            customer_uuid: ['', [Validators.required]]
        });

        this.form.patchValue({
            customer_uuid : this.customer_uuid
        });

    }


    public close():void {
        this._bottomSheetRef.dismiss();
    }


    cubetime () : void {
        setTimeout(() => {
            this.spinner = false;
        }, 500);
    }


    public onSubmit() {
        
        try {

            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
            });

            this._rewards.updatePoints( this.form.value )
            .subscribe({
                next: ( response: GralResponse ) => {
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Recompenza actualizada exitosamente.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                },
                error(error: any){
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Lo sentimos, hubo un error',
                        text: 'Hubo un problema al procesar la solicitud. Inténtalo más tarde.' + error,
                    });
                }
            });

            this._bottomSheetRef.dismiss(
                {reload: true}
            );

        } catch (error: any) {

            Swal.fire({
                icon: 'error',
                title: 'Lo sentimos, hubo un error',
                text: 'Hubo un problema al procesar la solicitud. Inténtalo más tarde.',
            });
            
            reload(error, this._router);
        }
    }

    private async getRewards() {

        this._rewards.getRewardsByCategory('sale')
            .subscribe({
            next: ( rewardsResponse: RewardsResponse) => {
                this.rewards = rewardsResponse.data;
            }
        });
    }

    public get quantityInvalid() {
        return this.form.get('quantity')?.invalid && (this.form.get('quantity')?.dirty || this.form.get('quantity')?.touched);
    }

    public get originInvalid() {
        return this.form.get('origin')?.invalid && (this.form.get('origin')?.dirty || this.form.get('origin')?.touched);
    }

    public get idInvalid() {
        return this.form.get('sale_id')?.invalid && (this.form.get('sale_id')?.dirty || this.form.get('sale_id')?.touched);
    }

    public get rewardInvalid() {
        return this.form.get('reward_uuid')?.invalid && (this.form.get('reward_uuid')?.dirty || this.form.get('reward_uuid')?.touched);
    }

}