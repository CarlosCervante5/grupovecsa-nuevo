import { Component, Input } from '@angular/core';

export interface BarMessage {
  p1: string,
  p2: string,
  brand: string
}
@Component({
    selector: 'app-bar-message',
    templateUrl: './bar-message.component.html',
    styleUrls: ['./bar-message.component.css'],
    standalone: false
})
export class BarMessageComponent {
  @Input() bar_message_data!: BarMessage;
}
