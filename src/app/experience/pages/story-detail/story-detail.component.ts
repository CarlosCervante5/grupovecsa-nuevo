import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { ExperienceService, ExperiencePostDetail } from '@services/experience.service';

@Component({
  selector: 'app-story-detail',
  templateUrl: './story-detail.component.html',
  styleUrls: ['./story-detail.component.css'],
  standalone: false,
})
export class StoryDetailComponent implements OnInit {
  post: ExperiencePostDetail | null = null;
  safeBody: SafeHtml | null = null;
  loading = true;
  error: string | null = null;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private experience: ExperienceService,
    private sanitizer: DomSanitizer
  ) {}

  ngOnInit(): void {
    const slug = this.route.snapshot.paramMap.get('slug');
    if (!slug) {
      this.router.navigateByUrl('/experience');
      return;
    }
    this.experience.getPostDetail({ slug }).subscribe({
      next: (res) => {
        const p = res.data?.post;
        if (!p) {
          this.error = 'Historia no encontrada';
          this.loading = false;
          return;
        }
        this.post = p;
        this.safeBody = p.body_html
          ? this.sanitizer.bypassSecurityTrustHtml(p.body_html)
          : null;
        this.loading = false;
      },
      error: () => {
        this.error = 'No se pudo cargar la historia';
        this.loading = false;
      },
    });
  }

  formatDate(dateStr: string): string {
    if (!dateStr) return '';
    const d = new Date(dateStr.replace(' ', 'T') + 'Z');
    return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
  }
}
