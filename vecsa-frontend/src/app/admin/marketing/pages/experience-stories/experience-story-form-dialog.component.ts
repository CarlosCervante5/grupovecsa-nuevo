import { Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { MatSnackBar } from '@angular/material/snack-bar';
import { reload } from '@helpers/session.helper';
import {
  ExperienceGalleryImageRow,
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
  { value: 'gallery', label: 'Galería de evento' },
];

type PostType = 'story' | 'event' | 'gallery';

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
  savedImagePath: string | null = null;
  status: 'published' | 'draft' | 'unpublished' = 'published';
  eventBeginDate = '';
  eventEndDate = '';

  postType: PostType = 'story';
  postTypeOptions: ExperienceStoryPostTypeOption[] = [...DEFAULT_POST_TYPES];

  wpCategoryOptions: string[] = [...FALLBACK_WP_CATEGORIES];
  eventAgendaKeywords: string[] = [];
  wpCategoryChoice = '';
  wpCategoryOther = '';

  metaLoaded = false;

  featuredImageFile: File | null = null;
  private featuredObjectUrl: string | null = null;

  existingGalleryImages: ExperienceGalleryImageRow[] = [];
  galleryPendingFiles: File[] = [];
  galleryDeleteUuids: string[] = [];

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
    this.savedImagePath = row.image_path?.trim() || null;
    this.status = (row.status as 'published' | 'draft' | 'unpublished') || 'published';
    this.eventBeginDate = this.toDateInput(row.event_begin_date);
    this.eventEndDate = this.toDateInput(row.event_end_date);

    const t = row.experience_post_type as PostType | undefined;
    if (t === 'gallery' || t === 'event' || t === 'story') {
      this.postType = t;
    } else {
      this.postType = row.event_begin_date ? 'event' : 'story';
    }

    this.existingGalleryImages = [...(row.gallery_images ?? [])];
    this.galleryDeleteUuids = [];
    this.galleryPendingFiles = [];

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

  get isGallery(): boolean {
    return this.postType === 'gallery';
  }

  get dialogTitle(): string {
    if (this.editing) {
      return this.isGallery ? 'Editar galería' : 'Editar historia';
    }
    return this.isGallery ? 'Nueva galería de evento' : 'Nueva historia';
  }

  get agendaHint(): string {
    if (!this.eventAgendaKeywords.length) {
      return '';
    }
    return `Para el calendario público, la categoría suele contener: ${this.eventAgendaKeywords.join(', ')}.`;
  }

  get featuredPreviewSrc(): string | null {
    if (this.featuredObjectUrl) {
      return this.featuredObjectUrl;
    }
    return this.savedImagePath;
  }

  get featuredImageLabel(): string {
    return this.featuredImageFile?.name ?? '';
  }

  get showOtherCategory(): boolean {
    return this.wpCategoryChoice === this.otherCategoryValue;
  }

  get visibleExistingGallery(): ExperienceGalleryImageRow[] {
    return this.existingGalleryImages.filter((img) => !this.galleryDeleteUuids.includes(img.uuid));
  }

  get galleryPhotosCount(): number {
    return this.visibleExistingGallery.length + this.galleryPendingFiles.length;
  }

  onPostTypeChange(): void {
    if ((this.postType === 'event' || this.postType === 'gallery') && !this.eventBeginDate.trim()) {
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
    if (!file || !this.validateImageFile(file, input)) {
      return;
    }
    this.revokeFeaturedPreview();
    this.featuredImageFile = file;
    this.featuredObjectUrl = URL.createObjectURL(file);
  }

  onGalleryFilesChange(ev: Event): void {
    const input = ev.target as HTMLInputElement;
    const list = input.files;
    if (!list?.length) {
      return;
    }
    const valid: File[] = [];
    for (let i = 0; i < list.length; i++) {
      const file = list.item(i);
      if (!file) {
        continue;
      }
      if (!this.validateImageFile(file)) {
        continue;
      }
      valid.push(file);
    }
    this.galleryPendingFiles = [...this.galleryPendingFiles, ...valid];
    input.value = '';
  }

  removePendingGalleryFile(index: number): void {
    this.galleryPendingFiles.splice(index, 1);
  }

  markGalleryImageDeleted(uuid: string): void {
    if (!this.galleryDeleteUuids.includes(uuid)) {
      this.galleryDeleteUuids.push(uuid);
    }
  }

  undoGalleryImageDelete(uuid: string): void {
    this.galleryDeleteUuids = this.galleryDeleteUuids.filter((id) => id !== uuid);
  }

  clearFeaturedFile(input?: HTMLInputElement): void {
    this.featuredImageFile = null;
    this.revokeFeaturedPreview();
    if (input) {
      input.value = '';
    }
  }

  private validateImageFile(file: File, input?: HTMLInputElement): boolean {
    const name = file.name.toLowerCase();
    const allowedExt = /\.(jpe?g|png|gif|webp|heic|heif)$/i.test(name);
    const allowedMime = !file.type || file.type.startsWith('image/');
    if (!allowedExt && !allowedMime) {
      this.snack.open('El archivo debe ser una imagen (JPEG, PNG, WebP, GIF, HEIC…)', 'OK', { duration: 4000 });
      if (input) {
        input.value = '';
      }
      return false;
    }
    if (file.size > FEATURED_MAX_BYTES) {
      this.snack.open('Cada imagen no debe superar 8 MB', 'OK', { duration: 4000 });
      if (input) {
        input.value = '';
      }
      return false;
    }
    return true;
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

  private hasFeaturedImageSource(): boolean {
    return !!this.featuredImageFile || !!this.savedImagePath;
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
    if ((this.postType === 'event' || this.postType === 'gallery') && !this.eventBeginDate.trim()) {
      const msg =
        this.postType === 'gallery'
          ? 'Indica la fecha del evento para la galería.'
          : 'Para el tipo «Evento» indica la fecha de inicio (aparece en el calendario público).';
      this.snack.open(msg, 'OK', { duration: 4500 });
      return;
    }
    if (this.showOtherCategory && !this.wpCategoryOther.trim()) {
      this.snack.open('Escribe la categoría en «Otra categoría» o elige una de la lista.', 'OK', {
        duration: 4000,
      });
      return;
    }

    if (this.status === 'published' && !this.hasFeaturedImageSource()) {
      this.snack.open('Para publicar selecciona una imagen de portada.', 'OK', { duration: 5000 });
      return;
    }

    if (this.isGallery && this.status === 'published' && this.galleryPhotosCount < 1) {
      this.snack.open('La galería debe incluir al menos una foto además de la portada.', 'OK', {
        duration: 5000,
      });
      return;
    }

    this.saving = true;
    const wpLabel = this.resolvedWpCategoryLabel();

    const payload: Record<string, unknown> = {
      title: this.title.trim(),
      url_name: this.urlName.trim() || undefined,
      excerpt: this.excerpt || null,
      body_html: this.isGallery ? null : this.bodyHtml || null,
      status: this.status,
      experience_post_type: this.postType,
      event_begin_date: this.eventBeginDate.trim() || null,
      event_end_date: this.eventEndDate.trim() || null,
      wp_category_label: wpLabel || null,
    };

    const featured = this.featuredImageFile;
    const galleryFiles = this.galleryPendingFiles;

    let req;
    if (this.editing) {
      const withUuid = { ...payload, uuid: this.editing.uuid };
      req = this.api.updateWithMedia(withUuid, featured, galleryFiles, this.galleryDeleteUuids);
    } else if (featured || galleryFiles.length) {
      req = this.api.storeWithImage(payload, featured, galleryFiles);
    } else {
      req = this.api.store(payload);
    }

    req.subscribe({
      next: (res: ExperienceStoryMutationResponse) => {
        this.saving = false;
        const post = res?.data?.post;
        const savedPath = post?.image_path?.trim() ?? '';
        if (featured && !savedPath) {
          this.snack.open(
            'Se guardó el texto pero la portada no se subió. Comprueba el formato o vuelve a intentar.',
            'OK',
            { duration: 6000 }
          );
          return;
        }
        if (savedPath) {
          this.savedImagePath = savedPath;
        }
        if (post?.gallery_images) {
          this.existingGalleryImages = [...post.gallery_images];
          this.galleryPendingFiles = [];
          this.galleryDeleteUuids = [];
        }
        const msg = this.isGallery
          ? this.editing
            ? 'Galería actualizada'
            : 'Galería creada'
          : this.editing
            ? 'Historia actualizada'
            : 'Historia creada';
        this.snack.open(msg, 'OK', { duration: 2500 });
        this.ref.close({ saved: true });
      },
      error: (err) => {
        this.saving = false;
        reload(err, this.router);
      },
    });
  }
}
