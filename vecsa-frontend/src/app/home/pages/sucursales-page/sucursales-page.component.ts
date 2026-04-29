import { Component, OnDestroy, OnInit, Renderer2 } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Meta, Title } from '@angular/platform-browser';
import { RouterLink } from '@angular/router';

import { LocationsComponent } from '../../components/locations/locations.component';
import { DisclaimerComponent } from '../../components/disclaimer/disclaimer.component';
import { HomeFooterComponent } from '../../components/home-footer/home-footer.component';
import { FloatingActionsComponent } from '../../components/floating-actions/floating-actions.component';

@Component({
  selector: 'app-sucursales-page',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    LocationsComponent,
    DisclaimerComponent,
    HomeFooterComponent,
    FloatingActionsComponent,
  ],
  templateUrl: './sucursales-page.component.html',
})
export class SucursalesPageComponent implements OnInit, OnDestroy {
  private originalFontSize = '';

  constructor(
    private readonly title: Title,
    private readonly meta: Meta,
    private readonly renderer: Renderer2,
  ) {}

  ngOnInit(): void {
    this.title.setTitle('Sucursales | Grupo VECSA');
    this.meta.updateTag({
      name: 'description',
      content:
        'Ubicación y contacto de las agencias Grupo VECSA en Puebla, Hidalgo, Veracruz, Oaxaca y más.',
    });

    const html = document.documentElement;
    this.originalFontSize = html.style.fontSize;
    this.renderer.addClass(html, 'home-page-active');
  }

  ngOnDestroy(): void {
    const html = document.documentElement;
    this.renderer.removeClass(html, 'home-page-active');
    if (this.originalFontSize) {
      this.renderer.setStyle(html, 'font-size', this.originalFontSize);
    } else {
      this.renderer.removeStyle(html, 'font-size');
    }
  }
}
