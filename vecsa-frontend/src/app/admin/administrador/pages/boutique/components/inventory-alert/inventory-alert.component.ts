import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-inventory-alert',
  standalone: true,
  imports: [CommonModule, MatIconModule],
  template: `
    <div class="alert-badge" [ngClass]="alertClass" *ngIf="stock <= threshold">
      <mat-icon class="alert-icon">{{ stock === 0 ? 'error' : 'warning' }}</mat-icon>
      <span class="alert-text">{{ stock === 0 ? 'Agotado' : 'Stock bajo' }}</span>
    </div>
  `,
  styles: [`
    .alert-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 2px 10px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
      white-space: nowrap;
    }
    .alert-icon {
      font-size: 14px;
      width: 14px;
      height: 14px;
    }
    .alert-out-of-stock {
      background-color: rgba(239, 68, 68, 0.15);
      color: #fca5a5;
    }
    .alert-low-stock {
      background-color: rgba(245, 158, 11, 0.15);
      color: #fde68a;
    }
  `]
})
export class InventoryAlertComponent {
  @Input() stock: number = 0;
  @Input() threshold: number = 5;

  get alertClass(): string {
    return this.stock === 0 ? 'alert-out-of-stock' : 'alert-low-stock';
  }
}
