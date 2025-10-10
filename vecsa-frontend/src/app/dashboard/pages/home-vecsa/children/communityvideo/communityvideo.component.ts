import { Component, Input } from '@angular/core';
import { Events } from '@interfaces/community.interface';

@Component({
    selector: 'app-communityvideo',
    templateUrl: './communityvideo.component.html',
    styleUrls: ['./communityvideo.component.css'],
    standalone: false
})
export class CommunityvideoComponent {
  @Input() principalImages!: Events[];
}
