import { Component, Input, OnInit } from '@angular/core';
export interface Clase {
  image: string;
}

@Component({
    selector: 'app-carrusel',
    templateUrl: './carrusel.component.html',
    styleUrls: ['./carrusel.component.css'],
    standalone: false
})

export class CarruselComponent  implements OnInit{


 
    currentSlide: number = 0; // Track the current slide index
    items: number[] = [0, 1, 2]; // Array to generate list items
    @Input() slides!: Clase[];
  
    constructor() { }
  
    ngOnInit(): void {
      if (this.slides.length > 1) {
        setInterval(() => this.fntExecuteSlide('next'), 5000);
      }
  
    }
  
    fntExecuteSlide(side: string): void {
      if (side === 'prev') {
        this.currentSlide = (this.currentSlide === 0) ? this.slides.length - 1 : this.currentSlide - 1;
      } else {
        this.currentSlide = (this.currentSlide === this.slides.length - 1) ? 0 : this.currentSlide + 1;
      }
    }
  }
