
import { Component , Output, EventEmitter} from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { LoadImagesPromoComponent } from '../../components/load-images-promo/load-images-promo.component';
import { environment } from '@environments/environment';
import { CreateCampaingComponent } from '../../components/create-campaing/create-campaing.component';
//import {  GetcampaingResponse, Campaign } from '../../interfaces/createCampaing.interface';
import { CampaingService } from '../../services/campaing.service';
import { UpdateImagesComponent } from '../../components/update-images/update-images.component';

//pruebas
import {GetcampaingResponse, Campaign} from '@interfaces/admin.interfaces';
import Swal from 'sweetalert2';

import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

interface Brand{
  brand: string;
  path: string | null;
}

@Component({
    selector: 'app-promotions',
    templateUrl: './promotions.component.html',
    styleUrls: ['./promotions.component.css'],
    standalone: false
})
export class PromotionsComponent {

  public baseUrl: string = environment.baseUrl;
  @Output() reload = new EventEmitter<Boolean>();
  public img_campaign_path!:string;


  public campaigns: Campaign[] = [];
  public length: number = 0;

  constructor(
    private _bottomSheet: MatBottomSheet,
    private _campaingService: CampaingService,
    private _router: Router
  ) {
    // this.promotionsByBrand(this.brands);
    this.showcampaing();
  }  


  //abre el modal para agregar promociones
  public openBottomSheet(campaign: string): void{
    const openLoadImages = this._bottomSheet.open(LoadImagesPromoComponent , {
      data: {
        campaign
      }
    });
    openLoadImages.afterDismissed().subscribe((dataFromChild) => {      
      if(dataFromChild != undefined && dataFromChild.reload === true ){        
        this.reload.emit(true);
      }     
    });
  }

  //ordenamiento de imagenes
  openUpdateOrder( campaign_id:string, promotions:any[] ): void {
    const bottomSheetRef2 = this._bottomSheet.open(UpdateImagesComponent, {
      data: {
        campaign_id,
        images: promotions
      }
    });  
    bottomSheetRef2.afterDismissed().subscribe((dataFromChild) => {                  
      if( dataFromChild != undefined && dataFromChild.first_image === true) {
        // this.vehicle.vehicle_images.shift();    
        this.reload.emit(true);                                
      }
      this.showcampaing();   
    }); 
  }

  //Agregar una nueva campaña

  newCampaing(): void {
    const bottomSheetRef = this._bottomSheet.open(CreateCampaingComponent);
    bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {      
      if(dataFromChild != undefined && dataFromChild.reload === true ){        
        this.reload.emit(true);
        this.showcampaing();
      }      
    });
  }

  //mostrar las campañas existente
  public showcampaing () {
    this._campaingService.getCampaing()
    .subscribe({
      next: (response: GetcampaingResponse) => {
     this.campaigns = response.data.campaigns;
     let x = this.campaigns;
     x.forEach(element => {
      let d = element.begin_date;
      let d2 = element.end_date;
      let td = this.formatDate(d);
      let td2 = this.formatDate(d2);
      element.begin_date = td;
      element.end_date = td2;
      if((element.promotions).length > 0){
        element.promo_Path = element.promotions[0].image_path;
      }else{
        element.promo_Path = "";
      }
     });
     this.campaigns = x;
     
    },
    error: (error:any) => {
      reload(error, this._router);
    }
    })
  }
    public formatDate(dateString: any): string {
    const date = new Date(dateString  + 'T00:00:00Z');
    const day = (String(date.getUTCDate()).padStart(2, '0'));
    const month = String(date.getUTCMonth()+1).padStart(2, '0'); // Los meses empiezan en 0
    const year = date.getUTCFullYear();
    return `${day}-${month}-${year}`;
}

  public deleteCampaign ( uuid : string){
    Swal.fire({
      title: 'Estas segur@ que quieres eliminar esta campaña?',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#008bcc',
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        this._campaingService.deleteCampaign( uuid )
            .subscribe({
              next: (resp) => {
                Swal.fire(resp.message, '', 'success');
              },
              error:(error:any)=>{
                reload(error, this._router);
              }
            })
                this.reload.emit(true);
                this.showcampaing();
      }
    })
  }

  public campai( primera_imagen:any ){      
 
    return primera_imagen || 'assets/images/demo_image.png';      
      
  }

}
