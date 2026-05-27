import { Component, Inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { ImageAiEditContext, ImageAiService, ImageAiTargetType } from '../../services/image-ai.service';

export interface ImageAiDialogData {
  sourceUrl: string;
  targetType: ImageAiTargetType;
  targetUuid?: string;
  title?: string;
}

@Component({
  selector: 'app-image-ai-dialog',
  standalone: true,
  imports: [CommonModule, MatDialogModule, MatButtonModule, MatIconModule],
  templateUrl: './image-ai-dialog.component.html',
  styleUrls: ['./image-ai-dialog.component.css'],
})
export class ImageAiDialogComponent implements OnInit {
  loadingConfig = true;
  processing = false;
  enabled = false;
  private readonly action = 'studio_white' as const;
  previewUrl: string | null = null;
  private previewBase64: string | null = null;
  private previewMime = 'image/jpeg';
  error = '';

  constructor(
    @Inject(MAT_DIALOG_DATA) public data: ImageAiDialogData,
    private dialogRef: MatDialogRef<ImageAiDialogComponent, { saved: boolean; imageUrl?: string } | undefined>,
    private imageAi: ImageAiService,
  ) {}

  ngOnInit(): void {
    this.imageAi.getConfig().subscribe({
      next: (res) => {
        const cfg = res.data;
        this.enabled = !!cfg?.enabled && !!cfg?.configured;
        this.loadingConfig = false;
      },
      error: () => {
        this.loadingConfig = false;
        this.enabled = false;
        this.error = 'No se pudo cargar la configuración de IA.';
      },
    });
  }

  get dialogTitle(): string {
    return this.data.title ?? 'Editar foto con IA';
  }

  runPreview(): void {
    this.requestPreview();
  }

  clearPreview(): void {
    this.previewUrl = null;
    this.previewBase64 = null;
  }

  applyAndSave(): void {
    if (!this.previewBase64) {
      this.error = 'Genera una vista previa antes de guardar.';
      return;
    }
    if (!this.data.targetUuid || this.data.targetType === 'preview_only') {
      return;
    }
    this.processing = true;
    this.error = '';

    this.imageAi
      .process({
        action: this.action,
        source_url: this.data.sourceUrl,
        target_type: this.data.targetType,
        target_uuid: this.data.targetUuid,
        replace_original: true,
        processed_base64: this.previewBase64,
        processed_mime: this.previewMime,
        context: this.editContext(),
      })
      .subscribe({
        next: (res) => {
          this.processing = false;
          const url = res.data?.image_url;
          if (res.data?.saved && url) {
            this.dialogRef.close({ saved: true, imageUrl: url });
            return;
          }
          this.error = 'No se pudo guardar la imagen.';
        },
        error: (err) => this.handleProcessError(err),
      });
  }

  private requestPreview(): void {
    if (!this.enabled || this.processing) {
      return;
    }
    this.processing = true;
    this.error = '';
    this.previewUrl = null;
    this.previewBase64 = null;

    this.imageAi
      .process({
        action: this.action,
        source_url: this.data.sourceUrl,
        target_type: 'preview_only',
        replace_original: false,
        context: this.editContext(),
      })
      .subscribe({
        next: (res) => {
          this.processing = false;
          const d = res.data;
          const preview = d?.preview_url ?? d?.image_url ?? null;
          if (!preview) {
            this.error = 'No se recibió URL de vista previa.';
            return;
          }
          this.previewUrl = preview;
          this.capturePreviewPayload(preview);
          if (!this.previewBase64) {
            this.error = 'No se pudo leer la vista previa para guardar.';
          }
        },
        error: (err) => this.handleProcessError(err),
      });
  }

  private capturePreviewPayload(preview: string): void {
    const match = /^data:([^;]+);base64,(.+)$/i.exec(preview);
    if (match) {
      this.previewMime = match[1];
      this.previewBase64 = match[2];
    }
  }

  private handleProcessError(err: unknown): void {
    this.processing = false;
    const e = (err as { error?: { message?: string; data?: { detail?: string }; error_code?: string } })?.error;
    const detail = typeof e?.data?.detail === 'string' ? e.data.detail : '';
    const base =
      e?.message ||
      (e?.error_code === 'IMAGE_AI_DISABLED'
        ? 'El procesamiento con IA no está habilitado.'
        : 'No se pudo procesar la imagen.');
    this.error = detail && !base.includes(detail) ? `${base} ${detail}` : base;
  }

  get canSave(): boolean {
    return !!this.previewBase64 && !!this.data.targetUuid && this.data.targetType !== 'preview_only';
  }

  private editContext(): ImageAiEditContext {
    return this.data.targetType === 'boutique_product_image' ? 'product' : 'vehicle';
  }

  close(): void {
    this.dialogRef.close();
  }
}
