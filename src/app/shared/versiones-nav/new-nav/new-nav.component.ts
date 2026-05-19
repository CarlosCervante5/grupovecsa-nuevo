import { CommonModule } from '@angular/common';
import { Component, HostListener, ElementRef, OnInit, OnDestroy } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from 'src/app/auth/services/auth.service';
import Swal from 'sweetalert2';
import { Subscription } from 'rxjs';
import { ChangeDetectorRef } from '@angular/core';
import { ShowData } from 'src/app/auth/interfaces/login.interface';
import { MatMenuModule } from '@angular/material/menu';
import { BoutiqueCartService } from 'src/app/boutique/services/boutique-cart.service';
import { CartDrawerComponent } from 'src/app/boutique/components/cart-drawer/cart-drawer.component';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';

@Component({
    selector: 'app-new-nav',
    templateUrl: './new-nav.component.html',
    styleUrls: ['./new-nav.component.css'],
    standalone: true,
    imports: [CommonModule, RouterModule, MatMenuModule, CartDrawerComponent]
})
export class NewNavComponent implements OnInit, OnDestroy {

  public vehicleLinks = [
    { name: 'BMW', params: ['/compra-tu-auto/Nuevo/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1'] },
    { name: 'MINI', params: ['/compra-tu-auto/Nuevo/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/1000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1'] },
    { name: 'MOTORRAD', params: ['/compra-tu-auto/Nuevo/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1'] },
    { name: 'Seminuevos', params: ['/compra-tu-auto/Seminuevo/sin-marcas/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/10000/60000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/ninguno/1'] }
  ];

  public url_dashboard: string = '/auth/mi-cuenta';
  public spinner: boolean = false;
  public auth_user: boolean = false;
  public imag_size: string = '60px';
  public anchoW!: number;
  public movil = false;

  isLoggedIn = false;
  user: ShowData | null = null;
  cartCount = 0;
  cartDrawerOpen = false;
  private authSubscription!: Subscription;
  private cartSubscription!: Subscription;

  isDropdownOpen = false;
  mobileOpen = false;

  vehicleMenuItems = [
    { label: 'Motos', link: '/compra-tu-auto' },
    { label: 'Seminuevos', link: '/seminuevos' },
    { label: 'Servicio', link: '/servicio' },
  ];

  constructor(
    private router: Router,
    private authService: AuthService,
    private cdr: ChangeDetectorRef,
    private elementRef: ElementRef,
    private cartService: BoutiqueCartService
  ) { 
  }

  ngOnInit(): void {
    this.authSubscription = this.authService.authStatus$.subscribe((status: boolean) => {
      const wasLoggedIn = this.isLoggedIn;
      this.isLoggedIn = status;
      if (status) {
        this.user = this.authService.getUserFromStorage();
        // Fetch server cart to seed the shared count
        this.cartService.get().subscribe({ error: () => {} });
      } else {
        this.user = null;
        // Only reset count if we were previously logged in (actual logout),
        // not on initial load as a guest — guest count comes from localStorage
        if (wasLoggedIn) {
          this.cartService.updateCount(null);
        }
      }
      this.cdr.detectChanges();
    });

    // Subscribe to reactive cart count (updates whenever add/remove/update is called anywhere)
    this.cartSubscription = this.cartService.cartCount$.subscribe(count => {
      this.cartCount = count;
      this.cdr.detectChanges();
    });
  }

  ngOnDestroy(): void {
    if (this.authSubscription) {
      this.authSubscription.unsubscribe();
    }
    if (this.cartSubscription) {
      this.cartSubscription.unsubscribe();
    }
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

  toggleCartDrawer(): void {
    this.cartDrawerOpen = !this.cartDrawerOpen;
  }

  closeCartDrawer(): void {
    this.cartDrawerOpen = false;
  }

  logout(): void {
    // Fire-and-forget the backend logout
    this.authService.logout().subscribe({ error: () => {} });
    // Clear local state immediately
    localStorage.clear();
    this.isLoggedIn = false;
    this.user = null;
    this.cartService.updateCount(null);
    this.router.navigateByUrl('/auth/iniciar-sesion');
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

  @HostListener('document:click', ['$event'])
  onDocumentClick(event: Event): void {
    if (this.elementRef && this.elementRef.nativeElement && !this.elementRef.nativeElement.contains(event.target)) {
      this.isDropdownOpen = false;
    }
  }

  toggleDropdown(event: Event): void {
    event.stopPropagation();
    this.isDropdownOpen = !this.isDropdownOpen;
  }

  closeDropdown(): void {
    this.isDropdownOpen = false;
  }

  reloadPage() {
    this.router.navigate(['/']).then(() => {
      window.location.reload();
    });
  }
}