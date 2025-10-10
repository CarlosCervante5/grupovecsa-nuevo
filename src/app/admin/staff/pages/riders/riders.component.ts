import { Component, ViewChild, Output, EventEmitter, OnInit, HostListener } from '@angular/core';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { UpdateRiderModalComponent } from '../../components/update-rider-modal/update-rider-modal.component'; 
import { NewCustomerComponent } from '../../components/new-customer/new-customer.component'; 
import { RiderResponse, tableData, Rider, CustomerResponse, CustomerSale, tableCustomersData } from '@interfaces/admin.interfaces';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';
import { UpdateInfoRiderComponent } from '../../components/update-info-rider/update-info-rider.component'; 
import { GralResponse } from '@interfaces/vehicle_data.interface';
import { Router } from '@angular/router';
import {reload} from '@helpers/session.helper';
import { NewRiderkmComponent } from '../../components/new-riderkm/new-riderkm.component';


@Component({
    selector: 'app-riders',
    templateUrl: './riders.component.html',
    styleUrls: ['./riders.component.css'],
    standalone: false
})

export class RidersComponent {
    public riders!: CustomerSale[];
    public uuids: tableCustomersData[] = [];
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

    private debounce_timer: any;

    dataSource = new MatTableDataSource(this.uuids);
    displayedColumns: string[] = [
        'id',
        'name',
        'telefono',
        'email',
        'puntos',
        'fecha',
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
                ];
            }else{
                if(window.innerWidth > 901 && window.innerWidth < 1020){
                    this.datosM = 'tablet';
                    this.displayedColumns = [
                        'id',
                        'name',
                        'telefono',
                    ];
                }else{
                    this.displayedColumns = [
                        'id',
                        'name',
                        'telefono',
                        'email',
                        'puntos',
                        'register_date',
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

        this.debounce(() => {
            this.getRiders(this.page, this.keyword, this.paginate);              
        });
    }

    private debounce(callback: () => void, delay: number = 600): void {
        clearTimeout(this.debounce_timer);
        this.debounce_timer = setTimeout(callback, delay);
    }
  
    public getRiders(page:number, keyword:string, paginate: number){
        this._riderservice.getCustomersRewards(page, keyword, paginate)
        .subscribe({
            next: (response: CustomerResponse) => {
                this.riders = response.data.clientes.data;

                const datosR = this.riders.map((rider, index) => ({
                    id:  ((this.pageIndex-1) * this.paginate)+(index+1),
                    name: rider.customer_name +' '+ rider.customer_last_name,
                    puntos: this.floor(rider.total_points),
                    customer_uuid: rider.customer_uuid,
                    email: rider.customer_email,
                    telefono: rider.customer_phone,
                    register_date: rider.register_date,
                    color: index % 2 === 0 ? '#e5e5e5' : '#fff',
                }));

                this.length = response.data.clientes.total;
                this.pageIndex = response.data.clientes.current_page;
                this.uuids = datosR;
                this.dataSource.data = this.uuids;
            },
            error: () => {
        
            }
        })

    }

    updatePoints( customer_uuid:string ): void {
        
        const bottomSheetRef = this._bottomSheet.open(NewRiderkmComponent, {
            data: {
                customer_uuid: customer_uuid,
            }
        });

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
                ];
            }else{
                if(this.anchoW > 901 && this.anchoW < 1320){
                    this.datosM = 'tablet';
                    this.displayedColumns = [
                        'id',
                        'name',
                        'telefono',
                    ];
                }else{
                    this.displayedColumns = [
                        'id',
                        'name',
                        'telefono',
                        'email',
                        'puntos',
                        'register_date',
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

    
    floor(value: number): number {
        return Math.floor(value);
    }
}
