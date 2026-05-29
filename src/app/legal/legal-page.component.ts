import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, OnDestroy, OnInit, inject, signal } from '@angular/core';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { Meta, Title } from '@angular/platform-browser';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { Subscription } from 'rxjs';
import { LegalAdminService } from '@services/legal-admin.service';

/** Archivo en assets/legal/ si la API no está disponible. */
const LEGAL_ASSET_BY_SLUG: Record<string, string> = {
  privacidad: 'aviso-privacidad',
  condiciones: 'condiciones-uso',
  devoluciones: 'politicas-devolucion',
  lealtad: 'lealtad',
  cookies: 'uso-cookies',
};

@Component({
  selector: 'app-legal-page',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './legal-page.component.html',
  styleUrls: ['./legal-page.component.css'],
})
export class LegalPageComponent implements OnInit, OnDestroy {
  private readonly http = inject(HttpClient);
  private readonly legalAdmin = inject(LegalAdminService);
  private readonly sanitizer = inject(DomSanitizer);
  private readonly route = inject(ActivatedRoute);
  private readonly title = inject(Title);
  private readonly meta = inject(Meta);

  readonly loading = signal(true);
  readonly loadError = signal(false);
  safeBody: SafeHtml | null = null;

  private sub?: Subscription;

  ngOnInit(): void {
    const data = this.route.snapshot.data as {
      legalSlug?: string;
      pageTitle?: string;
      metaDescription?: string;
    };
    const legalSlug = data.legalSlug ?? 'privacidad';
    const fallbackTitle = data.pageTitle ?? 'Grupo VECSA';
    const fallbackMeta = data.metaDescription ?? '';
    const asset = LEGAL_ASSET_BY_SLUG[legalSlug] ?? legalSlug;

    this.sub = this.legalAdmin.getPublic(legalSlug).subscribe({
      next: (doc) => {
        this.applyMeta(doc.title || fallbackTitle, doc.meta_description || fallbackMeta);
        this.safeBody = this.sanitizer.bypassSecurityTrustHtml(doc.body_html ?? '');
        this.loading.set(false);
        this.loadError.set(false);
      },
      error: () => {
        this.http.get(`assets/legal/${asset}.html`, { responseType: 'text' }).subscribe({
          next: (html) => {
            this.applyMeta(fallbackTitle, fallbackMeta);
            this.safeBody = this.sanitizer.bypassSecurityTrustHtml(html);
            this.loading.set(false);
            this.loadError.set(false);
          },
          error: () => {
            this.loading.set(false);
            this.loadError.set(true);
          },
        });
      },
    });
  }

  ngOnDestroy(): void {
    this.sub?.unsubscribe();
  }

  private applyMeta(pageTitle: string, metaDescription: string): void {
    this.title.setTitle(pageTitle);
    if (metaDescription) {
      this.meta.updateTag({ name: 'description', content: metaDescription });
    }
  }
}
