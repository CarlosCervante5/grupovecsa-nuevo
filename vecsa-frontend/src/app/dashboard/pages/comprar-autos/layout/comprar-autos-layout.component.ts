import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { NewNavComponent } from 'src/app/shared/versiones-nav/new-nav/new-nav.component';

@Component({
  selector: 'app-comprar-autos-layout',
  template: `
    <app-new-nav></app-new-nav>
    <router-outlet></router-outlet>
  `,
  standalone: true,
  imports: [RouterModule, NewNavComponent]
})
export class ComprarAutosLayoutComponent {} 