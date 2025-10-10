import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { RequestQuoteRoutingModule } from './request-quote-routing.module';
import { QuoteRequestComponent } from './components/quote-request/quote-request.component';
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';


@NgModule({
  declarations: [
    QuoteRequestComponent
  ],
  imports: [
    CommonModule,
    AngularMaterialModule,
    FormsModule,
    ReactiveFormsModule,
    RequestQuoteRoutingModule
  ]
})
export class RequestQuoteModule { }
