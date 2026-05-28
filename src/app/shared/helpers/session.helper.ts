
import { Router } from '@angular/router';
import Swal from 'sweetalert2';

export function reload(error: unknown, router: Router): void {
    const status =
        error && typeof error === 'object' && 'status' in error
            ? Number((error as { status: number }).status)
            : 0;
    if (status === 401) {
        void router.navigate(['/auth/iniciar-sesion']);
        void Swal.fire('Su sesión a expirado, para continuar inicie sesión.');
        return;
    }
    const errObj = error as {
        error?: { message?: string; data?: { detail?: string } };
        message?: string;
    };
    const apiDetail = errObj?.error?.data?.detail?.trim() ?? '';
    const detail =
        errObj?.error?.message ??
        (typeof errObj?.message === 'string' ? errObj.message : '') ??
        '';
    const text =
        apiDetail && apiDetail !== detail
            ? `${detail} (${apiDetail})`
            : detail || apiDetail;
    void Swal.fire({
        icon: 'error',
        title: 'Oupps..',
        text: 'Al parecer ocurrio un error' + (text ? ': ' + text : '.'),
        showConfirmButton: true,
        confirmButtonColor: '#EEB838',
        timer: 3500,
    });
}