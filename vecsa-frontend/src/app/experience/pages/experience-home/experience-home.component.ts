import { Component, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ExperienceService, ExperienceEvent, ExperiencePost } from '@services/experience.service';

@Component({
  selector: 'app-experience-home',
  templateUrl: './experience-home.component.html',
  styleUrls: ['./experience-home.component.css'],
  standalone: false,
})
export class ExperienceHomeComponent implements OnInit, OnDestroy {
  upcomingEvents: ExperienceEvent[] = [];
  pastEvents: ExperienceEvent[] = [];
  posts: ExperiencePost[] = [];

  loadingUpcoming = true;
  loadingGallery = true;
  loadingPosts = true;

  /** Índice del slide visible en el carrusel */
  carouselIndex = 0;
  private autoplayId: ReturnType<typeof setInterval> | null = null;

  /** Mes mostrado en el calendario (día 1 a las 12:00 local) */
  calendarMonth = this.startOfMonth(new Date());

  readonly weekdayLabels = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

  constructor(
    private experienceService: ExperienceService,
    private router: Router,
    private snack: MatSnackBar
  ) {}

  ngOnInit(): void {
    this.experienceService.getUpcomingEvents().subscribe({
      next: (res) => {
        this.upcomingEvents = res.data?.events ?? [];
        this.loadingUpcoming = false;
        this.carouselIndex = 0;
        this.startAutoplay();
      },
      error: () => {
        this.loadingUpcoming = false;
      },
    });

    this.experienceService.getPastEvents(1, 6).subscribe({
      next: (res) => {
        this.pastEvents = res.data?.gallery?.data ?? [];
        this.loadingGallery = false;
      },
      error: () => {
        this.loadingGallery = false;
      },
    });

    this.experienceService.getPosts(1, 6).subscribe({
      next: (res) => {
        this.posts = res.data?.posts?.data ?? [];
        this.loadingPosts = false;
      },
      error: () => {
        this.loadingPosts = false;
      },
    });
  }

  ngOnDestroy(): void {
    this.stopAutoplay();
  }

  get carouselSlide(): ExperienceEvent | null {
    if (!this.upcomingEvents.length) {
      return null;
    }
    const i = ((this.carouselIndex % this.upcomingEvents.length) + this.upcomingEvents.length) % this.upcomingEvents.length;
    return this.upcomingEvents[i] ?? null;
  }

  private startAutoplay(): void {
    this.stopAutoplay();
    if (this.upcomingEvents.length < 2) {
      return;
    }
    this.autoplayId = setInterval(() => {
      this.carouselIndex = (this.carouselIndex + 1) % this.upcomingEvents.length;
    }, 8000);
  }

  private stopAutoplay(): void {
    if (this.autoplayId != null) {
      clearInterval(this.autoplayId);
      this.autoplayId = null;
    }
  }

  prevSlide(): void {
    if (!this.upcomingEvents.length) {
      return;
    }
    this.carouselIndex = (this.carouselIndex - 1 + this.upcomingEvents.length) % this.upcomingEvents.length;
    this.restartAutoplay();
  }

  nextSlide(): void {
    if (!this.upcomingEvents.length) {
      return;
    }
    this.carouselIndex = (this.carouselIndex + 1) % this.upcomingEvents.length;
    this.restartAutoplay();
  }

  goToSlide(i: number): void {
    this.carouselIndex = i;
    this.restartAutoplay();
  }

  private restartAutoplay(): void {
    this.startAutoplay();
  }

  selectFromList(ev: ExperienceEvent): void {
    const i = this.upcomingEvents.findIndex((e) => e.uuid === ev.uuid);
    if (i >= 0) {
      this.carouselIndex = i;
      this.restartAutoplay();
    }
  }

  /** Días del mes con al menos un evento (YYYY-MM-DD) */
  eventDayKeys(): Set<string> {
    const s = new Set<string>();
    for (const ev of this.upcomingEvents) {
      if (ev.begin_date) {
        s.add(ev.begin_date.slice(0, 10));
      }
    }
    return s;
  }

  calendarTitle(): string {
    return this.calendarMonth.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' });
  }

  prevCalendarMonth(): void {
    const d = new Date(this.calendarMonth);
    d.setMonth(d.getMonth() - 1);
    this.calendarMonth = this.startOfMonth(d);
  }

  nextCalendarMonth(): void {
    const d = new Date(this.calendarMonth);
    d.setMonth(d.getMonth() + 1);
    this.calendarMonth = this.startOfMonth(d);
  }

  /**
   * Celdas del calendario: null = hueco, número = día del mes.
   */
  calendarCells(): { day: number; key: string; hasEvent: boolean }[] {
    const y = this.calendarMonth.getFullYear();
    const m = this.calendarMonth.getMonth();
    const firstDow = new Date(y, m, 1).getDay();
    const daysInMonth = new Date(y, m + 1, 0).getDate();
    const keys = this.eventDayKeys();
    const cells: { day: number; key: string; hasEvent: boolean }[] = [];

    for (let i = 0; i < firstDow; i++) {
      cells.push({ day: 0, key: '', hasEvent: false });
    }
    for (let d = 1; d <= daysInMonth; d++) {
      const key = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
      cells.push({ day: d, key, hasEvent: keys.has(key) });
    }
    return cells;
  }

  onCalendarDayClick(key: string, hasEvent: boolean): void {
    if (!hasEvent || !key) {
      return;
    }
    const match = this.upcomingEvents.filter((e) => (e.begin_date ?? '').slice(0, 10) === key);
    if (match.length) {
      const first = match.sort((a, b) => (a.begin_date ?? '').localeCompare(b.begin_date ?? ''))[0];
      this.selectFromList(first);
    }
  }

  formatDate(dateStr: string): string {
    if (!dateStr) {
      return '';
    }
    const d = new Date(dateStr + 'T12:00:00');
    return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' });
  }

  formatShortDate(dateStr: string): string {
    if (!dateStr) {
      return '';
    }
    const d = new Date(dateStr + 'T12:00:00');
    return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'long' });
  }

  /** Rango corto si end_date distinto a begin_date */
  formatDateRange(ev: ExperienceEvent): string {
    const a = (ev.begin_date ?? '').slice(0, 10);
    const b = (ev.end_date ?? '').slice(0, 10);
    if (!a) {
      return '';
    }
    if (!b || a === b) {
      return this.formatShortDate(ev.begin_date);
    }
    return `${this.formatShortDate(ev.begin_date)} – ${this.formatShortDate(ev.end_date)}`;
  }

  reservar(ev: ExperienceEvent): void {
    const slug = ev.story_slug?.trim();
    if (slug) {
      void this.router.navigate(['/experience', 'historia', slug]);
      return;
    }
    this.snack.open('Para reservar tu lugar, contacta a BMW Motorrad VECSA Hidalgo.', 'OK', { duration: 5000 });
  }

  trackByUuid(_i: number, ev: ExperienceEvent): string {
    return ev.uuid;
  }

  private startOfMonth(d: Date): Date {
    return new Date(d.getFullYear(), d.getMonth(), 1, 12, 0, 0, 0);
  }
}
