import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { StaffRoutingModule } from './staff-routing.module';
import { StaffLayoutComponent } from './pages/layout/staff-layout.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { AdminModule } from '../admin.module';
import { TableComponent } from './components/table/table.component';
import { RidersComponent } from './pages/riders/riders.component';
import { MatTableModule } from '@angular/material/table';
import { AngularMaterialModule } from '../../angular-material/angular-material.module';
import { UpdateRiderModalComponent } from './components/update-rider-modal/update-rider-modal.component';
import { NewCustomerComponent } from './components/new-customer/new-customer.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { SkCubeComponent } from '@components/sk-cube/sk-cube.component';
import { UpdateInfoRiderComponent } from './components/update-info-rider/update-info-rider.component';
import { MatChipsModule } from '@angular/material/chips';
import { SalesComponent } from './pages/sales/sales.component';
import { NewSaleComponent } from './components/new-sale/new-sale.component';
import { NewRiderkmComponent } from './components/new-riderkm/new-riderkm.component';
import { RedeemCouponsComponent } from './components/redeem-coupons/redeem-coupons.component';
import { TableCouponsComponent } from './components/table-coupons/table-coupons.component';


@NgModule({
    declarations: [
        StaffLayoutComponent,
        DashboardComponent,
        TableComponent,
        RidersComponent,
        UpdateRiderModalComponent,
        NewCustomerComponent,
        UpdateInfoRiderComponent,
        SalesComponent,
        NewSaleComponent,
        NewRiderkmComponent,
        RedeemCouponsComponent,
        TableCouponsComponent
    ],
    imports: [
        CommonModule,
        StaffRoutingModule,
        AdminModule,
        MatTableModule,
        AngularMaterialModule,
        ReactiveFormsModule,
        FormsModule,
        MatFormFieldModule,
        MatInputModule,
        SkCubeComponent,
        MatChipsModule,
    ],
    exports: [
        TableComponent,
        NewCustomerComponent
    ],
    schemas:[CUSTOM_ELEMENTS_SCHEMA]
})
export class StaffModule { }
