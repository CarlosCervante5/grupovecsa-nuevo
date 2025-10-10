import { Component, Input, OnInit } from '@angular/core';

@Component({
    selector: 'app-ct',
    templateUrl: './ct.component.html',
    styleUrls: ['./ct.component.css'],
    standalone: false
})
export class CtComponent implements OnInit{  
  @Input() cta_data!: Image_data;

  ngOnInit(): void {
    window.addEventListener('scroll', this.scroll, true); //third parameter
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

interface Image_data {
  src:string;  
  name:string;
  url:string;
}