import { CommonModule } from '@angular/common';
import { Component, HostListener } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';
import Swal from 'sweetalert2';
import { Campaign, GetcampaingResponse, menu } from '../../interfaces/admin.interfaces';
import { CampaingService } from '@services/campaing.service';

@Component({
    selector: 'nav-promotions',
    templateUrl: './nav-promotions.component.html',
    styleUrls: ['./nav-promotions.component.css'],
    imports: [CommonModule, RouterModule]
})
export class NavPromotionsComponent {
  public url_dashboard: string = '/auth/mi-cuenta';
  public spinner: boolean = false;
  public auth_user: boolean = false;
  public imag_size: string = '60px';
  public anchoW!: number;
  public movil = false;
  public campaigns: Campaign[] = [];
  public nuevos: menu[] = [];
  public semi: menu[]=[];
  public after: menu[] = [];

  constructor(
    private _router: Router,
    private _authService: AuthService,
    private _campaingService: CampaingService,
  ) { 
    this.getCampaign();
  }

  ngDoCheck(): void {
    this.checkSessionStorageUser();
    this.url_dashboard = this.get_url_dashboard();
  }

  closeNavbar() {
    const navbar = document.getElementById('navbarSupportedContent') as HTMLElement;
    if (navbar) {
      navbar.classList.remove('show');
    }
  }

  public logout() {
    this.spinner = false;
    this._authService.signOut(this._router);
  }

  public get_url_dashboard() {
        
    let role: any = localStorage.getItem('role');
    
    if(role != null){

        if(role === 'client')
            return `/auth/mi-cuenta`

        return adminDashboardUrl(role);
    }

    return `/admin/not-autorized`;

  }

  public checkSessionStorageUser() {

    this.auth_user = (localStorage.getItem('user_token') && localStorage.getItem('user')) ? true : false;    
  }
  @HostListener('window:resize', ['$event'])
  onResize(event: Event) {
    this.anchoW = window.innerWidth;
    this.imag_size = this.anchoW - 50+ 'px';
    
    if(this.anchoW < 300){
      this.imag_size = '30px';
    }else{
      if(this.anchoW < 540){
        this.imag_size = '50px';
        this.movil = true;
      }else{
          this.imag_size = '60px';
    }
    }
  }

  public getCampaign(){
    this._campaingService.getCampaing()
    .subscribe({
      next: (response: GetcampaingResponse) =>{
        this.campaigns = response.data.campaigns;
        this.campaigns.forEach(element => {
          const x = {
            name:     element.name,
            uuid:     element.uuid
          }
          if(element.category == 'vehiculos nuevos'){
            this.nuevos.push(x);
          }else{
            if(element.category == 'vehiculos seminuevos'){
              this.semi.push(x);
            }else{ 
              if(element.category == 'aftersales'){
                this.after.push(x);
              }
            }
          }
        });
      }
    })
  }

  scrollToTop(n:string) {
    const element = document.getElementById(n); // Asegúrate de tener un elemento con este ID
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
  }
}
