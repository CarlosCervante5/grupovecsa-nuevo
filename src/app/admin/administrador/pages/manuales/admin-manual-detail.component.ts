import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { PANEL_MANUALS, PanelManual } from './admin-manuals.data';

@Component({
  selector: 'app-admin-manual-detail',
  templateUrl: './admin-manual-detail.component.html',
  styleUrls: ['./admin-manual-detail.component.css'],
  standalone: false,
})
export class AdminManualDetailComponent implements OnInit {
  manual: PanelManual | null = null;

  constructor(private route: ActivatedRoute) {}

  ngOnInit(): void {
    const slug = this.route.snapshot.paramMap.get('panel') || '';
    this.manual = PANEL_MANUALS.find((m) => m.slug === slug) ?? null;
  }
}

