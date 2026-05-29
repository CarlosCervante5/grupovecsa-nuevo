import { Component, OnInit } from '@angular/core';
import { LegalAdminService, LegalDocumentListItem } from '@services/legal-admin.service';
import { LEGAL_DOCUMENTS } from './legal-documents.data';

@Component({
  selector: 'app-admin-legales-list',
  templateUrl: './admin-legales-list.component.html',
  styleUrls: ['./admin-legales-list.component.css'],
  standalone: false,
})
export class AdminLegalesListComponent implements OnInit {
  readonly documents = LEGAL_DOCUMENTS;
  loading = true;
  error = false;
  private apiBySlug = new Map<string, LegalDocumentListItem>();

  constructor(private legalAdmin: LegalAdminService) {}

  ngOnInit(): void {
    this.legalAdmin.list().subscribe({
      next: (rows) => {
        this.apiBySlug = new Map(rows.map((r) => [r.slug, r]));
        this.loading = false;
      },
      error: () => {
        this.error = true;
        this.loading = false;
      },
    });
  }

  statusLabel(slug: string): string {
    const row = this.apiBySlug.get(slug);
    if (!row?.has_content) return 'Sin contenido';
    return row.is_published ? 'Publicado' : 'Borrador';
  }

  statusClass(slug: string): string {
    const row = this.apiBySlug.get(slug);
    if (!row?.has_content) return 'status-muted';
    return row.is_published ? 'status-live' : 'status-draft';
  }
}
