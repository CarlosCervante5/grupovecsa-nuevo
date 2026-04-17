import { Component, Input, EventEmitter, Output  } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { MatSnackBar } from '@angular/material/snack-bar';
import { CompraTuAutoService } from '@services/compra-tu-auto.service';
import Swal from 'sweetalert2';
import { environment } from '@environments/environment';
import { LoadImagesComponent } from '../load-images/load-images.component';
import { UpdateImagesComponent } from '../update-images/update-images.component';
import { UpdateVehicleComponent } from '../update-vehicle/update-vehicle.component';
import {reload} from '@helpers/session.helper';
import { Vehicle} from 'src/app/dashboard/pages/comprar-autos/interfaces/detail/vehicle_data.interface';
import { Router } from '@angular/router';


@Component({
    selector: 'app-a-vehicle',
    templateUrl: './a-vehicle.component.html',
    styleUrls: ['./a-vehicle.component.css'],
    standalone: false
})
export class AVehicleComponent {
  @Input() vehicle!: Vehicle;    

  @Output() reload = new EventEmitter<Boolean>();
  @Output() add_vehicle_uuid = new EventEmitter<string>();
  @Output() remove_vehicle_uuid = new EventEmitter<string>();

  public baseUrl: string = environment.baseUrl;
  checked = false;
  

  constructor(
    private _compraTuAutoService: CompraTuAutoService,
    private _bottomSheet: MatBottomSheet,
    private _snackBar: MatSnackBar,
    private _router: Router
  ) { 
    
  }

  public image( primera_imagen:any ){      
 
    return primera_imagen || 'assets/images/demo_image.png';      
      
  }

  openBottomSheet( vehicle_id:string ): void {
    const bottomSheetRef = this._bottomSheet.open(LoadImagesComponent, {
      data: {
        vehicle_id
      }
    });   
    bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {      
      if(dataFromChild != undefined && dataFromChild.reload === true ){        
        this.reload.emit(true);
      }      
    });
  }

  openUpdateOrder( vehicle_id:string, vehicle_images:any ): void {
    const bottomSheetRef2 = this._bottomSheet.open(UpdateImagesComponent, {
      data: {
        vehicle_id,
        images: vehicle_images
      }
    });  
    bottomSheetRef2.afterDismissed().subscribe((dataFromChild) => {                  
      if( dataFromChild != undefined && dataFromChild.first_image === true) {
        // this.vehicle.vehicle_images.shift();                                       
      }
      this.reload.emit(true);
    }); 
  }

  public eliminar( vehicle_uuid:string ){
    Swal.fire({
      title: 'Estas segur@ que quieres eliminar esta unidad?',      
      showCancelButton: true,
      confirmButtonText: 'Eliminar', 
      confirmButtonColor: '#008bcc',           
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {        
        this._compraTuAutoService.deleteVehicle( vehicle_uuid )
        .subscribe({
          next:( resp ) => {                                  
            this.reload.emit(true);             
            Swal.fire(resp.message, '', 'success');
          },
          error: (error:any) => {
            reload(error, this._router);
          }
        })
      }
    })
  }

  openUpdateVehicleSheet( uuid:string ): void {
    const bottomSheetRef = this._bottomSheet.open(UpdateVehicleComponent, {
      data: {
        uuid
      }
    });   
    bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {      
      if(dataFromChild != undefined && dataFromChild.reload === true ){        
        this.reload.emit(true);
      }      
    });
  }

  cardChecked(){
    if( this.checked ){
      this.add_vehicle_uuid.emit(this.vehicle.uuid);
    }else {
      this.remove_vehicle_uuid.emit(this.vehicle.uuid);
    }
  }
}
