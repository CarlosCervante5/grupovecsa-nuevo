
import { Component, ViewChild, Output, EventEmitter, OnInit, Inject } from '@angular/core';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';

//prueba
import { RiderResponse, tableData, Rider } from '@interfaces/admin.interfaces';
import { AdminService } from '@services/admin.service';
import { Router } from '@angular/router';
import {reload} from '@helpers/session.helper';


@Component({
    selector: 'app-table-coupons',
    templateUrl: './table-coupons.component.html',
    styleUrls: ['./table-coupons.component.css'],
    standalone: false
})
export class TableCouponsComponent implements OnInit{
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

        @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
        private _bottomSheetRef: MatBottomSheetRef<any>,
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
    
}
