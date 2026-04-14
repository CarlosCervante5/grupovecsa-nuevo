import { Component, OnInit } from '@angular/core';
import { ImagesPromoService } from 'src/app/admin/gestor/services/images-promo.service';
import { GetcampaingResponse, Campaign } from '@interfaces/admin.interfaces';

@Component({
    selector: 'app-events',
    templateUrl: './events.component.html',
    styleUrls: ['./events.component.css', '../../../../../shared/styles/vecsa-page-hero.shared.css'],
    standalone: false
})
export class EventsComponent implements OnInit {
  public campaigns: Campaign[] = [];

  constructor(private _imagesPromoService: ImagesPromoService) {}

  ngOnInit(): void {
    this._imagesPromoService.getCampaing().subscribe({
      next: (response: GetcampaingResponse) => {
        console.log('Campaigns response:', response);
        this.campaigns = response.data.campaigns;
      },
      error: (err) => {
        console.error('Error loading campaigns:', err);
      }
    });
  }
}
