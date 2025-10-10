import { Component, Inject } from '@angular/core';
import { FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';


import Swal from 'sweetalert2';
import { Router } from '@angular/router';
import {reload} from '@helpers/session.helper';
import { RewardsService } from '@services/rewards.service';
import { PointsResponse, RedeemPointsResponse } from '@interfaces/rewards.interface';
import { Reward } from '@interfaces/rewards.interface';

@Component({
    selector: 'app-redeem-coupons',
    templateUrl: './redeem-coupons.component.html',
    styleUrls: ['./redeem-coupons.component.css'],
    standalone: false
})

export class RedeemCouponsComponent{

    public spinner = false;
    public form!: FormGroup;
    public button: boolean = false;

    public rewards:Reward[] = [];
    public customer_uuid !: string;
    public points: number = 0;

    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
        private _bottomSheetRef: MatBottomSheetRef<any>,
        private _formBuilder: UntypedFormBuilder,
        private _router: Router,
        private _rewards: RewardsService

    ){
        this.customer_uuid = data.customer_uuid;
        this.getPoints();
    }

    public close():void {
        this._bottomSheetRef.dismiss();
    }


    cubetime () : void {
        setTimeout(() => {
            this.spinner = false;
        }, 500);
    }


    get pointsInvalid(): boolean {
        return this.points <= 0;
    }
    

    public onSubmit() {

        try {

            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
            });

            this._rewards.redeemPoints( this.customer_uuid )
            .subscribe({
                next: ( response: RedeemPointsResponse ) => {
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Puntos canjeados exitosamente.',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    this._bottomSheetRef.dismiss(
                        {reload: true}
                    );
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

    private async getPoints() {

        this._rewards.customerPoints( this.customer_uuid )
            .subscribe({
            next: ( pointsResponse: PointsResponse) => {
                this.points = pointsResponse.data.total_earned_points;
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