import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { MarketingGuard } from './marketing/guards/marketing.guard';
import { GestorGuard } from './gestor/guards/gestor.guard';
import { ManagerPanelGuard } from './gestor/guards/manager-panel.guard';
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
import { ExpectedRolePanelGuard } from './shared/guards/expected-role-panel.guard';

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
  { path: 'manager',
    loadChildren: () => import('./gestor/gestor.module').then(m => m.GestorModule),
    canActivate: [ManagerPanelGuard],
    canLoad: [ManagerPanelGuard],
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
  { path: 'technician',
    loadChildren: () => import('./bodywork-paint-technician/bodywork-paint-technician.module').then(m => m.BodyworkPaintTechnicianModule),
    canActivate: [ExpectedRolePanelGuard],
    canLoad: [ExpectedRolePanelGuard],
    data: { expectedRole: 'technician' },
  },
  { path: 'bodywork_paint_technician',
    loadChildren: () => import('./bodywork-paint-technician/bodywork-paint-technician.module').then(m => m.BodyworkPaintTechnicianModule),
    canActivate: [ExpectedRolePanelGuard],
    canLoad: [ExpectedRolePanelGuard],
    data: { expectedRole: 'bodywork_paint_technician' },
  },
  { path: 'spare_parts',
    loadChildren: () => import('./spare-parts/spare-parts.module').then(m => m.SparePartsModule),
    canActivate: [ExpectedRolePanelGuard],
    canLoad: [ExpectedRolePanelGuard],
    data: { expectedRole: 'spare_parts' },
  },
  { path: 'seller',
    loadChildren: () => import('./seller-panel/seller-panel.module').then(m => m.SellerPanelModule),
    canActivate: [ExpectedRolePanelGuard],
    canLoad: [ExpectedRolePanelGuard],
    data: { expectedRole: 'seller', panelTitle: 'Vendedor', panelIcon: 'sell' },
  },
  { path: 'strega-seller',
    loadChildren: () => import('./strega-panel/strega-panel.module').then(m => m.StregaPanelModule),
    canActivate: [ExpectedRolePanelGuard],
    canLoad: [ExpectedRolePanelGuard],
    data: { expectedRole: 'strega-seller', panelTitle: 'Strega — Vendedor', panelIcon: 'storefront' },
  },
  { path: 'strega-manager',
    loadChildren: () => import('./strega-panel/strega-panel.module').then(m => m.StregaPanelModule),
    canActivate: [ExpectedRolePanelGuard],
    canLoad: [ExpectedRolePanelGuard],
    data: { expectedRole: 'strega-manager', panelTitle: 'Strega — Gerente', panelIcon: 'manage_accounts' },
  },
  { path: 'strega-administrator',
    loadChildren: () => import('./strega-panel/strega-panel.module').then(m => m.StregaPanelModule),
    canActivate: [ExpectedRolePanelGuard],
    canLoad: [ExpectedRolePanelGuard],
    data: { expectedRole: 'strega-administrator', panelTitle: 'Strega — Administración', panelIcon: 'admin_panel_settings' },
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
