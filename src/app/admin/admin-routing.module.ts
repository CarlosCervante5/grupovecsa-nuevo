import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { MarketingGuard } from './marketing/guards/marketing.guard';
import { GestorGuard } from './gestor/guards/gestor.guard';
import { StaffGuard } from './staff/guards/staff.guard';
import { ReceptionistGuard } from './receptionist/guards/receptionist.guard'; 
import { ValuatorGuard } from './valuator/guards/valuator.guard';
import { AdministradorGuard } from './administrador/guards/administrador.guard';
import { AppointmentManagerGuard } from './appointment-manager/guards/appointment_manager.guard';


import { DeveloperGuard } from './developer/guards/developer.guard';
import { BenchmarkGuard } from './shared/benchmark/benchmark.guard';
import { StoreManagementGuard } from './store/guards/store-management.guard';
import { GerenteGuard } from './gerente/guards/gerente.guard';
import { AdminEntryRedirectComponent } from './admin-entry-redirect.component';

const routes: Routes = [
  { path: '', pathMatch: 'full', component: AdminEntryRedirectComponent },
  { path: 'marketing',
    loadChildren: () => import('./marketing/marketing.module').then(m => m.MarketingModule), 
    canActivate: [MarketingGuard],
    canLoad: [MarketingGuard],
    data: { requiredRole: 'marketing' } 
  }, 
  { path: 'gestor',
    loadChildren: () => import('./gestor/gestor.module').then( m => m.GestorModule),
    canActivate: [GestorGuard],
    canLoad: [GestorGuard],
  },
  { path: 'staff',
    loadChildren: () => import('./staff/staff.module').then( m => m.StaffModule),
    canActivate: [StaffGuard],
    canLoad: [StaffGuard],
  },
  { path: 'receptionist',
    loadChildren: () => import('./receptionist/receptionist.module').then( m => m.ReceptionistModule),
    canActivate: [ReceptionistGuard],
    canLoad: [ReceptionistGuard],
  },
  { path: 'valuator',
    loadChildren: () => import('./valuator/valuator.module').then( m => m.ValuatorModule ),
    canActivate: [ValuatorGuard],
    canLoad: [ValuatorGuard]
  },
  { path: 'appointment_manager',
    loadChildren: () => import('./appointment-manager/appointment-manager.module').then( m => m.AppointmentManagerModule ),
    canActivate: [AppointmentManagerGuard],
    canLoad: [AppointmentManagerGuard]
  },
  { path: 'administrator',
    loadChildren: () => import('./administrador/administrador.module').then( m => m.AdministradorModule),
    canActivate: [AdministradorGuard],
    canLoad: [AdministradorGuard],
  },
  { path: 'bodywork_paint_technician',
    loadChildren: () => import('./bodywork-paint-technician/bodywork-paint-technician.module').then( m => m.BodyworkPaintTechnicianModule)
  },
  { path: 'spare_parts',
    loadChildren: () => import('./spare-parts/spare-parts.module').then(m => m.SparePartsModule)
  },
  { path: 'developer',
    loadChildren: () => import('./developer/developer.module').then(m => m.DeveloperModule),
    canActivate: [DeveloperGuard],
    canLoad: [DeveloperGuard],
  },
  { path: 'benchmark',
    loadChildren: () => import('./shared/benchmark/benchmark.module').then(m => m.BenchmarkModule),
    canActivate: [BenchmarkGuard],
    canLoad: [BenchmarkGuard],
  },
  { path: 'store',
    loadChildren: () => import('./store/store.module').then(m => m.StoreModule),
    canMatch: [StoreManagementGuard],
  },
  { path: 'gerente',
    loadChildren: () => import('./gerente/gerente.module').then(m => m.GerenteModule),
    canActivate: [GerenteGuard],
    canLoad: [GerenteGuard],
  },
  { path: '**', redirectTo: '/404' }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminRoutingModule { }
