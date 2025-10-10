    import { Component } from '@angular/core';
    import { AbstractControl, FormControl, FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MatAutocompleteSelectedEvent } from '@angular/material/autocomplete';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { DealerShipResponse, roles, RolesResponse , Dealership} from '@interfaces/admin.interfaces';
import { GralResponse } from '@interfaces/vehicle_data.interface';
import { AdminService } from '@services/admin.service';
import { Observable, of} from 'rxjs';
import { map,startWith } from 'rxjs/operators';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-add-user',
    templateUrl: './add-user.component.html',
    styleUrls: ['./add-user.component.css'],
    standalone: false
})
export class AddUserComponent {

    public form !: FormGroup;
    public spinner = false;
    public files!: File [];
    //variables para el select de roles
    public roles!: roles[];
    public rolesControl = new FormControl();
    public filteredRoles: Observable<roles[]> = of([]);
    public rol!: string;

    //variables para el select de locations
    public dealership!: Dealership[];
    public locationControl = new FormControl();
    public filteredLocation: Observable<Dealership[]> = of([]);


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

    public onSubmit(){
        this._adminservice.addUser(this.form.get('name')!.value, this.form.get('last_name')!.value, this.form.get('phone_1')!.value, this.form.get('phone_2')!.value,
        this.form.get('gender')!.value, this.form.get('email')!.value, this.form.get('location')!.value, this.form.get('role_name')!.value, this.files,
        this.form.get('password')!.value,)
        .subscribe({
            next: (response : GralResponse) =>{
                Swal.fire({                    
                    icon: 'success',
                    title: 'Usuario actualizado con exito',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                    });
                    this._bottomSheetRef.dismiss(
                    {reload: true}
                    );
            },
            error:(error)=>{
                console.log(error);
            }
        })
    }

    assignImagePromo( event: Event){
        const element = event.currentTarget as HTMLInputElement;
        let fileList: FileList | null = element.files;
        if (fileList) {
            //this.files = fileList[0];
            this.files = Array.from(fileList);
        }
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
                this.filters();
            }
        })
    }

    public getDealership(){
        this._adminservice.getDealerships()
        .subscribe({
            next: (response : DealerShipResponse) =>{
                this.dealership = response.data;
                this.filters();
            }
        })
    }

    private filters(): void {
        this.filteredRoles = this.rolesControl.valueChanges.pipe(
            startWith(''),
            map(value => this._filter(value, this.roles)),
          );
        this.filteredLocation = this.locationControl.valueChanges.pipe(
        startWith(''),
        map(value => this._filter(value, this.dealership)),
        );
    }

    private _filter<T extends { name: string }>(value: string, options: T[]): T[] {
        const filterValue = value.toLowerCase();
        return options.filter(option => option.name.toLowerCase().includes(filterValue));
    }

    onRolesSelected(event: MatAutocompleteSelectedEvent): void{
        const selectedRole = event.option.value;
        this.form.patchValue({ role_name: selectedRole });
    }

    onLocationSelected(event: MatAutocompleteSelectedEvent): void{
        const selectedLocation = event.option.value;
        this.form.patchValue({ location: selectedLocation });
    }
}
