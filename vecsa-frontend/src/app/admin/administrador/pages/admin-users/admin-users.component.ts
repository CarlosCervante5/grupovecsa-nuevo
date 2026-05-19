import { Component, Output, EventEmitter } from '@angular/core';
import { PageEvent } from '@angular/material/paginator';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { AdminService } from '@services/admin.service';
import { Datum, UsersResponse, userTable } from '@interfaces/admin.interfaces';
import { AddUserComponent } from '../../components/add-user/add-user.component';
import { UpdateUserComponent } from '../../components/update-user/update-user.component';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-admin-users',
  templateUrl: './admin-users.component.html',
  styleUrls: ['./admin-users.component.css'],
  standalone: false
})
export class AdminUsersComponent {
  public users: Datum[] = [];
  public uuids: userTable[] = [];
  public paginate = 15;
  public page = 1;
  public pageIndex = 1;
  public length = 0;
  public searchTerm = '';
  private searchTimeout?: ReturnType<typeof setTimeout>;

  @Output() reload = new EventEmitter<boolean>();

  constructor(
    private _adminservice: AdminService,
    private _bottomSheet: MatBottomSheet
  ) {
    this.getUsers(this.page);
  }

  get totalPages(): number {
    return Math.ceil(this.length / this.paginate) || 1;
  }

  getUsers(page: number): void {
    const keyword = this.searchTerm.trim();
    this._adminservice.getUsers(page, keyword || undefined, this.paginate).subscribe({
      next: (response: UsersResponse) => {
        this.users = response.data.data;
        this.length = response.data.total;
        this.pageIndex = response.data.current_page;
        this.uuids = this.users.map((user, index) => ({
          fecha: user.created_at,
          email: user.email,
          nickname: user.nickname,
          index: ((this.pageIndex - 1) * this.paginate) + (index + 1),
          color: '',
          uuid: user.uuid,
          name: user.profile?.name,
          last_name: user.profile?.last_name,
          rol: user.role,
          location: user.profile?.location,
          picture: user.profile?.picture,
        }));
      }
    });
  }

  onSearch(): void {
    if (this.searchTimeout) clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
      this.page = 1;
      this.getUsers(this.page);
    }, 400);
  }

  clearSearch(): void {
    this.searchTerm = '';
    this.page = 1;
    this.getUsers(this.page);
  }

  goToPage(page: number): void {
    this.page = page;
    this.getUsers(this.page);
  }

  addUser(): void {
    const ref = this._bottomSheet.open(AddUserComponent);
    ref.afterDismissed().subscribe((data) => {
      if (data?.reload) {
        this.reload.emit(true);
        this.getUsers(this.page);
      }
    });
  }

  updateUser(uuid: string): void {
    const ref = this._bottomSheet.open(UpdateUserComponent, { data: { uuid } });
    ref.afterDismissed().subscribe((data) => {
      if (data?.reload) {
        this.reload.emit(true);
        this.getUsers(this.page);
      }
    });
  }

  deleteUser(uuid: string): void {
    Swal.fire({
      title: '¿Eliminar este usuario?',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#008bcc',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        this._adminservice.deleteUser(uuid).subscribe((resp) => {
          Swal.fire(resp.message, '', 'success');
          this.reload.emit(true);
          this.getUsers(this.page);
        });
      }
    });
  }
}
