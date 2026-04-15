import { Component, OnInit } from '@angular/core';
import { DevCrudService } from '../../../developer/services/dev-crud.service';

@Component({
  selector: 'app-admin-permisos',
  templateUrl: './admin-permisos.component.html',
  styleUrls: ['./admin-permisos.component.css'],
  standalone: false
})
export class AdminPermisosComponent implements OnInit {
  matrixRoles: any[] = [];
  matrixPermissions: any[] = [];
  matrixLoading = true;
  matrixError = '';
  matrixSaving: Record<string, boolean> = {};

  adminModules = [
    'benchmark', 'store_management', 'marketing', 'administrator',
    'developer', 'staff', 'gestor', 'manager', 'valuator', 'receptionist',
    'appointment_manager', 'technician', 'bodywork_paint_technician', 'spare_parts',
    'gerente', 'seller', 'strega-seller', 'strega-manager', 'strega-administrator',
  ];

  constructor(private crud: DevCrudService) {}

  ngOnInit(): void {
    this.loadMatrix();
  }

  loadMatrix(): void {
    this.matrixLoading = true;
    this.matrixError = '';
    this.crud.fetch('permissions', 'GET', {}).subscribe({
      next: (perms: any) => {
        this.matrixPermissions = Array.isArray(perms) ? perms : (perms?.data || []);
        this.crud.fetch('roles', 'GET', {}).subscribe({
          next: (roles: any) => {
            const roleList = Array.isArray(roles) ? roles : (roles?.data || []);
            let loaded = 0;
            this.matrixRoles = [];
            if (roleList.length === 0) { this.matrixLoading = false; return; }
            roleList.forEach((r: any) => {
              this.crud.fetch('roles/' + r.id, 'GET', {}).subscribe({
                next: (detail: any) => {
                  const d = detail?.data || detail;
                  this.matrixRoles.push({
                    id: d.id,
                    name: d.name,
                    permissions: (d.permissions || []).map((p: any) => p.name),
                  });
                  loaded++;
                  if (loaded === roleList.length) {
                    this.matrixRoles.sort((a: any, b: any) => a.id - b.id);
                    this.matrixLoading = false;
                  }
                },
                error: () => { loaded++; if (loaded === roleList.length) this.matrixLoading = false; },
              });
            });
          },
          error: () => { this.matrixError = 'Error al cargar roles'; this.matrixLoading = false; },
        });
      },
      error: () => { this.matrixError = 'Error al cargar permisos'; this.matrixLoading = false; },
    });
  }

  isActive(role: any, permName: string): boolean {
    return Array.isArray(role.permissions) && role.permissions.includes(permName);
  }

  togglePermission(role: any, perm: any): void {
    const key = role.id + '-' + perm.id;
    this.matrixSaving[key] = true;
    const has = role.permissions.includes(perm.name);
    const newPerms = has
      ? role.permissions.filter((p: string) => p !== perm.name)
      : [...role.permissions, perm.name];
    this.crud.put('roles/' + role.id, { permissions: newPerms }).subscribe({
      next: () => { role.permissions = newPerms; this.matrixSaving[key] = false; },
      error: () => { this.matrixSaving[key] = false; },
    });
  }

  toggleModuleAccess(role: any, mod: string): void {
    const permName = 'access ' + mod;
    const existing = this.matrixPermissions.find((p: any) => p.name === permName);
    if (existing) {
      this.togglePermission(role, existing);
    } else {
      this.crud.store('permissions', { name: permName }).subscribe({
        next: (res: any) => {
          const newPerm = res?.data || res;
          if (newPerm?.id) {
            this.matrixPermissions.push(newPerm);
            this.togglePermission(role, newPerm);
          }
        },
      });
    }
  }
}
