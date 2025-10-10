import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BarMessageComponent } from './bar-message.component';

describe('BarMessageComponent', () => {
  let component: BarMessageComponent;
  let fixture: ComponentFixture<BarMessageComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ BarMessageComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BarMessageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
