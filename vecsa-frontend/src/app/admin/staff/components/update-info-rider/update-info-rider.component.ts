import { Component, Inject } from '@angular/core';
import { FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { GralResponse } from '@interfaces/vehicle_data.interface';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';
import { MatChipListboxChange } from '@angular/material/chips';
import { DetailsReward, Quiz, QuizzesData } from '@interfaces/auth.interface';
import { AccountService } from 'src/app/auth/pages/account/services/account.service';
import { Router } from '@angular/router';
import { reload } from '@helpers/session.helper';

@Component({
    selector: 'app-update-info-rider',
    templateUrl: './update-info-rider.component.html',
    styleUrls: ['./update-info-rider.component.css'],
    standalone: false
})
export class UpdateInfoRiderComponent {
    
    public customer_reward_uuid: string = '';
    public customer_uuid: string = '';

    public size_invalid: boolean = false;
    public gender_invalid: boolean = false;
    

    public form !: FormGroup;
    public spinner: boolean = true;
    public size: string | null = null;
    public size_uuid: string | null = null;
    public clothes_gender: Quiz | null = null;
    public accesories: Quiz[] = []; 
    public tallas!: boolean;
    public quiz_active!: boolean;

    public  gender:  string | null = null;
    public  gender_uuid: string | null = null;

    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
        private _formBuilder: UntypedFormBuilder,
        private _bottomSheetRef: MatBottomSheetRef<any>,
        private _vehicleService: AdminService,
        private _accountService: AccountService,
        private _router: Router
    ){
        this.customer_reward_uuid = data.customer_reward_uuid;

        this.createForm();
        this.getRewardDetail(this.customer_reward_uuid);
    }

    private createForm() {
        this.form = this._formBuilder.group({
            vin:                  [''],
            model:                [''],
            year:                 [''],
            name:                 ['', [Validators.required]],
            last_name:            ['', [Validators.required]],
            phone_1:              [''],
            email_1:              [''],
            origin_agency:        [''],
            gender:               [''],
            size:                 [''],
            gender_uuid:          [''],
            size_uuid:            [''],
            customer_reward_uuid: [''],
        });
    }

    public close():void {
      this._bottomSheetRef.dismiss();
    }

    public getRewardDetail(customer_reward_uuid:string){

        this._vehicleService.rewardDetail(customer_reward_uuid)
        .subscribe({
            next: (response: DetailsReward) => {

                this.customer_uuid = response.data.customer.uuid;

                this.form.patchValue({
                    name: response.data.customer.name,
                    last_name: response.data.customer.last_name,
                    email_1: response.data.customer.email_1,
                    phone_1: response.data.customer.phone_1,
                    vin: response.data.vehicle.vin,
                    model: response.data.vehicle.model_name,
                    year: response.data.vehicle.year,
                    origin_agency: response.data.customer.origin_agency,
                    customer_reward_uuid: this.customer_reward_uuid
                });

                this.getCustomerQuizzes();
            },
            error: (error:any) => {
                // this._bottomSheetRef.dismiss(
                //     {reload: false}
                // );
                // reload(error, this._router);
                Swal.fire({
                    icon: 'error',
                    title: 'Oupps..',
                    text: 'Al parecer ocurrio un error' + error.error.message,
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838',
                    timer: 3500
                });
            }
        })
    }

    public async onSubmit(){
        try {

            this.form.patchValue({
                size: this.size,
                size_uuid: this.size_uuid,
            });

            this.form.patchValue({
                gender: this.gender,
                gender_uuid: this.gender_uuid,
            });
            this._vehicleService.updateCustomerReward(
                this.form.value,
            ).subscribe({
                next:( response : GralResponse) => {
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Rider actualizado exitosamente.',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    this._bottomSheetRef.dismiss({ reload: true });

                }, error: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al actualizar el Rider',
                        text: 'No se recibieron los datos correctos del servidor. Intenta más tarde.',
                    });
                }
            });
                        
        } catch(error:any){

            Swal.fire({
                icon: 'error',
                title: 'Lo sentimos, hubo un error',
                text: 'Hubo un problema al procesar la solicitud. Inténtalo más tarde.'+ error,
            });
            // this._bottomSheetRef.dismiss(
            //     {reload: false}
            // );
            // reload(error, this._router);
        }
    }

    public get nameInvalid() {
        return this.form.get('name')?.invalid && (this.form.get('name')?.dirty || this.form.get('name')?.touched);
    }

    public get lastnameInvalid() {
        return this.form.get('last_name')?.invalid && (this.form.get('last_name')?.dirty || this.form.get('last_name')?.touched);
    }

    public get phoneOneInvalid() {
        return this.form.get('phone_1')?.invalid && (this.form.get('phone_1')?.dirty || this.form.get('phone_1')?.touched);
    }

    public get emailOneInvalid() {
        return this.form.get('email_1')?.invalid && (this.form.get('email')?.dirty || this.form.get('email')?.touched);
    }

    public get modelInvalid() {
        return this.form.get('model')?.invalid && (this.form.get('model')?.dirty || this.form.get('model')?.touched);
    }

    public get yearInvalid() {
        return this.form.get('year')?.invalid && (this.form.get('year')?.dirty || this.form.get('year')?.touched);
    }

    public get agencyInvalid() {
        return this.form.get('origin_agency')?.invalid && (this.form.get('origin_agency')?.dirty || this.form.get('origin_agency')?.touched);
    }

    public get sizeInvalid() {
        return this.size_invalid;
    }

    public get genderInvalid() {
        return this.gender_invalid;
    }
    
    onChipGenderChange(event: MatChipListboxChange, quiz_uuid: string) {

        this.gender = event.value;
        this.gender_uuid = quiz_uuid;

        this.gender_invalid =  this.gender ? false: true;
    }

    onChipSelectionChange(event: MatChipListboxChange, quiz_uuid: string) {

        this.size = event.value;
        this.size_uuid = quiz_uuid;

        this.size_invalid =  this.size ? false: true;

    }

    public getCustomerQuizzes(){
        this.tallas = true;
        this.quiz_active = true;
        this._accountService.customerQuizzes(this.customer_uuid)
        .subscribe({
            next: ( quizzes: QuizzesData) => {
                this.clothes_gender = quizzes.data[0];

                this.gender = this.clothes_gender.selected_value;
                this.gender_uuid = this.clothes_gender.uuid;

                this.accesories = quizzes.data.filter(quiz => quiz.group_name === 'profile_affinities');

                this.size = this.accesories[0].selected_value;
                this.size_uuid = this.accesories[0].uuid;
                
                this.quiz_active = false;
                this.spinner = false;

            },
            error: (error:any) => {
                // reload(error, this._router);
                Swal.fire({
                    icon: 'error',
                    title: 'Oupps..',
                    text: 'Al parecer ocurrio un error' + error.error.message,
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838',
                    timer: 3500
            });
              }
        });
    }
}
