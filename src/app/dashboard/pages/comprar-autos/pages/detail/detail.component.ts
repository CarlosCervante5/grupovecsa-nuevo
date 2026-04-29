
import { Component, OnInit, ViewChild, Inject, ElementRef, HostListener} from '@angular/core';
import { DOCUMENT } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { environment } from '@environments/environment';
import { Location } from '@angular/common';
import { Swiper, SwiperOptions} from 'swiper';
import { register } from 'swiper/element/bundle';
register();

// Angular Material
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { MatSnackBar, MatSnackBarHorizontalPosition, MatSnackBarVerticalPosition } from '@angular/material/snack-bar';
import { MatDialog } from '@angular/material/dialog';

// Services 
import { DetailService } from '@services/detail.service';
// Components
import { AskInformationComponent } from '../../components/ask-information/ask-information.component';
// Interfaces
import { DetailResponse, Vehicle, ImageCarousel, RecommendedResponse, Campaign } from '@interfaces/vehicle_data.interface';

@Component({
    selector: 'app-detail',
    templateUrl: './detail.component.html',
    styleUrls: ['./detail.component.css'],
    standalone: false
})

export class DetailComponent implements OnInit{

  swiperConfig = {
    slidesPerView: 2,
    spaceBetween: 10,
    pagination: { clickable: true },
    navigation: true,
  };

  @ViewChild('myModal') modal!: ElementRef;
  @ViewChild('myImg') img!: ElementRef;
  @ViewChild('img01') modalImg!: ElementRef; 
  @ViewChild('caption') caption!: ElementRef;
  public btNext!: Swiper;
  public swiper!: Swiper;
  public configSwiperV!: SwiperOptions;

  // References of Help
  public pageVehicle: string = '';
  public baseUrl: string = environment.baseUrl;

  // References of Button
  public route: boolean = false;
  public locationVeh: string = '';
  // References Vehicle
  public uuid!:string;
  public vehicle!: Vehicle;
  public campaigns!: Campaign[];
  public promotions !: any[];
  public imagesForSlider: ImageCarousel[] = [];  
  public pathStockBrand: string = '';
  public pathStockCarmodel: string = '';
  public description: string = '';
  public descriptions!: string[];
  public priceOffer: boolean = false;
  public legalDate!: Date;
  public dia!:any;
  public mes!:any;
  public year!:any;
  public dates!:any;
  public priceBond!:any;
  execute!:string;

  public ancho!: number;
  public anchoCards!: number;
  public anchoS!: string;
  public anchoW!: number;

  // Recommended vehicles
  public recommended_vehicles: Vehicle[] = [];

  public textButton:string = 'AÑADIR A LISTA';

  horizontalPosition: MatSnackBarHorizontalPosition = 'end';
  verticalPosition: MatSnackBarVerticalPosition = 'bottom';
  
  constructor(
    private _bottomSheet: MatBottomSheet,
    private _router: Router,
    private _activatedRoute: ActivatedRoute,
    private _detailService: DetailService,
    private _snackBar: MatSnackBar,
    private location: Location,
    public dialog: MatDialog,

    @Inject(DOCUMENT) private _document: Document
  ) { 
    // Assign active route for shared button    
    this.pageVehicle = window.location.href;   
    this._activatedRoute.params
    .subscribe({
      next: (params) => {
        this.uuid = params['uuid'];
        this.getVehicle();
      }
    });     
     
  }
  
  ngOnInit(): void {
    const date = new Date();
    const legalDate = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    
    const nombresMeses = [
      "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
      "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];
    
    const dia = legalDate.getDate();
    const mes = nombresMeses[legalDate.getMonth()];
    const year = legalDate.getFullYear();
    
     this.dates =dia + " de " + mes + " de " + year;
  }

  public notFound(){
    this._router.navigateByUrl('404');
  }

  /**
   * Button for Copy url active to shared button
   */
  public openSnackBarCopy() {  
    // Lauch Snackbar
    this._snackBar.open('Copiado', '', {
      horizontalPosition: 'center',
      verticalPosition: 'top',
      duration: 2000,
      panelClass: ['snackbar']
    });    
  }

  public getVehicle() {
    
    this._detailService.getVehicleDetail(`${ this.uuid }`)
    .subscribe({
      next: ( response: DetailResponse ) => {
        
        this.vehicle =  response.data;
        this.campaigns = this.vehicle.campaigns;
        this.getRecommended();
        this.existsInList(); 
        
        this.priceOffer = this.vehicle.offer_price != null ? true : false;
        this.description = this.vehicle.description!;
          
        this.vehicle.images.map( image => {
          this.imagesForSlider.push(
            { path: image.service_image_url }
          )
        });

        this.pathStockBrand = `/compra-tu-auto/${ this.vehicle.category == 'new' ? `Nuevo` : 'Seminuevo' }/${ this.vehicle.brand.name }/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/100000/5000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1`;
        this.pathStockCarmodel = `/compra-tu-auto/${ this.vehicle.category == 'new' ? `Nuevo` : 'Seminuevo' }/${ this.vehicle.brand.name }/sin-lineas/${ this.vehicle.model.name }/sin-carrocerias/sin-versiones/sin-anios/100000/5000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1`;

          
        if (this.description != null) {
          this.descriptions = this.description.split('\n');
        } else {
          this.descriptions = ["Lo sentimos, este vehículo no cuenta con alguna descripción activa."];
        }
               
        if ( this.vehicle.images.length == 0 ) {
          this.imagesForSlider.push(
            { path: this.baseUrl + '/api/image_vehicle/vacio' }
          );
        }

      },
      error(error){
        console.log(error);
      }
    });
  }

  @HostListener('window:resize', ['$event'])
  onResize(event: Event) {
    this.anchoW = window.innerWidth;
    this.anchoS = this.anchoW - 50+ 'px';
    if(this.anchoW < 500){
      this.ancho = 1;
      this.anchoCards = 1;
    }else{
      if(this.anchoW < 1000){
        this.ancho = 3;
        this.anchoCards = 2;
      }else{
        if(this.anchoW < 1200){
          this.anchoCards = 3
        }else{
          this.ancho = 4;
        this.anchoCards = 4;
        }
      }
    }
  }

  public getRecommended(){

    let priceMin = this.vehicle.list_price - 100000;
    let priceMax = this.vehicle.list_price + 100000; 

    this._detailService.getRecommendedVehicles(priceMin, priceMax)
    .subscribe({
      next: ( recommended: RecommendedResponse ) => {
        this.recommended_vehicles = recommended.data;
      }
    });
  }


  public changeImageSelected (img: string, i: number){
    let nImage: ImageCarousel[] = [];
    let ind = 0;
    //se busca la imagen seleccionada, y se obtiene su posición actual
    for (let j = 0; j < this.imagesForSlider.length; j++) {
      if(img == this.imagesForSlider[j].path){
          ind = j;
      }
    }
    //se guardan las imagenes posteriores a la seleccionada
    for (let h = ind; h < this.imagesForSlider.length; h++) {
      nImage.push(this.imagesForSlider[h]);
    }
    //se guardan las imagenes anteriores a la seleccionada
    for (let j = 0; j < ind; j++) {
      nImage.push(this.imagesForSlider[j]);
    }
    this.imagesForSlider = nImage;
  }

  /**
   * Ask Information Vehicle
   */
  public askInformation(vehicle: Vehicle) {
    this.addToList();
    this._bottomSheet.open(AskInformationComponent, {
      data: {
        vehicle_uuid: this.vehicle.uuid,
        vehicle: vehicle.name,
        brand: vehicle.brand.name,
        year: vehicle.model.year,
        dealership_name: vehicle.dealership.name
      }
    });
  }

  public saveVehicleLS(){
    localStorage.setItem("vehicle", JSON.stringify(this.vehicle));
  }
    
  public addToList(){    
    let vehicles: Vehicle[] = JSON.parse(localStorage.getItem('vehicles')!) != null ? JSON.parse(localStorage.getItem('vehicles')!) : [];
    let exists = vehicles.find( vehicle => vehicle.uuid == this.vehicle.uuid );    
  
    if( exists === undefined ){
      vehicles.push(this.vehicle);
    }    

    localStorage.setItem("vehicles", JSON.stringify(vehicles));
    this.existsInList();    
  }

  public existsInList(): void {
    let vehicles: Vehicle[] = JSON.parse(localStorage.getItem('vehicles')!) != null ? JSON.parse(localStorage.getItem('vehicles')!) : [];
    let exists = vehicles.find( vehicle => vehicle.uuid == this.vehicle.uuid );    
    if( exists !== undefined ){
      this.textButton = 'EN MI LISTA';
    }else{
      this.textButton = 'AÑADIR A MI LISTA';
    }
  }

  goBack(): void {
    this.location.back();
  }

  /**
   * Número para wa.me (solo dígitos, con prefijo país 52 si aplica).
   * Prioridad: API `dealership.whatsapp_phone` → nombre de agencia → fallback histórico.
   */
  whatsappPhoneDigits(): string {
    const d = this.vehicle?.dealership;
    const raw = d?.whatsapp_phone;
    if (raw != null && String(raw).trim() !== '') {
      const digits = String(raw).replace(/\D/g, '');
      if (digits.length >= 10) {
        return digits.startsWith('52') ? digits : `52${digits}`;
      }
    }
    const name = (d?.name || '').toLowerCase();
    if (/hidalgo|vecsa\s*hidalgo|bmw\s*vecsa/i.test(name)) {
      return '5217717954749';
    }
    return '5217717954749';
  }

  get whatsappInquiryHref(): string {
    const phone = this.whatsappPhoneDigits();
    const text = encodeURIComponent(`Me gustaría información de éste vehículo: ${this.pageVehicle}`);
    return `https://api.whatsapp.com/send?phone=${phone}&text=${text}`;
  }

  get whatsappShareHref(): string {
    const text = encodeURIComponent(`Te comparto este vehículo en bmwvecsahidalgo.com ${this.pageVehicle}`);
    return `https://api.whatsapp.com/send?text=${text}`;
  }

  showModal( src: string) {   
    let imagen = src;
    let legal = "";

    this.modal.nativeElement.style.display = "grid";
    this.modalImg.nativeElement.src = imagen;  
    this.caption.nativeElement.innerHTML = legal ;
  }
  
  closeModal( message:string ) {    
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
}