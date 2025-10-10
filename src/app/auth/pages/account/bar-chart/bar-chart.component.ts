import { Component, OnInit, AfterViewInit, HostListener, ElementRef} from '@angular/core';
//import { Grafica } from '@interfaces/grafica.interface';
import { Grafica } from '@interfaces/auth.interface';
import * as echarts from 'echarts';
import { max } from 'rxjs';

@Component({
    selector: 'app-bar-chart',
    templateUrl: './bar-chart.component.html',
    styleUrls: ['./bar-chart.component.css'],
    standalone: false
})
export class BarChartComponent implements AfterViewInit, OnInit {
  private myChart!: echarts.ECharts;
  private chartDomT!: HTMLElement;
  public altoG!: number;
  public divWidth!: string ;
  public anchoW!: number;

  public grafica : Grafica = {
    type: 'bar',
    title: 'Prueba de grafica',
    tags: ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
    data: [ 5, 10 , 9 , 12, 7 , 6 , 8],
    tags_img: [
      'assets/img/apple-touch-icon.png',
      'assets/img/about.jpg',
      'assets/img/apple-touch-icon.png',
      'assets/img/bmwpc.jpg',
      'assets/img/apple-touch-icon.png',
      'assets/img/about.jpg',
      'assets/img/apple-touch-icon.png',
    ],
  }

  ngOnInit(): void {
    let n_datos = this.grafica.data.length;
    let tam = ((n_datos * 12) + 400) ;
    this.altoG = tam ;
    this.anchoW = window.innerWidth;
    if(this.anchoW < 500){
      this.divWidth = '370px';
    }else{
      if(this.anchoW < 1000){
        this.divWidth = '700px';
      }else{
        this.divWidth = '100%';
      }
    }
  }
  ngAfterViewInit(): void {
    this.initChart();
  }

  initChart(): void {
    const chartDom = document.getElementById('grafica-completa')!;
    this.chartDomT = chartDom;
    this.myChart = echarts.init(chartDom);

    //definicion de los malores máximo y mínimo
    const maxDataValue = Math.max(...this.grafica.data);
    const minDataValue = Math.min(...this.grafica.data);

    const buffer = 5;  // Espacio adicional por encima del valor máximo
    const maxAxisValue = Math.ceil(maxDataValue + buffer); 
    const images = this.grafica.tags_img;

     // Establece imaganes de manera aletaoria de acuerdo a un arreglo
     const richTextStyles: any = {};
     images.forEach((_, index) => {
       richTextStyles[`a${index}`] = {
         height: 24,
         width: 24,
         align: 'center',
         verticalAlign: 'middle',
         backgroundColor: {
           image: images[index] // Dynamically set image for each tag
         },// Padding to separate image from text
       };
     });

     // Establece los colores de las barras
      const getGradientColor = (value: number) => {
      const ratio = (value - minDataValue) / (maxDataValue - minDataValue);
      const startColor = [215, 215, 215]; // Color inicial
      const endColor = [130, 130, 130]; // Blue color
      const interpolateColor = (start: number[], end: number[], ratio: number) => {
        return start.map((startValue, index) => Math.round(startValue + (end[index] - startValue) * ratio));
      };
      const gradientColor = interpolateColor(startColor, endColor, ratio);
      return `rgb(${gradientColor.join(',')})`;
    };

    // se detecta el tamaño de la pantalla y se determina posición de etiquetas
    const getAxisLabelPosition = () => {
      const containerWidth = this.chartDomT.clientWidth;
      return containerWidth < 600 ? 'inside' : 'outside';
    };

    const option = {
      grid: {
        left: '0%',
        right: '0%',
        bottom: '15%',
        containLabel: true
      },
      title: {
        text: this.grafica.title
      },
      tooltip: {
        trigger: 'item',
        borderWidth : 1,
        textStyle: {
          fontSize: 15,
        },
        padding: 10,
      },
      xAxis: {
        max:maxAxisValue,
      },
      yAxis: {
        data: this.grafica.tags,
        axisLabel: {
          rotate: 0, // Rotar las etiquetas del eje X a 45 grados
          margin: 5,
          formatter: (value: string, index : number) => {
            const icon = images[index] || '';
            return `{a${index}|}  ${value}`;  // `${params.name}` is the text
          },
          rich: richTextStyles,
          position: getAxisLabelPosition(),
        },
      },
      series: [{
        type: this.grafica.type,
        data: this.grafica.data,
        orientation: 'horizontal',
        // barWidth: 20,
        itemStyle: {
          color: (params: any) => {
            return getGradientColor(params.value);
          }
        },
        label:{
          show: true,
        },
        lineStyle: {
          color: '#52ff33'  // itemStyle.color: Cambia el color de los puntos de la serie.
        }
      }]
    };
    this.myChart.setOption(option);

  }

  //Ajusta la grafica de acuerdo al tamaño de su contenedor
  @HostListener('window:resize', ['$event'])
  onResize(event: Event) {
    this.anchoW = window.innerWidth;
    if(this.anchoW < 500){
      this.divWidth = '370px';
    }else{
      if(this.anchoW < 1000){
        this.divWidth = '700px';
      }else{
        this.divWidth = '100%';
      }
    }
    if (this.myChart) {
      this.myChart.resize();
      this.updateAxisLabelPosition();
    }
  }

  updateAxisLabelPosition(): void {
    const option = this.myChart.getOption() as echarts.EChartsCoreOption; 

    if (Array.isArray(option.yAxis)) {
      const yAxis = option.yAxis[0] as any; 
      if (yAxis && yAxis.axisLabel) {
        yAxis.axisLabel.position = this.getAxisLabelPosition(); // Cambia la posición
      }
    }

    this.myChart.setOption(option);
  }

  getAxisLabelPosition(): string {
    const containerWidth = this.chartDomT.clientWidth;
    return containerWidth < 600 ? 'inside' : 'outside';;
  }

}
