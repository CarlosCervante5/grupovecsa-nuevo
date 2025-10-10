import { Component, Input } from '@angular/core';

@Component({
    selector: 'app-team',
    templateUrl: './team.component.html',
    styleUrls: ['./team.component.css'],
    standalone: false
})
export class TeamComponent {  
  @Input() image_data!: Image_data;
}


interface Image_data {
  src:string;  
  name:string;
  url:string;
}