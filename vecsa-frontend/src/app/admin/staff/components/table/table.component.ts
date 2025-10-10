
import { Component, ViewChild, Output, EventEmitter, OnInit, HostListener } from '@angular/core';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { UpdateRiderModalComponent } from '../update-rider-modal/update-rider-modal.component';
import { NewCustomerComponent } from '../new-customer/new-customer.component';

//prueba
import { RiderResponse, tableData, Rider } from '@interfaces/admin.interfaces';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';
import { UpdateInfoRiderComponent } from '../update-info-rider/update-info-rider.component';
import { GralResponse } from '@interfaces/vehicle_data.interface';
import { Router } from '@angular/router';
import {reload} from '@helpers/session.helper';


@Component({
    selector: 'app-table',
    templateUrl: './table.component.html',
    styleUrls: ['./table.component.css'],
    standalone: false
})
export class TableComponent implements OnInit{
    public riders!: Rider[];
    public uuids: tableData[] = [];
    //variables de la paginación
    public page: number = 1;
    public keyword: string = '';
    public paginate: number = 15;
    public pageIndex: number = 1;
    public length: number  = 0;

    public isChecked!: boolean;
    public mostrar= false;
    public datosM = '';
    public mostrarId!: string;
    public anchoW !: number;

    dataSource = new MatTableDataSource(this.uuids);
    displayedColumns: string[] = [
        'id',
        'name',
        'talla',
        'agencia',
        'telefono',
        'email',
        //'vin',
       // 'kilometraje',
        //'puntos',
        'status',
        'fecha',
        //'actions'
    ];

    @ViewChild(MatPaginator) paginator!: MatPaginator;
    @ViewChild(MatSort) sort!: MatSort;
    @Output() reload = new EventEmitter<Boolean>();


    constructor(
        private _bottomSheet: MatBottomSheet, 
        private _riderservice: AdminService,
        private _router: Router
    ){
        if(window.innerWidth < 360){
            this.datosM = 'movil';
            this.displayedColumns = [
                'id',
                'name'
            ];
        }else{
            if(window.innerWidth  > 361 && window.innerWidth  < 900){
                this.datosM = 'movil2';
                this.displayedColumns = [
                    'id',
                    'name',
                    'talla',
                ];
            }else{
                if(window.innerWidth > 901 && window.innerWidth < 1020){
                    this.datosM = 'tablet';
                    this.displayedColumns = [
                        'id',
                        'name',
                        'talla',
                        'agencia',
                        'telefono',
                    ];
                }else{
                    this.displayedColumns = [
                        'id',
                        'name',
                        'talla',
                        'agencia',
                        'telefono',
                        'email',
                        'model',
                        'año',
                        // 'vin',
                        'kilometraje',
                        'puntos',
                        'status',
                        'fecha',
                        'actions'
                    ];
                }
            }
        }
    }

    ngOnInit(): void {
        this.getRiders(this.page, this.keyword, this.paginate);
    }

    public paginationChange(event: PageEvent) {
        this.page = event.pageIndex + 1;
        this.getRiders(this.page, this.keyword, this.paginate);
    }

    public applyFilter(event: Event) {
        const filterValue = (event.target as HTMLInputElement).value;
        this.keyword = filterValue;
        this.page = 1;
        this.getRiders(this.page, this.keyword, this.paginate);
    }
  
    public getRiders(page:number, keyword:string, paginate: number){
        this._riderservice.getRiders(page, keyword, paginate)
        .subscribe({
            next: (response: RiderResponse) => {
                this.riders = response.data.riders.data;

                const datosR = this.riders.map((rider, index) => ({
                    vehicle_uuid: rider.vehicle_uuid,
                    id:  ((this.pageIndex-1) * this.paginate)+(index+1),
                    name: rider.customer_name +' '+ rider.customer_last_name,
                    talla: rider.customer_size,
                    agencia: rider.customer_dealership,
                    // vin: rider.vehicle_vin,
                    kilometraje: rider.vehicle_mileage,
                    puntos: rider.total_points,
                    reward_uuid: rider.reward_uuid,
                    customer: rider.customer_uuid,
                    status: rider.status,
                    email: rider.customer_email,
                    telefono: rider.customer_phone,
                    fecha: rider.reward_created_date,
                    color: index % 2 === 0 ? '#e5e5e5' : '#fff',
                    customer_reward_uuid: rider.customer_reward_uuid,
                    model: rider.vehicle_model,
                    initialM: rider.initial_mileage,
                    finallyM: rider.final_mileage,
                    year: rider.vehicle_year
                }));

                this.length = response.data.riders.total;
                this.pageIndex = response.data.riders.current_page;
                this.uuids = datosR;
                this.dataSource.data = this.uuids;
            },
            error: () => {
        
            }
        })

    }

    onPagoChange(customer_uuid:string, vehicle_uuid:string,reward_uuid:string, isChecked: boolean) {
        let estado = '';
        
        if(isChecked == true){
            estado = 'pagado';
        } else {
            estado = 'null';
        }

       this._riderservice.updateInfoPago(customer_uuid, vehicle_uuid, reward_uuid, estado)
      .subscribe({
        next: ( response :GralResponse) => {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                  toast.onmouseenter = Swal.stopTimer;
                  toast.onmouseleave = Swal.resumeTimer;
                }
              });
              Toast.fire({
                icon: "success",
                title: "Estado de pago Actualizado"
              });
          this.getRiders(this.page, this.keyword, this.paginate);
      },
      })

    }

    updateRider( customer_reward_uuid:string, km:string, kmf:string ): void {
        
        const bottomSheetRef = this._bottomSheet.open(UpdateRiderModalComponent, {
            data: {
                customer_reward_uuid: customer_reward_uuid,
                km: km,
                kmf: kmf
            }
        });

        bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {      
            if(dataFromChild != undefined && dataFromChild.reload === true ){        
                this.reload.emit(true);
                this.getRiders(this.page, this.keyword, this.paginate);
            }
        });
    }

    updateInfoRider(customer_reward_uuid:string ):void{
        const bottomSheetRef = this._bottomSheet.open(UpdateInfoRiderComponent,{
            data:{
                customer_reward_uuid: customer_reward_uuid,
            }
        });
        bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {      
            if(dataFromChild != undefined && dataFromChild.reload === true ){
                this.reload.emit(true);
                this.getRiders(this.page, this.keyword, this.paginate);
            }
        });
    }

    newCustomer(): void {
        const bottomSheetRef = this._bottomSheet.open(NewCustomerComponent);

        bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {
            if(dataFromChild != undefined && dataFromChild.reload === true ){
                this.reload.emit(true);
                this.getRiders(this.page, this.keyword, this.paginate);
            }
        });
    }

    @HostListener('window:resize', ['$event'])
    onResize(event: Event) {
        this.anchoW = window.innerWidth;
        console.log(this.anchoW);
        if(this.anchoW > 300 && this.anchoW < 360){
            this.datosM = 'movil';
            this.displayedColumns = [
                'id',
                'name'
            ];
        }else{
            if(this.anchoW > 361 && this.anchoW < 900){
                this.datosM = 'movil2';
                this.displayedColumns = [
                    'id',
                    'name',
                    'talla',
                ];
            }else{
                if(this.anchoW > 901 && this.anchoW < 1320){
                    this.datosM = 'tablet';
                    this.displayedColumns = [
                        'id',
                        'name',
                        'talla',
                        'agencia',
                        // 'nickname',
                        'telefono',
                    ];
                }else{
                    this.displayedColumns = [
                        'id',
                        'name',
                        'talla',
                        'agencia',
                        'telefono',
                        'email',
                        'model',
                        'año',
                        // 'vin',
                        'kilometraje',
                        'puntos',
                        'status',
                        'fecha',
                        'actions'
                    ];
                }
            }
        }
    }

    details(uuid: string){
        this.mostrar= !this.mostrar;
        this.mostrarId = uuid;
    }

    public deleteRider ( uuid : string){
        Swal.fire({
            title: 'Estas segur@ que quieres eliminar este Rider?',
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            confirmButtonColor: '#008bcc',
        }).then((result) => {

            if (result.isConfirmed) {
                this._riderservice.deleteVehicleR( uuid )
                .subscribe(
                (resp) => {
                    Swal.fire(resp.message, '', 'success');
                })

                this.reload.emit(true);
                this.getRiders(this.page, this.keyword, this.paginate);
            }
        })
    }
}
