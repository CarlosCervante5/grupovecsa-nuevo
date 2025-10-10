import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { VenderAutosComponent } from './pages/vender-autos/vender-autos.component';

const routes: Routes = [{ path: '', component: VenderAutosComponent }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class VenderAutosRoutingModule { }
