import { Component, Input, OnInit, OnDestroy, AfterViewInit, ViewChild, ElementRef, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HomeTestimonial } from '@interfaces/admin.interfaces';

@Component({
  selector: 'app-success-day',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './success-day.component.html',
  styleUrls: ['./success-day.component.css'],
})
export class SuccessDayComponent implements OnInit, OnDestroy, AfterViewInit {
  @Input() testimonials: HomeTestimonial[] = [];
  @ViewChild('track') trackElement!: ElementRef<HTMLDivElement>;

  currentIndex = 0;
  private autoInterval?: ReturnType<typeof setInterval>;
  private touchStartX = 0;

  ngOnInit(): void {}

  ngAfterViewInit(): void {
    if (this.testimonials.length > 0) {
      this.startAutoPlay();
    }
  }

  ngOnDestroy(): void {
    this.stopAutoPlay();
  }

  getVisibleCards(): number {
    return typeof window !== 'undefined' && window.innerWidth >= 768 ? 3 : 1;
  }

  getMaxIndex(): number {
    return Math.max(0, this.testimonials.length - this.getVisibleCards());
  }

  get dots(): number[] {
    return Array.from({ length: this.getMaxIndex() + 1 }, (_, i) => i);
  }

  nextSlide(): void {
    this.currentIndex = this.currentIndex >= this.getMaxIndex() ? 0 : this.currentIndex + 1;
    this.updateCarousel();
  }

  prevSlide(): void {
    this.currentIndex = this.currentIndex <= 0 ? this.getMaxIndex() : this.currentIndex - 1;
    this.updateCarousel();
  }

  goToSlide(index: number): void {
    this.currentIndex = Math.min(index, this.getMaxIndex());
    this.updateCarousel();
  }

  updateCarousel(): void {
    if (!this.trackElement || this.testimonials.length === 0) return;
    const track = this.trackElement.nativeElement;
    const firstCard = track.querySelector('.sd-card') as HTMLElement;
    if (!firstCard) return;
    const gap = 24; // gap-6 = 1.5rem = 24px
    const cardWidth = firstCard.offsetWidth + gap;
    track.style.transform = `translateX(-${this.currentIndex * cardWidth}px)`;
  }

  startAutoPlay(): void {
    this.stopAutoPlay();
    this.autoInterval = setInterval(() => this.nextSlide(), 5000);
  }

  stopAutoPlay(): void {
    if (this.autoInterval) {
      clearInterval(this.autoInterval);
      this.autoInterval = undefined;
    }
  }

  onTouchStart(event: TouchEvent): void {
    this.touchStartX = event.changedTouches[0].screenX;
  }

  onTouchEnd(event: TouchEvent): void {
    const touchEndX = event.changedTouches[0].screenX;
    const diff = this.touchStartX - touchEndX;
    if (Math.abs(diff) > 50) {
      this.stopAutoPlay();
      if (diff > 0) { this.nextSlide(); } else { this.prevSlide(); }
      this.startAutoPlay();
    }
  }

  @HostListener('window:resize')
  onResize(): void {
    if (this.currentIndex > this.getMaxIndex()) {
      this.currentIndex = this.getMaxIndex();
    }
    this.updateCarousel();
  }
}
