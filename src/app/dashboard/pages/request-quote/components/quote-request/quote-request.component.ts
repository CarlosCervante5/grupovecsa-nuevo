// import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { RequestqService } from '../services/requestq.service';
//import { Sheetquote } from '../interfaces/requestq.interface';
import { Sheetquote } from '@interfaces/dashboard.interface';

import Swal from 'sweetalert2';

@Component({
    selector: 'app-quote-request',
    // standalone: true,
    // imports: [
    //     CommonModule,
    // ],
    templateUrl: './quote-request.component.html',
    styleUrls: ['./quote-request.component.css'],
    standalone: false
})
export class QuoteRequestComponent implements OnInit {

    public spinner: boolean = false;

    // References forms
    public requestQuoteFormGroup!: UntypedFormGroup;

    constructor(
        private _formBuilder: UntypedFormBuilder,
        private _requestQ: RequestqService
    ){
        // Form Initialization
        this.createFormRequestQuoteGroup();
    }

    ngOnInit(): void { }

    get prospectorNameInvalid() {
        return this.requestQuoteFormGroup.get('prospectorName')!.invalid && (this.requestQuoteFormGroup.get('prospectorName')!.dirty || this.requestQuoteFormGroup.get('prospectorName')!.touched);
    }

    get prospectorSurnameInvalid() {
        return this.requestQuoteFormGroup.get('prospectorSurname')!.invalid && (this.requestQuoteFormGroup.get('prospectorSurname')!.dirty || this.requestQuoteFormGroup.get('prospectorSurname')!.touched);
    }

    get placeProspectionInvalid() {
        return this.requestQuoteFormGroup.get('placeProspection')!.invalid && (this.requestQuoteFormGroup.get('placeProspection')!.dirty || this.requestQuoteFormGroup.get('placeProspection')!.touched);
    }

    get nameInvalid() {
        return this.requestQuoteFormGroup.get('name')!.invalid && (this.requestQuoteFormGroup.get('name')!.dirty || this.requestQuoteFormGroup.get('name')!.touched);
    }

    get surnameInvalid() {
        return this.requestQuoteFormGroup.get('surname')!.invalid && (this.requestQuoteFormGroup.get('surname')!.dirty || this.requestQuoteFormGroup.get('surname')!.touched);
    }

    get emailInvalid() {
        return this.requestQuoteFormGroup.get('email')!.invalid && (this.requestQuoteFormGroup.get('email')!.dirty || this.requestQuoteFormGroup.get('email')!.touched);
    }

    get phoneInvalid() {
        return this.requestQuoteFormGroup.get('phone')!.invalid && (this.requestQuoteFormGroup.get('phone')!.dirty || this.requestQuoteFormGroup.get('phone')!.touched);
    }

    get nextInvalid() {
        return this.requestQuoteFormGroup.get('next')!.invalid && (this.requestQuoteFormGroup.get('next')!.dirty || this.requestQuoteFormGroup.get('next')!.touched);
    }

    get brandTypeInvalid() {
        return this.requestQuoteFormGroup.get('brandType')!.invalid && (this.requestQuoteFormGroup.get('brandType')!.dirty || this.requestQuoteFormGroup.get('brandType')!.touched);
    }
    
    get newpreownedInvalid() {
        return this.requestQuoteFormGroup.get('newpreowned')!.invalid && (this.requestQuoteFormGroup.get('newpreowned')!.dirty || this.requestQuoteFormGroup.get('newpreowned')!.touched);
    }

    private createFormRequestQuoteGroup(){
        this.requestQuoteFormGroup = this._formBuilder.group({
            body: ['Prospección'],
            brand: ['Prospección'],
            model: ['Prospección'],
            prospectorName: ['', [Validators.required]],
            prospectorSurname: ['', [Validators.required]],
            placeProspection: ['', [Validators.required]],
            next: ['', [Validators.required]],
            name: ['', [Validators.required, Validators.pattern("[a-zA-Z ]+")]],
            surname: ['', [Validators.required, Validators.pattern("[a-zA-Z ]+")]],
            email: ['', [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],
            phone: ['', [Validators.required, Validators.pattern("[0-9]{10}"), Validators.minLength(10), Validators.maxLength(10)]],
            buyType: ['Prospección'],
            wantRelease: [null],
            initialCredit: [null],
            WhatsCurrentProfessionalSituation: [null],
            commentaryLead: [null],
            brandType: ['', [Validators.required]],
            newpreowned: ['', [Validators.required]],
            checkbox: [false, Validators.required]
        });
    }

    public onSubmit(){
        // Change spinner
        this.spinner = true;

        // Launch request
        let body = 'Prospección';
        let brand = 'Prospección';
        let model = 'Prospección';
        let prospectorName = this.requestQuoteFormGroup.get('prospectorName')?.value;
        let prospectorSurname = this.requestQuoteFormGroup.get('prospectorSurname')?.value;
        let placeProspection = this.requestQuoteFormGroup.get('placeProspection')?.value;
        let name = this.requestQuoteFormGroup.get('name')?.value;
        let surname = this.requestQuoteFormGroup.get('surname')?.value;
        let email = this.requestQuoteFormGroup.get('email')?.value;
        let phone = this.requestQuoteFormGroup.get('phone')?.value;
        let buyType = 'Prospección';
        let next = this.requestQuoteFormGroup.get('next')?.value;
        let brandType = this.requestQuoteFormGroup.get('brandType')?.value;
        let newpreowned = this.requestQuoteFormGroup.get('newpreowned')?.value;
        let commentaryLead = this.requestQuoteFormGroup.get('commentaryLead')?.value;
        
        // Genera la requisición de la cotización
        this._requestQ.setQuoteRequest(body, brand, model, prospectorName, prospectorSurname, placeProspection, name, surname, email, phone, buyType, next, brandType, newpreowned, commentaryLead)
        .subscribe({
            next: (sheetQ: Sheetquote) => {
                if (sheetQ.code === '200' && sheetQ.status === 'success') {
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
                }else {
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
            }
        });
    }

    public maxLengthCheck(object: any) {
        if (object.value.length > object.maxLength) {
          object.value = object.value.slice(0, object.maxLength)
        }
    }

}
