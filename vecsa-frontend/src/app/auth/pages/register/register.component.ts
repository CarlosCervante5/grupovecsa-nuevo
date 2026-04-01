import { Component, ElementRef, HostListener, OnInit, ViewChild } from '@angular/core';
import { AbstractControlOptions, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { Title } from '@angular/platform-browser';

// Animations
import Swal from 'sweetalert2';

// Services
import { AuthService } from '../../services/auth.service';

// Interfaces
import { ImageData, Quiz, QuizzesData, RegisterResponse, Validator } from '@interfaces/auth.interface';
import { MatChipListboxChange } from '@angular/material/chips';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { MatDialog } from '@angular/material/dialog';
import { AvatarsProfileComponent } from '../../components/avatars-profile/avatars-profile.component';
import { AccountService } from '@services/account.service';

import {reload} from '../../../shared/helpers/session.helper';
import { firstValueFrom } from 'rxjs';
import { GralResponse } from '@interfaces/vehicle_data.interface';

@Component({
    selector: 'app-register',
    templateUrl: './register.component.html',
    styleUrls: ['./register.component.css'],
    standalone: false
})

export class RegisterComponent {

    // Step wizard
    currentStep = 1;
    totalSteps = 5;
    stepLabels = ['Datos', 'Marca', 'Gustos', 'Tallas', 'Confirmar'];

    // References of Help
    public hide: boolean = true;
    public spinner: boolean = false;
    public active: boolean = false;
    public image_path: string = `assets/img/user.jpeg`;
    public quiz_active = true;
    public affinities_active = true;
    public status: boolean = false; 

    public gender: string | null = null;
    public gender_uuid: string | null = null;
    public statusBrand: boolean = false;

    public defaultBrandSelected: boolean = false;
    public motorradBrandSelected: boolean = false;
    public chevroletBrandSelected: boolean = false;

    public statusCards: boolean = false;

    public size: string | null = null;
    public size_uuid: string | null = null;
    public statusClothes: boolean = false;

    public brand: string | null = null;
    public brand_uuid: string | null = null;

    public clothes_gender: Quiz | null = null;
    public accesories: Quiz[] = [];
    public brand_quiz: Quiz | null = null;
    public cards: Quiz[] = [];
    public motorrad_cards: Quiz[] = [];
    public bmw_cards: Quiz[] = [];
    public mini_cards: Quiz[] = [];
    public chevrolet_cards: Quiz[] = [];
    public chevrolet_questions: Quiz[] = [];
    public default_questions: Quiz[] = [];
    
    public tallas = false;
    public isMobileView: boolean = false;
    public questions_form_invalid: boolean = false;
    public customer_uuid: string = '';
    public isFormValid: boolean = false;
    public user_uuid: string = '';

    public file: File | null = null;

    public chevrolet_validation: Validator[] = [];
    public default_validation: Validator[] = [];

    // Form References
    public form!: UntypedFormGroup;
    public auth!: UntypedFormGroup;

    @ViewChild('myModal') modal!: ElementRef;
    @ViewChild('myImg') img!: ElementRef;
    @ViewChild('img01') modalImg!: ElementRef; 
    @ViewChild('caption') caption!: ElementRef;
    execute!:string;

    constructor(
        private _router: Router,
        private _formBuilder: UntypedFormBuilder, 
        private _authService: AuthService,
        private _accountService: AccountService,
        private titleService: Title,
        private dialog: MatDialog,
    ) { 
        // Set Title View
        this.titleService.setTitle('Vecsa Hidalgo | Registrarme');

        // Create form
        this.checkViewport();
        this.createForm();
        this.getCustomerQuizzes();
    }

    checkViewport() {
        this.isMobileView = window.innerWidth <= 768;
    }

    /**
     * Getters Inputs Check
     */
    get nameInvalid() {
        return this.form.get('name')!.invalid && (this.form.get('name')!.dirty || this.form.get('name')!.touched);
    }

    get lastnameInvalid() {
        return this.form.get('last_name')!.invalid && (this.form.get('last_name')!.dirty || this.form.get('last_name')!.touched);
    }

    get emailInvalid() {
        return this.form.get('email')!.invalid && (this.form.get('email')!.dirty || this.form.get('email')!.touched);
    }

    get passwordInvalid() {
        return this.form.get('password')!.invalid && (this.form.get('password')!.dirty || this.form.get('password')!.touched);
    }

    get dateInvalid() {
        return this.form.get('birthday')!.invalid && (this.form.get('birthday')!.dirty || this.form.get('birthday')!.touched);
    }
    
    get phoneInvalid(){
        const phoneControl = this.form.get('phone_1');
        const phone = phoneControl?.value;

        const isNumeric = /^\d+$/.test(phone);
        const isValidLength = phone?.length >= 10 && phone?.length <= 15;

        return phoneControl?.touched && (!isNumeric || !isValidLength);
    }

    get passwordLength() {
        let password = this.form.get('password')!.value;
        return this.form.get('password')!.touched && (password.length < 8 || password.length > 32); 
    }

    /**
     * Login Form Initialization
     */
    public createForm() {
        this.form = this._formBuilder.group({
            name: ['', [Validators.required, Validators.pattern("[a-zA-ZÀ-ÿ ]+")]],
            last_name: ['', [Validators.required, Validators.pattern("[a-zA-ZÀ-ÿ ]+")]],
            birthday: ['', Validators.required],
            email: ['', [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],
            phone_1: [
                '', 
                [Validators.required, Validators.pattern("^[0-9]+$"), Validators.minLength(10), Validators.maxLength(10)]
            ],
            password: ['', [Validators.required, Validators.pattern(/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+/), Validators.minLength(8), Validators.maxLength(32)]],
            confirmPassword: ['', Validators.required]
        }, {validators: [this.passwordMatchValidator]} as AbstractControlOptions);
    }

    public esPar(valor: number): boolean {

        if(valor % 2 === 0){
            console.log(valor);
            return true
        }
        
        return false
    }

    public onInputChange(event: Event, question_uuid: string) {

        const inputElement = event.target as HTMLInputElement;
        
        if (!inputElement) return;

        const inputValue = inputElement.value?.trim() || '';

        if (this.chevroletBrandSelected) {
            
            this.validateQuestion(
                this.chevrolet_questions, 
                this.chevrolet_validation, 
                question_uuid, 
                inputValue
              );

            this.validateQuestionForm( this.chevrolet_validation );
            
        } else {

            this.validateQuestion(
                this.default_questions, 
                this.default_validation, 
                question_uuid, 
                inputValue
            );
          
            this.validateQuestionForm( this.default_validation );

        }
    }

    private validateQuestion(
        questionsArray: any[], 
        validationArray: any[], 
        question_uuid: string, 
        inputValue: string
    ) {

        const index = questionsArray.findIndex(quiz => quiz.uuid === question_uuid);
    
        if (index !== -1) {

            questionsArray[index].selected_value = inputValue.trim() || '';
    
            if (inputValue && inputValue.trim().length !== 0) {
                validationArray[index].invalid = false;
            } else {
                validationArray[index].invalid = true;
                validationArray[index].validation_message = `Complete la pregunta.`;
            }
        }
    }

    public validateQuestionForm( questions_set: Validator[]){

        this.questions_form_invalid = questions_set.every(quiz => quiz.invalid === false);

    }

    public async onSubmit() {
        try {
    
            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
            });
                
            const response = await this.register();

            if (response && response.data && response.data.profile && response.data.profile.uuid) {
                
                this.user_uuid = response.data.user.uuid;
                this.customer_uuid = response.data.profile.uuid;

                if(this.clothes_gender != null)
                    await this.attachQuiz(this.customer_uuid , this.clothes_gender!.uuid, this.clothes_gender!.selected_value);

                if(this.brand_quiz != null)
                    await this.attachQuiz(this.customer_uuid , this.brand_quiz.uuid, this.brand_quiz.selected_value);
                
                if(this.isMobileView){
                    this.cards = this.cards.reverse();
                }

                const cards_uuids = this.cards.map(card => card.uuid);

                const cards_selected_values = this.cards.map((card, index) => index.toString());

                this.attachQuizzes(this.customer_uuid , cards_uuids, cards_selected_values);


                const accesories_uuids = this.accesories.map(accesory => accesory.uuid);

                const accesories_selected_values = this.accesories.map((accesory, index) => accesory.selected_value);

                this.attachQuizzes(this.customer_uuid , accesories_uuids, accesories_selected_values);


                this.chevrolet_questions

                const chevrolet_questions_uuids = this.chevrolet_questions.map(chevrolet_question => chevrolet_question.uuid);

                const chevrolet_questions_values = this.chevrolet_questions.map((chevrolet_question, index) => chevrolet_question.selected_value);

                this.attachQuizzes(this.customer_uuid , chevrolet_questions_uuids, chevrolet_questions_values);


                this.default_questions

                const default_questions_uuids = this.default_questions.map(default_question => default_question.uuid);

                const default_questions_values = this.default_questions.map((default_question, index) => default_question.selected_value);

                this.attachQuizzes(this.customer_uuid , default_questions_uuids, default_questions_values);

                if(this.file != null){
                    await this.updateImage(this.file);
                }

                localStorage.setItem('user_token', response.data.token);
                localStorage.setItem('user', JSON.stringify( response.data.user));
                localStorage.setItem('role', response.data.role);
                localStorage.setItem('profile', JSON.stringify( response.data.profile));

                Swal.fire({
                    icon: 'success',
                    title: 'Registro creado exitosamente.',
                    showConfirmButton: false,
                    timer: 2000
                });

                this._router.navigate(['/auth/mi-cuenta'])
                        
            } else {
        
                Swal.fire({
                icon: 'error',
                title: 'Error al crear el registro',
                text: 'No se recibieron los datos correctos del servidor. Intenta más tarde.',
                });
        
            }
        } catch (error: any) {
    
            Swal.fire({
                icon: 'error',
                title: 'Lo sentimos, hubo un error',
                text: 'Hubo un problema al procesar la solicitud. Inténtalo más tarde.'+error,
            });
        }
    }

    private async register(): Promise<RegisterResponse | null> {
        try {
            return await firstValueFrom(this._authService.register(
                this.form.value
            ));
        } catch (error: any) {
            console.error('Error al crear el Rider:', error);
            throw new Error('Error en la creación del Rider.');
        }
    }

    public async updateImage(file: File): Promise<ImageData> {
        
        try {
            return await firstValueFrom(
                this._accountService.updateImage(this.user_uuid, file)
            );
        } catch (error: any) {
            console.error('Error al subir la imagen:', error);
            throw new Error('Error al subir la imagen del rider.');
        }
    }

    /**
     * Checking length input   
     * @param object any input
     */
    public maxLengthCheck(object: any) {   
        if (object.value.length > object.maxLength) {
        object.value = object.value.slice(0, object.maxLength)
        }
    }

    /**
     * Helper function to convert text String to Uppercase
     * @param event keyup
     * @returns string
     */
    public convertMayus(event: any): string {
        return event.target.value = event.target.value.toUpperCase();
    }

    public passwordMatchValidator(formGroup: UntypedFormGroup) {
        return formGroup.get('password')!.value === formGroup.get('confirmPassword')!.value ? null : { mismatch: true };
    }


    public onChipGenderChange(event: MatChipListboxChange, quiz_uuid: string) {

        this.gender = event.value;
        
        this.clothes_gender!.selected_value = event.value

        this.statusClothes = !!this.clothes_gender!.selected_value
        
        this.status = (this.statusClothes && this.statusBrand) ? true : false;

    }

    public onChipSelectionChange(event: MatChipListboxChange, quiz_uuid: string) {

        let index = this.accesories.findIndex(quiz => quiz.uuid === quiz_uuid);

        if (index !== -1) {
            this.accesories[index].selected_value = event.value;
        }

        this.validateAccesories();
    }

    public validateAccesories(){

        if( this.defaultBrandSelected ){

            let otherClothesValid = false;

            if( 
                this.accesories[0].selected_value !== null && this.accesories[0].selected_value !== undefined &&
                this.accesories[3].selected_value !== null && this.accesories[3].selected_value !== undefined &&
                this.accesories[5].selected_value !== null && this.accesories[5].selected_value !== undefined
            ) {
                otherClothesValid = true;
            }


            let pantalonValid = this.validateVariants('Pantalón');

            this.isFormValid = otherClothesValid && pantalonValid;

        }

        if( this.motorradBrandSelected ){

            let otherClothesValid = this.accesories
            .filter(quiz => quiz.question_type === 'ropa')
            .every(quiz => quiz.selected_value !== null && quiz.selected_value !== undefined);


            let calzadoValid = this.validateVariants('Calzado');

            let pantalonValid = this.validateVariants('Pantalón');

            this.isFormValid = otherClothesValid && calzadoValid && pantalonValid;

        }
    }
    
    public onChipBrandChange(event: MatChipListboxChange, quiz_uuid: string) {

        this.brand_quiz!.selected_value = event.value

        this.statusBrand = !!this.brand_quiz!.selected_value
        
        this.status = (this.statusClothes && this.statusBrand) ? true : false;

        if(event.value != undefined){

            if(event.value == 'bmw' || event.value == 'mini'){
                
                this.defaultBrandSelected = true;
                this.motorradBrandSelected = false;
                this.chevroletBrandSelected = false;

                this.isFormValid = false;

                this.validateQuestionForm( this.default_validation );
            }

            if(event.value == 'motorrad'){
                
                this.defaultBrandSelected = false;
                this.motorradBrandSelected = true;
                this.chevroletBrandSelected = false;

                this.isFormValid = false;

                this.validateQuestionForm( this.default_validation );
            }

            if(event.value == 'chevrolet'){
                
                this.defaultBrandSelected = false;
                this.motorradBrandSelected = false;
                this.chevroletBrandSelected = true;

                this.isFormValid = true;

                this.validateQuestionForm( this.chevrolet_validation );
            }

            this.assignCards(event.value);

        } else {

            this.defaultBrandSelected = false;
            this.motorradBrandSelected = false;
            this.chevroletBrandSelected = false;
            this.isFormValid = false;
        }

        this.validateAccesories();

    }

    public validateVariants = (name: string): boolean => {

        let variantMasculina = this.accesories.find(quiz => quiz.name === name && quiz.question_type === 'ropa masculina');
        let variantFemenina = this.accesories.find(quiz => quiz.name === name && quiz.question_type === 'ropa femenina');

        const isMasculinaValid = variantMasculina ? variantMasculina.selected_value !== null && variantMasculina.selected_value !== undefined : false;
        const isFemeninaValid = variantFemenina ? variantFemenina.selected_value !== null && variantFemenina.selected_value !== undefined : false;
    
        return isMasculinaValid || isFemeninaValid;
    };

    public drop(event: CdkDragDrop<Quiz[]>) {
        if (event.previousIndex !== event.currentIndex) {

            moveItemInArray(this.cards, event.previousIndex, event.currentIndex);

            this.statusCards = true;

        }
    }

    public avatar () {
        const dialogRef = this.dialog.open(AvatarsProfileComponent, { 
            width: '900px', 
            height: '600px',
            data: {
                page: 'register'
            }
        });

        dialogRef.afterClosed().subscribe(result => {
            if (result) {
                this.urlToFile(result, 'icon.png').then((file) => {
                    this.file = file;
                }).catch((error) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al subir la imagen de su perfil.',
                        text: `Error: ${ error }`,
                    });
                });
            
                this.image_path = result;
            }
        });
    }

    @HostListener('window:resize', ['$event'])
    onResize() {
        this.checkViewport();
    }

    @HostListener('window:scroll', [])
    scrollToTop() {
        const element = document.getElementById('top'); // Asegúrate de tener un elemento con este ID
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Función para convertir una URL a un archivo de tipo File
    public urlToFile(url: string, fileName: string): Promise<File> {
        return fetch(url)
        .then((response) => response.blob())
        .then((blob) => {
            return new File([blob], fileName, { type: blob.type });
        });
    }

    public getCustomerQuizzes(){

        this.tallas = true;

        this.quiz_active = true;

        this.affinities_active = true;
    
        this._accountService.quizzesProfile()
        .subscribe({
            
            next: ( quizzes: QuizzesData) => {

                if (!quizzes.data || quizzes.data.length === 0) {
                    this.tallas = false;
                    this.quiz_active = false;
                    this.affinities_active = false;
                    this.clothes_gender = null as any;
                    this.brand_quiz = null as any;
                    this.accesories = [];
                    this.motorrad_cards = [];
                    this.bmw_cards = [];
                    this.mini_cards = [];
                    this.chevrolet_cards = [];
                    this.chevrolet_questions = [];
                    this.default_questions = [];
                    return;
                }

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

                
                this.quiz_active = false;

                this.affinities_active = false;

            },
            error: () => {

            }
        });
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

    public async attachQuiz (customer_uuid: string, quiz_uuid:string, selected_value: string): Promise<GralResponse | void>{
        try {
            return await firstValueFrom(
                this._accountService.attatchQuiz(customer_uuid,quiz_uuid, selected_value)
            );
        } catch (error: any) {
            console.error('Error al adjuntar respuesta de afinidades:', error);
            throw new Error('Error al adjuntar respuesta de afinidades.');
        }
    }

    public async attachQuizzes (customer_uuid: string, quiz_uuids:string[], selected_values: string[]): Promise<GralResponse | void>{
        
        try {

            return await firstValueFrom(
                this._accountService.attatchQuizzes(customer_uuid,quiz_uuids,selected_values)
            );
        } catch (error: any) {
            console.error('Error al adjuntar respuesta de afinidades:', error);
            throw new Error('Error al adjuntar respuesta de afinidades.');
        }
    }


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

    // ── Step Navigation ──
    nextStep(): void {
      if (this.currentStep < this.totalSteps) {
        this.currentStep++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    prevStep(): void {
      if (this.currentStep > 1) {
        this.currentStep--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    goToStep(step: number): void {
      if (step >= 1 && step <= this.totalSteps) {
        this.currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    get canGoNext(): boolean {
      switch (this.currentStep) {
        case 1: return this.form.valid && this.image_path !== 'assets/img/user.jpeg';
        case 2: return this.statusBrand && !!this.gender;
        case 3: return true;
        case 4: return true;
        default: return true;
      }
    }

}
