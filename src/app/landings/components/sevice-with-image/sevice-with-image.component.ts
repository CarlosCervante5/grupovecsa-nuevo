import { Component, Input } from '@angular/core';
import { ImageService } from '../../interfaces/image_service.interface';

@Component({
    selector: 'app-sevice-with-image',
    templateUrl: './sevice-with-image.component.html',
    styleUrls: ['./sevice-with-image.component.css'],
    standalone: false
})
export class SeviceWithImageComponent {
  @Input() service_image!: ImageService;
}
