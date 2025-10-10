import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { VenderAutosRoutingModule } from './vender-autos-routing.module';
import { VenderAutosComponent } from './pages/vender-autos/vender-autos.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';


@NgModule({
  declarations: [
    VenderAutosComponent
  ],
  imports: [
    AngularMaterialModule,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    VenderAutosRoutingModule
  ]
})
export class VenderAutosModule { }
