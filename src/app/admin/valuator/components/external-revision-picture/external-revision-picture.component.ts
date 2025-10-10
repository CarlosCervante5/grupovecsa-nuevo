import { Component, ElementRef, EventEmitter, Inject, OnInit, Output, QueryList, ViewChildren } from '@angular/core';
import { FormArray, FormControl, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';

import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';

import { ExternalImagesService } from '@services/external-images.service';

import Swal from 'sweetalert2';

interface FileItem {
    file: File;
    uploaded: boolean;
}

@Component({
    selector: 'app-external-revision-picture',
    templateUrl: './external-revision-picture.component.html',
    styleUrls: ['./external-revision-picture.component.css'],
    standalone: false
})
export class ExternalRevisionPictureComponent implements OnInit {

    public uuid_valuator!: number;
    public spinner: boolean = false;
    public count: number = 0;
    public carImagesPaths: any[] = [];
    public allImagesUploaded = false;
    public carImagesPathsOthers: any[] = [];
    files: FileItem[] = [];
    public imgs: any[] = [];
    public totalRecords: number = 0;

    @ViewChildren('cambiarImgInput') cambiarImgInputs!: QueryList<ElementRef<HTMLInputElement>>
    @Output() imagesUploaded = new EventEmitter<boolean>();

    public imagesForm!: UntypedFormGroup;

    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
        private _fb: UntypedFormBuilder,
        private bottomsheet: MatBottomSheetRef,
        private _externalImagesService: ExternalImagesService
    ) {
        this.imagesForm = this._fb.group({
            imagesArray: this._fb.array([] as FormControl[])
        });
    }

    ngOnInit(): void { 
        this.valorData();
        this.getImages();
        this.getImagesOthers();
    }

    public valorData() {
        this.uuid_valuator = this.data.uuid_valuation;
        // console.log(this.uuid_valuator);
        
    }

    // Método para obtener un FormControl de imagesArray basado en el índice
    public getImageControl(index: number): FormControl {
        return this.imagesArray.at(index) as FormControl;
    }

    public getImages(): void {
        this.carImagesPaths = [];

        const imagesArray = this.imagesForm.get('imagesArray') as FormArray;
        imagesArray.clear();

        // Mapeo
        const nameToIndex: { [key: string]: number } = {
            'Frente': 0,
            'Detras': 1,
            'Izquierda': 2,
            'Derecha': 3,
            'Debajo': 4,
            'Cofre': 5
        };

        this._externalImagesService.getExternalImage( this.uuid_valuator )
            .subscribe({
                next: (resp) => {
                    if (resp.data && Array.isArray(resp.data)) {
                        for (let i = 0; i < 6; i++) {
                            this.carImagesPaths[i] = [i, '../../../../../assets/principalesExterior/' + (i+1) + '.svg', 'no'];
                            imagesArray.push(new FormControl(null, Validators.required));
                        }

                        resp.data.forEach(item => {
                            if (item && item.image_path) {
                                const tempPath = item.image_path;
                                const tempName = item.name;

                                const index = nameToIndex[tempName];
                                if (index !== undefined) {
                                    this.carImagesPaths[index] = [index, tempPath, 'si'];
                                }
                            }
                        });
                        this.checkAllImagesUploaded();
                    } else {
                        console.error('La respuesta de la API no es válida o está vacía');
                    }
                },
                error: (err) => {
                    console.error('Error al obtener las imágenes:', err);
                    for (let i = 0; i < 6; i++) {
                        this.carImagesPaths[i] = [i, '../../../../../assets/principalesExterior/' + (i+1) + '.svg', 'no'];
                        imagesArray.push(new FormControl(null, Validators.required));
                    }
                    this.checkAllImagesUploaded();
                }
            });
    }

    private checkAllImagesUploaded(): void {
        this.allImagesUploaded = this.carImagesPaths.every(image => image[2] === 'si');
        this.imagesUploaded.emit(this.allImagesUploaded);
    }

    public getImagesOthers(): void {
        this._externalImagesService.getOthersExternalImages( this.uuid_valuator )
            .subscribe({
                next: (resp) => {
                    if (resp.data && Array.isArray(resp.data)) {
                        resp.data.forEach(item => {
                            if (item && item.image_path) {
                                const tempPath = item.image_path;
                                this.carImagesPathsOthers.push(tempPath);
                            }
                        });
                    }
                    
                }
            });
    }

    get imagesArray(): FormArray {
        return this.imagesForm.get('imagesArray') as FormArray;
    }

    public saveImage(event: Event, index:number) {
        // console.log('Hola desde saveImage');
        const input = event.target as HTMLInputElement;
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            // this.files[index] = file;
            this.files[index] = { file: file, uploaded: false};
            // console.log(`Archivo guardado en índice ${index}: ${file.name}`);
            this.sendInfo(index, this.getImageName(index)).then(() => {
                this.files[index].uploaded = true;
                if (this.areAllImagesUploaded()) {
                    console.log('Todas las imégenes requeridas estan presentes.');
                    this.bottomsheet.dismiss({ data: true });
                } 
                // else {
                //     this.bottomsheet.dismiss({ data: false });
                // }
            }).catch(() => {
                console.error('Error al subir la imagen.');
            });
        } else {
            console.error('No se seleccionó un archivo válido');
        }
        
    }

    private sendInfo(index: number, name: string) {
        const file = this.files[index];
        console.log(file, this.files);
        
        const newItem = {
            valuation_uuid: this.uuid_valuator,
            name: name,
            group_name: 'Exterior',
            images: [file.file]
        };
        // console.log('Nuevo Item creado:', newItem);
        // console.log('Indice:', file);
        return new Promise<void>((resolve, reject) => {
            this._externalImagesService.setExternalImage(newItem)
                .subscribe({
                    next: () => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Inserción de Imagen exitosa.',
                            text: 'Set de imagen enviada correctamente',
                            showConfirmButton: true,
                            confirmButtonColor: '#EEB838',
                            timer: 3500
                        });

                        resolve();
    
                        setTimeout(() => {
                            this.getImages();
                        }, 3500);
                    },
                    error: (error) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ooops...',
                            text: 'Al parecer ocurrió un error' + error.error.message,
                            showConfirmButton: true,
                            confirmButtonColor: '#EEB838',
                            timer: 3500
                        });
                        reject(error);
                    }
        });
            });
    }

    private areAllImagesUploaded(): boolean {
        // return this.files.length > 0 && this.files.every(fileItem => fileItem.uploaded);
        return this.files.length === 6 && this.files.every(file => file.uploaded === true);
    }

    private getImageName(index: number): string {
        const names = ['Frente', 'Detras', 'Izquierda', 'Derecha', 'Debajo', 'Cofre'];
        return names[index] || 'Desconocido';
    }

    public fileCapture(event: any) {
        this.spinner = true;
        this.imgs = [];
        for(const document of event.target.files) {
            this.imgs.push(document)
        }
        this.totalRecords = this.imgs.length;
        this.count = 1;
        // console.log({'Files: ': this.imgs, 'Total Records: ': this.totalRecords});
        console.log('Files: ', this.imgs, 'Total Records: ', this.totalRecords);
        this.imgs.map( image => {
            this.saveImageOthers(image);
        });
    }

    public saveImageOthers(event: any){
        console.log(event);
        const newItem = {
            valuation_uuid: this.uuid_valuator,
            name: 'otros',
            group_name: 'Exterior_others',
            images: [event]
        };
        console.log('Nuevo item creado:', newItem);
        this._externalImagesService.setExternalImage(newItem)
            .subscribe({
                next: () => {
                    if (this.count < this.totalRecords) {
                        this.count++;
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Inserción de Imagenes exitosa.',
                            text: 'Set de imágenes enviadas correctamente',
                            showConfirmButton: true,
                            confirmButtonColor: '#EEB838',
                            timer: 3500
                        });
                        this.spinner = false;
                        // setTimeout(() => {
                        //     this.getImages();
                        // }, 3500);
                    }
                },
                error: (error) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ooops...',
                        text: 'Al parecer ocurrió un error' + error.error.message,
                        showConfirmButton: true,
                        confirmButtonColor: '#EEB838',
                        timer: 3500
                    });
                }
            });
    }

    public markAsTouched(index: number): void {
        this.getImageControl(index).markAsTouched();
    }

}
