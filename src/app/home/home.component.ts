import { Component, OnInit, OnDestroy, Renderer2 } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Subscription, forkJoin } from 'rxjs';

import { HomePublicService } from './services/home-public.service';
import { HomeSlide, HomeTestimonial } from '@interfaces/admin.interfaces';
import { HeroSliderComponent } from './components/hero-slider/hero-slider.component';
import { BrandsComponent } from './components/brands/brands.component';
import { ServicesSectionComponent } from './components/services-section/services-section.component';
import { SuccessDayComponent } from './components/success-day/success-day.component';
import { LocationsComponent } from './components/locations/locations.component';
import { DisclaimerComponent } from './components/disclaimer/disclaimer.component';
import { HomeFooterComponent } from './components/home-footer/home-footer.component';
import { FloatingActionsComponent } from './components/floating-actions/floating-actions.component';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [
    CommonModule,
    HeroSliderComponent,
    BrandsComponent,
    ServicesSectionComponent,
    SuccessDayComponent,
    LocationsComponent,
    DisclaimerComponent,
    HomeFooterComponent,
    FloatingActionsComponent,
  ],
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css'],
})
export class HomeComponent implements OnInit, OnDestroy {
  slides: HomeSlide[] = [];
  testimonials: HomeTestimonial[] = [];
  isLoading = true;

  private dataSubscription?: Subscription;
  private originalFontSize = '';

  constructor(
    private homePublicService: HomePublicService,
    private renderer: Renderer2,
  ) {}

  ngOnInit(): void {
    // The global styles.css sets html { font-size: 62.5% } for the legacy ABCars app,
    // which breaks all Tailwind rem-based sizes. Add a class to override for the home page.
    const html = document.documentElement;
    this.originalFontSize = html.style.fontSize;
    this.renderer.addClass(html, 'home-page-active');

    this.loadHomeData();
  }

  ngOnDestroy(): void {
    // Restore the original html font-size for other routes
    const html = document.documentElement;
    this.renderer.removeClass(html, 'home-page-active');
    if (this.originalFontSize) {
      this.renderer.setStyle(html, 'font-size', this.originalFontSize);
    } else {
      this.renderer.removeStyle(html, 'font-size');
    }

    this.dataSubscription?.unsubscribe();
  }

  private loadHomeData(): void {
    this.isLoading = true;
    this.dataSubscription = forkJoin({
      slides: this.homePublicService.getSlides(),
      testimonials: this.homePublicService.getTestimonials(),
    }).subscribe({
      next: ({ slides, testimonials }) => {
        this.slides = slides;
        this.testimonials = testimonials;
        this.isLoading = false;
      },
      error: (err) => {
        console.error('Error cargando datos del home:', err);
        this.slides = [];
        this.testimonials = [];
        this.isLoading = false;
      },
    });
  }
}
