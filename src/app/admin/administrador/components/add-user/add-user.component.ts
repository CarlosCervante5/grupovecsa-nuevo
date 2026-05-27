import { Component, OnDestroy } from '@angular/core';
import { AbstractControl, FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { DealerShipResponse, roles, RolesResponse, Dealership } from '@interfaces/admin.interfaces';
import { GralResponse } from '@interfaces/vehicle_data.interface';
import { AdminService } from '@services/admin.service';
import { roleAllowsMultipleDealerships } from 'src/app/admin/utils/user-dealership.util';
import { Subscription } from 'rxjs';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-add-user',
    templateUrl: './add-user.component.html',
    styleUrls: ['../user-form.styles.css'],
    standalone: false
})
export class AddUserComponent implements OnDestroy {

    public form!: FormGroup;
    public spinner = false;
    public files: File[] = [];
    public foto = 'assets/img/user.jpeg';
    public roles: roles[] = [];
    public dealership: Dealership[] = [];
    public multiDealership = false;
    private roleSub?: Subscription;

    constructor(
        private _formBuilder: UntypedFormBuilder,
        private _bottomSheetRef: MatBottomSheetRef<any>,
        private _adminservice: AdminService,
    ) {
        this.createForm();
        this.getRoles();
        this.getDealership();
    }

    ngOnDestroy(): void {
        this.roleSub?.unsubscribe();
    }

    private createForm() {
        this.form = this._formBuilder.group({
            name:           ['', [Validators.required, Validators.pattern('[a-zA-ZÀ-ÿ ]+')]],
            last_name:      ['', [Validators.required, Validators.pattern('[a-zA-ZÀ-ÿ ]+')]],
            phone_1:        ['', [this.phoneValidator.bind(this), Validators.required]],
            phone_2:        ['', [this.phoneValidator.bind(this)]],
            gender:         ['', [Validators.required]],
            email:          ['', [Validators.required, Validators.pattern('[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,4}$')]],
            dealership_ids: [[], [this.dealershipsRequired.bind(this)]],
            role_name:      ['', [Validators.required]],
            picture:        [''],
            password:       ['', [Validators.required]],
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

    public close(): void {
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
    get dealershipsInvalid() {
        return this.form.get('dealership_ids')!.invalid && (this.form.get('dealership_ids')!.dirty || this.form.get('dealership_ids')?.touched);
    }
    get role_nameInvalid() {
        return this.form.get('role_name')!.invalid && (this.form.get('role_name')!.dirty || this.form.get('role_name')?.touched);
    }
    get passwordInvalid() {
        return this.form.get('password')!.invalid && (this.form.get('password')!.dirty || this.form.get('password')?.touched);
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

        this._adminservice.addUser(
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
                    title: 'Usuario creado',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000,
                });
                this._bottomSheetRef.dismiss({ reload: true });
            },
            error: (error) => {
                const detail = this.formatValidationErrors(error?.error?.errors)
                    || error?.error?.message
                    || 'No se pudo crear el usuario.';
                Swal.fire({ icon: 'error', title: 'Error', text: detail });
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

    private formatValidationErrors(errors: unknown): string {
        if (!errors || typeof errors !== 'object') {
            return '';
        }
        const messages: string[] = [];
        for (const value of Object.values(errors as Record<string, unknown>)) {
            if (Array.isArray(value)) {
                value.forEach((msg) => messages.push(String(msg)));
            } else if (value != null) {
                messages.push(String(value));
            }
        }
        return messages.join('\n');
    }
}
