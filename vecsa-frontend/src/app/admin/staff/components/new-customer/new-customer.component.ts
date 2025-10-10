import { Component } from '@angular/core';
import { FormControl, FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { MatChipListboxChange } from '@angular/material/chips';
import { Reward } from '@interfaces/admin.interfaces';
import { RegisterResponse, Data, QuizzesData, Quiz } from '@interfaces/auth.interface';
import { RewardResponse } from '@interfaces/rewards.interface';
import { GralResponse } from '@interfaces/vehicle_data.interface';
import { AdminService } from '@services/admin.service';
import { firstValueFrom, Observable, of } from 'rxjs';
import { AccountService } from 'src/app/auth/pages/account/services/account.service';
import Swal from 'sweetalert2';
import { Router } from '@angular/router';
import {reload} from '@helpers/session.helper';

@Component({
    selector: 'app-new-customer',
    templateUrl: './new-customer.component.html',
    styleUrls: ['./new-customer.component.css'],
    standalone: false
})

export class NewCustomerComponent{

    public spinner = true;
    public form!: FormGroup;
    public respRider!: Data;
    public button: boolean = false;
    rewardControl = new FormControl();
    public rewards:Reward[] = [];
    public reward_uuid!: string;
    public user_uuid !: string;
    public customer_uuid !: string;

    public tallas = false;
    public quiz_active = true;
    public affinities_active = true;
    public clothes_gender: Quiz | null = null;
    public gender: string | null = null;
    public gender_uuid: string | null = null;
    public statusGender: boolean = false;
    public accesories: Quiz[] = [];
    public brand_quiz: Quiz | null = null;
    public status: boolean = false; 

    public size: string | null = null;
    public size_uuid: string | null = null;
    public statusSize: boolean = false;


    filteredRewards: Observable<Reward[]> = of([]);

    constructor(
        private _bottomSheetRef: MatBottomSheetRef<any>,
        private _formBuilder: UntypedFormBuilder,
        private _adminservice: AdminService,
        private _accountService: AccountService,
        private _router: Router

    ){
        this.createForm();
        this.getCustomerQuizzes();
    }

    private createForm() {
        this.form = this._formBuilder.group({
            name: ['', [Validators.required, Validators.pattern("[a-zA-ZÀ-ÿ ]+")]],
            last_name: ['', [Validators.required, Validators.pattern("[a-zA-ZÀ-ÿ ]+")]],
            phone_1: ['', [Validators.required]],
            gender: ['',],
            email: ['', [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],
            model: ['', [Validators.required]],
            year: ['', [Validators.required]],
            origin_agency: ['', [Validators.required]]
        });
    }

    public getCustomerQuizzes(){

        this.tallas = true;

        this.quiz_active = true;

        this.affinities_active = true;

        this._accountService.quizzesProfile()
        .subscribe({
            
            next: ( quizzes: QuizzesData) => {

                this.clothes_gender = quizzes.data[0];

                this.gender =  (quizzes.data[0].selected_value == "undefined")? 'null':  quizzes.data[0].selected_value;

                this.accesories = quizzes.data.filter(quiz => quiz.group_name === 'profile_affinities');

                this.quiz_active = false;

                this.affinities_active = false;

                this.spinner = false;
            },
            error: (error) => {
                this._bottomSheetRef.dismiss(
                    {reload: false}
                );
                reload(error, this._router);
            }
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


    public async onSubmit() {
        try {

            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
            });

            this.form.patchValue({
                origin_agency: 'vecsa_hidalgo',
            });

            const reward = await this.getRewardByName('oktoberfest 2024');

            const response = await this.createRider();

            if (response && response.data && response.data.profile && response.data.profile.uuid && reward && reward.data) {

                this.user_uuid = response.data.user.uuid;
                this.customer_uuid = response.data.profile.uuid;
                this.reward_uuid = reward.data.uuid;

                await this.assignVehicleToRider();


                if(this.gender_uuid != null && this.gender != null)
                    await this.attachQuiz(this.customer_uuid , this.gender_uuid, this.gender);

                if(this.size_uuid != null && this.size != null)
                    await this.attachQuiz(this.customer_uuid , this.size_uuid, this.size);

                Swal.fire({
                    icon: 'success',
                    title: 'Rider creado exitosamente.',
                    showConfirmButton: false,
                    timer: 2000
                });
                
                Swal.fire({
                    icon: 'success',
                    title: 'Rider creado exitosamente.',
                    showConfirmButton: false,
                    timer: 2000
                });

                this._bottomSheetRef.dismiss({ reload: true });

            } else {

                Swal.fire({
                    icon: 'error',
                    title: 'Error al crear el Rider',
                    text: 'No se recibieron los datos correctos del servidor. Intenta más tarde.',
                });

            }
        } catch (error: any) {

            // Swal.fire({
            //     icon: 'error',
            //     title: 'Lo sentimos, hubo un error',
            //     text: 'Hubo un problema al procesar la solicitud. Inténtalo más tarde.',
            // });
            
            reload(error, this._router);
        }
    }

    private async getRewardByName(name: string): Promise<RewardResponse | null> {
        try {
            return await firstValueFrom(this._adminservice.getRewardByName(
                name
            ));

        } catch (error: any) {

            reload(error, this._router);
            throw new Error('Error en la creación del Rider.');
        }
    }

    private async createRider(): Promise<RegisterResponse | null> {
        try {
            return await firstValueFrom(this._adminservice.setRiders(
                this.form.value
            ));
        } catch (error: any) {
            console.error('Error al crear el Rider:', error);
            reload(error, this._router);
            throw new Error('Error en la creación del Rider.');
        }
    }

    private async assignVehicleToRider(): Promise<void> {
        try {
            await firstValueFrom(
                this._adminservice.setVehicleRiderRegister(this.customer_uuid, this.form.get('year')?.value, this.form.get('model')?.value, this.reward_uuid)
            );
        } catch (error: any) {
            console.error('Error al asignar el vehículo:', error);
            reload(error, this._router);
            throw new Error('Error al asignar el vehículo al Rider.');
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
        return this.form.get('email')?.invalid && (this.form.get('email')?.dirty || this.form.get('email')?.touched);
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

    onChipGenderChange(event: MatChipListboxChange, quiz_uuid: string) {

        this.gender = event.value;
        this.gender_uuid = quiz_uuid;

        this.statusGender = (this.gender != null && this.gender != undefined ) ? true : false;
        
        this.status = (this.statusSize && this.statusGender) ? true : false

    }

    onChipSelectionChange(event: MatChipListboxChange, quiz_uuid: string) {

        this.size = event.value;
        this.size_uuid = quiz_uuid;

        this.statusSize = (this.size != null && this.size != undefined ) ? true : false;

        this.status = (this.statusSize && this.statusGender) ? true : false

    }

    public async attachQuiz (customer_uuid: string, quiz_uuid:string, selected_value: string): Promise<GralResponse | void>{
        try {
            return await firstValueFrom(
                this._accountService.attatchQuiz(customer_uuid,quiz_uuid, selected_value)
            );
        } catch (error: any) {
            console.error('Error al adjuntar respuesta de afinidades:', error);
            reload(error, this._router);
            throw new Error('Error al adjuntar respuesta de afinidades.');
        }
    }
}