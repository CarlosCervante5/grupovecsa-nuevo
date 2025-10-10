import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { LandingsRoutingModule } from './landings-routing.module';
import { AngularMaterialModule } from '../angular-material/angular-material.module';
import { ReactiveFormsModule } from '@angular/forms';

import { BmwComponent } from './aftersale/bmw/bmw.component';
import { MiniComponent } from './aftersale/mini/mini.component';
import { SeviceWithImageComponent } from './components/sevice-with-image/sevice-with-image.component';
import { FormularioCitaComponent } from './components/formulario-cita/formulario-cita.component';
import { BannerComponent } from './components/banner/banner.component';
import { BarMessageComponent } from './components/bar-message/bar-message.component';
import { MapVecsaComponent } from './components/map-vecsa/map-vecsa.component';
 

@NgModule({
  declarations: [
    BmwComponent,
    MiniComponent,
    SeviceWithImageComponent,
    FormularioCitaComponent,
    BannerComponent,
    BarMessageComponent,
    MapVecsaComponent
  ],
  imports: [
    AngularMaterialModule,
    CommonModule,
    LandingsRoutingModule,  
    ReactiveFormsModule  
  ]
})
export class LandingsModule { }
