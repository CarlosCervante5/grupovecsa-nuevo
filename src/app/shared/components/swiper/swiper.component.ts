import { Component, Input } from '@angular/core';
import { SwiperOptions } from 'swiper';

interface Images {
  image_path: string;
  legal: string;
  spec_sheet: string;
}

@Component({
    selector: 'app-swiper',
    templateUrl: './swiper.component.html',
    styleUrls: ['./swiper.component.css'],
    standalone: false
})

export class SwiperComponent {
  
  @Input() slidesPerView: number = 1;
  @Input() images: Images[] = [];

  public legal: string | null = '';
  public imagen: string | null = '';
  public specsLinks: string | null = '';
  public buttonLabel: string = '';

  constructor() {}

  public closeModal(): void {
    this.imagen = '';
  }

  public showModal(imagen: Images): void {
    this.legal = imagen.legal;
    this.imagen = imagen.image_path;
    this.specsLinks = imagen.spec_sheet;

    // Determinar el texto del botón
    if (this.isDriveDocument(this.specsLinks)) {
      this.buttonLabel = 'Descarga';
    } else if (this.isVideo(this.specsLinks)) {
      this.buttonLabel = 'Ver';
    }
  }

  private isDriveDocument(link: string | null): boolean {
    return link ? link.includes('drive.google.com/file/d/') : false;
  }

  private isVideo(link: string | null): boolean {
    if (!link) {
      return false;
    }
  
    const videoExtensions = ['.mp4', '.mov', '.avi', '.mkv', '.webm'];
    const isDirectVideo = videoExtensions.some(ext => link.endsWith(ext));
    const isYouTubeLink = link.includes('youtube.com') || link.includes('youtu.be');
  
    return isDirectVideo || isYouTubeLink;
  }
}