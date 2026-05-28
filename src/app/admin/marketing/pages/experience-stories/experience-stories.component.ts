import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { MatDialog } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { PageEvent } from '@angular/material/paginator';
import { reload } from '@helpers/session.helper';
import {
  ExperienceStoriesAdminService,
  ExperienceStoryRow,
} from '@services/experience-stories-admin.service';
import { ExperienceStoryFormDialogComponent } from './experience-story-form-dialog.component';

@Component({
  selector: 'app-experience-stories',
  templateUrl: './experience-stories.component.html',
  styleUrls: ['./experience-stories.component.css'],
  standalone: false,
})
export class ExperienceStoriesComponent {
  stories: ExperienceStoryRow[] = [];
  loading = true;
  importing = false;

  lastImportSummary: string | null = null;

  pageIndex = 0;
  pageSize = 15;
  totalStories = 0;

  constructor(
    private api: ExperienceStoriesAdminService,
    private snack: MatSnackBar,
    private router: Router,
    private dialog: MatDialog
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

  postTypeLabel(row: ExperienceStoryRow): string {
    const t = row.experience_post_type;
    if (t === 'gallery') {
      const n = row.gallery_images_count ?? row.gallery_images?.length ?? 0;
      return `Galería (${n} fotos)`;
    }
    if (t === 'event') {
      return 'Evento';
    }
    if (t === 'story') {
      return 'Historia';
    }
    return row.event_begin_date ? 'Evento' : 'Historia';
  }

  tagsLine(row: ExperienceStoryRow): string {
    const t = row.wp_tags;
    if (!t?.length) {
      return '—';
    }
    return t.join(', ');
  }

  openCreate(): void {
    this.openStoryDialog(null);
  }

  openEdit(row: ExperienceStoryRow): void {
    this.openStoryDialog(row);
  }

  private openStoryDialog(editing: ExperienceStoryRow | null): void {
    const ref = this.dialog.open(ExperienceStoryFormDialogComponent, {
      width: 'min(720px, 96vw)',
      maxHeight: '92vh',
      autoFocus: 'first-tabbable',
      data: { editing },
    });
    ref.afterClosed().subscribe((result) => {
      if (result?.saved) {
        this.load();
      }
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
