import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { BmwComponent } from './aftersale/bmw/bmw.component';
import { MiniComponent } from './aftersale/mini/mini.component';

const routes: Routes = [
  { path: 'bmw', component: BmwComponent },
  { path: 'mini', component: MiniComponent },
  { path: 'full', pathMatch: 'full', redirectTo: '' }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class LandingsRoutingModule { }
