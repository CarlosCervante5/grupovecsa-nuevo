import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

// Components
import { LoginComponent } from './pages/login/login.component';
import { RegisterComponent } from './pages/register/register.component';

// Modules
import { AuthRoutingModule } from './auth-routing.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { provideHttpClient, withInterceptorsFromDi } from '@angular/common/http';
import { RecoverAccountComponent } from './pages/recover-account/recover-account.component';
import { PasswordResetComponent } from './pages/password-reset/password-reset.component';
import { SkCubeComponent } from '@components/sk-cube/sk-cube.component';
import { Login2Component } from './pages/login2/login2.component';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';
import { AvatarsProfileComponent } from './components/avatars-profile/avatars-profile.component';

import { NavRewardsComponent } from '../shared/versiones-nav/nav-rewards/nav-rewards.component';
import { NewNavComponent } from '../shared/versiones-nav/new-nav/new-nav.component';

@NgModule({ declarations: [
        LoginComponent,
        RegisterComponent,
        RecoverAccountComponent,
        PasswordResetComponent,
        Login2Component,
        AvatarsProfileComponent
    ],
    schemas: [CUSTOM_ELEMENTS_SCHEMA], imports: [CommonModule,
        AuthRoutingModule,
        AngularMaterialModule,
        FormsModule,
        ReactiveFormsModule,
        SkCubeComponent,
        DragDropModule,
        SkCubeComponent,
        NavRewardsComponent,
        NewNavComponent], providers: [provideHttpClient(withInterceptorsFromDi())] })

export class AuthModule { }
