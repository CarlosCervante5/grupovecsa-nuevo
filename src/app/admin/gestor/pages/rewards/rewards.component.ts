import { Component } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { MatTableDataSource } from '@angular/material/table';
import { AdminService } from '@services/admin.service';
import { AddRewardrComponent } from '../../components/add-rewardr/add-rewardr.component';
import { DataReward, detailsRewardResponse, rewardsResponse, rewardTable } from '@interfaces/admin.interfaces';
import { UpdateRewardsComponent } from '../../components/update-rewards/update-rewards.component';

@Component({
    selector: 'app-rewards',
    templateUrl: './rewards.component.html',
    styleUrls: ['./rewards.component.css'],
    standalone: false
})
export class RewardsComponent {
  public reward !: DataReward;
  public uuids: rewardTable[] = [];
  constructor(
    private _riderservice: AdminService,
    private _bottomSheet: MatBottomSheet
  ){
    this.getRewards();
  }

  public data = [{
    'id': 1,
    'name': 'Riders',
    'fecha_i': '2024-09-01',
    'fecha_f': '2024-09-30',
    'type': 'motos'
  }];

  dataSource = new MatTableDataSource();
  displayedColumns: string[] = ['id', 'name','fecha_i', 'fecha_f','actions'];

  public newRewards(){
    const bottomSheetRef = this._bottomSheet.open(AddRewardrComponent);   
    bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {      
      if(dataFromChild != undefined && dataFromChild.reload === true ){        
     
      }      
      this.getRewards();
    });
  }

  getRewards(){
    this._riderservice.getRewards()
    .subscribe({
      next: (response: rewardsResponse) => {

        this.reward = response.data;
        const datosR = this.reward.rewards.map((reward, index) => ({
          uuid: reward.uuid,
          id: index +1,
          name: reward.name,
          description: reward.description,
          begin_date: this.formatDate(reward.begin_date),
          end_date: this.formatDate(reward.end_date),
          // type: reward.type
        }));
          this.uuids = datosR;
          this.dataSource.data = this.uuids;
    },
    error: () => {

    }
    })
  }

  public formatDate(dateString: any): string {
    const date = new Date(dateString  + 'T00:00:00Z');
    const day = (String(date.getUTCDate()).padStart(2, '0'));
    const month = String(date.getUTCMonth()+1).padStart(2, '0'); // Los meses empiezan en 0
    const year = date.getUTCFullYear();
    return `${day}-${month}-${year}`;
  }

  public updateReward( uuid: string){
    const bottomSheetRef = this._bottomSheet.open(UpdateRewardsComponent, {
      data: {
        uuid: uuid
      }
    });  
    bottomSheetRef.afterDismissed().subscribe((dataFromChild) => {      
      if(dataFromChild != undefined && dataFromChild.reload === true ){        
        
      }      
      this.getRewards();
    }); 
  }

}
