import { Component, ElementRef, HostListener, OnInit, ViewChild } from '@angular/core';
import { AbstractControl, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { Title } from '@angular/platform-browser';
import Swal from 'sweetalert2';
import { AccountService } from '../../services/account.service';
import { AuthService } from 'src/app/auth/services/auth.service';
import { environment } from '@environments/environment';
import { Question, Quiz, QuizzesData, ShowProfileResponse, Validator } from '@interfaces/auth.interface';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { MatBottomSheet, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { AvatarsProfileComponent } from '../../../../components/avatars-profile/avatars-profile.component';
import { MatDialog } from '@angular/material/dialog';

import {reload} from '@helpers/session.helper';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';

interface Card {
    id: number;
    name: string;
    description: string;
    value: string;
    image_path: string;
}

import { MatChipListboxChange } from '@angular/material/chips';

@Component({
    selector: 'app-settings',
    templateUrl: './settings.component.html',
    styleUrls: ['./settings.component.scss'],
    standalone: false
})

export class SettingsComponent {

    public hide: boolean = true;
    public spinner: boolean = false;  
    public form!: FormGroup;
    public url_dashboard: string = '/auth/mi-cuenta';
    public user_id!: number;
    public image_path: string = ''; 
    public customer_uuid: string = '';
    public porcentaje !: string;
    public n_resp!: number;
    private url: string = environment.baseUrl;
    
    public activeC = true;
    public quiz_active = true;
    public affinities_active = true;
    public gender: string | null = null;

    isMobileView = false;
    public  y = 0;

    @ViewChild('myModal') modal!: ElementRef;
    @ViewChild('myImg') img!: ElementRef;
    @ViewChild('img01') modalImg!: ElementRef; 
    @ViewChild('caption') caption!: ElementRef;
    public execute!:string;

    public defaultBrandSelected: boolean = false;
    public motorradBrandSelected: boolean = false;
    public chevroletBrandSelected: boolean = false;

    public clothes_gender: Quiz | null = null;
    public accesories: Quiz[] = [];
    public brand_quiz: Quiz | null = null;
    public cards: Quiz[] = [];
    public motorrad_cards: Quiz[] = [];
    public bmw_cards: Quiz[] = [];
    public mini_cards: Quiz[] = [];
    public chevrolet_cards: Quiz[] = [];

    public chevrolet_questions: Question[] = [];
    public default_questions: Question[] = [];

    public chevrolet_validation: Validator[] = [];
    public default_validation: Validator[] = [];

    public questions_form_invalid: boolean = false;
    public isFormValid: boolean = false;
    public status: boolean = false;
    public statusCards: boolean = false;
    public tallas = false;

    public statusBrand: boolean = false;

    public size: string | null = null;
    public size_uuid: string | null = null;
    public statusClothes: boolean = false;

    public brand: string | null = null;
    public brand_uuid: string | null = null;

    private debounce_timer: any;

    constructor (
        private _router: Router,
        private _formBuilder: FormBuilder,
        private _accountService: AccountService,
        private _authService: AuthService,
        private titleService: Title,
        private _bottomSheet: MatBottomSheet,
        private dialog: MatDialog,

    ) { 
        this.y = 0;
        this.n_resp = 0;
        this.createForm();
        this.titleService.setTitle('Vecsa Hidalgo | Perfil');
        this.createForm();
        this.getCustomerQuizzes();
        this.getUser();
        this.url_dashboard = this.get_url_dashboard();
        this.checkViewport();
        
    }

    public get_url_dashboard() {
        
        let role: any = localStorage.getItem('role');
        
        if(role != null){
    
            if(role === 'client')
                return `/auth/mi-cuenta`
    
            return adminDashboardUrl(role);
        }
    
        return `/admin/not-autorized`;
    
    }

    public get nicknameInvalid() {
        return this.form.get('nickname')?.invalid && (this.form.get('nickname')?.dirty || this.form.get('nickname')?.touched);
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

    public get phoneTwoInvalid() {
        return this.form.get('phone_2')?.invalid && (this.form.get('phone_2')?.dirty || this.form.get('phone_2')?.touched);
    }

    public get genderInvalid() {
        return this.form.get('gender')?.invalid && (this.form.get('gender')?.dirty || this.form.get('gender')?.touched);
    }

    public get emailOneInvalid() {
        return this.form.get('email_1')?.invalid && (this.form.get('email_1')?.dirty || this.form.get('email_1')?.touched);
    }

    public get emailTwoInvalid() {
        return this.form.get('email_2')?.invalid && (this.form.get('email_2')?.dirty || this.form.get('email_2')?.touched);
    }
    
    public get dateInvalid() {
        return this.form.get('birthday')!.invalid && (this.form.get('birthday')!.dirty || this.form.get('birthday')!.touched);
    }

    /**
     * Form Initialization
     */
    private createForm() {
        this.form = this._formBuilder.group({
            nickname: ['', [Validators.required, Validators.maxLength(25), Validators.pattern(/^[a-zA-ZÀ-ÿ0-9_ .]+$/)]], //se agrego el punto en los caracteres aceptados
            name: ['', [Validators.required, Validators.pattern("[a-zA-ZÀ-ÿ ]+")]],
            last_name: ['', [Validators.required, Validators.pattern("[a-zA-ZÀ-ÿ ]+")]],
            phone_1: ['', [this.phoneValidator.bind(this)]],
            phone_2: ['', [this.phoneValidator.bind(this)]],
            gender: ['',],
            email_1: [{ value: '', disabled: true }, [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],
            email_2: ['', [Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],
            birthday: ['', Validators.required],
            uuid: ['', [Validators.required]], 
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

    private getUser() {

        this.activeC = true;

        const user = JSON.parse(localStorage.getItem('user')!);

        this._accountService.getProfile(user.uuid)
        .subscribe({
            next: ({ data }: ShowProfileResponse) => {
                
                localStorage.setItem('profile', JSON.stringify( data.profile ));
                this.form.patchValue({
                    nickname: data.user.nickname,
                    name: data.profile.name,
                    last_name: data.profile.last_name,
                    phone_1: data.profile.phone_1 || '',
                    phone_2: data.profile.phone_2 || '',
                    gender: data.profile.gender,
                    email_1: data.profile.email_1,
                    email_2: data.profile.email_2,
                    birthday: data.profile.birthday,
                    uuid: data.user.uuid,
                });

                if (data.profile.phone_1) {
                    this.form.get('phone_1')?.disable();
                }

                this.customer_uuid = data.profile.uuid;
                this.image_path = data.profile.picture || `assets/icons/profile.svg`;
                this.activeC = false;
            },
            error: (error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oupps..',
                    text: 'Ocurrió un error al obtener su información, vuelva a intentarlo más tarde. ' + error.error.message,
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838',
                    timer: 3500
                });
            }
        });
    }

    public onSubmit() { 
        
        this.spinner = true;
        
        this.form.patchValue({
            gender: this.gender,
        });
        
        this._accountService.updateProfile(this.form.value)
        .subscribe({
            next: () => {
                this.spinner = false;
                Swal.fire({
                    icon: 'success',
                    title: 'Actualización',
                    text: 'Actualización exitosa.',
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838',
                    timer: 3500
                });

                this.getUser();

            },
            error: (error) => {
                this.spinner = false;
                reload(error, this._router);
            }
        });
    }

    public convertMayus(event: Event): string {
        const target = event.target as HTMLInputElement;
        return target.value = target.value.toUpperCase();
    }

    public updateImage(fileEvent: Event) {
        
        const target = fileEvent.target as HTMLInputElement;
        const file = target.files?.[0];
        
        if (!file) return;

        const user = JSON.parse(localStorage.getItem('user')!);

        this._accountService.updateImageProfile(user.uuid, file)
        .subscribe({
            
            next: () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Actualización',
                    text: 'Actualización exitosa.',
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838',
                    timer: 3500
                });

                this.getUser();
            
            },
            error: (error:any) => {
                reload(error, this._router);
            }
        });
    }

    public avatar () {
        const dialogRef = this.dialog.open(AvatarsProfileComponent, { 
            width: '900px', 
            height: '600px',
            data: {
                page: 'edit'
            }
        });

        dialogRef.afterClosed().subscribe(result => {
            if (result) {
                this.image_path = result;
            }
        });
    }
    
    public getCustomerQuizzes(){

        this.tallas = true;

        this.quiz_active = true;

        this.affinities_active = true;

        const profile = JSON.parse(localStorage.getItem('profile')!);
    
        this._accountService.customerQuizzes(profile.uuid)
        .subscribe({
            
            next: ( quizzes: QuizzesData) => {

                this.clothes_gender = quizzes.data[0];

                this.gender =  (quizzes.data[0].selected_value == "undefined")? 'null':  quizzes.data[0].selected_value;

                this.accesories = quizzes.data.filter(quiz => quiz.group_name === 'profile_affinities');

                this.brand_quiz = quizzes.data[11];

                this.motorrad_cards = quizzes.data.filter(quiz => quiz.group_name === 'event_preferences');

                this.bmw_cards = quizzes.data.filter(quiz => quiz.group_name === 'bmw_event_preferences');

                this.mini_cards = quizzes.data.filter(quiz => quiz.group_name === 'mini_event_preferences');

                this.chevrolet_cards = quizzes.data.filter(quiz => quiz.group_name === 'chevrolet_event_preferences');

                this.chevrolet_questions = quizzes.data.filter(quiz => quiz.group_name === 'chevrolet_questions')

                this.default_questions = quizzes.data.filter(quiz => quiz.group_name === 'default_questions')

                this.chevrolet_validation = this.chevrolet_questions.map(q => ({
                    uuid: q.uuid,
                    invalid: true,
                    validation_message: ''
                }));

                this.default_validation = this.default_questions.map(q => ({
                    uuid: q.uuid,
                    invalid: true,
                    validation_message: ''
                }));

                this.validateSelectedBrand(this.brand_quiz.selected_value);

                this.assignCards(this.brand_quiz.selected_value);
                
                this.quiz_active = false;

                this.affinities_active = false;

            },
            error: () => {

            }
        });
    }

    public onChipGenderChange(event: MatChipListboxChange, quiz_uuid: string) {

        this.gender = event.value;
        const type = 'genero';
        this.attachQuiz(this.customer_uuid , quiz_uuid, event.value, type);

    }

    public onChipBrandChange(event: MatChipListboxChange, quiz_uuid: string) {
        const type = 'marca';
        this.attachQuiz(this.customer_uuid , quiz_uuid, event.value, type);

        this.brand_quiz!.selected_value = event.value

        this.statusBrand = !!this.brand_quiz!.selected_value
        
        this.status = (this.statusClothes && this.statusBrand) ? true : false;

        if(event.value != undefined){

            this.validateSelectedBrand(event.value);

            this.assignCards(event.value);

        } else {

            this.defaultBrandSelected = false;
            this.motorradBrandSelected = false;
            this.chevroletBrandSelected = false;
            // this.isFormValid = false;
        }

    }

    public validateSelectedBrand(value:string){

        if(value == 'bmw' || value == 'mini'){
                
            this.defaultBrandSelected = true;
            this.motorradBrandSelected = false;
            this.chevroletBrandSelected = false;

        }

        if(value == 'motorrad'){
            
            this.defaultBrandSelected = false;
            this.motorradBrandSelected = true;
            this.chevroletBrandSelected = false;

        }

        if(value == 'chevrolet'){
            
            this.defaultBrandSelected = false;
            this.motorradBrandSelected = false;
            this.chevroletBrandSelected = true;

        }
    }

    public onChipSelectionChange(event: MatChipListboxChange, quiz_uuid: string) {
        const type = 'ropa';
        this.attachQuiz(this.customer_uuid , quiz_uuid, event.value, type);

    }

    public drop(event: CdkDragDrop<Quiz[]>) {
        if (event.previousIndex !== event.currentIndex) {

            moveItemInArray(this.cards, event.previousIndex, event.currentIndex);

            const quiz_uuids = this.cards.map(card => card.uuid);

            const selected_values = this.cards.map((card, index) => index.toString());

            this.attachQuizzes(this.customer_uuid , quiz_uuids, selected_values);

        }
    }

    public checkViewport() {
        this.isMobileView = window.innerWidth <= 768;
    }

    @HostListener('window:resize', ['$event'])
    onResize() {
        this.checkViewport();
    }

    public attachQuiz (customer_uuid: string, quiz_uuid:string, selected_value: string, type: string){
        this._accountService.attatchQuiz(customer_uuid,quiz_uuid, selected_value)
        .subscribe({
            next: () => {
               
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: "success",
                    title: "Guardado..."
                });

            },
            error: (error) => {
                reload(error, this._router);
            }
        });
    }

    public attachQuizzes (customer_uuid: string, quiz_uuids:string[], selected_values: string[]){
        
        this._accountService.attatchQuizzes(customer_uuid,quiz_uuids,selected_values)
        .subscribe({
            next: () => {

                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                  
                Toast.fire({
                    icon: "success",
                    title: "Guardado..."
                });

            },
            error: (error) => {
                reload(error, this._router);
            }
        });
    }

    // @HostListener('window:scroll', [])
    // scrollToTop() {
    //     const element = document.getElementById('top'); // Asegúrate de tener un elemento con este ID
    //     if (element) {
    //         element.scrollIntoView({ behavior: 'smooth' });
    //     }
    // }

    public showModal( tipo: string) {   
        let src = '';
        tipo === 'casco' ? src = 'assets/images/casco.jpeg': '';
        tipo === 'guantes' ? src = 'assets/images/guantes.jpeg': '';
        tipo === 'zapatos' ? src = 'assets/images/zapatos.jpeg': '';
        let imagen = src;
        let legal = "";
    
        this.modal.nativeElement.style.display = "grid";
        this.modalImg.nativeElement.src = imagen;  
        this.caption.nativeElement.innerHTML = legal ;
    }

    public closeModal( message:string ) {    
        if( message == "no" ) {
          this.execute = 'no';
        }else if ( message == "yes" && this.execute == 'no' ){
          this.execute = 'processing';
        }else {
          this.execute = 'yes';
        }
        if( this.execute == 'yes' ){
          this.modal.nativeElement.style.display = "none";
        }    
    }

    public onInputChange(event: Event, question_uuid: string) {

        const inputElement = event.target as HTMLInputElement;
        
        if (!inputElement) return;

        const inputValue = inputElement.value?.trim() || '';

        this.debounce(() => {
            
            if (inputValue.length !== 0) {
              
                this.attachQuiz(this.customer_uuid , question_uuid, inputValue, 'pregunta_abierta');
              
            }
        });

    }

    private debounce(callback: () => void, delay: number = 600): void {
        clearTimeout(this.debounce_timer);
        this.debounce_timer = setTimeout(callback, delay);
    }


    public assignCards( brand: string){

        if(brand == 'bmw'){

            this.cards = this.bmw_cards;
        }

        if(brand == 'mini'){

            this.cards = this.mini_cards;
        }

        if(brand == 'motorrad'){

            this.cards = this.motorrad_cards;
        }

        if(brand == 'chevrolet'){

            this.cards = this.chevrolet_cards;
        }


        this.cards.sort((a, b) => {
            const numA = isNaN(Number(a.selected_value)) ? 0 : Number(a.selected_value);
            const numB = isNaN(Number(b.selected_value)) ? 0 : Number(b.selected_value);
            return numA - numB;
        });

        if(this.isMobileView){
            this.cards = this.cards.reverse();
        }
    }


    
}
