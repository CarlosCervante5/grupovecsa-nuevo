import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';

import { ReceptionFormService } from '@services/forms/receptionForm.service';

import { ReceptionForm } from '@interfaces/dashboard.interface';

import Swal from 'sweetalert2';
import { VehicleService } from '@services/vehicle.service';
import {Brand, BrandsResponse, Model, Version, ModelsResponse} from '@interfaces/vehicle_data.interface';

@Component({
    selector: 'app-reception-form',
    templateUrl: './reception-form.component.html',
    styleUrls: ['./reception-form.component.css'],
    standalone: false
})
export class ReceptionFormComponent implements OnInit {

    // References Form
    public form!: UntypedFormGroup;

    // References of Help
    public minDate: Date = new Date();
    public maxDate: Date = new Date(new Date().setDate( new Date().getDate() + 365));
    public years: number[] = [];
    public process: boolean = false;  
    public spinner: boolean = false;  
    public hostes: string = '';
    public today!: Date;
    public currentTime: string = '';

    /**
     * Filter Days
     */
    public myFilter = (d: Date | null ): boolean => {
        const day = (d || new Date()).getDay();
        return day !== -1 && day !== 7;
    };

    public brands:Brand[] = [];
    public models:Model[] = [];
    public secondModels: Model[] = [];
    public modelValuation: Model[] = [];
    public versions:Version[] = [];

    constructor(
        private _formbuilder: UntypedFormBuilder,
        private _receptionFormService: ReceptionFormService,
        private _vehicleService:VehicleService,
    ){
        // Form initialization
        this.createFormInit();
        this.process = true;

        const profile = JSON.parse(localStorage.getItem('profile')!);
        this.hostes = profile.name +' '+profile.last_name;
    }

    ngOnInit(): void { 
        this.getBrands();

        let year = new Date().getFullYear();
        for (let i = year+1; i > year-9; i--) {      /**Antes era let i = year+1 */
            this.years.push(i);      
        }

        this.today = new Date();
        const hours = this.today.getHours().toString().padStart(2, '0');
        const minutes = this.today.getMinutes().toString().padStart(2, '0');
        this.currentTime = `${hours}:${minutes}`;
    }

    /**
     * Form Initialization
     */
    private createFormInit(){
        this.form = this._formbuilder.group({
            date: [''], /**, Validators.required */
            hour: [''],
            salesAdvisor: [''],
            brand: [''],
            model: [''],
            departureTime: [''],
            visitType: [''],
            visitFirsTime: [''],
            department: [''],
            otherDepartment: [''],
            idCRM: [''],
            honorific: [''],
            contactSalesplace: [''],
            saleType: [''],
            howFindOut: [''],
            contactSub: [''],
            clientName: [''],
            clientPhone: ['', [ Validators.pattern("[0-9]{10}"), Validators.minLength(10), Validators.maxLength(10)]],
            clientAge: [''],
            preferredMedium: [''],
            clientEmail: ['', [ Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],
            year: [(new Date().getFullYear() + 1).toString()],
            version: [''],
            versionSecondOption: [''],
            color: [''],
            colorSecondOption: [''],
            accessories: [''],
            brandSecondOption: [''],
            modelSecondOption: [''],
            testDrive: [''],
            receivedQuote: [''],
            FAndI: [''],
            leaveCarOnAccount: [''],
            customersCurrentCar: [''],
            wasClientProfile: [''],
            wasApplicationTaken: [''],
            commentaryFAndI: [''],
            brandValuation: [''],
            modelValuation: [''],
            yearValuation: [new Date().getFullYear().toString()],
            mileage: ['0'],
            dateValuator: [''],
            hourValuation: [''],
            subsidiaryValuation: [''],
            hostes: [''],
            financingType: [''],
            initialInvestment: [''],
            monthlyPayment: [''],
            termHowManyMonths: ['', [ Validators.minLength(1), Validators.maxLength(2)]], /** Validators.pattern("[0-9]{1}"), */
            commentaryFinancing: [''],
            segment: [''],
        });
    }

    // Vehicle Getters
    get brandInvalid() {
        return this.form.get('brand')!.invalid && (this.form.get('brand')!.dirty || this.form.get('brand')!.touched);
    }

    get modelInvalid() {
        return this.form.get('model')!.invalid && (this.form.get('model')!.dirty || this.form.get('model')!.touched);
    }
    
    get otherDepartmentInvalid() {
        return this.form.get('otherDepartment')!.invalid && (this.form.get('otherDepartment')!.dirty || this.form.get('otherDepartment')!.touched);
    }
    
    get howFindOutInvalid() {
        return this.form.get('howFindOut')!.invalid && (this.form.get('howFindOut')!.dirty || this.form.get('howFindOut')!.touched);
    }
    
    get clientNameInvalid() {
        return this.form.get('clientName')!.invalid && (this.form.get('clientName')!.dirty || this.form.get('clientName')!.touched);
    }
    
    get clientPhoneInvalid() {
        return this.form.get('clientPhone')!.invalid && (this.form.get('clientPhone')!.dirty || this.form.get('clientPhone')!.touched);
    }
    
    get clientEmailInvalid() {
        return this.form.get('clientEmail')!.invalid && (this.form.get('clientEmail')!.dirty || this.form.get('clientEmail')!.touched);
    }
    
    get yearInvalid() {
        return this.form.get('year')!.invalid && (this.form.get('year')!.dirty || this.form.get('year')!.touched);
    }
    
    get clientAgeInvalid() {
        return this.form.get('clientAge')!.invalid && (this.form.get('clientAge')!.dirty || this.form.get('clientAge')!.touched);
    }
    
    get preferredMediumInvalid() {
        return this.form.get('preferredMedium')!.invalid && (this.form.get('preferredMedium')!.dirty || this.form.get('preferredMedium')!.touched);
    }
    
    get versionInvalid() {
        return this.form.get('version')!.invalid && (this.form.get('version')!.dirty || this.form.get('version')!.touched);
    }
    
    get colorInvalid() {
        return this.form.get('color')!.invalid && (this.form.get('color')!.dirty || this.form.get('color')!.touched);
    }
    
    get accessoriesInvalid() {
        return this.form.get('accessories')!.invalid && (this.form.get('accessories')!.dirty || this.form.get('accessories')!.touched);
    }
    
    get testDriveInvalid() {
        return this.form.get('testDrive')!.invalid && (this.form.get('testDrive')!.dirty || this.form.get('testDrive')!.touched);
    }
    
    get receivedQuoteInvalid() {
        return this.form.get('receivedQuote')!.invalid && (this.form.get('receivedQuote')!.dirty || this.form.get('receivedQuote')!.touched);
    }
    
    get FAndiInvalid() {
        return this.form.get('FAndI')!.invalid && (this.form.get('FAndI')!.dirty || this.form.get('FAndI')!.touched);
    }
    
    get leaveCarOnAccountInvalid() {
        return this.form.get('leaveCarOnAccount')!.invalid && (this.form.get('leaveCarOnAccount')!.dirty || this.form.get('leaveCarOnAccount')!.touched);
    }
    
    get wasClientProfileInvalid() {
        return this.form.get('wasClientProfile')!.invalid && (this.form.get('wasClientProfile')!.dirty || this.form.get('wasClientProfile')!.touched);
    }
    
    get wasApplicationTakenInvalid() {
        return this.form.get('wasApplicationTaken')!.invalid && (this.form.get('wasApplicationTaken')!.dirty || this.form.get('wasApplicationTaken')!.touched);
    }
    
    get financingTypeInvalid() {
        return this.form.get('financingType')!.invalid && (this.form.get('financingType')!.dirty || this.form.get('financingType')!.touched);
    }

    get initialInvestmentInvalid() {
        return this.form.get('initialInvestment')!.invalid && (this.form.get('initialInvestment')!.dirty || this.form.get('initialInvestment')!.touched);
    }

    get monthlyPaymentInvalid() {
        return this.form.get('monthlyPayment')!.invalid && (this.form.get('monthlyPayment')!.dirty || this.form.get('monthlyPayment')!.touched);
    }

    get termHowManyMonthsInvalid() {
        return this.form.get('termHowManyMonths')!.invalid && (this.form.get('termHowManyMonths')!.dirty || this.form.get('termHowManyMonths')!.touched);
    }

    get idCRMInvalid() {
        return this.form.get('idCRM')!.invalid && (this.form.get('idCRM')!.dirty || this.form.get('idCRM')!.touched);
    }

    public changeDepartment(){
        this.form.patchValue({ 'otherDepartment': ''});
        (this.form.get('department')?.value === 'nuevos') ? this.form.patchValue({ 'otherDepartment': 'N/A'}) : '';
    }

    public changeVisitType(){
        this.form.patchValue({ 'howFindOut': ''});
        (this.form.get('visitType')?.value === 'freshUp') ? this.form.patchValue({'howFindOunt': 'N/A'}) : '';
    }
    
    public changeCarAccount(){
        this.form.patchValue({ 'customersCurrentCar': ''});
        (this.form.get('leaveCarOnAccount')?.value === 'si') ? this.form.patchValue({'customersCurrentCar': ''}) : '';
    }

    public maxLengthCheck(object: any) {
        if (object.value.length > object.maxLength) {
          object.value = object.value.slice(0, object.maxLength)
        }
    }

    public maxLengthAge(object: any) {
        if (object.value.length > object.maxLength) {
          object.value = object.value.slice(0, object.maxLength)
        }
    }

    public onSubmit(){
        this.spinner = true;

        this.form.patchValue({
            hostes: this.hostes,
        });

        this._receptionFormService.receptionForm(this.form.value)
        .subscribe({
            next: (receptionForm: ReceptionForm) => {
                    
                Swal.fire({
                    icon: 'success',
                    title: 'Formulario enviado correctamente',
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838',
                    timer: 3500
                });
                    
                setTimeout(() => {
                    window.location.reload();
                }, 2000);

                this.spinner = false;
                
            },
            error:() => {

                Swal.fire({
                    icon: 'error',
                    title: 'Ooopppps!',
                    text: `Algo fue mal, por favor intenta de nuevo.`,
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838',
                    timer: 3500
                });

                this.spinner = false;
            }
        });
        
    }

    public onChangeSalesAdvisor(){
        this._receptionFormService.sendMailFandI()
        .subscribe({
            next: (resp) => {
                this.alert(resp.message);
            } 
        });
    }

    public getBrands(): void {
        this._vehicleService.getBrands()
        .subscribe({
            next: (brandsResponse: BrandsResponse) => {
                this.brands = brandsResponse.data.vehicle_brands;
            }
        });
    }

    public getModelsByBrand(brand: string): void {
        this._vehicleService.getModelsByBrand(brand)
        .subscribe({
            next: (modelsResponse: ModelsResponse) => {
                this.models = modelsResponse.data.line_models;
            },
            error: (error) => {}
        });
    }

    public getSecondModels(brand: string): void {
        this._vehicleService.getModelsByBrand(brand)
        .subscribe({
            next: (modelsResponse: ModelsResponse) => {
                this.secondModels = modelsResponse.data.line_models;
            },
            error: (error) => {}
        });
    }
    
    private alert(msg: string){
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer),
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'success',
            title: msg
        });
    }

}

