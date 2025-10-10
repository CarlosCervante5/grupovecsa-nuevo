import { Component, OnInit, Input, ViewChild, ElementRef } from '@angular/core';

// Interfaces 
import { Vehicle } from '../../../../../shared/interfaces/data_buscador.interface';
import { environment } from '@environments/environment';

// Services
import { DetailService } from '../../services/detail/detail.service';

@Component({
    selector: 'app-vehicle-panorama-viewer',
    templateUrl: './vehicle-panorama-viewer.component.html',
    styleUrls: ['./vehicle-panorama-viewer.component.css'],
    standalone: false
})
export class VehiclePanoramaViewerComponent implements OnInit {
  
  public vehicle_image = ''; 
  public choice: boolean = false;
  public priceOffer: boolean = false;
  public baseUrl: string = environment.baseUrl;

  @ViewChild('slider') slider!: ElementRef;
  @ViewChild('canvas') canvas!: ElementRef<HTMLCanvasElement>;
  context!: CanvasRenderingContext2D;
  images:any = [];
  
  @Input() vehicle!: Vehicle; 

  constructor(private _detailService: DetailService) { }

  ngOnInit(): void {  
    this.priceOffer = this.vehicle.priceOffer != null ? true : false;
    
    let primera_imagen = this.vehicle.vehicle_images[0];  
    
    if( primera_imagen != undefined && primera_imagen.external_website == "yes" ){
      this.vehicle_image = primera_imagen.path;
    } else {
      this.vehicle_image = primera_imagen != undefined ? this.vehicle.vehicle_images[0].path : 'vacio';
      this.vehicle_image = this.baseUrl + '/api/image_vehicle/' + this.vehicle_image;
    }

    this.reservedVehicle();
    this.pageLoaded();  
  }

  /**
   * Checking exists choice   
   */
  public reservedVehicle() {
    if (this.vehicle.choices) {
      this.choice = (Object.entries(this.vehicle.choices).length === 0) ? false : true;    
    }
  }

  // /**
  //  * Find vehicle in choice 
  //  */
  // public choiceVehicle(vin: string) {
  //   this._detailService.getChoiceByVin(vin)
  //   .subscribe({
  //     next: (response) => {        
  //       if (response.choice) {
  //         this.choice = true;
  //       } else {
  //         this.choice = false;
  //       }
  //     }
  //   });
  // }

  public handleInputSlider() {      
    this.loadImage( this.slider.nativeElement.value - 1 );
  }

  public pageLoaded (){
    this.vehicle.vehicle_images.map( ( img, i ) => {      
      const url = this.baseUrl + '/api/image_vehicle/' + img.path;
      const image = new Image()
      image.src = url      
      image.addEventListener('load', () => {          
          this.images[i] = image
          // si el indice es igual a 1 cargamos la image
          if (i === 0) {
              this.loadImage(i)
          }          
      }) 
    });                    
  }

  public loadImage(index:number){
    const uno = this.canvas.nativeElement.getContext('2d');
    if( uno ){
      this.context = uno;
        /** 
        /* Recibe 5 parametros:
        /* indice de la imagen, x, y, tamaños ancho y alto 
        **/         
      this.context.drawImage(this.images[index], 0, 0, this.canvas.nativeElement.clientWidth, this.canvas.nativeElement.clientHeight)    
    }
  }

  public move(){
    this.images.map( (image:any, index:number) => {        
        //setTimeout( () => { loadImage(index) }, index * 65 );        
        setTimeout( () => { this.loadImage(index) }, index * 75);        
    });
  }

}