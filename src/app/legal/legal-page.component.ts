import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, OnDestroy, OnInit, inject, signal } from '@angular/core';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { Meta, Title } from '@angular/platform-browser';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-legal-page',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './legal-page.component.html',
  styleUrls: ['./legal-page.component.css'],
})
export class LegalPageComponent implements OnInit, OnDestroy {
  private readonly http = inject(HttpClient);
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
      legalAsset?: string;
      pageTitle?: string;
      metaDescription?: string;
    };
    const asset = data.legalAsset ?? 'aviso-privacidad';
    const pageTitle = data.pageTitle ?? 'Grupo VECSA';
    const metaDescription = data.metaDescription ?? '';

    this.title.setTitle(pageTitle);
    if (metaDescription) {
      this.meta.updateTag({ name: 'description', content: metaDescription });
    }

    const url = `assets/legal/${asset}.html`;
    this.sub = this.http.get(url, { responseType: 'text' }).subscribe({
      next: (html) => {
        this.safeBody = this.sanitizer.bypassSecurityTrustHtml(html);
        this.loading.set(false);
        this.loadError.set(false);
      },
      error: () => {
        this.loading.set(false);
        this.loadError.set(true);
      },
    });
  }

  ngOnDestroy(): void {
    this.sub?.unsubscribe();
  }
}
