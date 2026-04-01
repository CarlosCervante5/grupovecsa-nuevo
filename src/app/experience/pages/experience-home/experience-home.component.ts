import { Component, OnInit } from '@angular/core';
import { ExperienceService, ExperienceEvent, ExperiencePost } from '@services/experience.service';

@Component({
  selector: 'app-experience-home',
  templateUrl: './experience-home.component.html',
  styleUrls: ['./experience-home.component.css'],
  standalone: false
})
export class ExperienceHomeComponent implements OnInit {

  upcomingEvents: ExperienceEvent[] = [];
  pastEvents: ExperienceEvent[] = [];
  posts: ExperiencePost[] = [];

  loadingUpcoming = true;
  loadingGallery = true;
  loadingPosts = true;

  constructor(private experienceService: ExperienceService) {}

  ngOnInit(): void {
    this.experienceService.getUpcomingEvents().subscribe({
      next: (res) => { this.upcomingEvents = res.data?.events ?? []; this.loadingUpcoming = false; },
      error: () => { this.loadingUpcoming = false; }
    });

    this.experienceService.getPastEvents(1, 6).subscribe({
      next: (res) => { this.pastEvents = res.data?.gallery?.data ?? []; this.loadingGallery = false; },
      error: () => { this.loadingGallery = false; }
    });

    this.experienceService.getPosts(1, 6).subscribe({
      next: (res) => { this.posts = res.data?.posts?.data ?? []; this.loadingPosts = false; },
      error: () => { this.loadingPosts = false; }
    });
  }

  formatDate(dateStr: string): string {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00Z');
    return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
  }

  formatShortDate(dateStr: string): string {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00Z');
    return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'long', timeZone: 'UTC' });
  }
}
