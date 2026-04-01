import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Routes } from '@angular/router';
import { BenchmarkComponent } from './benchmark.component';

const routes: Routes = [
  { path: '', component: BenchmarkComponent },
];

@NgModule({
  declarations: [BenchmarkComponent],
  imports: [CommonModule, FormsModule, RouterModule.forChild(routes)],
})
export class BenchmarkModule {}
