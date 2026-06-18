import { Component, OnInit, AfterViewInit, OnDestroy, ViewChild, ElementRef, HostListener, Output, EventEmitter } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { Title } from '@angular/platform-browser';
import * as echarts from 'echarts/core';
import { register } from 'swiper/element/bundle';
import { AuthService } from '@services/auth.service';
import { Router } from '@angular/router';
import { CampaingService } from '@services/campaing.service';
import { GetcampaingResponse, promosUser } from '@interfaces/admin.interfaces';
import { CustomerPositionResponse, Datum, PointsResponse } from '@interfaces/auth.interface';
import { EventsService } from 'src/app/admin/gestor/services/events.service';
import { Events } from '@interfaces/community.interface';
// register Swiper custom elements

import {reload} from '@helpers/session.helper';
import { RedeemCouponsComponent } from 'src/app/admin/staff/components/redeem-coupons/redeem-coupons.component';
import { TableCouponsComponent } from 'src/app/admin/staff/components/table-coupons/table-coupons.component';


@Component({
    selector: 'app-profile',
    templateUrl: './profile.component.html',
    styleUrls: ['profile.component.css'],
    standalone: false
})

export class ProfileComponent implements AfterViewInit, OnInit, OnDestroy {
  
    @ViewChild('myModal') modal!: ElementRef;
    @ViewChild('myImg') imgM!: ElementRef;
    @ViewChild('img01') modalImg!: ElementRef; 
    @ViewChild('caption') caption!: ElementRef;

    @Output() reload = new EventEmitter<Boolean>();

    activeTab: string = 'perfil';

    execute!:string;
    // Information User
    public name: string = '';
    public nickname: string = '';
    public lastname: string = '';
    public email: string = '';
    public phone: string = '';
    public customer_uuid: string = '';
    public spinner = true;
    public img: string = '';
    public horaActual!: string;
    private intervalo: any;
    public promosN = 'promosN';
    public points: Datum[] = [];
    public customerP: number = 0;
    public puntos_mes: number = 0;
    public puntos_acum: number = 0;
    public puntosGrafica: number[] = [];
    showScrollButton = false;

    private myChart: echarts.ECharts | null = null;
    private chartDomT: HTMLElement | null = null;

    public ancho!: number;
    public anchoCards!: number;
    public tagsvert!: number;
    public anchoS!: string;
    public anchoW!: number;
    public promos: promosUser[] = []; 
    public images: Events[] = [];

    constructor(
       private titleService: Title,
       private _bottomSheet: MatBottomSheet,
       private _campaingService: CampaingService, 
       private _eventService: EventsService,
       private _authService: AuthService,
       private _router: Router) { 
        // Set Title View
        this.titleService.setTitle('Grupo Vecsa | Mi Cuenta');
        this.getPhotoUser ();
        register();
    }


    ngAfterViewInit(): void {
      this.userSessionStorage();
      this.cubetime();
    }

    ngOnDestroy(): void {
      this.disposeChart();
    }

    ngOnInit(): void {
      this.userSessionStorage();
      this.pointsRewards();
      this.customerPosition();
      // Load non-critical data after a short delay
      setTimeout(() => {
        this.getPromotions();
        this.imagenes();
      }, 300);
    }

    private userSessionStorage() {
        const user = JSON.parse(localStorage.getItem('user')!);
        this.nickname = user.nickname;
        const profile = JSON.parse(localStorage.getItem('profile')!);
        this.name = profile.name;
        this.lastname = profile.last_name;
        this.email = user.email;
        this.phone = profile.phone_1;
        this.customer_uuid = profile.uuid;
    }

    cubetime (): void {
        setTimeout(() => {
            this.spinner = false;
            this.scheduleChartRender();
        }, 500);
    }

    private getPhotoUser (){
        const user = JSON.parse(localStorage.getItem('profile')!);
        this.img = user.picture || `assets/icons/profile.svg`;
    } 

    // se detecta el tamaño de la pantalla y se determina posición de etiquetas
  public getAxisLabelPosition = () => {
      if (!this.chartDomT) {
        return 0;
      }
      const containerWidth = this.chartDomT.clientWidth;
      return containerWidth < 600 ? 45 : 0;
    };
    public getAxisLabelSize = () => {
      if (!this.chartDomT) {
        return 17;
      }
      const containerWidth = this.chartDomT.clientWidth;
      return containerWidth < 600 ? 10 : 17;
    };

    private scheduleChartRefresh(): void {
      setTimeout(() => this.refreshChart(), 0);
    }

    /** Espera a que *ngIf vuelva a montar #linea tras cambiar de pestaña. */
    private scheduleChartRender(attempt = 0): void {
      const maxAttempts = 8;
      setTimeout(() => {
        if (this.activeTab !== 'perfil' || this.spinner) {
          return;
        }
        if (document.getElementById('linea')) {
          this.renderChart();
          return;
        }
        if (attempt < maxAttempts) {
          this.scheduleChartRender(attempt + 1);
        }
      }, attempt === 0 ? 0 : 50);
    }

    private isChartDomMounted(): boolean {
      if (!this.myChart || this.myChart.isDisposed()) {
        return false;
      }
      const dom = this.myChart.getDom();
      return !!dom?.isConnected && dom.id === 'linea';
    }

    private disposeChart(): void {
      if (this.myChart && !this.myChart.isDisposed()) {
        this.myChart.dispose();
      }
      this.myChart = null;
      this.chartDomT = null;
    }

    private buildChartOption(): echarts.EChartsCoreOption {
      return {
        legend: {
          data: ['Puntos', 'Kilometros']
        },
        tooltip: {
          trigger: 'axis'
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: ['ene', 'feb', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'],
          axisLabel: {
            color: '#fff',
            fontSize: 17,
            fontFamily: 'Mulish',
          }
        },
        yAxis: {
          type: 'value',
          axisLabel: {
            color: '#fff',
            fontSize: this.getAxisLabelSize(),
            rotate: this.getAxisLabelPosition(),
            fontFamily: 'Mulish',
          }
        },
        series: [
          {
            name: 'puntos',
            type: 'line',
            stack: 'Total',
            areaStyle: {
              color: '#86BEDA'
            },
            emphasis: {
              focus: 'series'
            },
            data: this.puntosGrafica
          },
        ]
      };
    }

    private renderChart(): void {
      if (this.activeTab !== 'perfil' || this.spinner) {
        return;
      }

      const chartDom = document.getElementById('linea');
      if (!chartDom) {
        return;
      }

      this.chartDomT = chartDom;
      this.disposeChart();
      this.myChart = echarts.init(chartDom);
      this.myChart.setOption(this.buildChartOption());
    }

    private refreshChart(): void {
      if (this.activeTab !== 'perfil' || this.spinner) {
        return;
      }

      if (this.isChartDomMounted()) {
        this.myChart!.setOption(this.buildChartOption());
        this.myChart!.resize();
        return;
      }

      this.disposeChart();
      this.scheduleChartRender();
    }

    updateAxisLabelPosition(): void {
      if (!this.myChart || this.myChart.isDisposed()) {
        return;
      }

      const option = this.myChart.getOption() as echarts.EChartsCoreOption;

      if (Array.isArray(option.yAxis)) {
        const yAxis = option.yAxis[0] as { axisLabel?: { rotate?: number; fontSize?: number } };
        if (yAxis?.axisLabel) {
          yAxis.axisLabel.rotate = this.getAxisLabelPosition();
          yAxis.axisLabel.fontSize = this.getAxisLabelSize();
        }
      }

      this.myChart.setOption(option);
    }

    getPromotions(){
      this._campaingService.getCampaing()
      .subscribe({
        next: (response: GetcampaingResponse) => {
          response.data.campaigns.forEach(element => {
              element.promotions.forEach(element => {
                let p = {
                  'name': element.name,
                  'url': element.image_path,
                }
                this.promos.push(p);
              });
          });
        },
        error: (error:any) => {
          reload(error, this._router);
        }
      })
    }

    showModal( src: string) {   
      let imagen = src;
      let legal = "";
  
      this.modal.nativeElement.style.display = "grid";
      this.modalImg.nativeElement.src = imagen;  
      this.caption.nativeElement.innerHTML = legal ;
    }
    
    closeModal(message: string) {
        this.execute = message === 'yes' && this.execute === 'no' ? 'processing' : message;
      
        if (this.execute === 'yes') {
          this.modal.nativeElement.style.display = "none";
        }
    }

    @HostListener('window:resize')
    onResize(): void {
        if (this.myChart && !this.myChart.isDisposed()) {
            this.myChart.resize();
            this.updateAxisLabelPosition();
        }
        this.anchoW = window.innerWidth;

        if(this.anchoW < 500){
            this.anchoCards = 1;
            this.tagsvert = 2;
        }else{
            if(this.anchoW < 1000){
                this.anchoCards = 2;
                this.tagsvert = 2;
            }else{
                if(this.anchoW < 1200){
                    this.anchoCards = 3
                    this.tagsvert = 3;
                }else{
                    if(this.anchoW > 1200){
                        this.anchoCards = 4;
                        this.tagsvert = 3;
                    }
                }
            }
        }
    }

    public imagenes() {
        this._eventService.getEventsManager('schedule').subscribe({
            next: (response) => {
                
                response.data.events.map((item) => {
                this.images.push(item);
                });
            },
            error: (error:any) => {
                reload(error, this._router);
            }
        });
    }

    public pointsRewards(){
        this._authService.getPointsRewards().
        subscribe({
            next:(response: PointsResponse) =>{
                this.points = response.data;
            },
            error: (error:any) => {
                reload(error, this._router);
            }
        })
    }

    public customerPosition(){
        this._authService.getPositionCustomer(this.customer_uuid).
        subscribe({
            next:(response: CustomerPositionResponse) =>{
                this.customerP = response.data.profile.position;
                this.puntos_mes = response.data.profile.month_points;
                this.puntos_acum = response.data.profile.total_points;
                this.puntosGrafica = this.floorArray(response.data.months);
                this.scheduleChartRefresh();
            },
            error: (error:any) => {
                reload(error, this._router);
            }
        })
    }

    @HostListener('window:scroll', [])
    scrollToTop() {
        const element = document.getElementById('top'); // Asegúrate de tener un elemento con este ID
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    }

    floor(value: number): number {
        return Math.floor(value);
    }

    floorArray(arr: number[]): number[] {
        return arr.map(num => Math.floor(num));
    }

    selectTab(tab: string): void {
        if (tab !== 'perfil' && this.activeTab === 'perfil') {
          this.disposeChart();
        }
        this.activeTab = tab;
        if (tab === 'perfil') {
          this.scheduleChartRender();
        }
    }

    redeemPoints(): void {
            
        const bottomSheetRef = this._bottomSheet.open( RedeemCouponsComponent, {
            data: {
                customer_uuid: this.customer_uuid,
            }
        });

        bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {
            
            if(dataFromChild != undefined && dataFromChild.reload === true ){        
                
              setTimeout(() => {
                  console.log("hola2");
                  this.reload.emit(true);
                  this.customerPosition();
              }, 2000);  // Se tiene que validar que esta función pueda ser llamada sincronamente
            }
        });
    }


    seeCoupons(): void {
            
      const bottomSheetRef = this._bottomSheet.open( TableCouponsComponent, {
          data: {
              customer_uuid: this.customer_uuid,
          }
      });

      bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {
          
          if(dataFromChild != undefined && dataFromChild.reload === true ){        
              
            setTimeout(() => {
                console.log("hola2");
                this.reload.emit(true);
                this.customerPosition();
            }, 2000);  // Se tiene que validar que esta función pueda ser llamada sincronamente
          }
      });
  }

}
