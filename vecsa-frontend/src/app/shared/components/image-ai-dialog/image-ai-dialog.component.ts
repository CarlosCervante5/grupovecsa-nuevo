import { Component, Inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatRadioModule } from '@angular/material/radio';
import { FormsModule } from '@angular/forms';
import {
  ImageAiAction,
  ImageAiActionId,
  ImageAiService,
  ImageAiTargetType,
} from '../../services/image-ai.service';

export interface ImageAiDialogData {
  sourceUrl: string;
  targetType: ImageAiTargetType;
  targetUuid?: string;
  title?: string;
}

@Component({
  selector: 'app-image-ai-dialog',
  standalone: true,
  imports: [CommonModule, FormsModule, MatDialogModule, MatButtonModule, MatIconModule, MatRadioModule],
  templateUrl: './image-ai-dialog.component.html',
  styleUrls: ['./image-ai-dialog.component.css'],
})
export class ImageAiDialogComponent implements OnInit {
  loadingConfig = true;
  processing = false;
  enabled = false;
  actions: ImageAiAction[] = [];
  selectedAction: ImageAiActionId = 'remove_background';
  previewUrl: string | null = null;
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
        this.actions = cfg?.actions ?? [];
        if (this.actions.length > 0) {
          this.selectedAction = this.actions[0].id;
        }
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
    this.process(false);
  }

  applyAndSave(): void {
    this.process(true);
  }

  private process(save: boolean): void {
    if (!this.enabled || this.processing) {
      return;
    }
    this.processing = true;
    this.error = '';
    this.previewUrl = null;

    const targetType = save ? this.data.targetType : 'preview_only';
    const payload = {
      action: this.selectedAction,
      source_url: this.data.sourceUrl,
      target_type: targetType,
      target_uuid: save && this.data.targetUuid ? this.data.targetUuid : undefined,
      replace_original: true,
    };

    this.imageAi.process(payload).subscribe({
      next: (res) => {
        this.processing = false;
        const d = res.data;
        if (save && d?.saved && d.image_url) {
          this.dialogRef.close({ saved: true, imageUrl: d.image_url });
          return;
        }
        this.previewUrl = d?.preview_url ?? d?.image_url ?? null;
        if (!this.previewUrl) {
          this.error = 'No se recibió URL de vista previa.';
        }
      },
      error: (err) => {
        this.processing = false;
        const e = err?.error;
        const detail = typeof e?.data?.detail === 'string' ? e.data.detail : '';
        const base =
          e?.message ||
          (e?.error_code === 'IMAGE_AI_DISABLED'
            ? 'El procesamiento con IA no está habilitado.'
            : 'No se pudo procesar la imagen.');
        this.error = detail && !base.includes(detail) ? `${base} ${detail}` : base;
      },
    });
  }

  close(): void {
    this.dialogRef.close();
  }
}
