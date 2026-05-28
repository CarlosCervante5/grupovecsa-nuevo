import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import {
  ExperienceGalleryImage,
  ExperiencePostDetail,
  ExperienceService,
} from '@services/experience.service';

@Component({
  selector: 'app-gallery-detail',
  templateUrl: './gallery-detail.component.html',
  styleUrls: ['./gallery-detail.component.css'],
  standalone: false,
})
export class GalleryDetailComponent implements OnInit {
  post: ExperiencePostDetail | null = null;
  photos: ExperienceGalleryImage[] = [];
  loading = true;
  error: string | null = null;
  lightboxSrc: string | null = null;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private experience: ExperienceService
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
        if (!p || p.experience_post_type !== 'gallery') {
          this.error = 'Galería no encontrada';
          this.loading = false;
          return;
        }
        this.post = p;
        this.photos = p.gallery_images ?? [];
        this.loading = false;
      },
      error: () => {
        this.error = 'No se pudo cargar la galería';
        this.loading = false;
      },
    });
  }

  openLightbox(src: string): void {
    this.lightboxSrc = src;
  }

  closeLightbox(): void {
    this.lightboxSrc = null;
  }

  formatDate(dateStr: string | null | undefined): string {
    if (!dateStr) return '';
    const d = new Date(dateStr.replace(' ', 'T') + 'Z');
    return d.toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' });
  }
}
