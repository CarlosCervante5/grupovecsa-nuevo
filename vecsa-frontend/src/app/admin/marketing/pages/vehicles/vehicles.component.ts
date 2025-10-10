import { Component } from '@angular/core';
import { PageEvent } from '@angular/material/paginator';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { Router } from '@angular/router';
import {reload} from '@helpers/session.helper';
// Services
import { CompraTuAutoService } from '@services/compra-tu-auto.service';

// Interfaces
import { MatSnackBar } from '@angular/material/snack-bar';
import Swal from 'sweetalert2';
import { VehicleService, } from '@services/vehicle.service';

import { Vehicle, SearchResponse, LoadVehiclesResponse} from '@interfaces/vehicle_data.interface';
import { StoreVehicleComponent } from '../../components/store-vehicle/store-vehicle.component'; 



@Component({
    selector: 'app-vehicles',
    templateUrl: './vehicles.component.html',
    styleUrls: ['./vehicles.component.css'],
    standalone: false
})
export class VehiclesComponent {
  // MatPaginator Inputs
  public length: number = 0;
  public pageSize: number = 12;
  public pageSizeOptions: number[] = [15, 30, 45, 60, 150];

  // MatPaginator Output
  pageEvent!: PageEvent;

  // Vehiculos
  public vehicles: Vehicle[] = [];

  public palabra_busqueda:string = '';

  public relationship_names: string[] = ['brand','line','model','version','body','dealership','specification','firstImage','images'];

  files:File[] = [];
  disabled:Boolean = true;
  loading:Boolean = false;
  load_vehicle_message = 'Cargar vehículos con csv';
  errorMessage: string = '';

  public pageIndex: number = 1;

  public vehicle_uuids: string[] = [];

  constructor(
    private _vehicleService: VehicleService,
    private _compraTuAutoService: CompraTuAutoService,
    private _bottomSheet: MatBottomSheet,
    private _snackBar: MatSnackBar,
    private _router: Router
  ) {
    this.getVehicles(1);
  }    


    /**
     * Get vehicles
     */
    public getVehicles(page: number) {    

        this._vehicleService.getVehicles( page , this.palabra_busqueda, this.pageSize, this.relationship_names)
        .subscribe({
            next: (response: SearchResponse) => {
                this.vehicles = response.data.data;                                                
                this.length = response.data.total;

            },
            error: (error:any) => {
              reload(error, this._router);
            }
        });
    }

    /**
     *  Change pagination
     */
    public paginationChange(pageEvent: PageEvent) { 
      this.pageEvent = pageEvent;
      this.pageSize = this.pageEvent.pageSize;   
      this.pageIndex = this.pageEvent.pageIndex + 1;
      this.getVehicles( this.pageIndex );  
    }  

    selectCsv( event: Event ){
      const element = event.currentTarget as HTMLInputElement;
      let fileList: FileList | null = element.files;
      if (fileList) {  
        this.files = Array.from(fileList);  
        if(this.files.length > 0){
          this.disabled = false;
          this.load_vehicle_message = 'Listo para subir';        
        }else{
          this.disabled = true;
          this.load_vehicle_message = 'Cargar vehículos';        
        }
      }    
    }

    uploadCsv() {
      this._compraTuAutoService.uploadCsv(this.files[0])
        .subscribe({
          next: (LoadVehicles: LoadVehiclesResponse) => {
            const errors = LoadVehicles.data || [];
            const limitedErrors = errors.slice(0, 5);
            const remainingErrors = errors.length - limitedErrors.length;
    
            const createErrorListHtml = (errorArray: any[]) => `
              <ul>
                ${errorArray.map(err =>
                  `<li>Fila: ${err.row} | Atributo: ${err.attribute} - ${err.errors.join(', ')}</li>`
                ).join('')}
              </ul>`;
    
            const limitedErrorHtml = `
              <p>Se encontraron los siguientes errores:</p>
              ${createErrorListHtml(limitedErrors)}
              ${errors.length > 5 ? `<p>...y ${remainingErrors} errores más.</p>` : ''}
            `;
    
            Swal.fire({
              icon: errors.length > 0 ? 'warning' : 'success',
              title: LoadVehicles.message,
              html: limitedErrorHtml,
              showConfirmButton: true,
              confirmButtonColor: '#EEB838',
              width: '600px',
              timer: 30000,
              footer: errors.length > 5 ? 
                `<button id="seeAllErrors" class="see-all-errors-btn">Ver todos los errores</button>` 
                : ''
            });
    
            // Estilos del botón de "Ver todos los errores"
            const styles = `
              #seeAllErrors {
                background-color: #f5b042;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 14px;
                border-radius: 8px;
                cursor: pointer;
                transition: background-color 0.3s ease, transform 0.1s ease;
              }
              #seeAllErrors:hover {
                background-color: #ee9b00;
              }
              #seeAllErrors:active {
                transform: scale(0.95);
              }
            `;
    
            // Inyectar los estilos en el documento
            const styleSheet = document.createElement('style');
            styleSheet.type = 'text/css';
            styleSheet.innerText = styles;
            document.head.appendChild(styleSheet);
    
            const interval = setInterval(() => {
              const button = document.getElementById('seeAllErrors');
              if (button) {
                clearInterval(interval);
                button.addEventListener('click', () => {
                  Swal.fire({
                    icon: 'warning',
                    title: 'Errores completos',
                    html: createErrorListHtml(errors),
                    width: '900px',
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838'
                  });
                });
              }
            }, 100);
    
            this.getVehicles(1); // Recargar los vehículos
          },
          error: (error) => {

            Swal.fire({
              icon: 'error',
              title: 'Error al subir el archivo',
              text: 'Hubo un problema con la solicitud. Por favor, inténtalo de nuevo.',
              showConfirmButton: true,
              confirmButtonColor: '#EEB838'
            });
          }
        });
    }
    
   
    openSnackBar(message: string, verticalPosition:any, className:string) {
      this._snackBar.open(message, "cerrar", {
        duration: 3000,
        horizontalPosition: "end",
        verticalPosition: verticalPosition,
        panelClass: [className],
      });
    }

    procesaPropagar( action:any ){
      if( action === true ){
        this.getVehicles(this.pageIndex);
      }
    }

    addVehicle_uuid( vehicle_uuid:string ) {
      this.vehicle_uuids.push( vehicle_uuid );    
    }

    removeVehicle_uuid( vehicle_uuid:string ) {
      this.vehicle_uuids.map( ( element, index ) => {
        if( element === vehicle_uuid ){
          this.vehicle_uuids.splice(index, 1);
        }      
      });    
    }

    deleteSelectedVehicles():void {
      Swal.fire({
        title: 'Estas segur@ que quieres eliminar estas unidades?',      
        showCancelButton: true,
        confirmButtonText: 'Eliminar', 
        confirmButtonColor: '#008bcc',           
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {        
          this._compraTuAutoService.deleteVehicles( this.vehicle_uuids )
          .subscribe({
            next: () => {
              Swal.fire('vehículos eliminados con exito', '', 'success');
              this.vehicle_uuids = [];
              this.getVehicles( this.pageIndex );
            },
            error: (error:any) => {
              reload(error, this._router);
            }
          });
        }
      })
    }

    changeStatus( status:string ): void {
      Swal.fire({
        title: 'Estas segur@ que quieres cambiar el estatus estas unidades?',      
        showCancelButton: true,
        confirmButtonText: 'Actualizar', 
        confirmButtonColor: '#008bcc',           
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {

          this._compraTuAutoService.changeStatusVehicles( this.vehicle_uuids, status )
          .subscribe({
            next: () => {
              Swal.fire('vehículos actualizados con exito', '', 'success');
              this.vehicle_uuids = [];
              this.getVehicles( this.pageIndex );
            },
            error: (error:any) => {
              reload(error, this._router);
            }
          });
        
        }
      })
    }

    openStoreVehicleSheet(): void {
      const bottomSheetRef = this._bottomSheet.open(StoreVehicleComponent);

      bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {      
        if(dataFromChild != undefined && dataFromChild.reload === true ){        
          this.getVehicles( this.pageIndex );
        }      
      });
    }
}


