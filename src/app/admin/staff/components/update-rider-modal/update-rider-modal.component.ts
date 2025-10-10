
import { Component, Inject } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import {  FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import Swal from "sweetalert2";
import { UpdateVehicle } from '@interfaces/vehicle_data.interface';
import { AdminService } from '@services/admin.service';
import { GralResponse } from '@interfaces/admin.interfaces';
import { DetailsReward } from '@interfaces/auth.interface';
import { Router } from '@angular/router';
import {reload} from '@helpers/session.helper';

@Component({
    selector: 'app-update-rider-modal',
    templateUrl: './update-rider-modal.component.html',
    styleUrls: ['./update-rider-modal.component.css'],
    standalone: false
})

export class UpdateRiderModalComponent {

    public vehicle!: UpdateVehicle;
    public form!: FormGroup;
    public spinner = true;
    public files: File[] = [];
    public file2: File[]=[];
    public customer_reward_uuid !:string;

    disabled: Boolean = true;
    kmInitialUploaded: boolean = false;
    kmFinalUploaded: boolean = false;
    imagePromo: string | ArrayBuffer | null = null;
    imagePromo2: string | ArrayBuffer | null = null;

    //cambiar a false cuando se ejecute el actualizar
    public button: boolean = true;
    public kmIni: boolean = false;
    public kmFin: boolean = false;
    public mostrar: boolean = false;
    public mileage !: string;
    public mileageF !: string;
    public imgKm = 'assets/img/taza.png';
    public imgKmI !: string;
    public imgKmF !: string;
    public ini = true;
  
    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
        private _formBuilder: UntypedFormBuilder,
        private _vehicleService: AdminService,
        private _bottomSheetRef: MatBottomSheetRef<any>,
        private _router: Router
    ) {
        
        this.customer_reward_uuid = data.customer_reward_uuid;
        this.getImagesPoints(this.customer_reward_uuid);

        this.mileage = data.km;
        this.mileageF = data.kmf;
        
        if(data.km != null){
            this.kmIni = true;
        }
        if(data.kmf != null){
            this.kmFin = true;
        }
        // if(this.kmIni != true || this.kmFin != true){
        //     this.mostrar = true;
        // }
        if(this.kmFin != true){
            this.mostrar = true;
        }
        this.formInit();
    }

    ngOnInit(): void {
        if(this.kmIni){
            this.form.get('mileage')?.disable();
        }
        if(this.kmIni && this.kmFin){
            this.form.get('mileage')?.disable();
            this.form.get('mileage_fin')?.disable();
        }
        this.cubetime();
        if(this.mileageF!= null){
            setTimeout(() => {
                this.form.patchValue({
                    mileage_fin: this.mileageF,
                });
            }, 500);
        }
    }

    public getImagesPoints(customer:string){
        this._vehicleService.rewardDetail(customer)
        .subscribe({
            next: ( response : DetailsReward) =>{

                if(response.data.points[0].images[0].image_path != null){
                    this.imgKmI = response.data.points[0].images[0].image_path;
                }
                if(response.data.points[0].images[1].image_path != null){
                    this.imgKmF = response.data.points[0].images[1].image_path;
                }
            },
            error: (error:any) => {
                // this._bottomSheetRef.dismiss(
                //     {reload: false}
                // );
                // reload(error, this._router);
                Swal.fire({
                    icon: 'error',
                    title: 'Oupps..',
                    text: 'Al parecer ocurrio un error' + error.error.message,
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838',
                    timer: 3500
            });
              }
        })
    }

    private formInit() {
        this.form = this._formBuilder.group({
            mileage:        ['', [Validators.required]],
            mileage_fin: ['', [Validators.required]],
            image1: ['', Validators.required],
            image2: [ '' , Validators.required],
        })
    }

    onSubmit(km:boolean) {
    this._vehicleService.updateKm( this.form.get('mileage')?.value, this.files, this.form.get('mileage_fin')?.value,this.file2, this.customer_reward_uuid, km)
        .subscribe({
            next: ( response :GralResponse) => {
                Swal.fire({                    
                    icon: 'success',
                    title: 'Rider actualizado con exito',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                });

                this._bottomSheetRef.dismiss(
                    {reload: true}
                );
            },
            error: (error) => {
                // this._bottomSheetRef.dismiss(
                //     {reload: false}
                // );
                // reload(error, this._router);
                Swal.fire({
                    icon: 'error',
                    title: 'Oupps..',
                    text: 'Al parecer ocurrio un error' + error.error.message,
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838',
                    timer: 3500
            });
            }
        });

        this.button = false;
    }

    get kmInvalid(){
        return this.form.get('mileage')!.invalid && (this.form.get('mileage')!.dirty);
    }

    public close():void {
        this._bottomSheetRef.dismiss();
    }

    cubetime () : void {
        setTimeout(() => {
            this.spinner = false;
        }, 500);
    }

    assignImagePromo( event: Event){
        const input = event.target as HTMLInputElement;
        if (input.files && input.files.length) {
        
            this.kmInitialUploaded = true;
            
            const element = event.currentTarget as HTMLInputElement;
            
            let fileList: FileList | null = element.files;
            
            if (fileList) {
                this.files = Array.from(fileList);
                if (this.files.length > 0) {
                this.disabled = false;
                }else{
                this.disabled = true;
                }
            }
            
            const file = (event.target as HTMLInputElement).files?.[0];
            
            if(file){
                const reader = new FileReader();
                reader.onload = (event) => {
                const result = event.target?.result;
                if (result){
                    this.imagePromo = result;
                }
                };
                reader.readAsDataURL(file);
            }
        }
    }

    assignImagePromo2( event: Event){
        
        const input = event.target as HTMLInputElement;
        
        if (input.files && input.files.length) {
        
            this.kmFinalUploaded = true;
            
            const element = event.currentTarget as HTMLInputElement;
            
            let fileList: FileList | null = element.files;
            
            if (fileList) {
                this.file2 = Array.from(fileList);
                if (this.file2.length > 0) {
                this.disabled = false;
                }else{
                this.disabled = true;
                }
            }
            
            //código para la visaulización de la imagen cargada
            const file = (event.target as HTMLInputElement).files?.[0];
            
            if(file){
                const reader = new FileReader();
                reader.onload = (event) => {
                const result = event.target?.result;
                if (result){
                    this.imagePromo2 = result;
                }
                };
                reader.readAsDataURL(file);
            }
        }
    }
}
