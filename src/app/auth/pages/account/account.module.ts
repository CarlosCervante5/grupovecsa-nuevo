
import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

// Modules
import { AccountRoutingModule } from './account-routing.module';
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';

// Components
import { ProfileComponent } from './pages/profile/profile.component';
import { SettingsComponent } from './pages/settings/settings.component';
import { BarChartAComponent } from './bar-chart-a/bar-chart-a.component';
import { BarChartComponent } from './bar-chart/bar-chart.component';
import { SkCubeComponent } from '@components/sk-cube/sk-cube.component';
import { SidebarComponent } from './components/sidebar/sidebar.component';
import { SwiperVerticalComponent } from './components/swiper-vertical/swiper-vertical.component';
import { MSwiperComponent } from './components/m-swiper/m-swiper.component';
import { SwiperNoticesComponent } from './components/swiper-notices/swiper-notices.component';
import { OrdersTabComponent } from './components/orders-tab/orders-tab.component';
import { AppointmentsTabComponent } from './components/appointments-tab/appointments-tab.component';
import { QuotationsTabComponent } from './components/quotations-tab/quotations-tab.component';
import { ValuationsTabComponent } from './components/valuations-tab/valuations-tab.component';
import { DragDropModule } from '@angular/cdk/drag-drop';
// import { AvatarsProfileComponent } from '../../components/avatars-profile/avatars-profile.component';
import { MatChipsModule } from '@angular/material/chips';
import { NewNavComponent } from 'src/app/shared/versiones-nav/new-nav/new-nav.component';

@NgModule({
  declarations: [
    ProfileComponent,
    SettingsComponent,
    BarChartAComponent,
    BarChartAComponent,
    BarChartComponent,
    SidebarComponent,
    SwiperVerticalComponent,
    MSwiperComponent,
    SwiperNoticesComponent,
    OrdersTabComponent,
    AppointmentsTabComponent,
    QuotationsTabComponent,
    ValuationsTabComponent,
    // AvatarsProfileComponent
  ],
  imports: [
    CommonModule,
    AccountRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    AngularMaterialModule,
    SkCubeComponent,
    DragDropModule,
    MatChipsModule,
    NewNavComponent
  ],
  providers: [
    // MyServicesComponent
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})

export class AccountModule { }
