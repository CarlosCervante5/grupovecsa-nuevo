import { Component, Inject, OnDestroy } from '@angular/core';
import { AbstractControl, FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { DataDetailUser, Dealership, DealerShipResponse, DetailResponsive, roles, RolesResponse } from '@interfaces/admin.interfaces';
import { GralResponse } from '@interfaces/vehicle_data.interface';
import { AdminService } from '@services/admin.service';
import { roleAllowsMultipleDealerships } from 'src/app/admin/utils/user-dealership.util';
import { Subscription } from 'rxjs';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-update-user',
    templateUrl: './update-user.component.html',
    styleUrls: ['../user-form.styles.css'],
    standalone: false
})
export class UpdateUserComponent implements OnDestroy {
    public uuid_user!: string;
    public form!: FormGroup;
    public files: File[] = [];
    public users!: DataDetailUser;
    public spinner = true;
    public foto = 'assets/img/user.jpeg';
    public roles: roles[] = [];
    public dealership: Dealership[] = [];
    public multiDealership = false;
    private roleSub?: Subscription;

    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data: { uuid: string },
        private _formBuilder: UntypedFormBuilder,
        private _bottomSheetRef: MatBottomSheetRef<UpdateUserComponent>,
        private _adminservice: AdminService,
    ) {
        this.uuid_user = data.uuid;
        this.createForm();
        this.getUser();
        this.getDealership();
    }

    ngOnDestroy(): void {
        this.roleSub?.unsubscribe();
    }

    private createForm() {
        this.form = this._formBuilder.group({
            name:           ['', [Validators.pattern('[a-zA-ZÀ-ÿ ]+'), Validators.required]],
            last_name:      ['', [Validators.pattern('[a-zA-ZÀ-ÿ ]+'), Validators.required]],
            phone_1:        ['', [this.phoneValidator.bind(this), Validators.required]],
            phone_2:        ['', [this.phoneValidator.bind(this)]],
            gender:         ['', [Validators.required]],
            email:          ['', [Validators.required, Validators.pattern('[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$')]],
            dealership_ids: [[], [this.dealershipsRequired.bind(this)]],
            role_name:      ['', [Validators.required]],
            picture:        [''],
            password:       [''],
        });

        this.roleSub = this.form.get('role_name')!.valueChanges.subscribe((role) => {
            this.multiDealership = roleAllowsMultipleDealerships(role);
            if (!this.multiDealership) {
                const ids = this.form.get('dealership_ids')!.value as number[];
                if (ids.length > 1) {
                    this.form.patchValue({ dealership_ids: ids.slice(0, 1) });
                }
            }
            this.form.get('dealership_ids')!.updateValueAndValidity();
        });
    }

    private phoneValidator(control: AbstractControl) {
        const phone = control.value;
        if (!phone) {
            return null;
        }
        const phonePattern = /^[0-9]+$/;
        const valid = phonePattern.test(phone) && phone.length === 10;
        return valid ? null : { invalidPhone: true };
    }

    private dealershipsRequired(control: AbstractControl) {
        const role = (this.form?.get('role_name')?.value ?? '').toLowerCase();
        if (role === 'administrator' || role === 'developer') {
            return null;
        }
        const ids = control.value as number[];
        return ids?.length ? null : { required: true };
    }

    public onSingleDealershipChange(event: Event): void {
        const value = Number((event.target as HTMLSelectElement).value);
        this.form.patchValue({ dealership_ids: value ? [value] : [] });
    }

    public onMultiDealershipChange(event: Event): void {
        const select = event.target as HTMLSelectElement;
        const ids = Array.from(select.selectedOptions)
            .map((opt) => Number(opt.value))
            .filter((id) => !Number.isNaN(id) && id > 0);
        this.form.patchValue({ dealership_ids: ids });
    }

    public getUser() {
        this._adminservice.detailUser(this.uuid_user).subscribe({
            next: (response: DetailResponsive) => {
                this.spinner = false;
                this.users = response.data;
                this.multiDealership = roleAllowsMultipleDealerships(this.users.role);
                this.getRoles();
                const ids = this.users.dealership_ids ?? [];
                setTimeout(() => {
                    this.form.patchValue({
                        name: this.users.profile.name,
                        last_name: this.users.profile.last_name,
                        gender: this.users.profile.gender,
                        email: this.users.user.email,
                        phone_1: this.users.profile.phone_1,
                        phone_2: this.users.profile.phone_2,
                        role_name: this.users.role,
                        dealership_ids: ids,
                    });
                    this.foto = this.users.profile.picture ? this.users.profile.picture : 'assets/img/user.jpeg';
                }, 300);
            },
        });
    }

    public close(): void {
        this._bottomSheetRef.dismiss();
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
    get dealershipsInvalid() {
        return this.form.get('dealership_ids')!.invalid && (this.form.get('dealership_ids')!.dirty || this.form.get('dealership_ids')?.touched);
    }
    get role_nameInvalid() {
        return this.form.get('role_name')!.invalid && (this.form.get('role_name')!.dirty || this.form.get('role_name')?.touched);
    }

    private locationLabelFromIds(ids: number[]): string {
        return this.dealership
            .filter((d) => d.id != null && ids.includes(d.id))
            .map((d) => d.name)
            .join(', ');
    }

    public onSubmit(): void {
        const pics = this.files?.length ? this.files : [];
        const dealershipIds = (this.form.get('dealership_ids')!.value as number[]) ?? [];
        const location = this.locationLabelFromIds(dealershipIds);

        this._adminservice.updateUser(
            this.uuid_user,
            this.form.get('name')!.value,
            this.form.get('last_name')!.value,
            this.form.get('phone_1')!.value,
            this.form.get('phone_2')!.value,
            this.form.get('gender')!.value,
            this.form.get('email')!.value,
            location,
            this.form.get('role_name')!.value,
            pics,
            this.form.get('password')!.value,
            dealershipIds,
        ).subscribe({
            next: (response: GralResponse) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario actualizado',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000,
                });
                this._bottomSheetRef.dismiss({ reload: true });
            },
            error: (error) => {
                const validation = error?.error?.errors;
                const detail = validation
                    ? Object.values(validation).flat().join('\n')
                    : (error?.error?.message || 'No se pudo actualizar el usuario.');
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar',
                    text: detail,
                });
            },
        });
    }

    public getRoles() {
        this._adminservice.getRoles().subscribe({
            next: (response: RolesResponse[]) => {
                this.roles = response.map((rol) => ({
                    id: rol.id,
                    name: rol.name,
                }));
            },
        });
    }

    public getDealership() {
        this._adminservice.getDealerships().subscribe({
            next: (response: DealerShipResponse) => {
                this.dealership = response.data ?? [];
            },
        });
    }
}
