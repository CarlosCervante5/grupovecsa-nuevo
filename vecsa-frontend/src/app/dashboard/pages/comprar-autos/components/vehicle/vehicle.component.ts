import { Component, OnInit, Input } from '@angular/core';

// Interfaces 
import { environment } from '@environments/environment';

// Services
import { Vehicle } from '@interfaces/vehicle_data.interface';
import { DetailService } from '../../services/detail/detail.service';

@Component({
    selector: 'c-vender-autos-vehicle',
    templateUrl: './vehicle.component.html',
    styleUrls: ['./vehicle.component.css'],
    standalone: false
})

export class VehicleComponent implements OnInit {
  
  public vehicle_image = ''; 
  public choice: boolean = false;
  public baseUrl: string = environment.baseUrl;
  
  @Input() vehicle!: Vehicle; 

  constructor(private _detailService: DetailService) { }

  ngOnInit(): void {  

    this.vehicle_image = this.vehicle.first_image?.service_image_url;
  }

  /**
   * Checking exists choice   
   */
  // public reservedVehicle() {
  //   if (this.vehicle.choices) {
  //     this.choice = (Object.entries(this.vehicle.choices).length === 0) ? false : true;    
  //   }
  // }

  /**
   * Find vehicle in choice 
   */
  // public choiceVehicle(vin: string) {
  //   this._detailService.getChoiceByVin(vin)
  //   .subscribe({
  //     next: (response) => {        
  //       if (response.choice) {
  //         this.choice = true;
  //       } else {
  //         this.choice = false;
  //       }
  //     }
  //   });
  // }

}