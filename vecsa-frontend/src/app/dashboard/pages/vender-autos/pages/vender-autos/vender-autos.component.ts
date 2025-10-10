import { Component, OnInit } from '@angular/core';
import { FormControl, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { MatAutocompleteSelectedEvent } from '@angular/material/autocomplete';
import { Dealership, DealerShipResponse } from '@interfaces/admin.interfaces';
import { RegisterResponse } from '@interfaces/auth.interface';
import { Brand, BrandsResponse, Model, ModelsResponse } from '@interfaces/vehicle_data.interface';
import { AdminService } from '@services/admin.service';
import { AppointmentService } from '@services/appointment.service';
import { VehicleService } from '@services/vehicle.service';
import { firstValueFrom, map, Observable, of, startWith } from 'rxjs';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-vender-autos',
    templateUrl: './vender-autos.component.html',
    styleUrls: ['./vender-autos.component.css'],
    standalone: false
})
export class VenderAutosComponent implements OnInit {

  public form!: UntypedFormGroup;
  public years: number[] = [];
  public minDate: Date = new Date();
  public maxDate: Date = new Date(new Date().setDate( new Date().getDate() + 365));
  public spinner: boolean = false;

  public sucursales!: Dealership[] ;

  modelControl = new FormControl();
  public models: Model[] = [];
  filteredModels: Observable<Model[]> = of([]);

  brandControl = new FormControl();
  public brands: Brand[] = [];
  filteredBrands: Observable<Brand[]> = of([]);

  constructor(
    private _formBuilder: UntypedFormBuilder,
    private _vehicleService: VehicleService,
    private _adminservice: AdminService,
    private _appointmentService: AppointmentService
  ) {
    this.createFormInit();
  }

  ngOnInit(): void {
    // Allowed years
    let year = new Date().getFullYear()+1;
    for (let i = year; i > year-23; i--) {
        this.years.push(i);
    }
    this.getBrands();
   
    this.getDealerShip();
  }

  get nameInvalid(){
    return this.form.get('name')?.invalid && (this.form.get('name')?.dirty || this.form.get('name')?.touched);
  }
  get surnameInvalid(){
    return this.form.get('last_name')?.invalid && (this.form.get('last_name')?.dirty || this.form.get('last_name')?.touched);
  }
  get emailInvalid(){
    return this.form.get('email')?.invalid && (this.form.get('email')?.dirty || this.form.get('email')?.touched);
  }
  get phoneLength(){
    let phone = this.form.get('phone_1')?.value;
    return this.form.get('phone_1')?.touched && (phone.length < 10 || phone.length > 100);
  }
  get brandInvalid(){
    return this.form.get('brand_name')?.invalid && (this.form.get('brand_name')?.dirty || this.form.get('brand_name')?.touched);
  }
  get modelInvalid(){
    return this.form.get('model_name')?.invalid && (this.form.get('model_name')?.dirty || this.form.get('model_name')?.touched);
  }
  get yearInvalid(){
    return this.form.get('year')?.invalid && (this.form.get('year')?.dirty || this.form.get('year')?.touched);
  }
  get mileageInvalid(){
    return this.form.get('mileage')?.invalid && (this.form.get('mileage')?.dirty || this.form.get('mileage')?.touched);
  }
  get dateInvalid(){
    return this.form.get('scheduled_date')?.invalid && (this.form.get('scheduled_date')?.dirty || this.form.get('scheduled_date')?.touched);
  }
  get hourInvalid(){
    return this.form.get('hour')?.invalid && (this.form.get('hour')?.dirty || this.form.get('hour')?.touched);
  }
  get dealership_nameInvalid() {
    return this.form.get('dealership_name')!.invalid && (this.form.get('dealership_name')!.dirty || this.form.get('dealership_name')!.touched);
  }

  private createFormInit() {
    this.form = this._formBuilder.group({
      name:           ['', [Validators.required, Validators.pattern("[a-zA-ZñÑ ]+")]],
      last_name:      ['', [Validators.required, Validators.pattern("[a-zA-ZñÑ ]+")]],
      email:          ['', [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],
      phone_1:          ['', [Validators.required, Validators.pattern("[0-9]+"), Validators.minLength(10), Validators.maxLength(10)]],
      brand_name:     ['', Validators.required],
      model_name:     ['', Validators.required],
      year:           ['', Validators.required],
      mileage:        ['0', [Validators.required, Validators.pattern("[0-9]{1,10}"), Validators.minLength(1), Validators.maxLength(10)]],
      scheduled_date: ['', Validators.required],
      hour:           ['', Validators.required],
      dealership_name:[''],
      checkbox:       [false, [Validators.required]],
      type:           [''],
      customer_uuid:  ['']
    });
  }

  /**
     * Filter days
     */
  public myFilter = (d: Date | null): boolean => {
    const day = (d || new Date()).getDay();
    // Prevent Saturday and Sunday from being selected.
    return day !== -1 && day !== 7;
  }

  public getBrands(): void {
    this._vehicleService.getBrands()
      .subscribe({
        next: (brandResponse: BrandsResponse) => {
          this.brands = brandResponse.data.vehicle_brands;
          this.filters();
        }
      });
  }

  public maxLengthCheck(object: any) {   
    if (object.value.length > object.maxLength) {
    object.value = object.value.slice(0, object.maxLength)
    }
  }

  private filters(): void {
    this.filteredBrands = this.brandControl.valueChanges.pipe(
        startWith(''),
        map(value => this._filter(value, this.brands))
    );
    this.filteredModels = this.modelControl.valueChanges.pipe(
        startWith(''),
        map(value => this._filter(value, this.models))
    );
}

public getDealerShip(){
  this._adminservice.getDealerships()
  .subscribe({
    next: (response: DealerShipResponse) =>{
      this.sucursales = response.data;
    }
  })
}

private _filter<T extends { name: string }>(value: string, options: T[]): T[] {
    const filterValue = value.toLowerCase();
    return options.filter(option => option.name.toLowerCase().includes(filterValue));
}

  public onBrandSelected(event: MatAutocompleteSelectedEvent): void {
    const selectedBrand = event.option.value;
    
    this.form.patchValue({ brand: selectedBrand });
    // this.filters();
    this._vehicleService.getModelsByBrand(selectedBrand)
        .subscribe({
            next: (modelResponse: ModelsResponse) => {
                this.models = modelResponse.data.line_models;
                this.filters();
            }
        });
  }

  public onModelSelected(event: MatAutocompleteSelectedEvent): void {
    const selectedModel = event.option.value;
    this.form.patchValue({ model: selectedModel});
    this.filters();
  }

  public async onSubmit() {
    try {
      this.spinner = true;
      
      const scheduledDate = this.form.get('scheduled_date')?.value;
      const hour = this.form.get('hour')?.value;
      const dealership_name = this.form.get('dealership_name')?.value;
      

      const combinedDate = new Date(scheduledDate);
      const year = combinedDate.getFullYear();
      const month = (combinedDate.getMonth() + 1).toString().padStart(2, '0'); // Mes en formato 'MM'
      const day = combinedDate.getDate().toString().padStart(2, '0');

      const formattedDate = `${year}-${month}-${day} ${hour}`;

      const response = await this.createValuationClient();

      if (response && response.data && response.data.profile && response.data.profile.uuid) {
        this.form.patchValue({ type: 'valuation', customer_uuid: response.data.profile.uuid, scheduled_date: formattedDate, dealership_name: dealership_name });
        
        await this.createValuationAppointment();

        Swal.fire({
          icon: 'success',
          title: 'Cita creada exitosamente.',
          timer: 2000
        });

        this.reloadPage();

      }
      
    } catch (error: any) {
      Swal.fire({
        icon: 'error',
        title: 'Lo sentimos, hubo un error',
        text: 'Hubo un problema al precesar la solicitud, inténtelo más tarde.' + error
      });
    }
  }

  private async createValuationClient(): Promise<RegisterResponse | null> {
    try {
        return await firstValueFrom(this._adminservice.setRiders(
            this.form.value
        ));
    } catch (error: any) {
        console.error('Error al crear el cliente de valuación:', error);
        throw new Error('Error en la creación del Cliente de valuación.');
    }
  }

  private async createValuationAppointment(): Promise<void> {
    
    try {
        await firstValueFrom(
            // this._appointmentService.setAppointmentValuation(this.form.value)
            this._appointmentService.setExternalAppointmentValuation(this.form.value)
        );
    } catch(error: any) {
        console.error('Error al crear la cita de valuación:', error);
        throw new Error('Error al crear la cita de valuación.')
    }
  }

  reloadPage() {
    window.location.reload();
  }


}
