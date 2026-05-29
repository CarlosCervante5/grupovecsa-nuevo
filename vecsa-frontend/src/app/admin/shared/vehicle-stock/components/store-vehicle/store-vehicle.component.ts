import { Campaign } from '@interfaces/admin.interfaces';
import { Component, OnInit } from '@angular/core';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { MatDialog } from '@angular/material/dialog';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { AbstractControl, FormControl, FormGroup, UntypedFormBuilder, ValidatorFn, Validators } from '@angular/forms';
import { VehicleService } from '@services/vehicle.service';
import { ImagesService } from '@services/images.service';
import Swal from "sweetalert2";
import { MatChipInputEvent } from '@angular/material/chips';
import { Observable, of } from 'rxjs';
import { map, startWith } from 'rxjs/operators';
import { MatAutocompleteSelectedEvent } from '@angular/material/autocomplete';
import { ImageAiDialogComponent } from 'src/app/shared/components/image-ai-dialog/image-ai-dialog.component';
import { ImageOrder } from 'src/app/dashboard/pages/comprar-autos/interfaces/detail/vehicle_data.interface';

import { BrandsResponse, Brand, Line, LinesResponse, Model, Body, ModelsResponse, VersionsResponse, Version, BodiesResponse, VehicleStoreResponse, FullDetailResponse } from '@interfaces/vehicle_data.interface';
import { Dealership, GetcampaingResponse } from '@interfaces/admin.interfaces';
import { VehicleDealershipFormMode } from '../../helpers/vehicle-dealership-by-user.helper';
import { VehicleDealershipFormController } from '../../helpers/vehicle-dealership-form.controller';
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
  public form!: FormGroup;

  public button: boolean = false;
  public vehicleSaved = false;
  public listNeedsReload = false;

  readonly dealershipUi: VehicleDealershipFormController;

  get dealershipMode(): VehicleDealershipFormMode {
    return this.dealershipUi.dealershipMode;
  }

  get selectableDealerships(): Dealership[] {
    return this.dealershipUi.selectableDealerships;
  }

  get dealershipsLoading(): boolean {
    return this.dealershipUi.dealershipsLoading;
  }

  get locationFieldReadonly(): boolean {
    return this.dealershipUi.locationFieldReadonly;
  }

  vehicleImages: ImageOrder[] = [];
  photoFiles: File[] = [];
  photoUploadDisabled = true;
  photoUploading = false;
  imagesChanged = false;

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
    private _router: Router,
    private _imagesService: ImagesService,
    private _dialog: MatDialog,
  ) {
      this.dealershipUi = new VehicleDealershipFormController(
        () => this.form,
        this._campaignService,
        this._router,
      );
      this.formInit();
  }

  ngOnInit(): void {
    this.InitForm();
    this.dealershipUi.applyForSignedInUser();
  }

  onDealershipSelected(event: Event): void {
    this.dealershipUi.onDealershipSelected(event);
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

  private _filter<T extends { name: string }>(value: string | { name?: string } | null, options: T[]): T[] {
    const text = typeof value === 'string' ? value : value?.name ?? '';
    const filterValue = text.toLowerCase();
    return options.filter((option) => option.name.toLowerCase().includes(filterValue));
  }

  public add(event: MatChipInputEvent): void {
    const value = (event.value || '').trim();
    if (value) {
      this.pushCampaign(value);
      this.campaignControl.setValue(null);
      event.chipInput?.clear();
    }
  }

  onCampaignEnter(event: Event): void {
    event.preventDefault();
    const input = event.target as HTMLInputElement;
    const value = (input?.value || '').trim();
    if (value) {
      this.pushCampaign(value);
      this.form.patchValue({ campaign_2: '' });
      input.value = '';
    }
  }

  private pushCampaign(name: string): void {
    if (!this.camps.includes(name)) {
      this.camps.push(name);
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

  onCampaignSelected(event: MatAutocompleteSelectedEvent): void {
    this.pushCampaign(event.option.value);
    this.id_camp.push(event.option.id);
    this.form.patchValue({ campaign_2: '' });
  }

  onLineSelected(event: MatAutocompleteSelectedEvent): void {
    const selectedLine = event.option.value;
    this.form.patchValue({ line: selectedLine });
    this.getModels(selectedLine).then(() => {
      this.filters();
    });
  }

  onModelSelected(event: MatAutocompleteSelectedEvent): void {
    const selectedName = String(event.option.value ?? '');
    const selectedModel = this.models.find((m) => m.name === selectedName);
    this.form.patchValue({
      model: selectedName,
      year: selectedModel?.year ?? this.form.get('year')?.value,
    });
    this.form.get('model')?.markAsDirty();
    this.form.get('model')?.updateValueAndValidity();
    this.getVersions(selectedName).then(() => {
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

  get canManagePhotos(): boolean {
    return !!this.vehicle_uuid;
  }

  private refreshVehicleImages(): void {
    if (!this.vehicle_uuid) {
      return;
    }
    this._vehicleService.getVehicle(this.vehicle_uuid).subscribe({
      next: (detailResponse: FullDetailResponse) => {
        const imgs = detailResponse.data?.images ?? [];
        this.vehicleImages = imgs.map((img) => ({
          id: img.uuid ?? '',
          sort_id: String(img.sort_id ?? ''),
          path: img.service_image_url,
          path_public: img.service_public_id ?? '',
          external_website: 'no',
          selected: false,
        }));
        this.imagesChanged = true;
      },
      error: (err) => reload(err, this._router),
    });
  }

  dropPhoto(event: CdkDragDrop<ImageOrder[]>): void {
    const selected = this.vehicleImages.filter((image) => image.selected);
    if (selected.length > 0) {
      const remaining = this.vehicleImages.filter((image) => !image.selected);
      const insertIndex = event.currentIndex;
      this.vehicleImages = [
        ...remaining.slice(0, insertIndex),
        ...selected,
        ...remaining.slice(insertIndex),
      ];
    } else {
      moveItemInArray(this.vehicleImages, event.previousIndex, event.currentIndex);
    }
  }

  savePhotoOrder(): void {
    this._imagesService.changeOrder(this.vehicleImages).subscribe({
      next: (resp) => {
        Swal.fire({
          icon: 'success',
          title: resp.message,
          showConfirmButton: false,
          timer: 2000,
        });
        this.imagesChanged = true;
        this.listNeedsReload = true;
      },
      error: (err) => reload(err, this._router),
    });
  }

  openPhotoAi(image: ImageOrder, index: number): void {
    const ref = this._dialog.open(ImageAiDialogComponent, {
      width: '640px',
      maxWidth: '95vw',
      data: {
        sourceUrl: image.path,
        targetType: 'vehicle_image',
        targetUuid: image.id,
        title: 'Mejorar foto del vehículo',
      },
    });
    ref.afterClosed().subscribe((result) => {
      if (result?.saved && result.imageUrl) {
        this.vehicleImages[index].path = result.imageUrl;
        this.imagesChanged = true;
        this.listNeedsReload = true;
        Swal.fire({
          icon: 'success',
          title: 'Imagen actualizada con IA',
          showConfirmButton: false,
          timer: 2200,
        });
      }
    });
  }

  deletePhoto(vehicleImageUuid: string, index: number): void {
    Swal.fire({
      title: '¿Eliminar esta imagen?',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#1c69d4',
    }).then((result) => {
      if (!result.isConfirmed) {
        return;
      }
      this._imagesService.deleteImage(vehicleImageUuid).subscribe({
        next: (resp) => {
          this.vehicleImages.splice(index, 1);
          this.imagesChanged = true;
          this.listNeedsReload = true;
          Swal.fire(resp.message, '', 'success');
        },
        error: (err) => reload(err, this._router),
      });
    });
  }

  onPhotoFiles(event: Event): void {
    const element = event.currentTarget as HTMLInputElement;
    const fileList = element.files;
    if (fileList?.length) {
      this.photoFiles = Array.from(fileList);
      this.photoUploadDisabled = false;
    } else {
      this.photoFiles = [];
      this.photoUploadDisabled = true;
    }
  }

  uploadPhotos(): void {
    if (!this.vehicle_uuid || !this.photoFiles.length || this.photoUploading) {
      return;
    }
    this.photoUploading = true;
    this.photoUploadDisabled = true;
    this._imagesService.setImage(this.vehicle_uuid, this.photoFiles).subscribe({
      next: () => {
        this.photoUploading = false;
        this.photoFiles = [];
        Swal.fire({
          icon: 'success',
          title: 'Imágenes cargadas',
          showConfirmButton: false,
          timer: 2200,
        });
        this.listNeedsReload = true;
        this.refreshVehicleImages();
      },
      error: (err) => {
        this.photoUploading = false;
        this.photoUploadDisabled = false;
        reload(err, this._router);
      },
    });
  }

  private attachCampaigns(): void {
    if (!this.id_camp.length || !this.vehicle_uuid) {
      return;
    }
    this._vehicleService.attachVehicle(this.id_camp, this.vehicle_uuid).subscribe({
      next: () => {
        this.listNeedsReload = true;
      },
      error: (error) => reload(error, this._router),
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
    if (this.button) {
      return;
    }

    if (this.vehicleSaved) {
      this.finishAndClose();
      return;
    }

    this.button = true;
    this._vehicleService.storeVehicle(this.form.value).subscribe({
      next: (storeVehicleResponse: VehicleStoreResponse) => {
        this.button = false;
        this.vehicle_uuid = storeVehicleResponse.data.uuid;
        this.vehicleSaved = true;
        this.listNeedsReload = true;
        this.attachCampaigns();
        this.form.disable();
        Swal.fire({
          icon: 'success',
          title: 'Vehículo creado',
          text: `${storeVehicleResponse.data.name}. Ya puedes subir fotos.`,
          showConfirmButton: false,
          timer: 2500,
        });
      },
      error: (error) => {
        this.button = false;
        reload(error, this._router);
      },
    });
  }

  private finishAndClose(): void {
    this._bottomSheetRef.dismiss(
      this.listNeedsReload || this.imagesChanged ? { reload: true } : undefined,
    );
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

  public close(): void {
    if (this.listNeedsReload || this.imagesChanged || this.vehicleSaved) {
      this._bottomSheetRef.dismiss({ reload: true });
      return;
    }
    this._bottomSheetRef.dismiss();
  }

  

}
