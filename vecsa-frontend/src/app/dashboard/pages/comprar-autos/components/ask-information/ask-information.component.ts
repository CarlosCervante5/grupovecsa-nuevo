import { Component, Inject, OnInit } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MatBottomSheetRef, MAT_BOTTOM_SHEET_DATA } from '@angular/material/bottom-sheet';
import { Vehicle } from '@interfaces/vehicle_data.interface';
//import { Vehicle } from './../../interfaces/detail/vehicle_data.interface'; 
// Animaciones
import Swal from 'sweetalert2';

// Services
import { DetailService } from '../../services/detail/detail.service';

@Component({
    selector: 'app-ask-information',
    templateUrl: './ask-information.component.html',
    styles: ['.cp { cursor: pointer; }'
    ],
    standalone: false
})

export class AskInformationComponent implements OnInit {

  // References
  public spinner: boolean = false;
  public form!: UntypedFormGroup;
  public vehicle!: any;
  public vehicles!: Vehicle[];
  public total:number = 0;

  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
    private _formBuilder: UntypedFormBuilder,
    private _detailService: DetailService,
    private _bottomSheetRef: MatBottomSheetRef<AskInformationComponent>
  ) { 
    // Create form
    this.createForm();
  }

  ngOnInit(): void {
    this.vehicle = this.data;
    this.getVehicles();
  }

  /**
   * Getters Inputs Check
   */
  get nameInvalid() {
    return this.form.get('name')!.invalid && (this.form.get('name')!.dirty || this.form.get('name')!.touched);
  }

  get lastnameInvalid() {
    return this.form.get('lastname')!.invalid && (this.form.get('lastname')!.dirty || this.form.get('lastname')!.touched);
  }

  get emailInvalid() {
    return this.form.get('email')!.invalid && (this.form.get('email')!.dirty || this.form.get('email')!.touched);
  }

  get phoneInvalid() {
    return this.form.get('phone_1')!.invalid && (this.form.get('phone_1')!.dirty || this.form.get('phone_1')!.touched);
  }

  /**
   * Form Initialization
   */
  private createForm() {
    this.form = this._formBuilder.group({
      name: ['', [Validators.required, Validators.pattern("[a-zA-Z ]+")]],
      lastname: ['', [Validators.required, Validators.pattern("[a-zA-Z ]+")]],
      email: ['', [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],      
      phone_1: ['', [Validators.required, Validators.pattern("[0-9]+"), Validators.minLength(10), Validators.maxLength(10)]],      
      dealership_name: [''],
      auto: [''],
      brand: [''],
      vehicles_uuid: this._formBuilder.array([]),
      checkbox: [false, Validators.required]
    });
  }

  /**
   * Form Information
   */
  public onSubmit() {
    
    let vehicles_uuid: string[] = [];

    // Cambiar el estado del spinner a true
    this.spinner = true;

    // Recopilar UUIDs de vehículos
    this.vehicles.forEach(vehicle => vehicles_uuid.push(vehicle.uuid));

    // Establecer datos del formulario
    this.form.patchValue({
      vehicle_name: `${this.data.vehicle} ${this.data.year}`,
      dealership_name: this.data.dealership_name,
      brand: this.data.brand,
    });

    // Establecer el control de vehicles_uuid
    this.form.setControl('vehicles_uuid', this._formBuilder.array(vehicles_uuid));

    // Enviar solicitud
    this._detailService.generateLead(this.form.value).subscribe({
      next: (response: any) => {
        this.handleResponse(true, 'Solicitud de información enviada correctamente, nos pondremos en contacto con usted lo antes posible.');
      },
      error: (err: any) => {
        this.handleResponse(false, 'Ocurrió un error al enviar la solicitud, intenta más tarde.');
      }
    });
    
  }


  private handleResponse(success: boolean, message: string): void {
    this.spinner = false;
    Swal.fire({
      icon: success ? 'success' : 'error',
      title: success ? 'Envío correcto' : '¡Oops!',
      text: message,
      showConfirmButton: true,
      confirmButtonColor: '#EEB838',
      timer: 3500
    });
    this.openLink();
  }


  /**
   * Help function, close and open when clicked
   */
  public openLink(): void {
    this._bottomSheetRef.dismiss({data: 'aqui'});
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

  public addToList(){    
    let vehicles: Vehicle[] = JSON.parse(localStorage.getItem('vehicles')!) != null ? JSON.parse(localStorage.getItem('vehicles')!) : [];
    let exists = vehicles.find( vehicle => vehicle.uuid == this.vehicle.uuid );    
  
    if( exists === undefined ){
      vehicles.push(this.vehicle);
    }    

    localStorage.setItem("vehicles", JSON.stringify(vehicles));           
  }

  public getVehicles(): void {
    this.vehicles = JSON.parse(localStorage.getItem('vehicles')!) != null ? JSON.parse(localStorage.getItem('vehicles')!) : [];        
    this.total = 0;
    this.vehicles.map(

      vehicle => ( vehicle.offer_price ? this.total+=vehicle.offer_price : this.total+=vehicle.sale_price )

    );
  }

  public deleteElementToList(index:number):void {
    this.vehicles.splice(index, 1);
    localStorage.setItem("vehicles", JSON.stringify(this.vehicles)); 
    this.getVehicles();  
    if(this.vehicles.length === 0){
      this.openLink();
    }    
  }

  close() {
    this._bottomSheetRef.dismiss();
  }
}
