import { Component, ChangeDetectionStrategy } from '@angular/core';

@Component({
  selector: 'app-disclaimer',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  templateUrl: './disclaimer.component.html',
})
export class DisclaimerComponent {}
