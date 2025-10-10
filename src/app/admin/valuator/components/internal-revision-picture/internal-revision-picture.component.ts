import { Component, ElementRef, EventEmitter, Inject, Output, QueryList, ViewChildren, type OnInit } from '@angular/core';
import { FormArray, FormControl, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';

import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';

import { InternalImagesService } from '@services/internal-images.service';

import Swal from 'sweetalert2';

interface FileItem {
    file: File;
    uploaded: boolean;
}

@Component({
    selector: 'app-internal-revision-picture',
    templateUrl: './internal-revision-picture.component.html',
    styleUrls: ['./internal-revision-picture.component.css'],
    standalone: false
})
export class InternalRevisionPictureComponent implements OnInit {

    public uuid_valuator!: number;
    public spinner: boolean = false;
    public count: number = 0;
    public carImagesPaths: any[] = [];
    public allInternalImagesUploaded = false;
    public carImagesPathsOthers: any[] = [];
    // files: File[] = [];
    files: FileItem[] = [];
    public imgs: any[] = [];
    public totalRecords: number = 0;

    @ViewChildren('cambiarImgInput') cambiarImgInputs!: QueryList<ElementRef<HTMLInputElement>>
    @Output() imagesInternalUploaded = new EventEmitter<boolean>();

    public imagesForm!: UntypedFormGroup;

    constructor(
        @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
        private _fb: UntypedFormBuilder,
        private bottomsheet: MatBottomSheetRef,
        private _internalImagesService: InternalImagesService
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
        console.log(this.uuid_valuator);
        
    }

    public getImageControl(index: number): FormControl {
        return this.imagesArray.at(index) as FormControl;
    }
    
    public getImages(): void {
        this.carImagesPaths = [];

        const imagesArray = this.imagesForm.get('imagesArray') as FormArray;
        imagesArray.clear();

        // Mapeo
        const nameToIndex: { [key: string]: number } = {
            'Consola': 0,
            'Asiento trasero': 1,
            'Asiento delantero': 2,
            'Luces': 3,
            'Toma corriente': 4,
            'Computadora de viaje': 5,
            'Tacómetro': 6,
            'Quemacocos': 7,
            'Cinturones': 8,
            'Hoja de servicios': 9
        };

        this._internalImagesService.getInternalImage( this.uuid_valuator )
            .subscribe({
                next: (resp) => {
                    if (resp.data && Array.isArray(resp.data)) {
                        for (let i = 0; i < 10; i++) {
                            this.carImagesPaths[i] = [i, '../../../../../assets/principales/' + (i+23) + '.svg', 'no'];
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
                        this.checkAllInternalImagesUploaded();
                    } else {
                        console.error('La respuesta de la API no es válida o está vacía');
                    }
                },
                error: (err) => {
                console.error('Error al obtener las imágenes:', err);
                for (let i = 0; i < 10; i++) {
                    this.carImagesPaths[i] = [i, '../../../../../assets/principales/' + (i+23) + '.svg', 'no'];
                    imagesArray.push(new FormControl(null, Validators.required));
                }
                this.checkAllInternalImagesUploaded();
                }
            });
    }

    private checkAllInternalImagesUploaded(): void {
        this.allInternalImagesUploaded = this.carImagesPaths.every(image => image[2] === 'si');
        this.imagesInternalUploaded.emit(this.allInternalImagesUploaded);
    }

    public getImagesOthers(): void {
        this._internalImagesService.getOthersInternalImages( this.uuid_valuator)
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

    public saveImage(event: Event, index: number) {
        const input = event.target as HTMLInputElement;
        // if (this.imagesForm.valid) {
        if (input.files && input.files.length > 0) {
            // console.log('Todas las imágenes requeridas están presentes');
            const file = input.files[0];
            // this.files[index] = file;
            this.files[index] = { file: file, uploaded: false};
            this.sendInfo(index, this.getImageName(index)).then(() => {
                this.files[index].uploaded = true;
                if (this.areAllInternalImagesUploaded()) {
                    console.log('Todas las imágenes requeridas estan presentes.');
                    this.bottomsheet.dismiss({ data: true });
                }
            }).catch(() => {
                console.error('Error al subir la imagen.');
            });
        } else {
            console.error('No se seleccionó un archivo válido');
        }

    }

    private sendInfo(index: number, name: string) {
        const file = this.files[index];
        const newItem = {
            valuation_uuid: this.uuid_valuator,
            name: name,
            group_name: 'Interior',
            images: [file.file]
        };
        return new Promise<void>((resolve, reject) => {
            this._internalImagesService.setInternalImage(newItem)
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

    private areAllInternalImagesUploaded(): boolean {
        return this.files.length === 10 && this.files.every(file => file.uploaded === true);
    }

    private getImageName(index: number): string {
        const names = ['Consola', 'Asiento trasero', 'Asiento delantero', 'Luces', 'Toma corriente', 'Computadora de viaje', 'Tacómetro', 'Quemacocos', 'Cinturones', 'Hoja de servicios'];
        return names[index] || 'Desconocido';
    }

    public fileCapture(event: any) {
        this.spinner = true;
        this.imgs = [];
        for (const document of event.target.files) {
            this.imgs.push(document)
        }
        this.totalRecords = this.imgs.length;
        this.count = 1;
        console.log('Files:', this.imgs, 'Total Records:', this.totalRecords);
        this.imgs.map( image => {
            this.saveImageOthers(image);
        });
    }

    public saveImageOthers(event: any) {
        console.log(event);
        const newItem = {
            valuation_uuid: this.uuid_valuator,
            name: 'otros',
            group_name: 'Interior_others',
            images: [event]
        };
        console.log('Nuevo item creado:', newItem);
        this._internalImagesService.setInternalImage(newItem)
            .subscribe({
                next: () => {
                    if(this.count < this.totalRecords) {
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
