import { Component, HostListener, OnInit } from '@angular/core';
import { ImagesPromoService } from 'src/app/admin/gestor/services/images-promo.service';
import { GetcampaingResponse, Campaign} from '@interfaces/admin.interfaces';
 
@Component({
    selector: 'app-events',
    templateUrl: './events.component.html',
    styleUrls: ['./events.component.css'],
    standalone: false
})
export class EventsComponent  implements OnInit{  
  // References
  currentMonth: string | undefined;
  public campaigns: Campaign[] = [];

  constructor(
    private _imagesPromoService: ImagesPromoService
  ){}
  
  ngOnInit(): void {
    window.addEventListener('scroll', this.scroll, true); //third parameter

    const currentDate = new Date();
    this.currentMonth = currentDate.toLocaleString('default', { month: 'long' });

      this._imagesPromoService.getCampaing()
      .subscribe({
        next: (response: GetcampaingResponse) => {
         this.campaigns = response.data.campaigns;
         console.log(this.campaigns);
        },
      })
  }

  scroll = (event:any): void => {    
    document.querySelectorAll(".paused").forEach(elm => {
      if (this.isVisible(elm)) // que sean visibles...
        elm.classList.remove("paused"); // les quitamos la clase paused
    })
  };

  public isVisible(elm:any) {
    var rect = elm.getBoundingClientRect();
    var viewHeight = Math.max(document.documentElement.clientHeight, window.innerHeight);
    return !(rect.bottom < 0 || rect.top - viewHeight >= 0);
  }
}
