
import { Component, ElementRef, HostListener, ViewChild } from '@angular/core';
import {  FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { Title } from '@angular/platform-browser';
import Swal from 'sweetalert2';
import { AuthService } from 'src/app/auth/services/auth.service';
import { ImageData, Quiz, QuizzesData, RegisterResponse } from '@interfaces/auth.interface';
import { MatChipListboxChange } from '@angular/material/chips';
import { AccountService } from 'src/app/auth/pages/account/services/account.service';
import { firstValueFrom } from 'rxjs';
import { AdminService } from '@services/admin.service';
import { RewardResponse } from '@interfaces/rewards.interface';
import { GralResponse } from '@interfaces/vehicle_data.interface';

@Component({
    selector: 'app-riders',
    templateUrl: './riders.component.html',
    styleUrls: ['./riders.component.css'],
    standalone: false
})
export class RidersComponent {

    @ViewChild('myModal') modal!: ElementRef;
    @ViewChild('myImg') img!: ElementRef;
    @ViewChild('img01') modalImg!: ElementRef; 
    @ViewChild('caption') caption!: ElementRef;

    public spinner: boolean = false;  
    public status: boolean = false; 
    public form!: FormGroup;
    public image_path: string = `assets/img/user.jpeg`;
    public customer_uuid: string = '';
    public reward_uuid: string = '';
    public user_uuid: string = '';
    public n_resp = 0;
    public file: File | null = null;
    
    public activeC = false;
    public final = false;
    public quiz_active = true;
    public affinities_active = true;
    
    public gender: string | null = null;
    public gender_uuid: string | null = null;
    public statusGender: boolean = false;

    public size: string | null = null;
    public size_uuid: string | null = null;
    public statusSize: boolean = false;

    public brand: string | null = null;
    public brand_uuid: string | null = null;

    public clothes_gender: Quiz | null = null;
    public accesories: Quiz[] = [];
    public brand_quiz: Quiz | null = null;

    public cards: Quiz[] = [];
    public tallas = false;

    isMobileView = false;

    isTableCollapsed = true;

    constructor (
        private _router: Router,
        private _formBuilder: FormBuilder,
        private _accountService: AccountService,
        private _authService: AuthService,
        private titleService: Title,
        private _adminservice: AdminService
    ) { 
        this.titleService.setTitle('Vecsa Hidalgo | Perfil');
        this.createForm();
        this.checkViewport();
        this.getCustomerQuizzes();
    }

    // Función para alternar el estado de la tabla
    toggleTable() {
        this.isTableCollapsed = !this.isTableCollapsed;
    }


    downloadPdf() {
        const pdfUrl = 'assets/OKTOBERFEST2024_VECSA HIDALGO.pdf';
        const pdfName = 'OKTOBERFEST2024_VECSA HIDALGO.pdf';

        
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = pdfName;
        link.click();
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

    public async onSubmit() {
      try {
  
        Swal.fire({
          title: 'Procesando...',
          allowOutsideClick: false,
        });
  
        const reward = await this.getReward('oktoberfest 2024');
        
  
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
            
            if(this.file != null){
                await this.updateImage(this.file);
            }

            Swal.fire({
                icon: 'success',
                title: 'Rider creado exitosamente.',
                showConfirmButton: false,
                timer: 2000
            });
            
            this.reloadPage();
  
        } else {
  
          Swal.fire({
            icon: 'error',
            title: 'Error al crear el Rider',
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

    private async getReward(name: string): Promise<RewardResponse | null> {
        try {
            return await firstValueFrom(this._adminservice.getRewardByName(
                name
            ));

        } catch (error: any) {
            console.error('Error al obtener el reward:', error);
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
            throw new Error('Error al asignar el vehículo al Rider.');
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

    onImageSelected(event: Event): void {
        const input = event.target as HTMLInputElement;
    
        if (input.files && input.files[0]) {
            this.file = input.files[0];
            const reader = new FileReader();
            
            reader.readAsDataURL(this.file);
        
            reader.onload = () => {
                this.image_path = reader.result as string;
            };
        }
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

                this.brand_quiz = quizzes.data[11];

                this.cards = quizzes.data.filter(quiz => quiz.group_name === 'event_preferences');

                this.cards.sort((a, b) => {
                    const numA = isNaN(Number(a.selected_value)) ? 0 : Number(a.selected_value);
                    const numB = isNaN(Number(b.selected_value)) ? 0 : Number(b.selected_value);
                    return numA - numB;
                });

                this.quiz_active = false;

                this.affinities_active = false;

            },
            error: () => {

            }
        });
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

    checkViewport() {
        this.isMobileView = window.innerWidth <= 768;
    }

    @HostListener('window:resize', ['$event'])
    onResize() {
        this.checkViewport();
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

            this.final = true;

            return await firstValueFrom(
                this._accountService.attatchQuizzes(customer_uuid,quiz_uuids,selected_values)
            );
        } catch (error: any) {
            console.error('Error al adjuntar respuesta de afinidades:', error);
            throw new Error('Error al adjuntar respuesta de afinidades.');
        }
    }

    reloadPage() {
        window.location.reload();
    }

    @HostListener('window:scroll', [])
    scrollToTop() {
      const element = document.getElementById('top');
      if (element) {
          element.scrollIntoView({ behavior: 'smooth' });
      }
    }

    openModal(text: string) {

        let content = '';

        if(text === 'Welcome Kit piloto'){
            content = `
            <strong>Incluye:</strong>
            <ul style='text-align:left';>
            <li>Kit experience</li>
            <li>Jersey conmemorativo OktoberFest Vecsa Hidalgo 2024.</li>
            <li>Acceso a la cena de sábado 19 de Octubre en Hotel GAMMA.</li>
            </ul>`;
        } else {
            content = `
            <strong>Incluye:</strong>
            <ul style='text-align:left';>
              <li>Jersey conmemorativo OktoberFest Vecsa Hidalgo 2024.</li>
              <li>Acceso a la cena de sábado 19 de Octubre en Hotel GAMMA.</li>
            </ul>`;
        }

        this.caption.nativeElement.innerHTML = content;
        this.modal.nativeElement.style.display = 'flex'; 
      }

    closeModal() {    
        
        this.modal.nativeElement.style.display = "none";
    }
}
