import { Component, ElementRef, HostListener, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { FormControl, FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MatSelectChange } from '@angular/material/select';
import { Camp, Campaign, GetcampaingResponse, GralResponse, promos, Promotion } from '@interfaces/admin.interfaces';
import { CampaingService } from '@services/campaing.service';
import { CarCareBannerService } from '@services/carcare-banner.service';
import { Subscription } from 'rxjs';
import Swal from 'sweetalert2';

interface CarCareHeroSlide {
  uuid: string;
  title: string;
  subtitle: string;
  disclaimer: string;
  desktop_image_path: string;
  mobile_image_path: string;
}

@Component({
    selector: 'app-car-care',
    templateUrl: './car-care.component.html',
    styleUrls: ['./car-care.component.css'],
    standalone: false
})
export class CarCareComponent implements OnInit, OnDestroy {
  public form !: FormGroup;
  public otherB =  false;
  public promos: Campaign[] = []; 
  public prom: Camp[] = [];
  public anchoCards = 4;
  public anchoW!: number;

  public specificationsLink!: string|null;
  public bannerSlides: CarCareHeroSlide[] = [];
  public currentBannerSlide = 0;
  private bannerSub?: Subscription;
  private bannerInterval?: ReturnType<typeof setInterval>;


  @ViewChild('myModal') modal!: ElementRef;
  @ViewChild('myImg') img!: ElementRef;
  @ViewChild('img01') modalImg!: ElementRef; 
  @ViewChild('caption') caption!: ElementRef;
  execute!:string;
  constructor(
    private _formBuilder: UntypedFormBuilder, 
    private _campaingService: CampaingService,
    private _carCareBannerService: CarCareBannerService,
  ){
    this.createForm();
    this.promosServ();
  }

  ngOnInit(): void {
    this.loadBanners();
  }

  ngOnDestroy(): void {
    this.bannerSub?.unsubscribe();
    this.stopBannerAutoplay();
  }

  private loadBanners(): void {
    this.bannerSub = this._carCareBannerService.publicList().subscribe({
      next: (res) => {
        const banners = res?.data?.banners;
        if (!Array.isArray(banners) || banners.length === 0) {
          this.bannerSlides = [];
          return;
        }

        this.bannerSlides = banners.map((b) => ({
          uuid: b.uuid,
          title: b.title || '',
          subtitle: b.subtitle || '',
          disclaimer: b.disclaimer || '',
          desktop_image_path: b.desktop_image_path || '',
          mobile_image_path: b.mobile_image_path || '',
        }));
        this.currentBannerSlide = 0;
        this.startBannerAutoplay();
      },
      error: () => {
        this.bannerSlides = [];
      },
    });
  }

  private startBannerAutoplay(): void {
    this.stopBannerAutoplay();
    if (this.bannerSlides.length <= 1) {
      return;
    }
    this.bannerInterval = setInterval(() => {
      this.nextBannerSlide();
    }, 5000);
  }

  private stopBannerAutoplay(): void {
    if (this.bannerInterval) {
      clearInterval(this.bannerInterval);
      this.bannerInterval = undefined;
    }
  }

  goToBannerSlide(index: number): void {
    this.currentBannerSlide = index;
    this.startBannerAutoplay();
  }

  prevBannerSlide(): void {
    if (this.bannerSlides.length === 0) return;
    this.currentBannerSlide =
      (this.currentBannerSlide - 1 + this.bannerSlides.length) % this.bannerSlides.length;
    this.startBannerAutoplay();
  }

  nextBannerSlide(): void {
    if (this.bannerSlides.length === 0) return;
    this.currentBannerSlide = (this.currentBannerSlide + 1) % this.bannerSlides.length;
  }
    public createForm() {
        this.form = this._formBuilder.group({
            name:           ['', Validators.required],
            phone:          ['', Validators.required, Validators.pattern(/^\d{7,10}$/)],
            origin_agency:  ['', Validators.required],
            service:        ['', Validators.required],
            brand:          ['', Validators.required],
            other_brand:    [''],
            model:          ['', Validators.required],
            year:           ['', Validators.required],
            fecha:          ['', Validators.required],
            hour:          ['', Validators.required],
            email:          ['', [Validators.required, Validators.pattern("[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$")]],
            comentario:     ['']
        });
    }
    get nameInvalid(){
        return this.form.get('name')?.invalid && (this.form.get('name')?.dirty || this.form.get('name')?.touched);
    }
    get emailInvalid(){
        return this.form.get('email')?.invalid && (this.form.get('email')?.dirty || this.form.get('email')?.touched);
    }
    get phoneLength(){
        let phone = this.form.get('phone')?.value;
        return this.form.get('phone')?.touched && (phone.length < 10 || phone.length > 100);
    }
    get agencyInvalid(){
        return this.form.get('origin_agency')?.invalid && (this.form.get('origin_agency')?.dirty || this.form.get('origin_agency')?.touched);
    }
    get serviceInvalid(){
        return this.form.get('service')?.invalid && (this.form.get('service')?.dirty || this.form.get('service')?.touched);
    }
    get brandInvalid(){
        return this.form.get('brand_name')?.invalid && (this.form.get('brand_name')?.dirty || this.form.get('brand_name')?.touched);
    }
    get otherBrandInvalid(){
        return this.form.get('other_brand')?.invalid && (this.form.get('other_brand')?.dirty || this.form.get('other_brand')?.touched);
    }
    get modelInvalid(){
        return this.form.get('model_name')?.invalid && (this.form.get('model_name')?.dirty || this.form.get('model_name')?.touched);
    }
    get yearInvalid(){
        return this.form.get('year')?.invalid && (this.form.get('year')?.dirty || this.form.get('year')?.touched);
    }
    get dateInvalid(){
        return this.form.get('fecha')?.invalid && (this.form.get('fecha')?.dirty || this.form.get('fecha')?.touched);
    }
    get hourInvalid(){
        return this.form.get('hour')?.invalid && (this.form.get('hour')?.dirty || this.form.get('hour')?.touched);
    }
    
    public onBrandChange(event: Event): void {
        const value = (event.target as HTMLSelectElement).value;
        this.otherB = value === 'OTRA MARCA';
    }

    public onBrandSelected(event: MatSelectChange): void {
        const selectedBrand = event.value;
        if(selectedBrand == 'OTRA MARCA'){
            this.otherB = true;
        }else{
            this.otherB = false;
        }
    }

    public promosServ( )
    {
        this._campaingService.searchByName()
        .subscribe({
            next: (response:GetcampaingResponse)=>{

                this.promos = response.data.campaigns;
               this.promos.forEach(element => {

                   this.prom = element.promotions;

               });
            }
        })
    }

    public onSubmit(){
        let brand = '';
        if(this.form.get('brand')!.value != 'OTRA MARCA'){
            brand = this.form.get('brand')!.value;
        }else{
            brand = this.form.get('other_brand')!.value;
        }
        this._campaingService.setCarCare(this.form.get('name')!.value, this.form.get('email')!.value,this.form.get('phone')!.value,
        this.form.get('model')!.value,this.form.get('year')!.value, this.form.get('origin_agency')!.value, this.form.get('service')!.value,
        this.form.get('comentario')!.value, brand , this.form.get('fecha')!.value, this.form.get('hour')!.value,)
        .subscribe({
            next: (response: GralResponse) =>{
                Swal.fire({                    
                    icon: 'success',
                    title: 'Usuario actualizado con exito',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                  });
                  this.reloadPage();
            },
            error: (error)=>{
                console.log(error);
            }
        })
    }

    reloadPage() {
        window.location.reload();
    }

    showModal( src: string, link:string) {   
        let imagen = src;
        let legal = "";
    
        this.modal.nativeElement.style.display = "grid";
        this.modalImg.nativeElement.src = imagen;  
        this.caption.nativeElement.innerHTML = legal ;
        this.specificationsLink = link; 
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

      public onlyNumbers(event: KeyboardEvent): void {
        const charCode = event.which ? event.which : event.keyCode;
        if (charCode < 48 || charCode > 57) {
          event.preventDefault(); // Bloquea cualquier entrada que no sea un número
        }
      }

      setEnglishLocale(): void {
        const inputDate = document.querySelector('input[type="date"]') as HTMLInputElement;
        if (inputDate) {
          inputDate.setAttribute('lang', 'en'); // Establecer el idioma del input
        }
      }
      
    @HostListener('window:resize',['$event'])
  onResize(): void {
    this.anchoW = window.innerWidth;
    if(this.anchoW < 500){
      this.anchoCards = 1;
    }else{
      if(this.anchoW < 1000){
        this.anchoCards = 2;
      }else{
        if(this.anchoW < 1200){
          this.anchoCards = 3
        }else{
          if(this.anchoW > 1200){
            this.anchoCards = 4;
          }
        }
      }
    }
  }
}
