<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class AcquisitionCheckpointsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table(env('DB_TABLE_PREFIX', '') . 'acquisition_checkpoints')->insert([
            
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Contrato compra/venta firmado',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Factura original',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Copia factura origen (Sin leyenda copia sin valor)',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Copia Fiel de INE (legible y clara)',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'CURP',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Acuse respuesta de cambio de rol',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tarjeta de circulación',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Copia guía autométrica',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Consulta intelimotors',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Factura original en financiera',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Verificación fiscal de facturas',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Validación INE (Consulta nominal)',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Comprobante de domicilio',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Repuve',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Check list 100 puntos',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Copias de facturas intermedias (con endoso)',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Validación de la factura por parte de la agencia (de no estar el mail de la confirmación)',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Constancia de situación fiscal',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Cambio de rol (en cd y tac)',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Consulta transunión',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Fotomultas',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Monto de la fotomulta',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'PDI check de batería',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Adeudos por tenencias',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Monto adeudo por tenencia',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_12',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_11',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_10',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_9',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_8',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_7',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_6',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_5',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_4',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_3',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_2',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tenencia_1',
                'description' => '',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Información de la toma tenencia'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Manual del propietario',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Gato',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Llanta de refacción',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Antena',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Comprobante última verificación',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Carnet de servicio',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Maneral o llave de tuercas',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Reflejantes',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Duplicado de llaves',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Baja de placas',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Birlos de seguridad',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Pelicula de seguridad',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Cables pasa corriente',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentación de la unidad'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'No. de serie',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Fotos'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Herramienta',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Fotos'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Odómetro (kilometraje)',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Fotos'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Manual y póliza',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Fotos'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Llantas',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Fotos'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sellos de servicio',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Fotos'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Unidad (frente, trasera, costados, cajuela y cofre)',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Fotos'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Llanta de refacción',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Fotos'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Fotos en rampa (parte baja y daños)',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Fotos'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Placas físicas',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentos para tramites de placas'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Pagos completos de tenencias',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentos para tramites de placas'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Factura con endosos',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentos para tramites de placas'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tarjeta de circulación',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentos para tramites de placas'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'INE copia FIEL',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentos para tramites de placas'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Edo. cta de financiera o banco que indique el monto a liquidar',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentos para tramites de placas'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Acta constitutiva',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentos para tramites de placas'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'INE representante moral',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentos para tramites de placas'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Poder de representante legal',
                'description' => '',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Documentos para tramites de placas'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Observaciones',
                'description' => '',
                'values' => '',
                'value_type' => 'textArea',
                'section_name' => 'Documentos para tramites de placas'
            ],

        ]);
    }
}
