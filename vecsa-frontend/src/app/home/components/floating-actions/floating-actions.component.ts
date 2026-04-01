import { Component, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-floating-actions',
  standalone: true,
  imports: [CommonModule],
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './floating-actions.component.html',
  styleUrls: ['./floating-actions.component.css'],
})
export class FloatingActionsComponent {
  whatsappUrl = 'https://wa.me/522214316725';
  phoneNumber = 'tel:+522214316725';
  email = 'mailto:contacto@grupovecsa.com';
}
