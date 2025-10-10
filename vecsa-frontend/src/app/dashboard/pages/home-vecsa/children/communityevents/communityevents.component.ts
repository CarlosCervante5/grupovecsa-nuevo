import { Component, Input } from '@angular/core';
import { ModaleventsComponent } from '../modalevents/modalevents.component';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { environment } from '@environments/environment';
import { Events, Multimedia } from '@interfaces/community.interface';
 
@Component({
    selector: 'app-communityevents',
    templateUrl: './communityevents.component.html',
    styleUrls: ['./communityevents.component.css'],
    standalone: false
})
export class CommunityeventsComponent {

  constructor(private _bottomSheet: MatBottomSheet) {} 
  @Input() albums!: Events[];

  public images: string[] = [];
  public title: string = '';
  public date!: Date;
  public description: string = '';
  public imagesForBrand: Multimedia[] = [];

  public baseUrl: string = environment.baseUrl;
 
   open(id: string){
    for (let i = 0; i < this.albums.length; i++) {
      if (this.albums[i].uuid == id) {
        this.title = this.albums[i].name;
        this.date = this.albums[i].begin_date;
        this.description = this.albums[i].description;
        this.images = [];
        this.imagesForBrand = this.albums[i].multimedia;
        for (let j = 0; j < this.imagesForBrand.length; j++) {
          this.images.push(
            this.imagesForBrand[j].multimedia_path
          );
        }
      }
    }

    const openUpdateImages = this._bottomSheet.open(ModaleventsComponent, {
      data: {
        title: this.title,
        date: this.date,
        description: this.description,
        images: this.images
      }
    });
    
  }
 
}
