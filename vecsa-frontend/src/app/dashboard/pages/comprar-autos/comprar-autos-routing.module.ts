import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { CompraTuAutoComponent } from './pages/compra-tu-auto/compra-tu-auto.component';
import { DetailComponent } from './pages/detail/detail.component';
import { ComprarAutosLayoutComponent } from './layout/comprar-autos-layout.component';

const routes: Routes = [
  { 
    path: '', 
    component: ComprarAutosLayoutComponent,
    children: [
      { path: '', component: CompraTuAutoComponent },
      { path: 'detail/:uuid', component: DetailComponent }, 
      { path: ':categoria/:marca/:linea/:modelo/:carroceria/:version/:anio/:minprecio/:maxprecio/:estado/:busqueda/:transmision/:exterior_color/:interior_color/:order/:pagina', component: CompraTuAutoComponent },  
      { path: '**', redirectTo: '' }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ComprarAutosRoutingModule { }
