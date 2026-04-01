import { Component } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';

@Component({
  selector: 'app-chat-assistant',
  templateUrl: './chat-assistant.component.html',
  styleUrls: ['./chat-assistant.component.css'],
  standalone: false,
})
export class ChatAssistantComponent {
  open = false;
  message = '';
  sending = false;
  messages: { role: 'user' | 'assistant'; text: string }[] = [];

  private url = environment.baseUrl;

  constructor(private http: HttpClient) {}

  toggle(): void { this.open = !this.open; }

  send(): void {
    const text = this.message.trim();
    if (!text || this.sending) return;
    this.messages.push({ role: 'user', text });
    this.message = '';
    this.sending = true;

    const headers = new HttpHeaders({ 'Content-Type': 'application/json' });
    this.http.post<{ reply: string }>(`${this.url}/api/assistant/chat`, { message: text }, { headers }).subscribe({
      next: (res) => {
        this.messages.push({ role: 'assistant', text: res.reply });
        this.sending = false;
        setTimeout(() => this.scrollToBottom(), 50);
      },
      error: () => {
        this.messages.push({ role: 'assistant', text: 'Lo siento, hubo un error. Intenta de nuevo.' });
        this.sending = false;
      },
    });
  }

  private scrollToBottom(): void {
    const el = document.querySelector('.chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
  }
}
