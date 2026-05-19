import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { PasswordResetComponent } from './pages/password-reset/password-reset.component';
import { RegisterComponent } from './pages/register/register.component';
import { RecoverAccountComponent } from './pages/recover-account/recover-account.component';

// Guards
import { CustomerGuard } from './pages/account/guards/customer.guard';
import { Login2Component } from './pages/login2/login2.component';

const routes: Routes = [  
    { path: 'iniciar-sesion', component: Login2Component },
    { path: 'login', component: Login2Component },
    { path: 'registro', component: RegisterComponent },
    { path: 'recuperar', component: RecoverAccountComponent },
    { path: 'restablecer/:token_user/:token_validate', component: PasswordResetComponent },
    { path: 'mi-cuenta', loadChildren: () => import('./pages/account/account.module').then(m => m.AccountModule), canActivate: [CustomerGuard], canLoad: [CustomerGuard] },
    { path: '**', redirectTo: 'iniciar-sesion' }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})

export class AuthRoutingModule { }
