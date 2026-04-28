import { Component } from '@angular/core';
import { PANEL_MANUALS, PanelManual } from './admin-manuals.data';

@Component({
  selector: 'app-admin-manuals',
  templateUrl: './admin-manuals.component.html',
  styleUrls: ['./admin-manuals.component.css'],
  standalone: false,
})
export class AdminManualsComponent {
  manuals: PanelManual[] = PANEL_MANUALS;
}

