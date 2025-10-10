import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { AdminModule } from "../admin.module";
import { AngularMaterialModule } from "src/app/angular-material/angular-material.module";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { DashboardAdminComponent } from "./pages/dashboard/dashboardAdmin.component";
import { AdministradorRoutingModule } from "./administrador-routing.module";
import { AdminUsersComponent } from './pages/admin-users/admin-users.component';
import { AdminPermisosComponent } from './pages/admin-permisos/admin-permisos.component';
import { AddUserComponent } from './components/add-user/add-user.component';
import { SkCubeComponent } from "@components/sk-cube/sk-cube.component";
import { MatFormFieldModule } from "@angular/material/form-field";
import { MatInputModule } from "@angular/material/input";
import { UpdateUserComponent } from './components/update-user/update-user.component';
import { NewNavComponent } from "src/app/shared/versiones-nav/new-nav/new-nav.component";

@NgModule({
    declarations: [
        DashboardAdminComponent,
        AdminUsersComponent,
        AdminPermisosComponent,
        AddUserComponent,
        UpdateUserComponent
    ],
    imports: [
        CommonModule,
        AngularMaterialModule,
        ReactiveFormsModule,
        AdminModule,
        AdministradorRoutingModule,
        SkCubeComponent,
        MatFormFieldModule,
        MatInputModule,
        FormsModule,
        NewNavComponent
    ]
  })
  export class AdministradorModule { }
  