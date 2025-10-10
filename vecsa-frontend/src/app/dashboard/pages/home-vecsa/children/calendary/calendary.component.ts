import { Component, Input } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { environment } from '@environments/environment';
import { ModaleventsComponent } from '../modalevents/modalevents.component';
import { Events, Multimedia } from '@interfaces/community.interface';

export interface Clase {
  image: string,
  title: string,
  marca: string
}
@Component({
    selector: 'app-calendary',
    templateUrl: './calendary.component.html',
    styleUrls: ['./calendary.component.css'],
    standalone: false
})
export class CalendaryComponent {
  @Input() images!: Events[];

  public images_c: string[] = [];
  public title: string = '';
  public date!: Date;
  public description: string = '';
  public imagesForBrand: Multimedia[] = [];

  public baseUrl: string = environment.baseUrl;

  constructor(private _bottomSheet: MatBottomSheet){}
 
   open(id: string){
    for (let i = 0; i < this.images.length; i++) {
      if (this.images[i].uuid == id) {
        this.title = this.images[i].name;
        this.date = this.images[i].begin_date;
        this.description = this.images[i].description;
        this.images_c = [];
        this.imagesForBrand = this.images[i].multimedia;
        for (let j = 0; j < this.imagesForBrand.length; j++) {
          this.images_c.push(
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
        images: this.images_c
      }
    });
    
  }

}
