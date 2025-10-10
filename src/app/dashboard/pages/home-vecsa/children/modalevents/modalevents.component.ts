import { Component, ElementRef, HostListener, Inject, ViewChild } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA } from '@angular/material/bottom-sheet';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';

@Component({
    selector: 'app-modalevents',
    templateUrl: './modalevents.component.html',
    styleUrls: ['./modalevents.component.css'],
    standalone: false
})
export class ModaleventsComponent {

  @ViewChild('scrollableContainer') scrollableContainer!: ElementRef;

  public descriptions!: string[];
  private isDown = false;
  private startX: number = 0;
  private scrollLeft: number = 0;

  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,  
    private _bottomSheetRef: MatBottomSheetRef<ModaleventsComponent>
  ){
    if (data.description != null) {
      let d = data.description.includes('\n') ? true : false;
      if (d) {
        this.descriptions = data.description.split('\n');
      }
    }
  }

  close() {
    this._bottomSheetRef.dismiss();
  }

  @HostListener('mousedown', ['$event'])
  onMouseDown(event: MouseEvent): void {
    this.isDown = true;
    this.scrollableContainer.nativeElement.classList.add('active');
    this.startX = event.pageX - this.scrollableContainer.nativeElement.offsetLeft;
    this.scrollLeft = this.scrollableContainer.nativeElement.scrollLeft;
  }

  @HostListener('mouseleave')
  onMouseLeave(): void {
    this.isDown = false;
    this.scrollableContainer.nativeElement.classList.remove('active');
  }

  @HostListener('mouseup')
  onMouseUp(): void {
    this.isDown = false;
    this.scrollableContainer.nativeElement.classList.remove('active');
  }

  @HostListener('mousemove', ['$event'])
  onMouseMove(event: MouseEvent): void {
    if (!this.isDown) return;
    event.preventDefault();
    const x = event.pageX - this.scrollableContainer.nativeElement.offsetLeft;
    const walk = (x - this.startX) * 2; // Ajusta la velocidad del scroll (multiplica por 2 para más rápido)
    this.scrollableContainer.nativeElement.scrollLeft = this.scrollLeft - walk;
  }

}
