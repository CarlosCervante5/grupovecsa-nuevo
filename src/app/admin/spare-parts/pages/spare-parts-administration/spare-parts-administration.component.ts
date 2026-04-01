import { Component, OnInit } from '@angular/core';
import { SparePartsService } from '@services/spare-parts.service';
import { GetSearchParts, Datum } from '@interfaces/getSearchParts.interfaces';

@Component({
  selector: 'app-spare-parts-administration',
  templateUrl: './spare-parts-administration.component.html',
  styleUrls: ['./spare-parts-administration.component.css'],
  standalone: false
})
export class SparePartsAdministrationComponent implements OnInit {
  items: any[] = [];
  loading = true;
  length = 0;
  pageIndex = 1;
  pageSize = 10;
  searchTerm = '';
  private searchTimeout?: ReturnType<typeof setTimeout>;

  constructor(private _sparePartsService: SparePartsService) {}

  ngOnInit(): void {
    this.loadData(this.pageIndex);
  }

  get totalPages(): number {
    return Math.ceil(this.length / this.pageSize) || 1;
  }

  loadData(page: number): void {
    this.loading = true;
    this._sparePartsService.getSearchParts(page).subscribe({
      next: (resp: GetSearchParts) => {
        this.items = resp.data.data;
        this.length = resp.data.total;
        this.pageIndex = resp.data.current_page;
        this.loading = false;
      },
      error: () => {
        this.items = [];
        this.loading = false;
      }
    });
  }

  onSearch(): void {
    if (this.searchTimeout) clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => {
      this.pageIndex = 1;
      this.loadData(this.pageIndex);
    }, 400);
  }

  clearSearch(): void {
    this.searchTerm = '';
    this.pageIndex = 1;
    this.loadData(this.pageIndex);
  }

  goToPage(page: number): void {
    this.pageIndex = page;
    this.loadData(page);
  }
}
