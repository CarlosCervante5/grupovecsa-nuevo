import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { Component, ViewChild, ElementRef, Inject } from '@angular/core';
import Swal from 'sweetalert2';

import { AccountService } from '@services/account.service';
import {reload} from '../../../shared/helpers/session.helper';
import { Router } from '@angular/router';

@Component({
    selector: 'app-avatars-profile',
    templateUrl: './avatars-profile.component.html',
    styleUrls: ['./avatars-profile.component.css'],
    standalone: false
})

export class AvatarsProfileComponent {

  @ViewChild(' videoElement', { static: false }) videoElement: ElementRef | undefined;
  public capturedImage: string | undefined;  // Imagen capturada
  public mensaje: string ='';
  public cameraActive: boolean = false;
  public selectedAvatar: string | undefined;
  public iconSeleccionado: string = '';
  public iconButton: boolean = false;
  public selectedIcon: string | null = null;
  public selectedCategory: string = 'todos';
  public iconos: { src: string, categoria: string }[] = [
   { src: "assets/iconos_avatares/128ti.png", categoria: 'autos' },
   { src: "assets/iconos_avatares/ i5 M60 xDrive.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ i7.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ R 18 Classic. CU1.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/ Serie 2 Coupé. CU1.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ SERIE 2 GRAN COUPÉ CU1.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ SERIE 2 GRAN COUPÉ CU2.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/G 310 R CU2.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/ SERIE 2 GRAN COUPÉ CU3.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ SERIE 2 GRAN COUPÉ.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ X2 CU1.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ X2 CU2.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ X2 CU3.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ X2 CU4.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ X2 CU5.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/G 310 R CU3.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/ X2 M35i xDrive CU1.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ X2 M35i xDrive CU2.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ X2 M35i xDrive CU3.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ X3 CU1.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ X6 M60i xDRIVE.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/ Z4 M40i ROADSTER CU1.png", categoria: 'autos'},
   { src: "assets/iconos_avatares/C 400 GT.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/G 310 R.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/C 400 X CU1.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/C 400 X.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/CE 02.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/CE 04 CU1.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/CE 04 CU2.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/CE 04.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/F 800 GS.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/i4 .png", categoria: 'autos'},
   { src: "assets/iconos_avatares/F 900 GS Adventure.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/F 900 GS.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/F 900 R CU1.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/F 900 R.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/F 900 XR.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/G 310 GS.png", categoria: ''},
   { src: "assets/iconos_avatares/G 310 R CU1.png", categoria: 'motos'},
   { src: "assets/iconos_avatares/1.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/2.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/3.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/4.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/5.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/6.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/7.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/8.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/9.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/10.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/11.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/12.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/13.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/14.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/15.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/16.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/19.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/20.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/27.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/28.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/33.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/34.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/35.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/36.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/41.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/50.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/51.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/58.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/74.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/98.jpg", categoria: 'motos'},
   { src: "assets/iconos_avatares/99.jpg", categoria: 'motos'},
  ];
  public filteredIcons: { src: string, categoria: string } [] = this.iconos;

  constructor(
    public dialogRef: MatDialogRef<AvatarsProfileComponent>, 
    @Inject(MAT_DIALOG_DATA) public data: any,
    private _accountService: AccountService,
    private _router: Router,
  ) { }

  openCamera() {
    this.mensaje = 'La foto se tomará en 4 segundos...';
    this.cameraActive = true;
    const videoElement = document.createElement('video');
    const canvasElement = document.createElement('canvas');
    const constraints = {
      video: { facingMode: 'user' }
    };

    navigator.mediaDevices.getUserMedia(constraints)
    .then((stream) => {
      if (this.videoElement) {
        const video = this.videoElement.nativeElement as HTMLVideoElement;
        video.srcObject = stream;
        video.play();  // Iniciar la reproducción del video en la previsualización
      }

      // Capturar la imagen después de 4 segundos
      setTimeout(() => {

        const video = this.videoElement?.nativeElement as HTMLVideoElement;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');
        if (context) {
          context.drawImage(video, 0, 0, canvas.width, canvas.height);
          this.capturedImage = canvas.toDataURL('image/png');  // Guardar la imagen capturada
        }
        // Detener el stream de video
        const stream = video.srcObject as MediaStream;
        stream.getTracks().forEach(track => track.stop());

        this.cameraActive = false;  // Ocultar la previsualización después de capturar
      }, 4000);
    })
    .catch((error) => {
      console.error('Error al acceder a la cámara: ', error);
    });
}

  // Confirmar la foto y cerrarla
  confirmPhoto() {
    if (this.capturedImage) {
      this.enviarFoto(this.capturedImage);
      this.dialogRef.close(this.capturedImage);
    }
  }

  // Volver a tomar la foto
  retakePhoto() {
    this.capturedImage = undefined; // Limpiar la imagen capturada
    this.openCamera(); // Volver a abrir la cámara
  }

  // Cancelar la foto y cerrar el modal
  cancelPhoto() {
    this.capturedImage = undefined;
    this.dialogRef.close(); 
  }

  // Función para cerrar el modal
  onClose(): void {
    this.dialogRef.close();
  }


  // Funcion para selecionar icono
  setIcon(icon: string) {
    // Limpiar elementos para cargar correctamente el icon
    this.selectedIcon = icon;
    this.iconButton = false;
    this.iconSeleccionado = '';
    
    if (icon) {
      this.iconButton = true;
      this.iconSeleccionado = icon;
    }
  }

  // Función para filtrar los iconos según la categoría seleccionada
  filtrarIconos(categoria: string) {
    if (categoria === 'todos') {
      this.filteredIcons = this.iconos;
    } else {
      this.filteredIcons = this.iconos.filter(icon => icon.categoria === categoria);
    }
  }

  enviarIcon(): void {
    // Validar si se selecciono algun icono
    if (this.iconButton) {
      // Validar que imagen se envia, si el icono o la imagen tomada
      this.enviarFoto(this.iconSeleccionado);
      this.dialogRef.close(this.iconSeleccionado);
    }
  }

  public enviarFoto(image: string) {
    const user = JSON.parse(localStorage.getItem('user')!);
    let file: any;

    if (this.data.page === 'edit') {
      if (image.startsWith('data:image')) {
        file = this.base64ToFile(image, 'image.png');
        this.procesarEnvioDeFoto(file, user.uuid);
      } else {
        this.urlToFile(image, 'icon.png').then((file) => {
          this.procesarEnvioDeFoto(file, user.uuid);
        }).catch(error => {
          console.error('Error al convertir URL a archivo:', error);
        });
      }
    }
  }

  // Función separada para procesar el envío de la foto
  private procesarEnvioDeFoto(file: File, uuid: string) {
    this._accountService.updateImageProfile(uuid, file).subscribe({
      next: (response: { data?: { picture?: string } }) => {
        const picture = response?.data?.picture;
        if (picture) {
          const profile = JSON.parse(localStorage.getItem('profile') || '{}');
          profile.picture = picture;
          localStorage.setItem('profile', JSON.stringify(profile));
        }

        Swal.fire({
          icon: 'success',
          title: 'Actualización',
          text: 'Actualización exitosa.',
          showConfirmButton: true,
          confirmButtonColor: '#EEB838',
          timer: 3500
        });
      },
      error: (error) => {
        reload(error, this._router);
      }
    });
  }

  // Función para convertir Base64 a un archivo de tipo File
  public base64ToFile(base64String: string, fileName: string): File {
    // Eliminar el prefijo de la cadena Base64 (e.g. "data:image/png;base64,")
    const arr = base64String.split(',');
    const mimeType = arr[0].match(/:(.*?);/)?.[1] || ''; // Obtener el tipo MIME
    const byteString = atob(arr[1]); // Decodificar Base64
    const byteNumbers = new Array(byteString.length);
  
    // Convertir la cadena de caracteres a bytes
    for (let i = 0; i < byteString.length; i++) {
      byteNumbers[i] = byteString.charCodeAt(i);
    }
  
    const byteArray = new Uint8Array(byteNumbers);
    const blob = new Blob([byteArray], { type: mimeType });
  
    // Crear un archivo tipo File
    return new File([blob], fileName, { type: mimeType });
  }

  // Función para convertir una URL a un archivo de tipo File
  public urlToFile(url: string, fileName: string): Promise<File> {
    return fetch(url)
      .then((response) => response.blob())
      .then((blob) => {
        return new File([blob], fileName, { type: blob.type });
      });
  }

}