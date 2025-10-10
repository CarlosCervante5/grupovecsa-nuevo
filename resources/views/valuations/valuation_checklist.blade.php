<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Check List de Valuación</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div style="font-size: 10px;">
                    <div class="row">
                        <table class="table" style="width: 100%; border-collapse: collapse; border: none;">
                            <tr>
                                <!-- Columna izquierda -->
                                <td style="text-align: left; vertical-align: middle; width: 70%;">
                                    <div style="display: inline-block; text-align: left;">
                                        <span style="font-size: 18px; font-weight: bold; color: #333; white-space: nowrap;">
                                            100 Puntos, Check List
                                        </span>
                                        <span style="font-size: 12px; font-weight: normal; color: #555; margin-left: 10px; white-space: nowrap;">
                                            Valuación y Certificación de Unidades
                                        </span>
                                    </div>
                                </td>
                                
                                <!-- Columna derecha -->
                                <td style="text-align: right; vertical-align: middle; width: 30%;">
                                    <img src="{{ public_path('./images/logo-text.png') }}" alt="Logo" style="width: 120px; height: auto;">
                                </td>
                            </tr>
                        </table>

                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <tr>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Nombre Cliente:</strong> {{ $valuation->appointment->customer->name }} {{ $valuation->appointment->customer->last_name }}</td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Teléfono:</strong> {{ $valuation->appointment->customer->phone_1 }}</td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Distribuidor:</strong> {{ $valuation->dealership->name }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Fecha de Valuación:</strong> {{ $valuation->created_at }}</td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>VIN:</strong> {{ $valuation->vehicle->vin }}</td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Marca:</strong> {{ $valuation->vehicle->brand->name }}</td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Modelo:</strong> {{ $valuation->vehicle->model->name }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px;"><strong>Versión:</strong> {{ $valuation->vehicle->version->name }}</td>
                                <td style="padding: 5px;"><strong>Año:</strong> {{ $valuation->vehicle->model->year }}</td>
                                <td style="padding: 5px;"><strong>Kilometraje:</strong> {{ $valuation->vehicle->mileage }}</td>
                                <td style="padding: 5px;"><strong>Color:</strong> {{ $valuation->vehicle->exterior_color }}</td>
                            </tr>

                            <tr>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Placa:</strong> {{ $valuation->vehicle->specifications->plates ?? 'No tiene' }}</td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Manual de Garantía:</strong> Si / No</td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Compra Directa:</strong> 
                                    {{ $valuation->take_type && str_contains($valuation->take_type, 'Compra directa') ? 'Sí' : 'No' }}
                                </td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Toma a Cuenta:</strong> 
                                    {{ $valuation->take_type && str_contains($valuation->take_type, 'Toma a cuenta') ? 'Sí' : 'No' }}
                                </td>
                                <td style="padding: 5px; border-bottom: 1px solid #ddd;"><strong>Garantía Vigente:</strong> Si / No</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <table class="table " style="font-size: 10px;">
                        <td>
                            <h2 style="font-size:  11px !important; font-weight: bold !important;">VERIFICACIÓN MECANICA Y ESTÉTICA</h2>
                            <h2 style="background: rgb(254, 194, 73); color: white; font-size: 9px;">REVISIÓN EXTERIOR</h2>
                            
                            @foreach ($mapped_checklists['Revisión Exterior'] as $checkpoint)

                                <span style="font-weight: bold !important;"  class="{{ strtolower($checkpoint['selected_value']) === 'si' || strtolower($checkpoint['selected_value']) === 'servicio realizado' ? 'positive' : 
                                            (strtolower($checkpoint['selected_value']) === 'no' || strtolower($checkpoint['selected_value']) === 'requiere servicio' ? 'negative' : 
                                            (strtolower($checkpoint['selected_value']) === 'n/a' ? 'na' : '')) }}">
                                </span>
                                <span class="break-text">
                                    {{ $checkpoint['name'] }}
                                </span>

                                <br>
                            @endforeach

                            <br>

                            <h2 style="background: rgb(254, 194, 73); color: white; font-size: 9px; mt-2">REVISIÓN INTERIOR</h2>

                            @foreach ($mapped_checklists['Revisión Interior'] as $checkpoint)

                                <span style="font-weight: bold !important;"  class="{{ strtolower($checkpoint['selected_value']) === 'si' || strtolower($checkpoint['selected_value']) === 'servicio realizado' ? 'positive' : 
                                            (strtolower($checkpoint['selected_value']) === 'no' || strtolower($checkpoint['selected_value']) === 'requiere servicio' ? 'negative' : 
                                            (strtolower($checkpoint['selected_value']) === 'n/a' ? 'na' : '')) }}">
                                </span>
                                <span class="break-text">
                                    {{ $checkpoint['name'] }}
                                </span>
                                <br>
                            @endforeach

                        </td>
                        <td>
                            @if ($valuation['technician'] && $valuation['technician'][0]->userProfile)
                                <h2 style="font-size:  11px !important; font-weight: bold !important;">
                                    TÉCNICO: 
                                    {{ $valuation['technician'][0]->userProfile->name }} 
                                    {{ $valuation['technician'][0]->userProfile->last_name }}
                                </h2>
                            @else
                                <h2 style="font-size:  11px !important; font-weight: bold !important;">
                                    TÉCNICO: No asignado
                                </h2>
                            @endif

                            <h2 style="background: rgb(254, 194, 73); color: white; font-size: 9px; ">MECANICA Y ELECTRICA</h2>

                            @for ($i = 0; $i < count($mapped_checklists['Mecánica y Eléctrica']) && $i <= 36; $i++)

                                @php
                                    $checkpoint = $mapped_checklists['Mecánica y Eléctrica'][$i];
                                @endphp

                                <span style="font-weight: bold !important;"  class="{{ strtolower($checkpoint['selected_value']) === 'si' || strtolower($checkpoint['selected_value']) === 'servicio realizado' ? 'positive' : 
                                            (strtolower($checkpoint['selected_value']) === 'no' || strtolower($checkpoint['selected_value']) === 'requiere servicio' ? 'negative' : 
                                            (strtolower($checkpoint['selected_value']) === 'n/a' ? 'na' : '')) }}">
                                </span>

                                {{ $checkpoint['name'] }}

                                <br>
                            @endfor

                            @for ($i = 37; $i < count($mapped_checklists['Mecánica y Eléctrica']) && $i <= 40; $i++)
                                @php
                                    $checkpoint = $mapped_checklists['Mecánica y Eléctrica'][$i];
                                @endphp

                                {{ $checkpoint['name'] }}

                                <span>
                                    {{ $checkpoint['selected_value'] }}
                                </span>

                                <br>
                            @endfor
                            

                        </td>
                    </table>
                </div>

                <div class="row">
                    <table class="table " style="font-size: 10px;">
                        <td>

                            @for ($i = 41; $i < count($mapped_checklists['Mecánica y Eléctrica']) && $i <= 42; $i++)
                                @php
                                    $checkpoint = $mapped_checklists['Mecánica y Eléctrica'][$i];
                                @endphp

                                <span style="font-weight: bold !important;"  class="{{ strtolower($checkpoint['selected_value']) === 'si' || strtolower($checkpoint['selected_value']) === 'servicio realizado' ? 'positive' : 
                                            (strtolower($checkpoint['selected_value']) === 'no' || strtolower($checkpoint['selected_value']) === 'requiere servicio' ? 'negative' : 
                                            (strtolower($checkpoint['selected_value']) === 'n/a' ? 'na' : '')) }}">
                                </span>

                                {{ $checkpoint['name'] }}

                                <br>
                            @endfor

                            Neumáticos.
                            <br>

                            @for ($i = 43; $i < count($mapped_checklists['Mecánica y Eléctrica']) && $i <= 43; $i++)
                                @php
                                    $checkpoint = $mapped_checklists['Mecánica y Eléctrica'][$i];
                                @endphp

                                <span style="font-weight: bold !important;"  class="{{ strtolower($checkpoint['selected_value']) === 'si' || strtolower($checkpoint['selected_value']) === 'servicio realizado' ? 'positive' : 
                                            (strtolower($checkpoint['selected_value']) === 'no' || strtolower($checkpoint['selected_value']) === 'requiere servicio' ? 'negative' : 
                                            (strtolower($checkpoint['selected_value']) === 'n/a' ? 'na' : '')) }}">
                                </span>

                                {{ $checkpoint['name'] }}

                                <br>

                            @endfor

                            @for ($i = 44; $i < count($mapped_checklists['Mecánica y Eléctrica']) && $i <= 47; $i++)
                                @php
                                    $checkpoint = $mapped_checklists['Mecánica y Eléctrica'][$i];
                                @endphp

                                {{ $checkpoint['name'] }}

                                <span>
                                    {{ $checkpoint['selected_value'] }}
                                </span>

                                <br>
                            @endfor

                            @for ($i = 48; $i < count($mapped_checklists['Mecánica y Eléctrica']); $i++)
                                @php
                                    $checkpoint = $mapped_checklists['Mecánica y Eléctrica'][$i];
                                @endphp

                                <span style="font-weight: bold !important;"  class="{{ strtolower($checkpoint['selected_value']) === 'si' || strtolower($checkpoint['selected_value']) === 'servicio realizado' ? 'positive' : 
                                            (strtolower($checkpoint['selected_value']) === 'no' || strtolower($checkpoint['selected_value']) === 'requiere servicio' ? 'negative' : 
                                            (strtolower($checkpoint['selected_value']) === 'n/a' ? 'na' : '')) }}">
                                </span>

                                {{ $checkpoint['name'] }}

                                <br>
                            @endfor
                            
                            <h2 style="background: rgb(254, 194, 73); color: white; font-size: 9px;">CERTIFICACIÓN DE VEHÍCULO</h2>

                            {{ $mapped_checklists['comments'][2]['name'] }}

                            <span>
                                {{ $mapped_checklists['comments'][2]['selected_value'] }}
                            </span>
                            
                            <br/>

                            @foreach ($mapped_checklists['Certificación de Vehículo'] as $checkpoint)

                                <span style="font-weight: bold !important;"  class="{{ strtolower($checkpoint['selected_value']) === 'si' || strtolower($checkpoint['selected_value']) === 'si realizado' ? 'positive' : 
                                            (strtolower($checkpoint['selected_value']) === 'no' || strtolower($checkpoint['selected_value']) === 'no realizado' ? 'negative' : 
                                            (strtolower($checkpoint['selected_value']) === 'n/a' ? 'na' : '')) }}">
                                </span>

                                {{ $checkpoint['name'] }}

                                <br>
                            @endforeach
                            
                            <br>
                            <span style="font-weight: bold !important;  "></span>CÓDIGOS DE REFERENCIA:</span>
                            <br>
                            <span style="font-weight: bold !important;">
                                <span class="negative"> </span> 
                                Inspección Realizada
                                <span class="positive"> </span>
                                Requiere Servicio
                                <span class="na"> </span> 
                                N/A
                            </span>

                        </td>
                    </table>
                </div>
            </div>
        </div>

        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>

        <div class="row">
            <table class="table " style="font-size: 10px;">
                <td>
                    <br>
                    <br>
                    <span style="font-size:  14px !important; font-weight: bold !important;  ">__________________________________</span>
                    <br>
                    
                    <span style="font-size:  14px !important; font-weight: bold !important;  ">TECNICO CERTIFICADO POR GM</span>
                    <br>
                    
                </td>
                
                <td>
                    <br>
                    <br>
                    <span style="font-size:  14px !important; font-weight: bold !important;  ">_______________________________</span>
                    <br>
                    
                    <span style="font-size:  14px !important; font-weight: bold !important;  ">GERENTE DE SEMINUEVOS</span>
                    <br>
                </td>
                
                <td>
                    <br>
                    <br>
                    <span style="font-size:  14px !important; font-weight: bold !important;  ">_______________________________</span>
                    <br>
                    
                    <span style="font-size:  14px !important; font-weight: bold !important;  ">VALUADOR - COMPRADOR</span>
                    <br>
                </td>
            </table>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; color: #333;">
                        <tr>
                            <td style="vertical-align: top; padding: 10px; width: 30%; border-right: 1px solid #ddd;">
                                <h3 style="font-size: 14px; font-weight: bold; margin: 0 0 10px 0; color: #333;">COTIZACIÓN</h3>
                                
                                <p style="margin: 0; font-weight: bold;">Referencia libro:</p>
                                <p style="margin: 5px 0 0 0;">Toma: $ {{ number_format($valuation->book_trade_in_offer, 2) }}</p>
                                <p style="margin: 5px 0 0 0;">Venta: $ {{ number_format($valuation->book_sale_price, 2) }}</p>
                                
                                <p style="margin: 15px 0 0 0; font-weight: bold;">Referencia Intelimotors:</p>
                                <p style="margin: 5px 0 0 0;">Baja: $ {{ number_format($valuation->intellimotors_trade_in_offer, 2) }}</p>
                                <p style="margin: 5px 0 0 0;">Alta: $ {{ number_format($valuation->intellimotors_sale_price, 2) }}</p>
                                
                                <p style="margin: 15px 0 0 0; font-weight: bold;">Reacondicionamiento:</p>
                                <p style="margin: 5px 0 0 0;">Mano de obra: $ {{ number_format($valuation->estimated_total, 2) }}</p>
                            </td>
                            
                            <td style="vertical-align: top; padding: 10px; width: 70%;">
                                <h3 style="font-size: 14px; font-weight: bold; margin: 0 0 10px 0; color: #333;">COMENTARIOS</h3>
                                
                                <p style="margin: 5px 0 0 0;">{{ $mapped_checklists['comments'][0]['selected_value'] ?? 'Sin comentarios' }}</p>
                                <p style="margin: 5px 0 0 0;">{{ $mapped_checklists['comments'][1]['selected_value'] ?? 'Sin comentarios' }}</p>
                                <p style="margin: 5px 0 0 0;">{{ $valuation->comments ?? 'Sin comentarios adicionales' }}</p>
                            </td>
                        </tr>
                    </table>

                </div>
            </div>
        </div>

        {{-- <div class="row">
            <table class="table" style="font-size: 8px;">
                <td>
                    <table style="font-size: 8px;">
                        <tr>
                            <td>Original</td>
                            <td></td>
                            <td></td>
                            <td>Genérica</td>
                            <td></td>
                            <td></td>
                            <td>Usada/Reparada</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Nom Refacción</td>
                            <td>Precio</td>
                            <td>Cantidad</td>
                            <td>Nom Refacción</td>
                            <td>Precio</td>
                            <td>Cantidad</td>
                            <td>Nom Refacción</td>
                            <td>Precio</td>
                            <td>Cantidad</td>
                        </tr>
                        @foreach($sparePart as $spare)
                            <tr>
                                <td>{{ $spare->name }}</td>
                                <td>{{ $spare->priceOriginal }}</td>
                                <td>{{ $spare->amount }}</td>
                                <td>{{ $spare->name }}</td>
                                <td>{{ $spare->priceGeneric }}</td>
                                <td>{{ $spare->amount }}</td>
                                <td>{{ $spare->name }}</td>
                                <td>{{ $spare->priceUsed }}</td>
                                <td>{{ $spare->amount }}</td>
                            </tr>
                        @endforeach
                    </table>

                    <span style="font-weight: bold !important;  "></span>Partes/refacciones Originales: $ {{ $sumOriginal }}</span>
                    <br>
                    
                    <span style="font-weight: bold !important;  "></span>Partes/refacciones Genéricas: $ {{ $sumGeneric }}</span>
                    <br>
                    
                    <span style="font-weight: bold !important;  "></span>Partes/refacciones Usadas: $ {{ $sumUsed }}</span>
                    <br>
                    
                    <span style="font-weight: bold !important;  "></span>HyP: $ {{ $valuation->body_work_painting_cost }}</span>
                    <br>
                    
                    <span style="font-weight: bold !important;  "></span>Total: $ {{ $valuation->estimated_total }}</span>
                    <br>
                    
                    <span style="font-size:  10px !important; font-weight: bold !important;  ">Toma y Oferta Final:</span>
                    <br>
                    
                    <span style="font-weight: bold !important;  "></span>Valor toma: $ {{ $valuation->final_offer }}</span>
                    <br>
                    
                    <span style="font-weight: bold !important;  "></span>Oferta final: $ {{ $valuation->trade_in_final }}</span>
                    <br>
                </td>
            </table>
        </div> --}}


    </div>
</body>

</html>

<style>
    .negative:after {
        font-weight: bold !important;
        color: red;
        content: 'x';
    }

    .positive:after {
        font-weight: bold !important;
        color: green;
        content: '✓'
    }

    .na:after {
        font-weight: bold !important;
        color: gray;
        content: 'n/a'
    }

    .bb {

        border-top-style: solid;
        border-top-color: gray;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
    }


    .break-text {
        word-wrap: break-word;  /* Permite que el texto se divida cuando sea necesario */
        white-space: normal;    /* Asegura que el texto se divida en varias líneas en lugar de una sola línea */
    }
</style>
