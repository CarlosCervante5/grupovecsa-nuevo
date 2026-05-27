import { Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { reload } from '@helpers/session.helper';
import {
  ExperienceStoriesAdminService,
  ExperienceStoryMutationResponse,
  ExperienceStoryPostTypeOption,
  ExperienceStoryRow,
} from '@services/experience-stories-admin.service';
import { IMAGE_UPLOAD_ACCEPT } from '../../../../shared/constants/image-upload';

export interface ExperienceStoryFormDialogData {
  editing: ExperienceStoryRow | null;
}

const FEATURED_MAX_BYTES = 8 * 1024 * 1024;

const FALLBACK_WP_CATEGORIES = ['Noticia', 'Evento', 'Eventos', 'Rodada', 'Comunidad', 'Lanzamiento'];

const DEFAULT_POST_TYPES: ExperienceStoryPostTypeOption[] = [
  { value: 'story', label: 'Historia o noticia' },
  { value: 'event', label: 'Evento (calendario público)' },
];

@Component({
  selector: 'app-experience-story-form-dialog',
  templateUrl: './experience-story-form-dialog.component.html',
  styleUrls: ['./experience-story-form-dialog.component.css'],
  standalone: false,
})
export class ExperienceStoryFormDialogComponent implements OnInit, OnDestroy {
  readonly otherCategoryValue = '__other__';
  readonly featuredImageAccept = IMAGE_UPLOAD_ACCEPT;

  editing: ExperienceStoryRow | null;

  title = '';
  urlName = '';
  excerpt = '';
  bodyHtml = '';
  imageUrl = '';
  status: 'published' | 'draft' | 'unpublished' = 'published';
  eventBeginDate = '';
  eventEndDate = '';

  /** Tipo de publicación (solo UI; el servidor usa categoría + fechas). */
  postType: 'story' | 'event' = 'story';
  postTypeOptions: ExperienceStoryPostTypeOption[] = [...DEFAULT_POST_TYPES];

  wpCategoryOptions: string[] = [...FALLBACK_WP_CATEGORIES];
  eventAgendaKeywords: string[] = [];
  /** Valor del select; `__other__` abre el texto libre. */
  wpCategoryChoice = '';
  wpCategoryOther = '';

  metaLoaded = false;

  /** Archivo elegido para imagen destacada (multipart). */
  featuredImageFile: File | null = null;
  private featuredObjectUrl: string | null = null;

  saving = false;

  constructor(
    private ref: MatDialogRef<ExperienceStoryFormDialogComponent, { saved: boolean } | undefined>,
    @Inject(MAT_DIALOG_DATA) data: ExperienceStoryFormDialogData,
    private api: ExperienceStoriesAdminService,
    private snack: MatSnackBar,
    private router: Router
  ) {
    this.editing = data.editing;
  }

  ngOnInit(): void {
    this.api.getMeta().subscribe({
      next: (res) => {
        const d = res.data;
        this.wpCategoryOptions =
          d?.wp_category_options?.length ? [...d.wp_category_options] : [...FALLBACK_WP_CATEGORIES];
        this.eventAgendaKeywords = d?.event_agenda_keywords?.length
          ? [...d.event_agenda_keywords]
          : ['evento'];
        this.postTypeOptions = d?.post_types?.length ? [...d.post_types] : [...DEFAULT_POST_TYPES];
        this.metaLoaded = true;
        this.hydrateForm();
      },
      error: () => {
        this.wpCategoryOptions = [...FALLBACK_WP_CATEGORIES];
        this.eventAgendaKeywords = ['evento'];
        this.postTypeOptions = [...DEFAULT_POST_TYPES];
        this.metaLoaded = true;
        this.hydrateForm();
      },
    });
  }

  ngOnDestroy(): void {
    this.revokeFeaturedPreview();
  }

  private hydrateForm(): void {
    if (!this.editing) {
      this.postType = 'story';
      this.wpCategoryChoice = '';
      this.wpCategoryOther = '';
      return;
    }
    const row = this.editing;
    this.title = row.title;
    this.urlName = row.url_name;
    this.excerpt = row.excerpt ?? '';
    this.bodyHtml = row.body_html ?? '';
    this.imageUrl = row.image_path ?? '';
    this.status = (row.status as 'published' | 'draft' | 'unpublished') || 'published';
    this.eventBeginDate = this.toDateInput(row.event_begin_date);
    this.eventEndDate = this.toDateInput(row.event_end_date);

    this.postType = row.event_begin_date ? 'event' : 'story';

    const label = row.wp_category_label?.trim() ?? '';
    if (label && this.wpCategoryOptions.includes(label)) {
      this.wpCategoryChoice = label;
      this.wpCategoryOther = '';
    } else if (label) {
      this.wpCategoryChoice = this.otherCategoryValue;
      this.wpCategoryOther = label;
    } else {
      this.wpCategoryChoice = '';
      this.wpCategoryOther = '';
    }
  }

  get dialogTitle(): string {
    return this.editing ? 'Editar historia' : 'Nueva historia';
  }

  get agendaHint(): string {
    if (!this.eventAgendaKeywords.length) {
      return '';
    }
    return `Para el calendario público, la categoría suele contener: ${this.eventAgendaKeywords.join(', ')}.`;
  }

  /** Vista previa: archivo nuevo o URL / ruta actual. */
  get featuredPreviewSrc(): string | null {
    if (this.featuredObjectUrl) {
      return this.featuredObjectUrl;
    }
    const u = this.imageUrl?.trim();
    return u || null;
  }

  /** Nombre del archivo elegido (solo subida nueva). */
  get featuredImageLabel(): string {
    return this.featuredImageFile?.name ?? '';
  }

  get showOtherCategory(): boolean {
    return this.wpCategoryChoice === this.otherCategoryValue;
  }

  onPostTypeChange(): void {
    if (this.postType === 'event' && !this.eventBeginDate.trim()) {
      const match = this.wpCategoryOptions.find((c) =>
        this.eventAgendaKeywords.some((k) => c.toLowerCase().includes(k.toLowerCase()))
      );
      if (match && !this.wpCategoryChoice) {
        this.wpCategoryChoice = match;
      }
    }
  }

  onFeaturedFileChange(ev: Event): void {
    const input = ev.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    if (!file) {
      return;
    }
    const name = file.name.toLowerCase();
    const allowedExt = /\.(jpe?g|png|gif|webp|heic|heif)$/i.test(name);
    const allowedMime = !file.type || file.type.startsWith('image/');
    if (!allowedExt && !allowedMime) {
      this.snack.open('El archivo debe ser una imagen (JPEG, PNG, WebP, GIF, HEIC…)', 'OK', { duration: 4000 });
      input.value = '';
      return;
    }
    if (file.size > FEATURED_MAX_BYTES) {
      this.snack.open('La imagen no debe superar 8 MB', 'OK', { duration: 4000 });
      input.value = '';
      return;
    }
    this.revokeFeaturedPreview();
    this.featuredImageFile = file;
    this.featuredObjectUrl = URL.createObjectURL(file);
  }

  clearFeaturedFile(input?: HTMLInputElement): void {
    this.featuredImageFile = null;
    this.revokeFeaturedPreview();
    if (input) {
      input.value = '';
    }
  }

  private revokeFeaturedPreview(): void {
    if (this.featuredObjectUrl) {
      URL.revokeObjectURL(this.featuredObjectUrl);
      this.featuredObjectUrl = null;
    }
  }

  private toDateInput(v: string | null | undefined): string {
    if (!v) return '';
    return v.slice(0, 10);
  }

  private resolvedWpCategoryLabel(): string {
    if (this.wpCategoryChoice === this.otherCategoryValue) {
      return this.wpCategoryOther.trim();
    }
    return (this.wpCategoryChoice || '').trim();
  }

  /** Hay imagen: archivo nuevo, URL válida como imagen, o imagen ya guardada al editar. */
  private hasFeaturedImageSource(): boolean {
    if (this.featuredImageFile) {
      return true;
    }
    const u = this.imageUrl.trim();
    if (u) {
      return this.looksLikeImageUrl(u);
    }
    return !!this.editing?.image_path?.trim();
  }

  /** URL o ruta que parezca apuntar a un archivo de imagen. */
  private looksLikeImageUrl(url: string): boolean {
    const u = url.trim();
    if (!u) {
      return false;
    }
    const lower = u.toLowerCase();
    if (lower.startsWith('data:image/')) {
      return true;
    }
    if (/\.(jpe?g|png|gif|webp|avif|svg)(\?|#|$)/i.test(u)) {
      return true;
    }
    try {
      const parsed = new URL(u, typeof window !== 'undefined' ? window.location.origin : 'https://vecsa.local');
      return /\.(jpe?g|png|gif|webp|avif|svg)(\?|#|$)/i.test(parsed.pathname);
    } catch {
      return /\.(jpe?g|png|gif|webp|avif|svg)(\?|#|$)/i.test(u);
    }
  }

  cancel(): void {
    this.ref.close();
  }

  save(): void {
    if (!this.metaLoaded) {
      return;
    }
    if (!this.title.trim()) {
      this.snack.open('El título es obligatorio', 'OK', { duration: 3000 });
      return;
    }
    if (this.postType === 'event' && !this.eventBeginDate.trim()) {
      this.snack.open(
        'Para el tipo «Evento» indica la fecha de inicio (aparece en el calendario público).',
        'OK',
        { duration: 4500 }
      );
      return;
    }
    if (this.showOtherCategory && !this.wpCategoryOther.trim()) {
      this.snack.open('Escribe la categoría en «Otra categoría» o elige una de la lista.', 'OK', {
        duration: 4000,
      });
      return;
    }

    const urlTrim = this.imageUrl.trim();
    if (urlTrim && !this.looksLikeImageUrl(urlTrim)) {
      this.snack.open(
        'La imagen destacada por URL debe ser un enlace a un archivo de imagen (.jpg, .png, .webp, .gif, .svg).',
        'OK',
        { duration: 5000 }
      );
      return;
    }
    if (this.status === 'published' && !this.hasFeaturedImageSource()) {
      this.snack.open(
        'Para publicar necesitas una imagen destacada: usa «Seleccionar imagen» o una URL válida de imagen.',
        'OK',
        { duration: 5000 }
      );
      return;
    }

    this.saving = true;
    const wpLabel = this.resolvedWpCategoryLabel();

    const payload: Record<string, unknown> = {
      title: this.title.trim(),
      url_name: this.urlName.trim() || undefined,
      excerpt: this.excerpt || null,
      body_html: this.bodyHtml || null,
      image_url: this.imageUrl.trim() || null,
      status: this.status,
      event_begin_date: this.eventBeginDate.trim() || null,
      event_end_date: this.eventEndDate.trim() || null,
      wp_category_label: wpLabel || null,
    };

    let req;
    if (this.editing) {
      const withUuid = { ...payload, uuid: this.editing.uuid };
      req = this.featuredImageFile
        ? this.api.updateWithOptionalImage(withUuid, this.featuredImageFile)
        : this.api.update(withUuid);
    } else {
      req = this.featuredImageFile
        ? this.api.storeWithImage(payload, this.featuredImageFile)
        : this.api.store(payload);
    }

    req.subscribe({
      next: (res: ExperienceStoryMutationResponse) => {
        this.saving = false;
        const savedPath = res?.data?.post?.image_path?.trim() ?? '';
        if (this.featuredImageFile && !savedPath) {
          this.snack.open(
            'Se guardó el texto pero la imagen no se subió. Comprueba el formato o vuelve a intentar.',
            'OK',
            { duration: 6000 }
          );
          return;
        }
        this.snack.open(this.editing ? 'Historia actualizada' : 'Historia creada', 'OK', { duration: 2500 });
        this.ref.close({ saved: true });
      },
      error: (err) => {
        this.saving = false;
        reload(err, this.router);
      },
    });
  }
}
