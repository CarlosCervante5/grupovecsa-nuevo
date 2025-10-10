<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ValuationCheckpointsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Llenar 100 puntos de la valuación

        // Inicia Mecánica y Eléctrica
        DB::table(env('DB_TABLE_PREFIX', '') . 'valuation_checkpoints')->insert([
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Escaneo de vehículo',
                'description' => 'Diagnosticar fallas que se encuentran en la computadora del auto.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sensores',
                'description' => 'Diagnosticar fallas que se encuentran en la reversa, punto ciego, proximidad, etc.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Medidores/tonos de aviso.',
                'description' => 'Diagnosticar fallas en la operación de Medidores/tonos de aviso.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Encendido y estabilidad motor.',
                'description' => 'Diagnosticar fallas al encender el auto y desgaste en los componentes del motor.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Funcionamiento del motor/desempeño/aceleración.',
                'description' => 'Diagnosticar fallas del motor en la potencia de salida y velocidad.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Transmisión automático/manual.',
                'description' => 'Diagnosticar fallas en la caja de velocidades automático/manual, funcionamiento correcto.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Control de tracción.',
                'description' => 'Diagnosticar fallas y la operación en Los sensores de velocidad de las ruedas.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Frenos/abs.',
                'description' => 'Diagnosticar fallas en la operación, sensación pedal y función ABS.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Dirección/alineación y balanceo.',
                'description' => 'Diagnosticar fallas en la operación, ruidos, vibración de la dirección, alineación y balanceo a 80 km.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Chasis/alineación.',
                'description' => 'Diagnosticar ruidos, vibración, aspereza en el chasis del auto.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Caja de transferencia.',
                'description' => 'Diagnosticar fallas en la operación, F/RWD, 4W, AWD dificultad para cambiar la marcha, ruidos de la parte baja del vehículo.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Control de crucero.',
                'description' => 'Diagnosticar fallas en la aceleración, desaceleración, cancelación, etc.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Velocímetro/tacómetro y odómetro.',
                'description' => 'Diagnosticar fallas en la operación.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Calentador/aire acondicionado.',
                'description' => 'Diagnosticar fallas en la eficiencia, soplador y controles del Calentador/aire acondicionado.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Volante de dirección telescópico y de altura.',
                'description' => 'Diagnosticar fallas en la operación.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Claxon.',
                'description' => 'Diagnosticar fallas en la operación del mismo, suena no suena.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Limpiaparabrisas/chisgueteros y plumas.',
                'description' => 'Diagnosticar fallas en el funcionamiento y estado físico.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Ajustes de pedales/volante.',
                'description' => 'Diagnosticar fallas si el ajuste no es el óptimo.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Inspección visual.',
                'description' => 'Diagnosticar fallas si en la inspección visual hay fugas evidentes o faltan piezas o estampas.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sistema de enfriamiento del motor/radiador/mangueras.',
                'description' => 'Diagnosticar si hay fugas en el sistema de enfriamiento.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sistema de dirección.',
                'description' => 'Diagnosticar si hay problemas con la bomba, cremallera, columna, motor, fugas, operación en el sistema de dirección.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sistema eléctrico.',
                'description' => 'Diagnosticar si hay algún problema con la batería, alternador, arnés, cables, computadoras, etc. en el sistema eléctrico.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sistema de frenos.',
                'description' => 'Diagnosticar si hay algún problema con el cilindro, bomba, booster de freno, líneas, calipers, discos en el sistema de frenos.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sistema de encendido.',
                'description' => 'Diagnosticar si hay algún problema con la marcha, bujías, bobinas, condición de enrutamiento en el sistema de encendido.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sistema de combustible.',
                'description' => 'Diagnosticar si hay algún problema con la bomba, líneas, fugas, conexiones, funcionamiento en el sistema de combustible.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Compresor a/ac.',
                'description' => 'Diagnosticar si hay algún problema con la polea, correa, funcionamiento, eficiencia, controles del compresor.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Inspección de filtros.',
                'description' => 'Diagnosticar si hay algún problema con los filtros de gasolina, aire, etc.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Inspección de mangueras.',
                'description' => 'Diagnosticar si hay algún problema con las mangueras: de combustible, radiador, calentador, etc.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Inspección de bandas.',
                'description' => 'Diagnosticar si hay algún problema con las bandas: de distribución, sincrónica, dentada, etc.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Prueba de batería.',
                'description' => 'Diagnosticar si hay algún problema con el funcionamiento de la batería.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Prueba de compresión/fugas y degradación de aceite motor.',
                'description' => 'Diagnosticar si hay alguna Pérdida de potencia al andar, Exceso de humo, Vibración en el vehículo, El vehículo se apaga constantemente, etc.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Verificar estado de catalizador/sensores de oxígeno/emisiones.',
                'description' => 'Diagnosticar si hay algún problema en la lectura de CO2 del tubo de escape.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Prueba de eficiencia de a/ac y carga si es necesario.',
                'description' => 'Diagnosticar si hay algún problema en la eficiencia de carga.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Visual (cuerpo, parte inferior del cuerpo, debajo de la carrocería).',
                'description' => 'Diagnosticar si hay algún problema en la parte de debajo del auto.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Marco.',
                'description' => 'Diagnosticar si hay algún signo de reparación o daños en el vehículo.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(), // no encuentro este punto
                'sort_id' => 0,
                'name' => 'Sistema de escape sin daños.',
                'description' => 'Diagnosticar si el tubo de escape está colgando, el motor hace más ruido, disminuye la potencia del motor, emisiones tóxicas, etc.',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Espesor > 6mm frenos disco > 2mm tambor de espesor.',
                'description' => 'Diagnosticar el espesor apropiado en la condición de las pastillas de freno, balatas.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Derecha Delantera (DD)mm.',
                'description' => 'Diagnosticar el espesor apropiado en la condición de las pastillas de freno, balatas de la llanta Derecha Delantera.',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Izquierda Delantera (ID)mm.',
                'description' => 'Diagnosticar el espesor apropiado en la condición de las pastillas de freno, balatas de la llanta Izquierda Delantera.',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Izquierda Trasera (IT)mm.',
                'description' => 'Diagnosticar el espesor apropiado en la condición de las pastillas de freno, balatas de la llanta Izquierda Trasera.',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Derecha Trasera (DT)mm.',
                'description' => 'Diagnosticar el espesor apropiado en la condición de las pastillas de freno, balatas de la llanta Derecha Trasera.',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Discos, pinzas, calipers tambores.',
                'description' => 'Diagnosticar la condición y dimensiones, rectificar si es necesario.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Freno hidráulico.',
                'description' => 'Diagnosticar el nivel, líneas, mangueras.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Profundidad 8/32 o mayor.',
                'description' => 'Diagnosticar la profundidad, marca, tipo, tamaño, IGUALES las 5.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Profundidad Derecha Delantera (DD)mm.',
                'description' => 'Diagnosticar la profundidad de la llanta Derecha Delantera.',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Profundidad Izquierda Delantera (ID)mm.',
                'description' => 'Diagnosticar la profundidad de la llanta Izquierda Delantera.',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Profundidad Izquierda Trasera (IT)mm.',
                'description' => 'Diagnosticar la profundidad de la llanta Izquierda Trasera.',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Profundidad Derecha Trasera (DT)mm.',
                'description' => 'Diagnosticar la profundidad de la llanta Derecha Trasera.',
                'values' => '',
                'value_type' => 'number',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Ruedas de acero o aleación originales según modelo y versión.',
                'description' => 'Diagnosticar la calidad de las ruedas del auto, de acero o aleación, originales.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Amortiguadores.',
                'description' => 'Diagnosticar la operación, fugas, etc. de los amortiguadores.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Resorte/barras estabilizadoras.',
                'description' => 'Diagnosticar la operación de resortes y barras estabilizadoras.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Soportes motor/caja/escape.',
                'description' => 'Diagnosticar la condición, montaje, bujes de los soportes del motor, caja y escape.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Dirección/enlace.',
                'description' => 'Diagnosticar fallas en la barra de dirección/terminales, la articulación.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Compartimiento del motor.',
                'description' => 'Diagnosticar la condición del acabado, el aislamiento, las calcomanías.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Motor.',
                'description' => 'Diagnosticar la condición, funcionamiento, sin fugas ni golpes.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Transmisión.',
                'description' => 'Diagnosticar la condición, funcionamiento, sin fugas ni golpes.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Caja de transferencia.',
                'description' => 'Diagnosticar la condición, funcionamiento, sin fugas ni golpes.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Montaje, ejes.',
                'description' => 'Diagnosticar la condición, funcionamiento, sin fugas y golpes.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Diferencial.',
                'description' => 'Diagnosticar la condición, funcionamiento, sin fugas y golpes.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Comentarios.',
                'description' => 'Comentarios específicos del proceso de Mecánica y Eléctrica.',
                'values' => '',
                'value_type' => 'textArea',
                'section_name' => 'Mecánica y Eléctrica'
            ],
            // Fin Mecánica y Eléctrica

            // Inicia Revisión Exterior
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Vehículo ha sufrido modificaciones.',
                'description' => 'Diagnosticar si el vehículo ha sufrido modificaciones.',
                'values' => 'si,no',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'A) Carrocería.',
                'description' => 'Diagnosticar si el vehículo ha sufrido modificaciones en la carrocería.',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'B) Chasis.',
                'description' => 'Diagnosticar si el vehículo ha sufrido modificaciones en el chasis.',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'C) Kits deportivos.',
                'description' => 'Diagnosticar si el vehículo ha sufrido modificaciones en los Kits deportivos.',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'D) Chips de desempeño u otros.',
                'description' => 'Diagnosticar si el vehículo ha sufrido modificaciones en los Chips de desempeño u otros.',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Costado derecho y alineación de puertas.',
                'description' => 'Diagnosticar el estado del costado derecho y la alineación de puertas.',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Costado izquierdo y alineación de puertas.',
                'description' => 'Diagnosticar el estado del costado izquierdo y la alineación de puertas.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Defensa delantera.',
                'description' => 'Diagnosticar el estado de la fascia, protectores, molduras, alineación, acabado.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Cofre.',
                'description' => 'Diagnosticar el acabado, brisa, decoloración, granizo, pintura original, alineación.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Toldos.',
                'description' => 'Diagnosticar el estado de rieles, capote.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Defensa trasera.',
                'description' => 'Diagnosticar el estado de la fascia, protectores, molduras, alineación, acabado, caja, emblemas, etc.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tapa de gasolina.',
                'description' => 'Diagnosticar el estado y calidad de la tapa de gasolina.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Tapa de cajuela/caja/bedliner.',
                'description' => 'Diagnosticar el acabado, brisa, decoloración, granizo, pintura original.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Cajuela.',
                'description' => 'Diagnosticar si se encuentra la llanta de refacción, herramientas, gato, red de carga, etc.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Rines y ruedas/cubierta de neumáticos/biseles/tapones.',
                'description' => 'Diagnosticar si los rines, ruedas, cubierta de neumáticos, biseles, tapones tienen rasguños, picaduras.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Cristal.',
                'description' => 'Diagnosticar el estado del cristal verificando golpes, rasguños, picaduras, estrellado, originalidad.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Estribos.',
                'description' => 'Diagnosticar el tipo de estribos fijos o eléctricos.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Retrovisores.',
                'description' => 'Diagnosticar el estado y calidad de espejo, carcasa.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Antena.',
                'description' => 'Diagnosticar el estado, calidad y si tiene o no la antena.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sellos, gomas, empaques de puertas.',
                'description' => 'Diagnosticar el estado, calidad de los sellos, gomas, empaques de puertas.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Puertas/Cerraduras.',
                'description' => 'Diagnosticar el estado, calidad de las cerraduras y puertas.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Luces exteriores.',
                'description' => 'Diagnosticar el estado, funcionamiento de DRL, bajas, altas, freno, reversa, emergencia, direccionales, espejo.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Alarma.',
                'description' => 'Diagnosticar el estado, funcionamiento de la alarma operativos, a distancia.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Exterior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Comentarios.',
                'description' => 'Comentarios específicos del proceso de Revisión Exterior.',
                'values' => '',
                'value_type' => 'textArea',
                'section_name' => 'Revisión Exterior'
            ],
            // Fin Revisión Exterior

            // Inicia Revisión Interior
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Apertura remota.',
                'description' => 'Diagnosticar si es funcional la apertura remota.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Freno de estacionamiento.',
                'description' => 'Diagnosticar el estado del freno de estacionamiento.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Asientos, anclaje de seguridad para niños.',
                'description' => 'Diagnosticar el estado de los Asientos y si cuenta con anclaje de seguridad para niños.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Cinturones.',
                'description' => 'Diagnosticar el estado de los cinturones de seguridad.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Cristales.',
                'description' => 'Diagnosticar el estado de los cristales.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Quemacocos.',
                'description' => 'Diagnosticar el estado del quemacocos en relación a si estan operativos, en condiciones, sin entradas de agua/aire.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sistema de navegación.',
                'description' => 'Diagnosticar el estado del sistema de navegación.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Sistema de audio y dvd.',
                'description' => 'Diagnosticar el estado del sistema de audio y dvd.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Conectividad, revisión de usb/aux/bluetooth.',
                'description' => 'Diagnosticar el estado y revisión de usb/aux/bluetooth.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Reloj/termómetro.',
                'description' => 'Diagnosticar el estado y funcionamiento del reloj/termómetro.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Computadora de viaje.',
                'description' => 'Diagnosticar el estado y funcionamiento de la computadora de viaje.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Toma corriente(s).',
                'description' => 'Diagnosticar el estado y funcionamiento operativo del toma corriente.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Luces de interior.',
                'description' => 'Diagnosticar el estado y funcionamiento de luces de mapa, plafón, puertas, tablero, etc.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Desempañador trasero.',
                'description' => 'Diagnosticar el estado y si esta operacional.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Panel de instrumentos.',
                'description' => 'Diagnosticar el estado y limpieza, rasgaduras, funciones.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Asientos traseros/reposacabezas.',
                'description' => 'Diagnosticar la operación, estado, limpieza, rasgaduras.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Consola/tapa del compartimiento - del/tras.',
                'description' => 'Diagnosticar el funcionamiento y estado de la consola/tapa del compartimento delantero-trasero.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Onstar presionar botón.',
                'description' => 'Diagnosticar el funcionamiento del botón Onstar.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Onstar verificar conectividad de módulo.',
                'description' => 'Diagnosticar el funcionamiento de la conectividad de módulo Onstar.',
                'values' => 'requiere servicio,servicio realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Revisión Interior'
            ],
            // Fin Revisión Interior
            
            // Inicia Certificación de vehículo
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Manual de propietario.',
                'description' => 'Verificar la existencia del Manual de propietario.',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Campañas abiertas.',
                'description' => 'Verificar la existencia de campañas abiertas.',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'El vehículo es certificable.',
                'description' => 'Diagnosticar si realmente el vehículo es certificable.',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Fecha último mantenimiento comprobable.',
                'description' => 'Si el vehículo es certificable colocar la fecha del último mantenimiento.',
                'values' => '',
                'value_type' => 'date',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Detallado exterior e interior.',
                'description' => 'Diagnosticar los detallados exterior e interior.',
                'values' => 'si,no,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Documentación completa.',
                'description' => 'Verificar que se tenga la documentación completa',
                'values' => 'si realizado,no realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Onstar pre-activación completada.',
                'description' => 'Verificar que este completada la pre-activación Onstar.',
                'values' => 'si realizado,no realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Prueba de estado de salud de la batería.',
                'description' => 'Verificar el estado de salud de la batería.',
                'values' => 'si realizado,no realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Realizar campañas abiertas.',
                'description' => 'Verificar la realización de campañas abiertas.',
                'values' => 'si realizado,no realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Cambio de aceite de motor y filtro.',
                'description' => 'Diagnosticar el aceite y filtro si es necesario monitor reestablecer la vida del aceite.',
                'values' => 'si realizado,no realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Inspeccionar/cambiar filtros.',
                'description' => 'Inspeccionar y/o cambiar filtros de acerdo al programa del fabricante.',
                'values' => 'si realizado,no realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Inspeccionar y poner a nivel todos los fluídos.',
                'description' => 'Inspeccionar y poner a nivel todos los fluídos frenos, dirección, transmisión, limpiaparabrisas, etc.',
                'values' => 'si realizado,no realizado,n/a',
                'value_type' => 'select',
                'section_name' => 'Certificación de Vehículo'
            ],
            // Fin Certificación de vehículo
        ]);
    }
}
