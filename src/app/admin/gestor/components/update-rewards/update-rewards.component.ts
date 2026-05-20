import { Component, Inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { detailsRewardResponse } from '@interfaces/admin.interfaces';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';
import { reload } from '@helpers/session.helper';
import { Router } from '@angular/router';

@Component({
    selector: 'app-update-rewards',
    templateUrl: './update-rewards.component.html',
    styleUrls: ['./update-rewards.component.css'],
    standalone: false
})
export class UpdateRewardsComponent {
  form!: FormGroup;
  public uuid!: string;

  constructor(
    private fb: FormBuilder,
    private _bottomSheetRef: MatBottomSheetRef<UpdateRewardsComponent>,
    private _eventsService: AdminService,
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: { uuid: string },
    private _router: Router
  ) {
    this.uuid = data.uuid;
    this.createForm();
    this.getReward();
  }

  createForm(): void {
    this.form = this.fb.group({
      name: ['', Validators.required],
      description: ['', Validators.required],
      begin_date: ['', Validators.required],
      end_date: ['', Validators.required],
    });
  }

  close(): void {
    this._bottomSheetRef.dismiss();
  }

  getReward(): void {
    this._eventsService.detailReward(this.uuid).subscribe({
      next: (response: detailsRewardResponse) => {
        const d = response.data;
        this.form.patchValue({
          name: d.name,
          description: d.description,
          begin_date: this.toDateInputValue(d.begin_date),
          end_date: this.toDateInputValue(d.end_date),
        });
      },
      error: (error) => {
        reload(error, this._router);
      },
    });
  }

  private toDateInputValue(value: Date | string): string {
    if (value instanceof Date) {
      return value.toISOString().slice(0, 10);
    }
    const s = String(value);
    return s.length >= 10 ? s.slice(0, 10) : s;
  }

  onSubmit(): void {
    this._eventsService
      .updateRewards(
        this.uuid,
        this.form.get('name')?.value,
        this.form.get('description')?.value,
        this.form.get('begin_date')?.value,
        this.form.get('end_date')?.value,
        this.form.get('type')?.value
      )
      .subscribe({
        next: () => {
          Swal.fire({
            icon: 'success',
            title: 'Recompensa actualizada',
            text: 'Los cambios se guardaron correctamente.',
            showConfirmButton: true,
            confirmButtonColor: '#008bcc',
            timer: 3500,
          });
          this._bottomSheetRef.dismiss();
        },
        error: (err) => {
          reload(err, this._router);
          this._bottomSheetRef.dismiss();
        },
      });
  }
}
