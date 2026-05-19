import { Component } from '@angular/core';
import { AbstractControl, FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { DealerShipResponse, roles, RolesResponse, Dealership } from '@interfaces/admin.interfaces';
import { GralResponse } from '@interfaces/vehicle_data.interface';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-add-user',
    templateUrl: './add-user.component.html',
    styleUrls: ['../user-form.styles.css'],
    standalone: false
})
export class AddUserComponent {

    public form!: FormGroup;
    public spinner = false;
    public files: File[] = [];
    /** Vista previa; placeholder hasta elegir archivo */
    public foto = 'assets/img/user.jpeg';
    public roles: roles[] = [];
    public dealership: Dealership[] = [];


    constructor(
        private _formBuilder: UntypedFormBuilder,
        private _bottomSheetRef: MatBottomSheetRef<any>,
        private _adminservice : AdminService,
    ){
        this.createForm();
        this.getRoles();
        this.getDealership();
    }

    private createForm() {
        this.form = this._formBuilder.group({
            name:           ['', [Validators.required, Validators.pattern("[a-zA-ZÀ-ÿ ]+")]],
            last_name:      ['', [Validators.required, Validators.pattern("[a-zA-ZÀ-ÿ ]+")]],
            phone_1:        ['', [this.phoneValidator.bind(this), Validators.required]],
            phone_2:        ['', [this.phoneValidator.bind(this)]],
            gender:         ['', [Validators.required]],
            email:          ['', [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],
            location:       ['', [Validators.required]],
            role_name:      ['', [Validators.required]],
            picture:        [''],
            password:       ['', [Validators.required]],
        });
    }

    private phoneValidator(control: AbstractControl) {
        const phone = control.value;

        if (!phone) {
            return null; // If the field is empty, it's valid
        }

        // If a phone number is provided, validate the format
        const phonePattern = /^[0-9]+$/;
        const valid = phonePattern.test(phone) && phone.length === 10;

        if (!valid) {
        return { invalidPhone: true };
        }

        return null; // Valid phone number
    }

    public close():void {
        this._bottomSheetRef.dismiss();
    }

    get nameInvalid() {
        return this.form.get('name')!.invalid && (this.form.get('name')!.dirty || this.form.get('name')?.touched);
    }
    get last_nameInvalid() {
        return this.form.get('last_name')!.invalid && (this.form.get('last_name')!.dirty || this.form.get('last_name')?.touched);
    }
    public get phoneOneInvalid() {
        return this.form.get('phone_1')?.invalid && (this.form.get('phone_1')?.dirty || this.form.get('phone_1')?.touched);
    }
    public get phoneTwoInvalid() {
        return this.form.get('phone_2')?.invalid && (this.form.get('phone_2')?.dirty || this.form.get('phone_2')?.touched);
    }
    get genderInvalid() {
        return this.form.get('gender')!.invalid && (this.form.get('gender')!.dirty || this.form.get('gender')?.touched);
    }
    get emailInvalid() {
        return this.form.get('email')!.invalid && (this.form.get('email')!.dirty || this.form.get('email')?.touched);
    }
    get locationInvalid() {
        return this.form.get('location')!.invalid && (this.form.get('location')!.dirty || this.form.get('location')?.touched);
    }
    get role_nameInvalid() {
        return this.form.get('role_name')!.invalid && (this.form.get('role_name')!.dirty || this.form.get('role_name')?.touched);
    }
    get passwordInvalid() {
        return this.form.get('password')!.invalid && (this.form.get('password')!.dirty || this.form.get('password')?.touched);
    }

    public onSubmit(): void {
        const pics = this.files?.length ? this.files : [];
        this._adminservice.addUser(
            this.form.get('name')!.value,
            this.form.get('last_name')!.value,
            this.form.get('phone_1')!.value,
            this.form.get('phone_2')!.value,
            this.form.get('gender')!.value,
            this.form.get('email')!.value,
            this.form.get('location')!.value,
            this.form.get('role_name')!.value,
            pics,
            this.form.get('password')!.value,
        ).subscribe({
            next: (response: GralResponse) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario creado',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000,
                });
                this._bottomSheetRef.dismiss({ reload: true });
            },
            error: (error) => {
                console.log(error);
            },
        });
    }

    assignImagePromo(event: Event): void {
        const element = event.currentTarget as HTMLInputElement;
        const fileList = element.files;
        if (!fileList?.length) {
            return;
        }
        this.files = Array.from(fileList);
        const file = fileList[0];
        const reader = new FileReader();
        reader.onload = () => {
            this.foto = reader.result as string;
        };
        reader.readAsDataURL(file);
    }


    public getRoles(){
        this._adminservice.getRoles()
        .subscribe({
            next: (response: RolesResponse[]) =>{
                const datosR = response.map((rol) => ({
                    'id':       rol.id,
                    'name':     rol.name
                }));
                this.roles = datosR;
            }
        })
    }

    public getDealership(){
        this._adminservice.getDealerships()
        .subscribe({
            next: (response : DealerShipResponse) =>{
                this.dealership = response.data;
            }
        })
    }

}
