import { Campaign } from '@interfaces/admin.interfaces';
import { Component, OnInit } from '@angular/core';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { AbstractControl, FormControl, FormGroup, UntypedFormBuilder, ValidatorFn, Validators } from '@angular/forms';
import { VehicleService } from '@services/vehicle.service';
import Swal from "sweetalert2";
import { MatChipInputEvent } from '@angular/material/chips';
import { Observable, of } from 'rxjs';
import { map, startWith } from 'rxjs/operators';
import { MatAutocompleteSelectedEvent } from '@angular/material/autocomplete';

import {UpdateVehicle,  FullDetailResponse, BrandsResponse, Brand, Line, LinesResponse, Model, Body, ModelsResponse, VersionsResponse, Version, BodiesResponse, VehicleUpdateResponse, GralResponse, VehicleStoreResponse} from '@interfaces/vehicle_data.interface';
import { GetcampaingResponse } from '@interfaces/admin.interfaces';
//import { CampaingService } from 'src/app/admin/gestor/services/campaing.service';
import { AdminService } from '@services/admin.service';

import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';


@Component({
    selector: 'app-store-vehicle',
    templateUrl: './store-vehicle.component.html',
    styleUrls: ['./store-vehicle.component.css'],
    standalone: false
})
export class StoreVehicleComponent  implements OnInit{
  
  
  public vehicle_uuid: string = '';
  public vehicle!: UpdateVehicle;
  public form!: FormGroup;

  public button: boolean = false;

  public camps: string[] = [];
  public id_camp: string[] = [];
  public responseCamp: Campaign[] = [];

  brandControl = new FormControl();
  public brands:Brand[] = [];
  filteredBrands: Observable<Brand[]> = of([]);

  campaignControl = new FormControl();
  public campaigns: Campaign[] = [];
  filteredCampaigns: Observable<Campaign[]> = of([]);

  lineControl = new FormControl();
  public lines:Line[] = [];
  filteredLines: Observable<Line[]> = of([]);

  modelControl = new FormControl();
  public models:Model[] = [];
  filteredModels: Observable<Model[]> = of([]);

  versionControl = new FormControl();
  public versions:Version[] = [];
  filteredVersions: Observable<Version[]> = of([]);

  bodyControl = new FormControl();
  public bodies:Body[] = [];
  filteredBodies: Observable<Body[]> = of([]);

  constructor(
    private _formBuilder: UntypedFormBuilder,
    private _vehicleService:VehicleService,
    private _campaignService:AdminService,
    private _bottomSheetRef: MatBottomSheetRef<any>,
    private _router: Router
  ) {
      this.formInit();
  }

  ngOnInit(): void {
    this.InitForm();
  }

  private filters(): void {

    this.filteredCampaigns = this.campaignControl.valueChanges.pipe(
      startWith(''),
      map(value => this._filter(value, this.campaigns)),
    )

    this.filteredBrands = this.brandControl.valueChanges.pipe(
      startWith(''),
      map(value => this._filter(value, this.brands))
    );

    this.filteredLines = this.lineControl.valueChanges.pipe(
      startWith(''),
      map(value => this._filter(value, this.lines))
    );

    this.filteredModels = this.modelControl.valueChanges.pipe(
      startWith(''),
      map(value => this._filter(value, this.models))
    );

    this.filteredVersions = this.versionControl.valueChanges.pipe(
      startWith(''),
      map(value => this._filter(value, this.versions))
    );

    this.filteredBodies = this.bodyControl.valueChanges.pipe(
      startWith(''),
      map(value => this._filter(value, this.bodies))
    );
  }

  private _filter<T extends { name: string }>(value: string, options: T[]): T[] {
    const filterValue = value.toLowerCase();
    return options.filter(option => option.name.toLowerCase().includes(filterValue));
  }

  public add( event: MatChipInputEvent ): void {
    const value = (event.value || '').trim();
    if (value) {
          this.camps.push(value);
          this.campaignControl.setValue(null);
          event.chipInput!.clear();
    }
    
  }

  public remove( event: string): void{
    let index = this.camps.indexOf(event);
    this.camps.splice(index, 1);
    this.id_camp.splice(index, 1);
  }

  onBrandSelected(event: MatAutocompleteSelectedEvent): void {
    const selectedBrand = event.option.value;
    this.form.patchValue({ brand: selectedBrand });
    this.getLines(selectedBrand).then(() => {
      this.filters();
    });
  }

  onCampaignSelected(event: MatAutocompleteSelectedEvent): void{
    this.camps.push(event.option.value);
    this.id_camp.push(event.option.id);
  }

  onLineSelected(event: MatAutocompleteSelectedEvent): void {
    const selectedLine = event.option.value;
    this.form.patchValue({ line: selectedLine });
    this.getModels(selectedLine).then(() => {
      this.filters();
    });
  }

  onModelSelected(event: MatAutocompleteSelectedEvent): void {
    const selectedModel = event.option.value;
    this.form.patchValue({ model: selectedModel.name });
    this.form.patchValue({ year: selectedModel.year });
    this.getVersions(selectedModel).then(() => {
      this.filters();
    });
  }
  
  onVersionSelected(event: MatAutocompleteSelectedEvent): void {
    const selectedVersion = event.option.value;
    this.form.patchValue({ version: selectedVersion });
    this.filters();
  }

  onBodySelected(event: MatAutocompleteSelectedEvent): void {
    const selectedBody = event.option.value;
    this.form.patchValue({ body: selectedBody });
    this.filters();
  }

  public getBrands(): void {
    this._vehicleService.getBrands()
      .subscribe({
        next: (brandsResponse: BrandsResponse) => {
          this.brands = brandsResponse.data?.vehicle_brands ?? [];
          this.filters();
        }
      });
  }

  public getCampaigns(): void{
    this._campaignService.getCampaing()
    .subscribe({
     next: (campaignResponse: GetcampaingResponse) => {
      this.campaigns = campaignResponse.data?.campaigns ?? [];
      this.filters();
     }
    })
  }

  public getLines(brand: string): Promise<void> {
    return new Promise<void>((resolve, reject) => {
      this._vehicleService.getLines(brand)
        .subscribe({
          next: (linesResponse: LinesResponse) => {
            this.lines = linesResponse.data.brand_lines;
            resolve();
          },
          error: (error) => reject(error)
        });
    });
  }

  public getModels(line: string): Promise<void> {
    return new Promise<void>((resolve, reject) => {
      this._vehicleService.getModels(line)
        .subscribe({
          next: (modelsResponse: ModelsResponse) => {
            this.models = modelsResponse.data.line_models;
            resolve();
          },
          error: (error) => reject(error)
        });
    });
  }

  public getVersions(model: string): Promise<void> {
    return new Promise<void>((resolve, reject) => {
      this._vehicleService.getVersions(model)
        .subscribe({
          next: (versionsResponse: VersionsResponse) => {
            this.versions = versionsResponse.data.model_versions;
            resolve();
          },
          error: (error) => reject(error)
        });
    });
  }

  public getBodies(): Promise<void> {
    return new Promise<void>((resolve, reject) => {
      this._vehicleService.getBodies()
        .subscribe({
          next: (bodiesResponse: BodiesResponse) => {
            this.bodies = bodiesResponse.data.vehicle_bodies;
            this.filters();
            resolve();
          },
          error: (error) => reject(error)
        });
    });
  }

  private formInit() {
      this.form = this._formBuilder.group({
          name:           ['', [Validators.required]],
          description:    ['', [Validators.required]],
          vin:            ['', [Validators.required]],
          purchase_date:  ['', [Validators.required]],
          sale_price:     ['', [Validators.required]],
          list_price:     ['', [Validators.required]],
          mileage:        ['', [Validators.required]],
          type:           ['', [Validators.required]],
          category:       ['', [Validators.required]],
          cylinders:      ['', [Validators.required]],
          interior_color: ['', [Validators.required]],
          exterior_color: ['', [Validators.required]],
          transmission:   ['', [Validators.required]],
          fuel_type:      ['', [Validators.required]],
          page_status:    ['', [Validators.required]],
          brand:          ['', [Validators.required]],
          line:           ['', [Validators.required]],
          year:           ['', [Validators.required]],
          model:          ['', [Validators.required]],
          version:        ['', [Validators.required]],
          body:           ['', [Validators.required]],
          dealership_name:['', [Validators.required]],
          location:       ['', [Validators.required]],
          offer_price:    ['', [this.offerPriceValidator('sale_price')]],
          campaign_2:       [''],
      });
  }
  /**
   * Alta de vehículo: solo catálogos base. Líneas/modelos/versiones se cargan al elegir marca/línea/modelo.
   * (Antes se llamaba getLines(this.getBrands.name) → URL .../getBrands y 404; y this.vehicle.campaigns rompía sin vehículo.)
   */
  public InitForm(): void {
    this.camps = [];
    this.id_camp = [];
    this.getBrands();
    void this.getBodies().catch(() => {});
    this.getCampaigns();
  }

  onSubmit() {
    this._vehicleService.storeVehicle( this.form.value)
    .subscribe({
      next: ( storeVehicleResponse :VehicleStoreResponse) => {
        this.vehicle_uuid = storeVehicleResponse.data.uuid;
        if(this.id_camp.length > 0){
          this._vehicleService.attachVehicle(this.id_camp, this.vehicle_uuid)
          .subscribe({
            next: ( relationVehicleResponse :GralResponse) => {
                Swal.fire({                    
                  icon: 'success',
                  title: 'Vehículo relacionado con campañacon exitosamente',
                  text: '',
                  showConfirmButton: false,
                  timer: 2000
                });
    
                this._bottomSheetRef.dismiss(
                  {reload: true}
                );
            },
            error: (error) => {
              reload(error, this._router);
            }
          });
        }
          Swal.fire({                    
            icon: 'success',
            title: 'Vehículo actualizado con exito',
            text: storeVehicleResponse.data.name,
            showConfirmButton: false,
            timer: 2000
          });

          this._bottomSheetRef.dismiss(
            {reload: true}
          );
      },
      error: (error) => {
        reload(error, this._router);
      }
    });

    this.button = false;
  }

  private offerPriceValidator(salePriceControlName: string): ValidatorFn {
    return (control: AbstractControl): { [key: string]: any } | null => {

      const salePrice = control.parent?.get(salePriceControlName)?.value;
      const offerPrice = control.value;

      if (offerPrice === null || offerPrice === undefined || offerPrice === '') {
          return null;
      }

      // Verificar si el valor es un número válido
      if (isNaN(offerPrice) || !isFinite(offerPrice)) {
          return { 'notANumber': true };
      }

      // Verificar si el valor es mayor a cero
      if (offerPrice <= 0) {
          return { 'lessThanOrEqualToZero': true };
      }

      // Verificar si el valor es mayor o igual al sale_price
      if (offerPrice >= salePrice) {
          return { 'greaterThanSalePrice': true };
      }
  
      return null;
    };
  }

  get nameInvalid() {
    return this.form.get('name')!.invalid && (this.form.get('name')!.dirty);
  }

  get descriptionInvalid() {
    return this.form.get('description')!.invalid && (this.form.get('description')!.dirty);
  }

  get locationInvalid() {
    return this.form.get('location')!.invalid && (this.form.get('location')!.dirty);
  }

  get yearModelInvalid() {
    return this.form.get('year')!.invalid && (this.form.get('year')!.dirty);
  }

  get purchaseDateInvalid() {
    return this.form.get('purchase_date')!.invalid && (this.form.get('purchase_date')!.dirty);
  }

  get listPriceInvalid() {
    return this.form.get('list_price')!.invalid && (this.form.get('list_price')!.dirty);
  }

  get salePriceInvalid() {
    return this.form.get('sale_price')!.invalid && (this.form.get('sale_price')!.dirty);
  }

  get cylindersInvalid(){
    return this.form.get('cylinders')!.invalid && (this.form.get('cylinders')!.dirty);
  }

  get colorIntInvalid(){
    return this.form.get('interior_color')!.invalid && (this.form.get('interior_color')!.dirty);
  }

  get colorExtInvalid(){
    return this.form.get('exterior_color')!.invalid && (this.form.get('exterior_color')!.dirty);
  }

  get kmInvalid(){
    return this.form.get('mileage')!.invalid && (this.form.get('mileage')!.dirty);
  }

  get priceOfferInvalid(){
    return this.form.get('offer_price')!.invalid && (this.form.get('offer_price')!.dirty);
  }

  get vinInvalid(){
    return this.form.get('vin')!.invalid && (this.form.get('vin')!.dirty);
  }

  get brandInvalid(){
    return this.form.get('brand')!.invalid && (this.form.get('brand')!.dirty);
  }

  get lineInvalid(){
    return this.form.get('line')!.invalid && (this.form.get('line')!.dirty);
  }

  get modelInvalid(){
    return this.form.get('model')!.invalid && (this.form.get('model')!.dirty);
  }

  get versionInvalid(){
    return this.form.get('version')!.invalid && (this.form.get('version')!.dirty);
  }

  get bodyInvalid(){
    return this.form.get('body')!.invalid && (this.form.get('body')!.dirty);
  }

  get dealershipInvalid(){
    return this.form.get('dealership_name')!.invalid && (this.form.get('dealership_name')!.dirty);
  }

  public close():void {
    this._bottomSheetRef.dismiss();
  }

  

}
