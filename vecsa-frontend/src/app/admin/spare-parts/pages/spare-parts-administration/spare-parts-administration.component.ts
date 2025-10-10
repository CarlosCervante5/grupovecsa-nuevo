import { Component, ViewChild, type OnInit } from '@angular/core';
import { MatTableDataSource } from '@angular/material/table';

import { SparePartsService } from '@services/spare-parts.service';

import { MatPaginator } from '@angular/material/paginator';
import { GetSearchParts } from '@interfaces/getSearchParts.interfaces';

@Component({
    selector: 'app-spare-parts-administration',
    templateUrl: './spare-parts-administration.component.html',
    styleUrls: ['./spare-parts-administration.component.css'],
    standalone: false
})
export class SparePartsAdministrationComponent implements OnInit {

    public displayedColumns: string[] = ['id', 'technicianName', 'technicianSurname', 'brandName', 'modelName', 'vin', 'year', 'status', 'statusRefac', 'actions'];
    public dataSource!: MatTableDataSource<any>

    public length: number = 0;
    public pageIndex: number = 1;

    @ViewChild(MatPaginator) paginator!: MatPaginator;

    constructor(
        private _sparePartsService: SparePartsService
    ) {}

    ngOnInit(): void { 
        this.getSearchParts(this.pageIndex);
    }

    // public applyFilter(event: Event) {
    //     const filterValue = (event.target as HTMLInputElement).value;
    //     this.dataSource.filter = filterValue.trim().toLowerCase();
    
    //     if (this.dataSource.paginator) {
    //       this.dataSource.paginator.firstPage();
    //     }
    // }

    public getSearchParts(page?: number) {
        this._sparePartsService.getSearchParts(page)
            .subscribe({
                next: ( resp: GetSearchParts) => {
                    console.log(resp.data.data);
                    
                    this.paginator.length = resp.data.total;
                    this.dataSource = new MatTableDataSource(resp.data.data);
                }
            });
    }

}
