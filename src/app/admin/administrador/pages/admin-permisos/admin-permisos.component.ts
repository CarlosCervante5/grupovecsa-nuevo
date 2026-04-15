import { Component, OnInit } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { HttpErrorResponse } from '@angular/common/http';
import { DevCrudService } from '../../../developer/services/dev-crud.service';

@Component({
  selector: 'app-admin-permisos',
  templateUrl: './admin-permisos.component.html',
  styleUrls: ['./admin-permisos.component.css'],
  standalone: false,
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

  constructor(
    private crud: DevCrudService,
    private snackBar: MatSnackBar,
  ) {}

  ngOnInit(): void {
    this.loadMatrix();
  }

  private toast(msg: string, isError = false): void {
    this.snackBar.open(msg, 'Cerrar', { duration: isError ? 8000 : 4000 });
  }

  private httpErrorMessage(err: unknown): string {
    if (err instanceof HttpErrorResponse) {
      const body = err.error;
      if (body?.message) {
        return typeof body.message === 'string' ? body.message : JSON.stringify(body.message);
      }
      if (body?.errors) {
        return JSON.stringify(body.errors);
      }
      return err.message || `Error HTTP ${err.status}`;
    }
    return 'Error de red';
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
            if (roleList.length === 0) {
              this.matrixLoading = false;
              return;
            }
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
                error: () => {
                  loaded++;
                  if (loaded === roleList.length) {
                    this.matrixLoading = false;
                  }
                },
              });
            });
          },
          error: () => {
            this.matrixError = 'Error al cargar roles';
            this.matrixLoading = false;
          },
        });
      },
      error: () => {
        this.matrixError = 'Error al cargar permisos';
        this.matrixLoading = false;
      },
    });
  }

  isActive(role: any, permName: string): boolean {
    return Array.isArray(role.permissions) && role.permissions.includes(permName);
  }

  /** Clave de UI alineada con el template (módulos admin). */
  moduleSavingKey(role: any, mod: string): string {
    return `${role.id}-access-${mod}`;
  }

  private applyRolePermissionsFromServer(role: any, detail: any): void {
    const d = detail?.data || detail;
    role.permissions = (d.permissions || []).map((p: any) => p.name);
  }

  /** Logs en consola para depurar guardado de permisos (filtrar por "AdminPermisos"). */
  private logPermiso(event: string, payload?: Record<string, unknown>): void {
    if (payload) {
      console.log(`[AdminPermisos] ${event}`, payload);
    } else {
      console.log(`[AdminPermisos] ${event}`);
    }
  }

  private refreshRoleFromApi(role: any, done: () => void): void {
    this.crud.fetch('roles/' + role.id, 'GET', {}).subscribe({
      next: (detail: any) => {
        this.applyRolePermissionsFromServer(role, detail);
        done();
      },
      error: () => {
        this.loadMatrix();
        done();
      },
    });
  }

  togglePermission(role: any, perm: any): void {
    const key = `${role.id}-${perm.id}`;
    this.matrixSaving[key] = true;
    const has = role.permissions.includes(perm.name);
    const newPerms = has
      ? role.permissions.filter((p: string) => p !== perm.name)
      : [...role.permissions, perm.name];
    this.logPermiso('Enviando PUT roles (permiso)', {
      roleId: role.id,
      roleName: role.name,
      permiso: perm.name,
      accion: has ? 'revocar' : 'otorgar',
      totalPermisosTrasGuardar: newPerms.length,
    });
    this.crud.put('roles/' + role.id, { permissions: newPerms }).subscribe({
      next: () => {
        this.refreshRoleFromApi(role, () => {
          this.matrixSaving[key] = false;
          this.logPermiso('Guardado OK (permiso)', {
            roleId: role.id,
            permiso: perm.name,
            permisosEnServidor: role.permissions?.length,
          });
          this.toast('Permiso actualizado');
        });
      },
      error: (err) => {
        this.matrixSaving[key] = false;
        this.logPermiso('Error al guardar permiso', {
          roleId: role.id,
          permiso: perm.name,
          error: this.httpErrorMessage(err),
        });
        console.error('[AdminPermisos] detalle HTTP', err);
        this.toast(this.httpErrorMessage(err), true);
        this.refreshRoleFromApi(role, () => {});
      },
    });
  }

  toggleModuleAccess(role: any, mod: string): void {
    const permName = 'access ' + mod;
    const saveKey = this.moduleSavingKey(role, mod);
    const existing = this.matrixPermissions.find((p: any) => p.name === permName);

    const runToggle = () => {
      this.matrixSaving[saveKey] = true;
      const has = role.permissions.includes(permName);
      const newPerms = has
        ? role.permissions.filter((p: string) => p !== permName)
        : [...role.permissions, permName];
      this.logPermiso('Enviando PUT roles (acceso módulo)', {
        roleId: role.id,
        roleName: role.name,
        modulo: mod,
        permiso: permName,
        accion: has ? 'revocar' : 'otorgar',
        totalPermisosTrasGuardar: newPerms.length,
      });
      this.crud.put('roles/' + role.id, { permissions: newPerms }).subscribe({
        next: () => {
          this.refreshRoleFromApi(role, () => {
            this.matrixSaving[saveKey] = false;
            this.logPermiso('Guardado OK (acceso módulo)', {
              roleId: role.id,
              modulo: mod,
              permiso: permName,
              permisosEnServidor: role.permissions?.length,
            });
            this.toast('Acceso a módulo actualizado');
          });
        },
        error: (err) => {
          this.matrixSaving[saveKey] = false;
          this.logPermiso('Error al guardar acceso módulo', {
            roleId: role.id,
            modulo: mod,
            error: this.httpErrorMessage(err),
          });
          console.error('[AdminPermisos] detalle HTTP', err);
          this.toast(this.httpErrorMessage(err), true);
          this.refreshRoleFromApi(role, () => {});
        },
      });
    };

    if (existing) {
      runToggle();
      return;
    }

    this.matrixSaving[saveKey] = true;
    this.logPermiso('Creando permiso en API (luego PUT rol)', { permiso: permName, roleId: role.id, modulo: mod });
    this.crud.store('permissions', { name: permName }).subscribe({
      next: (res: any) => {
        const newPerm = res?.data ?? res;
        if (newPerm?.id) {
          this.matrixPermissions.push(newPerm);
          this.matrixSaving[saveKey] = false;
          this.logPermiso('Permiso creado, aplicando toggle al rol', { permiso: permName, permissionId: newPerm.id });
          runToggle();
        } else {
          this.matrixSaving[saveKey] = false;
          this.toast('Respuesta inválida al crear permiso (sin id). Recarga la página.', true);
          this.loadMatrix();
        }
      },
      error: (err) => {
        this.matrixSaving[saveKey] = false;
        if (err instanceof HttpErrorResponse && err.status === 422) {
          this.logPermiso('POST permiso 422 (probable duplicado), recargando lista y reintentando', { permiso: permName });
          this.crud.fetch('permissions', 'GET', {}).subscribe({
            next: (perms: any) => {
              this.matrixPermissions = Array.isArray(perms) ? perms : (perms?.data || []);
              const found = this.matrixPermissions.find((p: any) => p.name === permName);
              if (found) {
                this.logPermiso('Permiso ya existía, ejecutando PUT rol', { permiso: permName, roleId: role.id });
                runToggle();
              } else {
                this.toast(this.httpErrorMessage(err), true);
              }
            },
            error: () => this.toast(this.httpErrorMessage(err), true),
          });
          return;
        }
        this.toast(this.httpErrorMessage(err), true);
      },
    });
  }
}
