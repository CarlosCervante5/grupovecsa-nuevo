import { Component, Input } from '@angular/core';
import { Banner } from '../../interfaces/banner.interface';

@Component({
    selector: 'app-banner',
    templateUrl: './banner.component.html',
    styleUrls: ['./banner.component.css'],
    standalone: false
})
export class BannerComponent {
  @Input() banner_data!: Banner;
}
