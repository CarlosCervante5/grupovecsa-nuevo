
import { Router } from '@angular/router';
import Swal from 'sweetalert2';

export function reload(error:any, router: Router){
    if(error.status == 401){
        router.navigate(['/auth/iniciar-sesion']);
        Swal.fire('Su sesión a expirado, para continuar inicie sesión.');
    }else{
        Swal.fire({
                icon: 'error',
                title: 'Oupps..',
                text: 'Al parecer ocurrio un error' + error.error.message,
                showConfirmButton: true,
                confirmButtonColor: '#EEB838',
                timer: 3500
        });
    }
}