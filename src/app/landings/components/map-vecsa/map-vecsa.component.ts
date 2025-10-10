import { Component, ElementRef, ViewChild } from '@angular/core';

@Component({
    selector: 'app-map-vecsa',
    templateUrl: './map-vecsa.component.html',
    styleUrls: ['./map-vecsa.component.css'],
    standalone: false
})
export class MapVecsaComponent {

  @ViewChild('map') mapFrame : ElementRef | undefined;

  cambiarIframe(iframe: string) {
     
    if (this.mapFrame) {
      const iframeElement = this.mapFrame.nativeElement as HTMLIFrameElement;
      const url = 'https://www.google.com/maps/embed?pb=!';
      iframeElement.src = url + iframe;
    }
  }

  
}
