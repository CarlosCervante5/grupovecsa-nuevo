import { Injectable } from '@angular/core';

/** Sonido corto al recibir mensajes nuevos (Web Audio, sin archivo externo). */
@Injectable({ providedIn: 'root' })
export class ChatNotificationSoundService {
  private audioContext: AudioContext | null = null;
  private lastPlayedAt = 0;
  private readonly minIntervalMs = 2500;
  private readonly volume = 0.12;

  /** Desbloquea audio tras gesto del usuario (requerido en móvil). */
  unlock(): void {
    if (typeof window === 'undefined') {
      return;
    }
    try {
      if (!this.audioContext) {
        const Ctx = window.AudioContext
          || (window as unknown as { webkitAudioContext?: typeof AudioContext })
            .webkitAudioContext;
        if (!Ctx) {
          return;
        }
        this.audioContext = new Ctx();
      }
      if (this.audioContext.state === 'suspended') {
        void this.audioContext.resume();
      }
    } catch {
      /* ignore */
    }
  }

  playNewMessage(): void {
    if (typeof window === 'undefined') {
      return;
    }
    const now = Date.now();
    if (now - this.lastPlayedAt < this.minIntervalMs) {
      return;
    }
    this.lastPlayedAt = now;

    try {
      this.unlock();
      const ctx = this.audioContext;
      if (!ctx) {
        return;
      }
      const t0 = ctx.currentTime;
      this.playTone(ctx, 880, t0, 0.1);
      this.playTone(ctx, 1175, t0 + 0.12, 0.14);
    } catch {
      /* ignore */
    }
  }

  private playTone(
    ctx: AudioContext,
    frequency: number,
    start: number,
    duration: number
  ): void {
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.value = frequency;
    osc.connect(gain);
    gain.connect(ctx.destination);
    gain.gain.setValueAtTime(0.0001, start);
    gain.gain.exponentialRampToValueAtTime(this.volume, start + 0.015);
    gain.gain.exponentialRampToValueAtTime(0.0001, start + duration);
    osc.start(start);
    osc.stop(start + duration + 0.02);
  }
}
