import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { BenchmarkComponent } from './benchmark.component';

@NgModule({
  declarations: [BenchmarkComponent],
  imports: [CommonModule, FormsModule],
  exports: [BenchmarkComponent],
})
export class BenchmarkModule {}
