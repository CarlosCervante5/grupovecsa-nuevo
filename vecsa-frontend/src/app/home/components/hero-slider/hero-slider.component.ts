import {
  Component,
  Input,
  OnInit,
  OnDestroy,
  HostListener,
} from '@angular/core';
import { CommonModule } from '@angular/common';

import { HomeSlide } from '@interfaces/admin.interfaces';

@Component({
  selector: 'app-hero-slider',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './hero-slider.component.html',
  styleUrls: ['./hero-slider.component.css'],
})
export class HeroSliderComponent implements OnInit, OnDestroy {
  @Input() slides: HomeSlide[] = [];

  currentSlide = 0;
  private autoPlayInterval?: ReturnType<typeof setInterval>;
  private touchStartX = 0;

  ngOnInit(): void {
    if (this.slides.length > 0) {
      this.startAutoPlay();
    }
  }

  ngOnDestroy(): void {
    this.stopAutoPlay();
  }

  showSlide(index: number): void {
    if (this.slides.length === 0) return;
    this.currentSlide = index;
  }

  nextSlide(): void {
    if (this.slides.length === 0) return;
    this.currentSlide = (this.currentSlide + 1) % this.slides.length;
  }

  prevSlide(): void {
    if (this.slides.length === 0) return;
    this.currentSlide =
      (this.currentSlide - 1 + this.slides.length) % this.slides.length;
  }

  startAutoPlay(): void {
    this.stopAutoPlay();
    this.autoPlayInterval = setInterval(() => this.nextSlide(), 8000);
  }

  stopAutoPlay(): void {
    if (this.autoPlayInterval != null) {
      clearInterval(this.autoPlayInterval);
      this.autoPlayInterval = undefined;
    }
  }

  onMouseEnter(): void {
    this.stopAutoPlay();
  }

  onMouseLeave(): void {
    this.startAutoPlay();
  }

  onTouchStart(event: TouchEvent): void {
    this.touchStartX = event.changedTouches[0].screenX;
  }

  onTouchEnd(event: TouchEvent): void {
    const touchEndX = event.changedTouches[0].screenX;
    const diff = this.touchStartX - touchEndX;
    if (Math.abs(diff) > 50) {
      this.stopAutoPlay();
      if (diff > 0) {
        this.nextSlide();
      } else {
        this.prevSlide();
      }
      this.startAutoPlay();
    }
  }

  onArrowClick(direction: 'next' | 'prev'): void {
    this.stopAutoPlay();
    if (direction === 'next') {
      this.nextSlide();
    } else {
      this.prevSlide();
    }
    this.startAutoPlay();
  }

  onIndicatorClick(index: number): void {
    this.stopAutoPlay();
    this.showSlide(index);
    this.startAutoPlay();
  }

  @HostListener('document:keydown', ['$event'])
  onKeyDown(event: KeyboardEvent): void {
    if (event.key === 'ArrowLeft') {
      this.onArrowClick('prev');
    } else if (event.key === 'ArrowRight') {
      this.onArrowClick('next');
    }
  }
}
