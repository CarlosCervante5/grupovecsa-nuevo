import { Component, OnDestroy, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { EditorComponent } from '@tinymce/tinymce-angular';
import { LegalAdminService } from '@services/legal-admin.service';
import { LEGAL_DOCUMENTS } from './legal-documents.data';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-admin-legal-edit',
  templateUrl: './admin-legal-edit.component.html',
  styleUrls: ['./admin-legal-edit.component.css'],
  standalone: false,
})
export class AdminLegalEditComponent implements OnInit, OnDestroy {
  loading = true;
  saving = false;
  error = false;
  slug = '';
  pageLabel = '';
  publicPath = '';

  readonly editorInit: EditorComponent['init'] = {
    height: 520,
    menubar: false,
    plugins: 'lists link table code autoresize',
    toolbar:
      'undo redo | blocks fontsize fontfamily | bold italic underline strikethrough | ' +
      'alignleft aligncenter alignright | bullist numlist | link table | removeformat code',
    content_style: 'body { font-family: Mulish, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.6; }',
    branding: false,
    promotion: false,
    license_key: 'gpl',
  };

  readonly tinymceScriptSrc = '/tinymce/tinymce.min.js';

  form = this.fb.group({
    title: ['', [Validators.required, Validators.maxLength(255)]],
    meta_description: ['', [Validators.maxLength(500)]],
    body_html: ['', [Validators.required]],
    is_published: [true],
  });

  private routeSub?: Subscription;

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private legalAdmin: LegalAdminService,
  ) {}

  ngOnInit(): void {
    this.routeSub = this.route.paramMap.subscribe((params) => {
      this.slug = params.get('slug') ?? '';
      const def = LEGAL_DOCUMENTS.find((d) => d.slug === this.slug);
      if (!def) {
        void this.router.navigate(['/admin/administrator/legales']);
        return;
      }
      this.pageLabel = def.label;
      this.publicPath = def.publicPath;
      this.loadDocument();
    });
  }

  ngOnDestroy(): void {
    this.routeSub?.unsubscribe();
  }

  private loadDocument(): void {
    this.loading = true;
    this.error = false;
    this.legalAdmin.get(this.slug).subscribe({
      next: (doc) => {
        this.form.patchValue({
          title: doc.title,
          meta_description: doc.meta_description ?? '',
          body_html: doc.body_html ?? '',
          is_published: doc.is_published ?? true,
        });
        this.loading = false;
      },
      error: () => {
        this.error = true;
        this.loading = false;
      },
    });
  }

  save(): void {
    if (this.form.invalid || this.saving) {
      this.form.markAllAsTouched();
      return;
    }

    this.saving = true;
    const value = this.form.getRawValue();
    this.legalAdmin
      .update(this.slug, {
        title: value.title ?? '',
        meta_description: value.meta_description ?? '',
        body_html: value.body_html ?? '',
        is_published: !!value.is_published,
      })
      .subscribe({
        next: () => {
          this.saving = false;
          Swal.fire({
            icon: 'success',
            title: 'Guardado',
            text: 'El documento legal se actualizó correctamente.',
            timer: 1800,
            showConfirmButton: false,
          });
        },
        error: (err) => {
          this.saving = false;
          Swal.fire({
            icon: 'error',
            title: 'Error al guardar',
            text: err?.error?.message ?? 'No se pudo guardar el documento.',
            confirmButtonColor: '#1c69d4',
          });
        },
      });
  }
}
