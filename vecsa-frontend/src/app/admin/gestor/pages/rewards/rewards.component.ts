import { Component } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { AdminService } from '@services/admin.service';
import { AddRewardrComponent } from '../../components/add-rewardr/add-rewardr.component';
import { DataReward, rewardsResponse, rewardTable } from '@interfaces/admin.interfaces';
import { UpdateRewardsComponent } from '../../components/update-rewards/update-rewards.component';

@Component({
    selector: 'app-rewards',
    templateUrl: './rewards.component.html',
    styleUrls: ['./rewards.component.css'],
    standalone: false
})
export class RewardsComponent {
  public reward!: DataReward;
  public uuids: rewardTable[] = [];
  public filtered: rewardTable[] = [];
  public searchTerm = '';
  public loading = true;

  constructor(
    private _riderservice: AdminService,
    private _bottomSheet: MatBottomSheet
  ) {
    this.getRewards();
  }

  public newRewards(): void {
    const bottomSheetRef = this._bottomSheet.open(AddRewardrComponent);
    bottomSheetRef.afterDismissed().subscribe(() => {
      this.getRewards();
    });
  }

  getRewards(): void {
    this.loading = true;
    this._riderservice.getRewards().subscribe({
      next: (response: rewardsResponse) => {
        this.reward = response.data;
        this.uuids = this.reward.rewards.map((reward, index) => ({
          uuid: reward.uuid,
          id: index + 1,
          name: reward.name,
          description: reward.description,
          begin_date: this.formatDate(reward.begin_date),
          end_date: this.formatDate(reward.end_date),
        }));
        this.applyFilter();
        this.loading = false;
      },
      error: () => {
        this.uuids = [];
        this.filtered = [];
        this.loading = false;
      }
    });
  }

  onSearch(): void {
    this.applyFilter();
  }

  clearSearch(): void {
    this.searchTerm = '';
    this.applyFilter();
  }

  private applyFilter(): void {
    const q = this.searchTerm.trim().toLowerCase();
    if (!q) {
      this.filtered = [...this.uuids];
      return;
    }
    this.filtered = this.uuids.filter(
      (r) =>
        (r.name || '').toLowerCase().includes(q) ||
        (r.description || '').toLowerCase().includes(q) ||
        (r.begin_date || '').toLowerCase().includes(q) ||
        (r.end_date || '').toLowerCase().includes(q)
    );
  }

  public formatDate(value: Date | string): string {
    const dateString =
      value instanceof Date ? value.toISOString().slice(0, 10) : String(value).slice(0, 10);
    const date = new Date(dateString + 'T00:00:00Z');
    const day = String(date.getUTCDate()).padStart(2, '0');
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const year = date.getUTCFullYear();
    return `${day}-${month}-${year}`;
  }

  public updateReward(uuid: string): void {
    const bottomSheetRef = this._bottomSheet.open(UpdateRewardsComponent, {
      data: { uuid }
    });
    bottomSheetRef.afterDismissed().subscribe(() => {
      this.getRewards();
    });
  }
}
