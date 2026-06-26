import { Component, OnInit, ElementRef, ViewChild, HostListener, AfterViewInit, OnDestroy } from '@angular/core';
import { forkJoin, Observable, Subscription } from 'rxjs';
import { map, startWith } from 'rxjs/operators';
import { FormControl } from '@angular/forms';

import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { MatAutocompleteSelectedEvent } from '@angular/material/autocomplete';
import { MatChipInputEvent } from '@angular/material/chips';
import { MatAccordion } from '@angular/material/expansion';
import { MatPaginator, PageEvent } from '@angular/material/paginator';

// Services
import { CompraTuAutoService } from '@services/compra-tu-auto.service';

// Interfaces
import { ActivatedRoute, Params, Router } from '@angular/router';
import { FiltersResponse, Vehicle, linksImage} from '@interfaces/vehicle_data.interface';
import { register } from 'swiper/element/bundle';
// register Swiper custom elements
register();
@Component({
    selector: 'app-compra-tu-auto',
    templateUrl: './compra-tu-auto.component.html',
    styleUrls: ['./compra-tu-auto.component.css'],
    standalone: false
})

export class CompraTuAutoComponent implements OnInit, AfterViewInit, OnDestroy {

  public dataImages:linksImage[] = [
    {
      "url"  : 'assets/img/carousel_logos/logo_1.jpeg',
      "link" : 'https://abcars.mx'
    },
    {
      "url"  : 'assets/img/carousel_logos/logo_2.jpeg',
      "link" : '/compra-tu-auto/Seminuevo/sin-marcas/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/100000/5000000/sin-estados/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/1'
    },
    {
      "url"  : 'assets/img/carousel_logos/logo_3.jpeg',
      "link" : 'https://www.chevrolet.com.mx/'
    },
    {
      "url"  : 'assets/img/carousel_logos/logo_4.jpeg',
      "link" : '/compra-tu-auto/Nuevo/Motorrad/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/100000/5000000/Hidalgo/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/1'
    },
    {
      "url"  : 'assets/img/carousel_logos/logo_5.jpeg',
      "link" : '/compra-tu-auto/Nuevo/Bmw/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/100000/5000000/Hidalgo/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/1'
    },
    {
      "url"  : 'assets/img/carousel_logos/logo_6.jpeg',
      "link" : '/compra-tu-auto/Nuevo/Mini/sin-lineas/sin-modelos/sin-carrocerias/sin-versiones/sin-anios/100000/5000000/Hidalgo/sin-busqueda/sin-transmisiones/sin-colores/sin-colores/1'
    }
];

  public ancho!: number;
  public anchoW!: number;
  public status: boolean = true;
  public isMobile: boolean = false;
  @HostListener('window:resize', ['$event'])
    onResize(event: Event) {
      this.anchoW = window.innerWidth;
      if(this.anchoW < 500){
        this.ancho = 1;
      }else{
        if(this.anchoW < 1000){
          this.ancho = 2;
        }else{
          this.ancho = 3;
        }
      }
      if(this.anchoW <= 768){
        this.status = false;
        this.isMobile = true;
      }else{
        this.status = true;
        this.isMobile = false;
      }
    }
  
  // References Input
  public selectable = true;
  public removable = true;
  public separatorKeysCodes: number[] = [ENTER, COMMA];

  // References "Categories"
  public allCategories: string[] = [];
  public filteredCategories: Observable<string[]>;
  public categoryCtrl = new FormControl('');
  public categories: string[] = [];

  // References "Brands"
  public allBrands: string[] = [];
  public filteredBrands: Observable<string[]>;
  public brandCtrl = new FormControl('');
  public brands: string[] = [];

  // References "lines"
  public allLines: string[] = [];
  public filteredLines: Observable<string[]>
  public lineCtrl = new FormControl('');
  public lines: string[] = [];

  // References "versions"
  public allVersions: string[] = [];
  public filteredVersions: Observable<string[]>
  public versionCtrl = new FormControl('');
  public versions: string[] = [];

  // References "bodies"
  public allBodies: string[] = [];
  public filteredBodies: Observable<string[]>
  public bodyCtrl = new FormControl('');
  public bodies: string[] = [];

  // References "types"
  public allTypes: string[] = [];
  public filteredTypes: Observable<string[]>;
  public typeCtrl = new FormControl('');
  public types: string[] = [];

  // References "Models"
  public allModels: string[] = [];  
  public filteredModels: Observable<string[]>;
  public modelCtrl = new FormControl('');
  public models: string[] = [];

  // References "Years"
  public allYears: string[] = [];  
  public filteredYears: Observable<string[]>;
  public yearCtrl = new FormControl('');
  public years: string[] = [];

  // References "States"
  public allStates: string[] = [];  
  public filteredStates: Observable<string[]>;
  public stateCtrl = new FormControl('');
  public states: string[] = [];


  // References "Transmission"
  public allTransmissions: string[] = [];  
  public filteredTransmissions: Observable<string[]>;
  public transmissionCtrl = new FormControl('');
  public transmissions: string[] = [];

  // References "Exterior Color"
  public allExtColors: string[] = [];  
  public filteredExtColors: Observable<string[]>;
  public extColorCtrl = new FormControl('');
  public extColors: string[] = [];

  // References "Interior Color"
  public allIntColors: string[] = [];  
  public filteredIntColors: Observable<string[]>;
  public intColorCtrl = new FormControl('');
  public intColors: string[] = [];

  public orden: string = 'ninguno'; /** Antes era vacio */

  // Vehiculos
  public spinner = true;
  public vehicles: Vehicle[] = [];
  public filters: string[] = [];
  public palabra_busqueda: string = '';

  private timer: any;
  
  @ViewChild('brandInput') brandInput!: ElementRef<HTMLInputElement>;
  @ViewChild('lineInput') lineInput!: ElementRef<HTMLInputElement>;
  @ViewChild('versionInput') versionInput!: ElementRef<HTMLInputElement>;
  @ViewChild('bodyInput') bodyInput!: ElementRef<HTMLInputElement>;
  @ViewChild('modelInput') modelInput!: ElementRef<HTMLInputElement>;
  @ViewChild('yearInput') yearInput!: ElementRef<HTMLInputElement>;
  @ViewChild('stateInput') stateInput!: ElementRef<HTMLInputElement>;
  @ViewChild('transmissionInput') transmissionInput!: ElementRef<HTMLInputElement>; 
  @ViewChild('typeInput') typeInput!: ElementRef<HTMLInputElement>; 
  @ViewChild('categoryInput') categoryInput!: ElementRef<HTMLInputElement>; 
  @ViewChild('extColorInput') extColorInput!: ElementRef<HTMLInputElement>; 
  @ViewChild('intColorInput') intColorInput!: ElementRef<HTMLInputElement>; 

  @ViewChild(MatAccordion) accordion!: MatAccordion;
  @ViewChild(MatPaginator) paginator!: MatPaginator;

  // References "Enganche"
  public hitchTickInterval = 1;  
  public hitchMax = 30000000;
  public hitchMin = 10000;
  public hitchStep = 0;
  // public hitchValue = 3000000;
  public thumbLabel = true;
  public disabled = false;
  public showTicks = false;

  // References "Price"
  public max_price = 30000000;
  public min_price = 0;

  // MatPaginator Inputs
  public length = 0;
  public pageSize = 15;
  public pageIndex: number = 1;

  // MatPaginator Output
  public pageEvent!: PageEvent;

  private searchSubscription?: Subscription;

  constructor(
    private _compraTuAutoService: CompraTuAutoService,
    private _activatedRoute: ActivatedRoute,
    private _router: Router    
  )
  {
    /**
     * Filtered Elements   
     */     

    // Categories
    this.filteredCategories = this.categoryCtrl.valueChanges.pipe(startWith(null),
      map((category: string | null) => category ? this._filterCategories(category) : this.allCategories.slice()));
    
      // Brands
    this.filteredBrands = this.brandCtrl.valueChanges.pipe(startWith(null),
      map((brand: string | null) => brand ? this._filterBrands(brand) : this.allBrands.slice()));

    // Lines
    this.filteredLines = this.lineCtrl.valueChanges.pipe(startWith(null),
      map((line: string | null) => line ? this._filterLines(line) : this.allLines.slice()));

    // Versions
    this.filteredVersions = this.versionCtrl.valueChanges.pipe(startWith(null),
      map((version: string | null) => version ? this._filterVersions(version) : this.allVersions.slice()));
    
    // Bodies
    this.filteredBodies = this.bodyCtrl.valueChanges.pipe(startWith(null),
      map((body: string | null) => body ? this._filterBodies(body) : this.allBodies.slice()));
      
    // Models
    this.filteredModels = this.modelCtrl.valueChanges.pipe(startWith(null),
      map((model: string | null) => model ? this._filterModels(model) : this.allModels.slice()));

    // Years
    this.filteredYears = this.yearCtrl.valueChanges.pipe(startWith(null),
      map((year: string | null) => year ? this._filterYears(year) : this.allYears.slice()));

    // States 
    this.filteredStates = this.stateCtrl.valueChanges.pipe(startWith(null),
      map((state: string | null) => state ? this._filterStates(state) : this.allStates.slice()));

    // Transmissions 
    this.filteredTransmissions = this.transmissionCtrl.valueChanges.pipe(startWith(null),
      map((transmission: string | null) => transmission ? this._filterTransmissions(transmission) : this.allTransmissions.slice()));
    
    // Exterior colors 
    this.filteredExtColors = this.extColorCtrl.valueChanges.pipe(startWith(null),
      map((extColor: string | null) => extColor ? this._filterExtColors(extColor) : this.allExtColors.slice()));

    // Interior color 
    this.filteredIntColors = this.intColorCtrl.valueChanges.pipe(startWith(null),
      map((intColor: string | null) => intColor ? this._filterIntColors(intColor) : this.allIntColors.slice()));
    
    // Types
    this.filteredTypes = this.typeCtrl.valueChanges.pipe(startWith(null),
      map((type: string | null) => type ? this._filterTypes(type) : this.allTypes.slice()));
      
  }


  ngOnInit(): void {  
    this.anchoW = window.innerWidth;
      if(this.anchoW <= 768){
        this.status = false;
        this.isMobile = true;
      }else{
        this.status = true;
        this.isMobile = false;
      }
    this._activatedRoute.params.subscribe(params => {
      this.applyRouteParams(params);
    });
  }

  ngAfterViewInit(): void {
    if (this.pageIndex > 1) {
      this.scrollTop();
    }
  }

  ngOnDestroy(): void {
    this.searchSubscription?.unsubscribe();
  }

  capitalizeFirstLetter(string:string):string {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  eliminarDuplicados( array: string []): string[]{
    return array.filter( (ele:string,pos:number)=>array.indexOf(ele) == pos);        
  }

  scrollTop() {
    const drawerContent = document.querySelector('.mat-drawer-content') as HTMLElement;
    if (drawerContent) {
      drawerContent.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  titleCase(str: string | null | undefined): string {
    if (str == null || str === '') {
      return '';
    }
    return str.toLowerCase().split(' ').map(function(word) {
      return (word.charAt(0).toUpperCase() + word.slice(1));
    }).join(' ');
  }

  /**
   * Change status accordion
   */
  public openAccordion(accordion: boolean) {
    this.status = !accordion;    
  }

  /**
   * Add Models
   */
  public add( event: MatChipInputEvent, input: string ): void {
    const value = (event.value || '').trim();

    // Add element
    if (value) {
      switch (input) {
        case 'brands':
          this.brands.push(value);
          this.brandCtrl.setValue(null);
          event.chipInput!.clear();
          break;

        case 'lines':
          this.lines.push(value);
          this.lineCtrl.setValue(null);
          event.chipInput!.clear();
          break;

        case 'versions':
          this.versions.push(value);
          this.versionCtrl.setValue(null);
          event.chipInput!.clear();
          break;

        case 'bodies':
          this.bodies.push(value);
          this.bodyCtrl.setValue(null);
          event.chipInput!.clear();
          break;

        case 'models':
          this.models.push(value);
          this.modelCtrl.setValue(null);
          event.chipInput!.clear();
          break;

        case 'years':
          this.years.push(value);
          this.yearCtrl.setValue(null);
          event.chipInput!.clear();
          break;
        
        case 'states':
          this.states.push(value);
          this.stateCtrl.setValue(null);
          event.chipInput!.clear();
          break;

        case 'transmissions':
          this.transmissions.push(value);
          this.transmissionCtrl.setValue(null);
          event.chipInput!.clear();
          break;
        case 'extColors':
          this.extColors.push(value);
          this.extColorCtrl.setValue(null);
          event.chipInput!.clear();
          break;
        case 'intColors':
          this.intColors.push(value);
          this.intColorCtrl.setValue(null);
          event.chipInput!.clear();
          break;
        case 'categories':
          this.categories.push(value);
          this.categoryCtrl.setValue(null);
          event.chipInput!.clear();
          break;
      }
    }

    this.navigate();
  }
  
  /**
   * Remove element
   */
  public remove( model: string, input: string ): void {
    let index;

    switch (input) {
      case 'brands':
        index = this.brands.indexOf(model);

        if (index >= 0) {
          this.brands.splice(index, 1);
        }
        break;

      case 'lines':
        index = this.lines.indexOf(model);

        if (index >= 0) {
          this.lines.splice(index, 1);
        }
        break;
      
      case 'versions':
        index = this.versions.indexOf(model);

        if (index >= 0) {
          this.versions.splice(index, 1);
        }
        break;

      case 'bodies':
        index = this.bodies.indexOf(model);

        if (index >= 0) {
          this.bodies.splice(index, 1);
        }
        break;

      case 'models':
        index = this.models.indexOf(model);

        if (index >= 0) {
          this.models.splice(index, 1);
        }
        break;

      case 'years':
        index = this.years.indexOf(model);

        if (index >= 0) {
          this.years.splice(index, 1);
        }        
        break;

      case 'states':
        index = this.states.indexOf(model);

        if (index >= 0) {
          this.states.splice(index, 1);
        }        
        break;
      
      case 'transmissions':
        index = this.transmissions.indexOf(model);

        if (index >= 0) {
          this.transmissions.splice(index, 1);
        }        
        break;

      case 'categories':
      index = this.categories.indexOf(model);

      if (index >= 0) {
        this.categories.splice(index, 1);
      }        
      break;

      case 'extColors':
        index = this.extColors.indexOf(model);

        if (index >= 0) {
          this.extColors.splice(index, 1);
        }        
        break;
      case 'intColors':
        index = this.intColors.indexOf(model);

        if (index >= 0) {
          this.intColors.splice(index, 1);
        }        
        break;
    }

    this.navigate();
  }

  /**
   * Select element    
   */
  public selected( event: MatAutocompleteSelectedEvent, input: string ): void {    
    this.palabra_busqueda = ''; 
    switch (input) {
      case 'brands':        
        if(!this.existsInArray( this.brands, event.option.viewValue)){
          this.brands.push(event.option.viewValue);
        }          
        this.brandInput.nativeElement.value = '';
        this.brandCtrl.setValue(null);
        break;
      
      case 'lines':
        if (!this.existsInArray( this.lines, event.option.viewValue)) {
          this.lines.push(event.option.viewValue);
        }
        this.lineInput.nativeElement.value = '';
        this.lineCtrl.setValue(null);
        break;

      case 'versions':
        if (!this.existsInArray( this.versions, event.option.viewValue)) {
          this.versions.push(event.option.viewValue);
        }
        this.versionInput.nativeElement.value = '';
        this.versionCtrl.setValue(null);
        break;

      case 'bodies':
        if (!this.existsInArray( this.bodies, event.option.viewValue)) {
          this.bodies.push(event.option.viewValue);
        }
        this.bodyInput.nativeElement.value = '';
        this.bodyCtrl.setValue(null);
        break;

      case 'models':
        if(!this.existsInArray( this.models, event.option.viewValue)){
          this.models.push(event.option.viewValue);
        }         
        this.modelInput.nativeElement.value = '';
        this.modelCtrl.setValue(null);        
        break;  

      case 'years':
        if(!this.existsInArray( this.years, event.option.viewValue)){
          this.years.push(event.option.viewValue);
        }          
        this.yearInput.nativeElement.value = '';
        this.yearCtrl.setValue(null);
        break;
      
      case 'states':
        if(!this.existsInArray( this.states, event.option.viewValue)){
          this.states.push(event.option.viewValue);
        }          
        this.stateInput.nativeElement.value = '';
        this.stateCtrl.setValue(null);
        break;
      
      case 'transmissions':
        if(!this.existsInArray( this.transmissions, event.option.viewValue)){
          this.transmissions.push(event.option.viewValue);
        }          
        this.transmissionInput.nativeElement.value = '';
        this.transmissionCtrl.setValue(null);
        break;
      case 'types':
        if(!this.existsInArray( this.types, event.option.viewValue)){
          this.types.push(event.option.viewValue);
        }          
        this.typeInput.nativeElement.value = '';
        this.typeCtrl.setValue(null);
        break;
      case 'categories':
        if(!this.existsInArray( this.categories, event.option.viewValue)){
          this.categories.push(event.option.viewValue);
        }          
        this.categoryInput.nativeElement.value = '';
        this.categoryCtrl.setValue(null);
        break;
      case 'extColors':
        if(!this.existsInArray( this.extColors, event.option.viewValue)){
          this.extColors.push(event.option.viewValue);
        }          
        this.extColorInput.nativeElement.value = '';
        this.extColorCtrl.setValue(null);
        break;
      case 'intColors':
        if(!this.existsInArray( this.intColors, event.option.viewValue)){
          this.intColors.push(event.option.viewValue);
        }          
        this.intColorInput.nativeElement.value = '';
        this.intColorCtrl.setValue(null);
        break;
    }

    this.navigate();
  }

  /**
   * Filter models
   */
  private _filterCategories( value: string ): string[] {
    const filterValue = value.toLowerCase();    
    return this.allCategories.filter(element => element.toLowerCase().includes(filterValue));                    
  }

  private _filterBrands( value: string ): string[] {
    const filterValue = value.toLowerCase();    
    return this.allBrands.filter(element => element.toLowerCase().includes(filterValue));                    
  }

  private _filterLines( value: string ): string[] {
    const filterValue = value.toLowerCase();
    return this.allLines.filter(element => element.toLowerCase().includes(filterValue));
  }

  private _filterVersions( value: string ): string[] {
    const filterValue = value.toLowerCase();
    return this.allVersions.filter(element => element.toLowerCase().includes(filterValue));
  }

  private _filterBodies( value: string ): string[] {
    const filterValue = value.toLowerCase();
    return this.allBodies.filter(element => element.toLowerCase().includes(filterValue));
  }

  private _filterModels( value: string ): string[] {
    const filterValue = value.toLowerCase();    
    return this.allModels.filter(element => element.toLowerCase().includes(filterValue));                    
  }

  private _filterYears( value: string ): string[] {
    const filterValue = value.toLowerCase();    
    return this.allYears.filter(element => element.toLowerCase().includes(filterValue));                    
  }

  private _filterStates( value: string ): string[] {
    const filterValue = value.toLowerCase();    
    return this.allStates.filter(element => element.toLowerCase().includes(filterValue));                    
  }

  private _filterTransmissions( value: string ): string[] {
    const filterValue = value.toLowerCase();    
    return this.allTransmissions.filter(element => element.toLowerCase().includes(filterValue));                    
  }

  private _filterExtColors( value: string ): string[] {
    const filterValue = value.toLowerCase();    
    return this.allExtColors.filter(element => element.toLowerCase().includes(filterValue));                    
  }

  private _filterIntColors( value: string ): string[] {
    const filterValue = value.toLowerCase();    
    return this.allIntColors.filter(element => element.toLowerCase().includes(filterValue));                    
  }

  private _filterTypes( value: string ): string[] {
    const filterValue = value.toLowerCase();    
    return this.allTypes.filter(element => element.toLowerCase().includes(filterValue));                    
  }

  /**
   * Number display label Hitch
   */
  formatLabelHitch( value: number ): string {   
    
    if (value >= 1) {
      return '$' + Math.round(value / 1000);
    }

    return  '$0';
  }

  public existsInArray( arreglo:any[], elemento:any ): boolean {   
      let exists = false;
      arreglo.find( element => {
        if( element == elemento ){
          exists = true;        
        }
      });   
      return exists;
  }
  

  public precio() {
    
    if (this.timer){
      clearTimeout(this.timer);
    }

    this.timer = setTimeout(() => {
      this.navigate();
    }, 300);
  }
  
  public searchByKeyword(){
    this.navigate();
  }
  
  public searchKeyboard(){    

    if (this.timer){
      clearTimeout(this.timer);
    }

    this.timer = setTimeout(() => {
      this.searchByKeyword();
    }, 700);
          
  }

  private paramToList(value: string | undefined, emptyToken: string): string[] {
    if (!value || value === emptyToken) {
      return [];
    }
    return value.split('-');
  }

  private applyRouteParams(params: Params): void {
    if (params['pagina'] == null) {
      this.executeSearch(this.pageIndex || 1);
      return;
    }

    const page = Number(params['pagina']);
    this.pageIndex = Number.isFinite(page) && page > 0 ? page : 1;

    this.categories = this.paramToList(params['categoria'], 'sin-categorias');
    this.brands = this.paramToList(params['marca'], 'sin-marcas');
    this.lines = this.paramToList(params['linea'], 'sin-lineas');
    this.models = this.paramToList(params['modelo'], 'sin-modelos');
    this.bodies = this.paramToList(params['carroceria'], 'sin-carrocerias');
    this.versions = this.paramToList(params['version'], 'sin-versiones');
    this.years = this.paramToList(params['anio'], 'sin-anios');
    this.states = this.paramToList(params['estado'], 'sin-estados');
    this.transmissions = this.paramToList(params['transmision'], 'sin-transmisiones');
    this.extColors = this.paramToList(params['exterior_color'], 'sin-colores');
    this.intColors = this.paramToList(params['interior_color'], 'sin-colores');

    if (params['minprecio']) {
      this.hitchMin = Number(params['minprecio']) + 500;
    }
    if (params['maxprecio']) {
      this.hitchMax = Number(params['maxprecio']) - 500;
    }

    if (params['busqueda'] && params['busqueda'] !== 'sin-busqueda') {
      this.palabra_busqueda = params['busqueda'];
    } else {
      this.palabra_busqueda = '';
    }

    if (params['order']) {
      this.orden = params['order'];
    }

    this.executeSearch(this.pageIndex);
  }

  public executeSearch( page:number ){

    const searchPage = page > 0 ? page : 1;
    const priceRange = [(this.hitchMin - 1), (this.hitchMax + 1)] as [number, number];

    this.spinner = true;
    this.searchSubscription?.unsubscribe();

    this.allCategories = [];
    this.allBrands = [];
    this.allModels = [];
    this.allVersions = [];
    this.allBodies = [];
    this.allYears = [];
    this.allTransmissions = [];
    this.allExtColors = [];
    this.allIntColors = [];
    this.allLines = [];

    this.searchSubscription = forkJoin({
      vehicles: this._compraTuAutoService.getVehicles(
        this.categories, this.brands, this.lines, this.models, this.bodies, this.versions, this.years,
        priceRange, this.palabra_busqueda, searchPage,
        this.states, this.transmissions,
        this.extColors, this.intColors, this.orden
      ),
      filters: this._compraTuAutoService.getFilters(
        this.categories, this.brands, this.lines, this.models, this.bodies, this.versions, this.years,
        priceRange, this.palabra_busqueda, searchPage, this.states, this.transmissions, this.extColors, this.intColors,
        true, this.orden
      ),
    }).subscribe({
      next: ({ vehicles, filters }) => {
        this.vehicles = vehicles.data.data;
        this.length = vehicles.data.total;
        this.pageIndex = vehicles.data.current_page;
        this.pageSize = vehicles.data.per_page;
        this.applyFiltersResponse(filters);
        this.spinner = false;
      },
      error: () => {
        this.spinner = false;
      },
    });
  }

  private applyFiltersResponse(response: FiltersResponse): void {
    (response.data.categories ?? []).filter(Boolean).forEach(category => {
      if (!this.existsInArray(this.categories, this.titleCase(category))) {
        this.allCategories.push(category == 'new' ? 'nuevo' : category == 'pre_owned' ? 'seminuevo' : category);
      }
    });
    this.filteredCategories = this.categoryCtrl.valueChanges.pipe(
      startWith(null),
      map((category: string | null) => category ? this._filterCategories(category) : this.allCategories.slice())
    );

    (response.data.brands ?? []).filter(Boolean).forEach(brand => {
      if (!this.existsInArray(this.brands, this.titleCase(brand))) {
        this.allBrands.push(brand);
      }
    });
    this.filteredBrands = this.brandCtrl.valueChanges.pipe(
      startWith(null),
      map((brand: string | null) => brand ? this._filterBrands(brand) : this.allBrands.slice())
    );

    (response.data.lines ?? []).filter(Boolean).forEach(line => {
      if (!this.existsInArray(this.lines, this.titleCase(line))) {
        this.allLines.push(line);
      }
    });
    this.filteredLines = this.lineCtrl.valueChanges.pipe(
      startWith(null),
      map((line: string | null) => line ? this._filterLines(line) : this.allLines.slice())
    );

    (response.data.versions ?? []).filter(Boolean).forEach(version => {
      if (!this.existsInArray(this.versions, this.titleCase(version))) {
        this.allVersions.push(version);
      }
    });
    this.filteredVersions = this.versionCtrl.valueChanges.pipe(
      startWith(null),
      map((version: string | null) => version ? this._filterVersions(version) : this.allVersions.slice())
    );

    (response.data.bodies ?? []).filter(Boolean).forEach(body => {
      if (!this.existsInArray(this.bodies, this.titleCase(body))) {
        this.allBodies.push(body);
      }
    });
    this.filteredBodies = this.bodyCtrl.valueChanges.pipe(
      startWith(null),
      map((body: string | null) => body ? this._filterBodies(body) : this.allBodies.slice())
    );

    (response.data.models ?? []).filter(Boolean).forEach(model => {
      if (!this.existsInArray(this.models, this.titleCase(model))) {
        this.allModels.push(model);
      }
    });
    this.filteredModels = this.modelCtrl.valueChanges.pipe(
      startWith(null),
      map((model: string | null) => model ? this._filterModels(model) : this.allModels.slice())
    );

    (response.data.years ?? []).filter((y): y is number => y != null).forEach(year => {
      if (!this.existsInArray(this.years, `${year}`)) {
        this.allYears.push(`${year}`);
      }
    });
    this.filteredYears = this.yearCtrl.valueChanges.pipe(
      startWith(null),
      map((year: string | null) => year ? this._filterYears(year) : this.allYears.slice())
    );

    (response.data.transmissions ?? []).filter(Boolean).forEach(transmission => {
      if (!this.existsInArray(this.transmissions, this.titleCase(transmission))) {
        this.allTransmissions.push(transmission);
      }
    });
    this.filteredTransmissions = this.transmissionCtrl.valueChanges.pipe(
      startWith(null),
      map((transmission: string | null) => transmission ? this._filterTransmissions(transmission) : this.allTransmissions.slice())
    );

    (response.data.exterior_colors ?? []).filter(Boolean).forEach(color => {
      if (!this.existsInArray(this.extColors, this.titleCase(color))) {
        this.allExtColors.push(color);
      }
    });
    this.filteredExtColors = this.extColorCtrl.valueChanges.pipe(
      startWith(null),
      map((extColor: string | null) => extColor ? this._filterExtColors(extColor) : this.allExtColors.slice())
    );

    (response.data.interior_colors ?? []).filter(Boolean).forEach(color => {
      if (!this.existsInArray(this.intColors, this.titleCase(color))) {
        this.allIntColors.push(color);
      }
    });
    this.filteredIntColors = this.intColorCtrl.valueChanges.pipe(
      startWith(null),
      map((intColor: string | null) => intColor ? this._filterIntColors(intColor) : this.allIntColors.slice())
    );

    (response.data.locations ?? []).filter(Boolean).forEach(location => {
      if (!this.existsInArray(this.states, this.titleCase(location))) {
        this.allStates.push(location);
      }
    });
    this.filteredStates = this.stateCtrl.valueChanges.pipe(
      startWith(null),
      map((state: string | null) => state ? this._filterStates(state) : this.allStates.slice())
    );
  }

  public paginationChange( pageEvent:PageEvent ){
    this.pageEvent = pageEvent;
    this.pageSize = this.pageEvent.pageSize;
    this.pageIndex = this.pageEvent.pageIndex + 1;
    this.scrollTop(); 
    
    this.navigate();
  }

  public cambiarOrden( orden: string ){
    this.orden = orden;
    
    this.navigate();
  }

  public clean(){   
    this.categories = []; 
    this.brands = [];
    this.lines = [];
    this.models = [];
    this.years = [];
    this.palabra_busqueda = '';    
    this.states = [];
    this.transmissions = [];
    this.types = [];
    this.orden = 'ninguno';

    this.navigate();
  }


  private navigate(){

    let categorias = this.categories.length > 0 ? this.categories.join('-') : 'sin-categorias';
    let marcas = this.brands.length > 0 ? this.brands.join('-') : 'sin-marcas';
    let lineas = this.lines.length > 0 ? this.lines.join('-') : 'sin-lineas';
    let modelos = this.models.length > 0 ? this.models.join('-') : 'sin-modelos';
    let carrocerias = this.bodies.length > 0 ? this.bodies.join('-') : 'sin-carrocerias';
    let versiones = this.versions.length > 0 ? this.versions.join('-') : 'sin-versiones';
    let anios = this.years.length > 0 ? this.years.join('-') : 'sin-anios';
    let busqueda = this.palabra_busqueda.length > 0 ? this.palabra_busqueda : 'sin-busqueda';    
    let estados = this.states.length > 0 ? this.states.join('-') : 'sin-estados';
    let transmisiones = this.transmissions.length > 0 ? this.transmissions.join('-') : 'sin-transmisiones';
    let extColors = this.extColors.length > 0 ? this.extColors.join('-') : 'sin-colores';    
    let intColors = this.intColors.length > 0 ? this.intColors.join('-') : 'sin-colores';
    
    this._router.navigate(['compra-tu-auto', categorias, marcas, lineas, modelos, carrocerias, versiones, anios, (this.hitchMin -500), (this.hitchMax +500), estados, busqueda, transmisiones, extColors, intColors, this.orden, this.pageIndex ]);

  }

}
