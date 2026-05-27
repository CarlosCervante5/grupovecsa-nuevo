import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../../auth/services/auth.service';
import { RewardsService } from '@services/rewards.service';
import { CustomerPoints } from '@interfaces/rewards.interface';

@Component({
  selector: 'app-rewards-home',
  templateUrl: './rewards-home.component.html',
  styleUrls: ['./rewards-home.component.css'],
  standalone: false
})
export class RewardsHomeComponent implements OnInit {

  loginForm: FormGroup;
  isLoggedIn = false;
  isLoading = false;
  loginError = '';
  totalPoints = 0;
  showPassword = false;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private rewardsService: RewardsService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', Validators.required]
    });
  }

  ngOnInit(): void {
    const token = localStorage.getItem('user_token');
    if (token) {
      this.isLoggedIn = true;
      this.loadPoints();
    }
  }

  loadPoints(): void {
    const userData = this.authService.getUserFromStorage();
    if (userData?.uuid) {
      this.rewardsService.customerPoints(userData.uuid).subscribe({
        next: (res) => {
          this.totalPoints = res.data?.total_earned_points ?? 0;
        },
        error: () => {}
      });
    }
  }

  onLogin(): void {
    if (this.loginForm.invalid) return;
    this.isLoading = true;
    this.loginError = '';

    this.authService.login(this.loginForm).subscribe({
      next: () => {
        this.isLoggedIn = true;
        this.isLoading = false;
        this.loadPoints();
      },
      error: () => {
        this.loginError = 'Correo o contraseña incorrectos.';
        this.isLoading = false;
      }
    });
  }

  goToRegister(): void {
    this.router.navigate(['/auth/register']);
  }
}
