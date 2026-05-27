import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatPaginatorModule } from '@angular/material/paginator';
import { AngularMaterialModule } from '../../../angular-material/angular-material.module';
import { AssistantChatsComponent } from '../../marketing/pages/assistant-chats/assistant-chats.component';

@NgModule({
  declarations: [AssistantChatsComponent],
  imports: [CommonModule, FormsModule, MatPaginatorModule, AngularMaterialModule],
  exports: [AssistantChatsComponent],
})
export class AssistantChatsSharedModule {}
