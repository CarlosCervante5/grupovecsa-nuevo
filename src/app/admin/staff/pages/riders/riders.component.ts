import { Component, Output, EventEmitter, OnInit } from '@angular/core';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { CustomerResponse, CustomerSale, tableCustomersData } from '@interfaces/admin.interfaces';
import { AdminService } from '@services/admin.service';
import { NewRiderkmComponent } from '../../components/new-riderkm/new-riderkm.component';

@Component({
  selector: 'app-riders',
  templateUrl: './riders.component.html',
  styleUrls: ['./riders.component.css'],
  standalone: false
})
export class RidersComponent implements OnInit {
  public riders: CustomerSale[] = [];
  public uuids: tableCustomersData[] = [];
  public page = 1;
  public keyword = '';
  public paginate = 15;
  public pageIndex = 1;
  public length = 0;
  private debounce_timer: any;

  @Output() reload = new EventEmitter<boolean>();

  constructor(
    private _bottomSheet: MatBottomSheet,
    private _riderservice: AdminService
  ) {}

  ngOnInit(): void {
    this.getRiders(this.page, this.keyword, this.paginate);
  }

  get totalPages(): number {
    return Math.ceil(this.length / this.paginate) || 1;
  }

  getRiders(page: number, keyword: string, paginate: number): void {
    this._riderservice.getCustomersRewards(page, keyword, paginate).subscribe({
      next: (response: CustomerResponse) => {
        this.riders = response.data.clientes.data;
        this.length = response.data.clientes.total;
        this.pageIndex = response.data.clientes.current_page;
        this.uuids = this.riders.map((rider, index) => ({
          id: ((this.pageIndex - 1) * this.paginate) + (index + 1),
          name: rider.customer_name + ' ' + rider.customer_last_name,
          puntos: this.floor(rider.total_points),
          customer_uuid: rider.customer_uuid,
          email: rider.customer_email,
          telefono: rider.customer_phone,
          register_date: rider.register_date,
          color: '',
        }));
      },
    });
  }

  onSearch(): void {
    clearTimeout(this.debounce_timer);
    this.debounce_timer = setTimeout(() => {
      this.page = 1;
      this.getRiders(this.page, this.keyword, this.paginate);
    }, 400);
  }

  clearSearch(): void {
    this.keyword = '';
    this.page = 1;
    this.getRiders(this.page, this.keyword, this.paginate);
  }

  goToPage(page: number): void {
    this.page = page;
    this.getRiders(this.page, this.keyword, this.paginate);
  }

  updatePoints(customer_uuid: string): void {
    const ref = this._bottomSheet.open(NewRiderkmComponent, { data: { customer_uuid } });
    ref.afterDismissed().subscribe((data) => {
      if (data?.reload) {
        this.reload.emit(true);
        this.getRiders(this.page, this.keyword, this.paginate);
      }
    });
  }

  floor(value: number): number {
    return Math.floor(value);
  }
}
