import { Component, Input } from '@angular/core';
import { Events } from '@interfaces/community.interface';

@Component({
    selector: 'app-communitycard',
    templateUrl: './communitycard.component.html',
    styleUrls: ['./communitycard.component.css'],
    standalone: false
})
export class CommunitycardComponent {
  @Input() albums!: Events[];


}
