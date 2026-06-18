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

    private readonly sizeQuestionTypes = ['ropa', 'ropa masculina', 'ropa femenina'];
    private readonly defaultAffinityPercent = 50;

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

                localStorage.setItem('user_token', response.data.token);
                localStorage.setItem('user', JSON.stringify( response.data.user));
                localStorage.setItem('role', response.data.role);

                if(this.file != null){
                    const imageResponse = await this.updateImage(this.file) as { data?: { picture?: string } };
                    if (imageResponse?.data?.picture) {
                        response.data.profile.picture = imageResponse.data.picture;
                    }
                }

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
                this._accountService.updateImageProfile(this.user_uuid, file)
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

        this.validateAccesories();

    }

    public onChipSelectionChange(event: MatChipListboxChange, quiz_uuid: string) {

        let index = this.accesories.findIndex(quiz => quiz.uuid === quiz_uuid);

        if (index !== -1) {
            this.accesories[index].selected_value = event.value;
        }

        this.validateAccesories();
    }

    public getAffinityPercent(quiz: Quiz): number {
        const parsed = Number(quiz.selected_value);

        if (!Number.isFinite(parsed)) {
            return this.defaultAffinityPercent;
        }

        return Math.min(100, Math.max(0, Math.round(parsed)));
    }

    public onAffinityPercentChange(
        quiz_uuid: string,
        rawValue: string | number,
        source: 'accesories' | 'default_questions' = 'accesories'
    ): void {
        const value = Math.min(100, Math.max(0, Math.round(Number(rawValue))));
        const percent = String(value);

        if (source === 'default_questions') {
            const index = this.default_questions.findIndex(quiz => quiz.uuid === quiz_uuid);

            if (index !== -1) {
                this.default_questions[index].selected_value = percent;
                this.default_validation[index].invalid = false;
            }

            this.validateQuestionForm(this.default_validation);
            return;
        }

        const index = this.accesories.findIndex(quiz => quiz.uuid === quiz_uuid);

        if (index !== -1) {
            this.accesories[index].selected_value = percent;
        }
    }

    private initializeGustosPercents(): void {
        if (this.hasInterestAffinities) {
            for (const quiz of this.affinityQuizzes) {
                if (quiz.selected_value == null || quiz.selected_value === '') {
                    quiz.selected_value = String(this.defaultAffinityPercent);
                }
            }
            return;
        }

        this.default_questions.forEach((quiz, index) => {
            if (quiz.selected_value == null || quiz.selected_value === '') {
                quiz.selected_value = String(this.defaultAffinityPercent);
            }

            this.default_validation[index].invalid = false;
        });

        this.validateQuestionForm(this.default_validation);
    }

    public onSizeQuizChange(event: MatChipListboxChange, quiz: Quiz): void {
        if (this.usesDefaultQuestionsForSizes) {
            quiz.selected_value = event.value;
            this.validateQuestion(
                this.default_questions,
                this.default_validation,
                quiz.uuid,
                event.value
            );
            this.validateQuestionForm(this.default_validation);
        } else {
            this.onChipSelectionChange(event, quiz.uuid);
        }

        this.validateAccesories();
    }

    get affinityQuizzes(): Quiz[] {
        return this.accesories.filter(quiz =>
            !this.sizeQuestionTypes.includes(quiz.question_type)
        );
    }

    get hasInterestAffinities(): boolean {
        return this.affinityQuizzes.length > 0;
    }

    get gustosQuizzes(): Array<{ quiz: Quiz; source: 'accesories' | 'default_questions' }> {
        if (this.hasInterestAffinities) {
            return this.affinityQuizzes
                .filter(quiz => this.shouldShowSizeQuiz(quiz))
                .map(quiz => ({ quiz, source: 'accesories' as const }));
        }

        return this.default_questions.map(quiz => ({
            quiz,
            source: 'default_questions' as const,
        }));
    }

    get brandValues(): string[] {
        return this.brand_quiz ? this.quizValues(this.brand_quiz) : [];
    }

    get genderValues(): string[] {
        return this.clothes_gender ? this.quizValues(this.clothes_gender) : [];
    }

    get sizeQuizzes(): Quiz[] {
        const affinitySizes = this.accesories.filter(quiz =>
            this.sizeQuestionTypes.includes(quiz.question_type)
        );

        return affinitySizes.length > 0 ? affinitySizes : this.default_questions;
    }

    get usesDefaultQuestionsForSizes(): boolean {
        return !this.accesories.some(quiz => this.sizeQuestionTypes.includes(quiz.question_type));
    }

    shouldShowSizeQuiz(quiz: Quiz): boolean {
        if (quiz.question_type === 'ropa femenina' && this.gender != 'M') {
            return false;
        }

        if (quiz.question_type === 'ropa masculina' && this.gender == 'M') {
            return false;
        }

        return true;
    }

    private isSizeValueSelected(quiz: Quiz): boolean {
        const value = quiz.selected_value;
        return value !== null && value !== undefined && value !== '';
    }

    public validateAccesories(){

        if( this.defaultBrandSelected ){

            if (this.usesDefaultQuestionsForSizes) {
                this.isFormValid = this.default_validation.every(quiz => quiz.invalid === false);
                return;
            }

            const sizeItems = this.accesories.filter(quiz =>
                this.sizeQuestionTypes.includes(quiz.question_type) &&
                quiz.name !== 'Pantalón' &&
                this.shouldShowSizeQuiz(quiz)
            );

            const otherClothesValid = sizeItems.every(quiz => this.isSizeValueSelected(quiz));

            let pantalonValid = this.validateVariants('Pantalón');

            this.isFormValid = otherClothesValid && pantalonValid;

        }

        if( this.motorradBrandSelected ){

            if (this.usesDefaultQuestionsForSizes) {
                this.isFormValid = this.default_validation.every(quiz => quiz.invalid === false);
                return;
            }

            let otherClothesValid = this.accesories
            .filter(quiz => quiz.question_type === 'ropa' && this.shouldShowSizeQuiz(quiz))
            .every(quiz => this.isSizeValueSelected(quiz));


            let calzadoValid = this.validateVariants('Calzado');

            let pantalonValid = this.validateVariants('Pantalón');

            this.isFormValid = otherClothesValid && calzadoValid && pantalonValid;

        }

        if (this.chevroletBrandSelected) {
            this.isFormValid = true;
        }
    }
    
    private normalizeBrand(brand: string | null | undefined): string {
        return (brand ?? '').trim().toLowerCase();
    }

    private quizValues(quiz: Quiz | null | undefined): string[] {
        if (!quiz?.values) {
            return [];
        }

        const values = quiz.values as string[] | string;

        if (Array.isArray(values)) {
            return values.map(value => value.trim()).filter(Boolean);
        }

        return String(values).split(',').map(value => value.trim()).filter(Boolean);
    }

    private normalizeQuiz(quiz: Quiz): Quiz {
        return {
            ...quiz,
            values: this.quizValues(quiz),
        };
    }

    private isBrandQuiz(quiz: Quiz): boolean {
        const values = this.quizValues(quiz).map(value => this.normalizeBrand(value));
        const brandOptions = ['bmw', 'mini', 'motorrad', 'chevrolet'];

        return brandOptions.some(option => values.includes(option));
    }

    private resolveBrandQuiz(quizzes: Quiz[]): Quiz | null {
        const byGroup = quizzes.find(quiz =>
            quiz.group_name === 'brand_preference' || quiz.group_name === 'brand'
        );
        if (byGroup) {
            return this.normalizeQuiz(byGroup);
        }

        const byChip = quizzes.find(quiz => quiz.element_type === 'chip' && this.isBrandQuiz(quiz));
        if (byChip) {
            return this.normalizeQuiz(byChip);
        }

        const byName = quizzes.find(quiz => /marca/i.test(quiz.name));
        if (byName) {
            return this.normalizeQuiz(byName);
        }

        if (quizzes[11]) {
            return this.normalizeQuiz(quizzes[11]);
        }

        return null;
    }

    private resolveGenderQuiz(quizzes: Quiz[]): Quiz | null {
        const byGroup = quizzes.find(quiz =>
            quiz.group_name === 'profile_gender' || quiz.group_name === 'clothes_gender'
        );
        if (byGroup) {
            return this.normalizeQuiz(byGroup);
        }

        if (quizzes[0]) {
            return this.normalizeQuiz(quizzes[0]);
        }

        return null;
    }

    public onChipBrandChange(event: MatChipListboxChange, quiz_uuid: string) {

        this.brand_quiz!.selected_value = event.value

        this.statusBrand = !!this.brand_quiz!.selected_value
        
        this.status = (this.statusClothes && this.statusBrand) ? true : false;

        if(event.value != undefined){

            const brand = this.normalizeBrand(event.value);

            if(brand === 'bmw' || brand === 'mini'){
                
                this.defaultBrandSelected = true;
                this.motorradBrandSelected = false;
                this.chevroletBrandSelected = false;

                this.isFormValid = false;

                this.validateQuestionForm( this.default_validation );
            }

            if(brand === 'motorrad'){
                
                this.defaultBrandSelected = false;
                this.motorradBrandSelected = true;
                this.chevroletBrandSelected = false;

                this.isFormValid = false;

                this.validateQuestionForm( this.default_validation );
            }

            if(brand === 'chevrolet'){
                
                this.defaultBrandSelected = false;
                this.motorradBrandSelected = false;
                this.chevroletBrandSelected = true;

                this.isFormValid = true;

                this.validateQuestionForm( this.chevrolet_validation );
            }

            this.assignCards(brand);

        } else {

            this.defaultBrandSelected = false;
            this.motorradBrandSelected = false;
            this.chevroletBrandSelected = false;
            this.isFormValid = false;
        }

        this.validateAccesories();

    }

    public validateVariants = (name: string): boolean => {

        const variants = this.accesories.filter(quiz =>
            quiz.name === name &&
            this.sizeQuestionTypes.includes(quiz.question_type) &&
            this.shouldShowSizeQuiz(quiz)
        );

        if (variants.length === 0) {
            return true;
        }

        return variants.every(quiz => this.isSizeValueSelected(quiz));
    };

    public drop(event: CdkDragDrop<Quiz[]>) {
        if (event.previousIndex !== event.currentIndex) {

            moveItemInArray(this.cards, event.previousIndex, event.currentIndex);

            this.statusCards = true;

        }
    }

    public avatar () {
        const dialogRef = this.dialog.open(AvatarsProfileComponent, {
            width: '920px',
            maxWidth: '95vw',
            maxHeight: '90vh',
            panelClass: 'avatar-picker-dialog',
            autoFocus: false,
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

                this.clothes_gender = this.resolveGenderQuiz(quizzes.data);

                this.gender = (this.clothes_gender?.selected_value == "undefined")
                    ? 'null'
                    : (this.clothes_gender?.selected_value ?? null);

                this.accesories = quizzes.data
                    .filter(quiz => quiz.group_name === 'profile_affinities')
                    .map(quiz => this.normalizeQuiz(quiz));

                this.brand_quiz = this.resolveBrandQuiz(quizzes.data);

                this.motorrad_cards = quizzes.data.filter(quiz =>
                    quiz.group_name === 'event_preferences' || quiz.group_name === 'motorrad_event_preferences'
                );

                this.bmw_cards = quizzes.data.filter(quiz => quiz.group_name === 'bmw_event_preferences');

                this.mini_cards = quizzes.data.filter(quiz => quiz.group_name === 'mini_event_preferences');

                this.chevrolet_cards = quizzes.data.filter(quiz => quiz.group_name === 'chevrolet_event_preferences');

                this.chevrolet_questions = quizzes.data
                    .filter(quiz => quiz.group_name === 'chevrolet_questions')
                    .map(quiz => this.normalizeQuiz(quiz));

                this.default_questions = quizzes.data
                    .filter(quiz => quiz.group_name === 'default_questions')
                    .map(quiz => this.normalizeQuiz(quiz));

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

                this.initializeGustosPercents();

                
                this.quiz_active = false;

                this.affinities_active = false;

            },
            error: () => {

            }
        });
    }

    public assignCards( brand: string){

        const normalizedBrand = this.normalizeBrand(brand);

        if(normalizedBrand === 'bmw'){

            this.cards = this.bmw_cards;
        }

        if(normalizedBrand === 'mini'){

            this.cards = this.mini_cards;
        }

        if(normalizedBrand === 'motorrad'){

            this.cards = this.motorrad_cards;
        }

        if(normalizedBrand === 'chevrolet'){

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
        if (this.currentStep === 3) {
          this.validateAccesories();
        }
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
        case 4:
          if (this.chevroletBrandSelected) {
            return true;
          }
          if (this.usesDefaultQuestionsForSizes && (this.defaultBrandSelected || this.motorradBrandSelected)) {
            return this.default_validation.every(quiz => quiz.invalid === false);
          }
          this.validateAccesories();
          return this.isFormValid;
        default: return true;
      }
    }

}
