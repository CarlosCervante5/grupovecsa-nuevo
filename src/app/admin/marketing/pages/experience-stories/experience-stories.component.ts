import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { PageEvent } from '@angular/material/paginator';
import { reload } from '@helpers/session.helper';
import {
  ExperienceStoriesAdminService,
  ExperienceStoryRow,
} from '@services/experience-stories-admin.service';

@Component({
  selector: 'app-experience-stories',
  templateUrl: './experience-stories.component.html',
  styleUrls: ['./experience-stories.component.css'],
  standalone: false,
})
export class ExperienceStoriesComponent {
  stories: ExperienceStoryRow[] = [];
  loading = true;
  saving = false;
  importing = false;
  showForm = false;
  editing: ExperienceStoryRow | null = null;

  title = '';
  urlName = '';
  excerpt = '';
  bodyHtml = '';
  imageUrl = '';
  status: 'published' | 'draft' | 'unpublished' = 'published';
  /** YYYY-MM-DD para agenda Experience (posts categoría evento) */
  eventBeginDate = '';
  eventEndDate = '';

  lastImportSummary: string | null = null;

  pageIndex = 0;
  pageSize = 15;
  totalStories = 0;

  constructor(
    private api: ExperienceStoriesAdminService,
    private snack: MatSnackBar,
    private router: Router
  ) {
    this.load();
  }

  load(): void {
    this.loading = true;
    const page = this.pageIndex + 1;
    this.api.search(page, this.pageSize).subscribe({
      next: (res) => {
        const p = res.data?.posts;
        this.stories = p?.data ?? [];
        this.totalStories = p?.total ?? 0;
        if (p?.per_page != null) {
          this.pageSize = p.per_page;
        }
        this.loading = false;
      },
      error: (err) => {
        this.loading = false;
        reload(err, this.router);
      },
    });
  }

  onPageChange(ev: PageEvent): void {
    this.pageSize = ev.pageSize;
    this.pageIndex = ev.pageIndex;
    this.load();
  }

  tagsLine(row: ExperienceStoryRow): string {
    const t = row.wp_tags;
    if (!t?.length) {
      return '—';
    }
    return t.join(', ');
  }

  openCreate(): void {
    this.editing = null;
    this.title = '';
    this.urlName = '';
    this.excerpt = '';
    this.bodyHtml = '';
    this.imageUrl = '';
    this.status = 'published';
    this.eventBeginDate = '';
    this.eventEndDate = '';
    this.showForm = true;
  }

  openEdit(row: ExperienceStoryRow): void {
    this.editing = row;
    this.title = row.title;
    this.urlName = row.url_name;
    this.excerpt = row.excerpt ?? '';
    this.bodyHtml = row.body_html ?? '';
    this.imageUrl = row.image_path ?? '';
    this.status = (row.status as 'published' | 'draft' | 'unpublished') || 'published';
    this.eventBeginDate = this.toDateInput(row.event_begin_date);
    this.eventEndDate = this.toDateInput(row.event_end_date);
    this.showForm = true;
  }

  private toDateInput(v: string | null | undefined): string {
    if (!v) return '';
    return v.slice(0, 10);
  }

  cancelForm(): void {
    this.showForm = false;
    this.editing = null;
  }

  save(): void {
    if (!this.title.trim()) {
      this.snack.open('El título es obligatorio', 'OK', { duration: 3000 });
      return;
    }
    this.saving = true;
    const payload: Record<string, unknown> = {
      title: this.title.trim(),
      url_name: this.urlName.trim() || undefined,
      excerpt: this.excerpt || null,
      body_html: this.bodyHtml || null,
      image_url: this.imageUrl.trim() || null,
      status: this.status,
      event_begin_date: this.eventBeginDate.trim() || null,
      event_end_date: this.eventEndDate.trim() || null,
    };
    const req = this.editing
      ? this.api.update({ ...payload, uuid: this.editing.uuid })
      : this.api.store(payload);
    req.subscribe({
      next: () => {
        this.saving = false;
        this.snack.open(this.editing ? 'Historia actualizada' : 'Historia creada', 'OK', { duration: 2500 });
        this.cancelForm();
        this.load();
      },
      error: (err) => {
        this.saving = false;
        reload(err, this.router);
      },
    });
  }

  remove(row: ExperienceStoryRow): void {
    if (!confirm('¿Eliminar esta historia?')) return;
    this.api.delete(row.uuid).subscribe({
      next: () => {
        this.snack.open('Eliminada', 'OK', { duration: 2500 });
        this.load();
      },
      error: (err) => reload(err, this.router),
    });
  }

  importWp(): void {
    if (!confirm('¿Importar posts desde vecsaexperience.com? Se actualizarán entradas ya vinculadas por ID de WordPress.')) {
      return;
    }
    this.importing = true;
    this.lastImportSummary = null;
    this.api.importWordpress('https://vecsaexperience.com').subscribe({
      next: (res) => {
        this.importing = false;
        const d = res.data;
        this.lastImportSummary = `Importadas: ${d.imported}, errores: ${d.skipped}`;
        this.snack.open(this.lastImportSummary, 'OK', { duration: 6000 });
        this.load();
      },
      error: (err) => {
        this.importing = false;
        reload(err, this.router);
      },
    });
  }
}
