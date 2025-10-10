import { Component, OnInit, AfterViewInit, HostListener, ElementRef} from '@angular/core';
import { DataItem , UserGrafic} from '@interfaces/auth.interface';
//import { DataItem, Grafica, UserGrafic } from '@interfaces/grafica.interface';

import { AccountService } from '../services/account.service';
import * as echarts from 'echarts';
@Component({
    selector: 'app-bar-chart-a',
    templateUrl: './bar-chart-a.component.html',
    styleUrls: ['./bar-chart-a.component.css'],
    standalone: false
})
export class BarChartAComponent implements AfterViewInit{

  public name!: string;
  private myChart!: echarts.ECharts;
  private chartDomT!: HTMLElement;
  public user = JSON.parse(localStorage.getItem('user')!);


  ngAfterViewInit(): void {
   
    this.initChart();
  }
   
  public  userGrafic : UserGrafic = {
    data : [
      {name: 'carro 1', value: 12000},
      {name: 'carro 2', value: 3000},
      {name: 'carro 3', value: 9823},
      {name: 'carro 4', value: 15000},
      {name: 'carro 5', value: 9000}
    ],
    user: this.user.nickname,
    img: 'assets/img/apple-touch-icon.png',
  }


  initChart(): void {
    const chartDom = document.getElementById('grafica-individual')!;
    this.chartDomT = chartDom;
    this.myChart = echarts.init(chartDom);

    //Recorrer el número de elementos que se mostraran en series
    const data = this.userGrafic.data
    const series = data.map((item: DataItem) => ({
      name: item.name,
      type: 'bar',
      stack: 'total',
      label: {
        show: true,
        rotate: 45,
      },
      emphasis: {
        focus: 'series'
      },
      data: [item.value]
    }));

    // se detecta el tamaño de la pantalla y se determina posición de etiquetas
    const getAxisLabelLegends = () => {
      const containerWidth = this.chartDomT.clientWidth;
      return containerWidth < 600 ? 90 : 0;
    };

    const option = {
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow' 
        }
      },
      legend: {
        show: false,
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
      },
      xAxis: {
        type: 'value',
        axisLabel: {
         rotate : getAxisLabelLegends()
        }
      },
      yAxis: {
        type: 'category',
        data: [this.userGrafic.user],
        axisLabel:{
          formatter: (value: string) => {
            return `{icon|}  ${value}`;  // se muestra el icono de usuario y el nombre
          },
          rich: {
            icon: {
              height: 20,
              align: 'center',
              backgroundColor: {
                image: this.userGrafic.img //se pasa la url de la imagen
              }
            }
          },
          rotate : getAxisLabelLegends()
        },
      },
      series:series
    };
    this.myChart.setOption(option);

  }

  //Ajusta la grafica de acuerdo al tamaño de su contenedor
  @HostListener('window:resize', ['$event'])
  onWindowResize(): void {
    if (this.myChart) {
      this.myChart.resize();
      this.updateAxisLabelLegends();
      this.updateAxisXLabelLegends();
    }
  }
//Actualiza la rotación de las etiquetas del eje y
  updateAxisLabelLegends(): void {
    const option = this.myChart.getOption() as echarts.EChartsCoreOption; 

    if (Array.isArray(option.yAxis)) {
      const yAxis = option.yAxis[0] as any; 
      if (yAxis && yAxis.axisLabel) {
        yAxis.axisLabel.rotate = this. getAxisLabelLegends(); // Cambia la posición
      }
    }

    this.myChart.setOption(option);
  }
//Actualiza la rotación de las etiquetas del eje x
  updateAxisXLabelLegends(): void {
    const option = this.myChart.getOption() as echarts.EChartsCoreOption; 

    if (Array.isArray(option.xAxis)) {
      const xAxis = option.xAxis[0] as any; 
      if (xAxis && xAxis.axisLabel) {
        xAxis.axisLabel.rotate = this. getAxisLabelLegends(); // Cambia la posición
      }
    }

    this.myChart.setOption(option);
  }
//determina el ancho de la pantalla y determina los grados
  getAxisLabelLegends(): number {
    const containerWidth = this.chartDomT.clientWidth;
    return containerWidth < 600 ? 90 : 0;;
  }

  
}
