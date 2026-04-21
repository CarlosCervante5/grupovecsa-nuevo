<?php

namespace App\Http\Controllers\Valuations;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Repairs\UpdateRepairRequest;
use App\Http\Requests\SpareParts\UpdateSparePartRequest;
use App\Http\Requests\Valuations\AttatchCheckpointRequest;
use App\Http\Requests\Valuations\ChecklistValuationRequest;
use App\Http\Requests\Valuations\DetailRepairsRequest;
use App\Http\Requests\Valuations\DetailValuationRequest;
use App\Http\Requests\Valuations\DownloadValuationRequest;
use App\Http\Requests\Valuations\SearchValuationsRequest;
use App\Http\Requests\Valuations\StoreValuationVehicleRequest;
use App\Http\Requests\Valuations\UpdateValuationRequest;
use App\Models\User;
use App\Models\Valuations\CheckpointValue;
use App\Models\Valuations\PartSupplier;
use App\Models\Valuations\SpareSupplier;
use App\Models\Valuations\ValuationCheckpoint;
use App\Models\Valuations\ValuationPart;
use App\Models\Valuations\ValuationRepair;
use App\Models\Valuations\VehicleValuation;
use App\Services\UserService;
use App\Services\ValuationService;
use App\Services\VehicleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ValuationController extends Controller
{

    protected $valuationService;

    protected $vehicleService;

    protected $userService;

    public function __construct(ValuationService $valuationService, VehicleService $vehicleService, UserService $userService)
    {
        $this->valuationService = $valuationService;

        $this->vehicleService = $vehicleService;

        $this->userService = $userService;
    }

    /**
     * Mostrar valuación mediante uuid.
     * 
     * @param  \App\Http\Requests\Users\DetailValuationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail( DetailValuationRequest $request)
    {
        try {
            
            $data = $request->validated();
            
            $valuation = VehicleValuation::findByUuid($data['valuation_uuid'], ['vehicle.brand', 'vehicle.line', 'vehicle.model', 'vehicle.version', 'vehicle.body', 'vehicle.specification','dealership', 'appointment.vehicle', 'appointment.customer', 'technician.userProfile', 'seller.userProfile', 'spareParts.partSupplierOriginal', 'repairs']);

            if($valuation){
                return ApiResponseHelper::apiSuccess(200, 'Valuacion encontrada', $valuation);
            } else {
                return ApiResponseHelper::apiError('La valuación no existe', 'No existe el el uuid: '. $data['valuation_uuid'] ,404, 'RECOVER_VALUATION_ERROR');
            }
        
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la valuacion', $e->getMessage(), 500, 'GET_VALUATION_ERROR');
        }
    }

    /**
     * Actualizar valuación existente.
     *
     * @param  \App\Http\Requests\Users\UpdateValuationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateValuationRequest $request)
    {
        try {

            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid']);
            
            $data = array_filter($data, function ($value) {
                return !is_null($value) && $value !== '';
            });

            foreach ($data as $key => $value) {
                if (in_array(
                        $key, 
                        [
                            'book_trade_in_offer',
                            'book_sale_price',
                            'intellimotors_trade_in_offer',
                            'intellimotors_sale_price',
                            'labor_cost',
                            'spare_parts_cost',
                            'body_work_painting_cost',
                            'estimated_total',
                            'final_offer',
                            'trade_in_final',
                            'status',
                            'comments',
                            'take_type'
                        ])
                ) {
                    $valuation->$key = $value;
                }
            }

            if (!empty($data['seller_uuid'])) {

                $seller = User::findByUuid($data['seller_uuid']);

                $roleProfile = $seller->getRoleProfile();
                $role = $roleProfile['role'];

                $valuation->seller()->detach();

                $valuation->seller()->attach($seller->id, ['user_role_name' => $role]);

            }
            
            $valuation->save();

            return ApiResponseHelper::apiSuccess(201, 'Valuacion actualizada exitosamente', $valuation);

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el vehículo', $e->getMessage(), 500, 'UPDATE_VEHICLE_ERROR');
        }
    }

    /**
     * Encontrar valuaciones con la información de las citas.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search( SearchValuationsRequest $request)
    {
        try {

            $data = $request->validated();

            $user = auth()->user();

            $valuations = $user->valuations()->with('appointment.customer', 'appointment.vehicle', 'valuator', 'vehicle')
            ->where(function ($query) use ($data) {
                if (!empty($data['keyword'])) {
                    $keyword = '%' . $data['keyword'] . '%';

                    // Agrupamos las condiciones relacionadas al customer y al vehicle
                    $query->whereHas('appointment.customer', function ($query) use ($keyword) {
                        $query->where('name', 'LIKE', $keyword)
                            ->orWhere('last_name', 'LIKE', $keyword)
                            ->orWhere('phone_1', 'LIKE', $keyword)
                            ->orWhere('email_1', 'LIKE', $keyword);
                    })->orWhereHas('appointment.vehicle', function ($query) use ($keyword) {
                        $query->where('model_name', 'LIKE', $keyword)
                            ->orWhere('brand_name', 'LIKE', $keyword)
                            ->orWhere('year', 'LIKE', $keyword)
                            ->orWhere('vin', 'LIKE', $keyword);
                    });
                }
            })
            ->paginate(15);
            
            return ApiResponseHelper::apiSuccess(200, 'Valuaciones obtenidas exitosamente', $valuations);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la búsqueda de valuaciones', $e->getMessage(), 500, 'GET_VALUATION_SEARCH_ERROR');
        }
    }


    /**
     * Encontrar valuaciones con la información de las reparaciones.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchBodyworks( SearchValuationsRequest $request)
    {
        try {

            $data = $request->validated();

            $valuations = VehicleValuation::with('appointment.customer', 'appointment.vehicle', 'valuator', 'vehicle', 'repairs')
            ->where(function ($query) use ($data) {
                if (!empty($data['keyword'])) {
                    $keyword = '%' . $data['keyword'] . '%';

                    $query->whereHas('appointment.customer', function ($query) use ($keyword) {
                        $query->where('name', 'LIKE', $keyword)
                            ->orWhere('last_name', 'LIKE', $keyword)
                            ->orWhere('phone_1', 'LIKE', $keyword)
                            ->orWhere('email_1', 'LIKE', $keyword);
                    })->orWhereHas('appointment.vehicle', function ($query) use ($keyword) {
                        $query->where('model_name', 'LIKE', $keyword)
                            ->orWhere('brand_name', 'LIKE', $keyword)
                            ->orWhere('year', 'LIKE', $keyword)
                            ->orWhere('vin', 'LIKE', $keyword);
                    });
                }
            })
            ->paginate(15);
            
            return ApiResponseHelper::apiSuccess(200, 'Valuaciones obtenidas exitosamente', $valuations);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la búsqueda de valuaciones', $e->getMessage(), 500, 'GET_VALUATION_SEARCH_ERROR');
        }
    }


    public function searchRepairs( SearchValuationsRequest $request)
    {
        try {

            $data = $request->validated();

            $valuations = VehicleValuation::with([
                'appointment.customer', 
                'appointment.vehicle', 
                'valuator',
                'technician.userProfile',
                'vehicle', 
                'repairs' => function ($query) {
                    $query->where('status', 'on_hold');
                },
            ])
            ->where(function ($query) use ($data) {
                if (!empty($data['keyword'])) {
                    $keyword = '%' . $data['keyword'] . '%';

                    $query->whereHas('appointment.customer', function ($query) use ($keyword) {
                        $query->where('name', 'LIKE', $keyword)
                            ->orWhere('last_name', 'LIKE', $keyword)
                            ->orWhere('phone_1', 'LIKE', $keyword)
                            ->orWhere('email_1', 'LIKE', $keyword);
                    })->orWhereHas('appointment.vehicle', function ($query) use ($keyword) {
                        $query->where('model_name', 'LIKE', $keyword)
                            ->orWhere('brand_name', 'LIKE', $keyword)
                            ->orWhere('year', 'LIKE', $keyword)
                            ->orWhere('vin', 'LIKE', $keyword);
                    });
                }
            }) 
            ->whereHas('repairs', function ($query) {
                $query->where('status', 'on_hold');
            })
            ->paginate(15);
            
            return ApiResponseHelper::apiSuccess(200, 'Valuaciones obtenidas exitosamente', $valuations);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la búsqueda de valuaciones', $e->getMessage(), 500, 'GET_VALUATION_SEARCH_ERROR');
        }
    }


    public function searchParts( SearchValuationsRequest $request)
    {
        try {

            $data = $request->validated();

            $valuations = VehicleValuation::with([
                'appointment.customer', 
                'appointment.vehicle', 
                'technician.userProfile',
                'vehicle.brand',
                'vehicle.line',
                'vehicle.model',
                'vehicle.version',
                'vehicle.body', 
                'spareParts' => function ($query) {
                    $query->where('status', 'on_hold');
                },
            ])
            ->where(function ($query) use ($data) {
                if (!empty($data['keyword'])) {
                    $keyword = '%' . $data['keyword'] . '%';

                    $query->whereHas('appointment.customer', function ($query) use ($keyword) {
                        $query->where('name', 'LIKE', $keyword)
                            ->orWhere('last_name', 'LIKE', $keyword)
                            ->orWhere('phone_1', 'LIKE', $keyword)
                            ->orWhere('email_1', 'LIKE', $keyword);
                    })->orWhereHas('appointment.vehicle', function ($query) use ($keyword) {
                        $query->where('model_name', 'LIKE', $keyword)
                            ->orWhere('brand_name', 'LIKE', $keyword)
                            ->orWhere('year', 'LIKE', $keyword)
                            ->orWhere('vin', 'LIKE', $keyword);
                    });
                }
            }) 
            ->whereHas('spareParts', function ($query) {
                $query->where('status', 'on_hold');
            })
            ->paginate(15);
            
            return ApiResponseHelper::apiSuccess(200, 'Valuaciones obtenidas exitosamente', $valuations);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la búsqueda de valuaciones', $e->getMessage(), 500, 'GET_VALUATION_SEARCH_ERROR');
        }
    }

    public function detailParts( DetailRepairsRequest $request)
    {
        try {

            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid'], ['vehicle.brand', 'vehicle.line','vehicle.model','vehicle.version','vehicle.body','vehicle.dealership','vehicle.specification']);

            if (!$valuation) {

                return ApiResponseHelper::apiError('No se encontró la información solicitada', 'No existe algun uuid: '. $data['valuation_uuid'] ,404, 'GET_VALUATION_PARTS_ERROR');
            }

            $parts = ValuationPart::with([
                'suppliers' => function ($query) {
                    $query->select('uuid', 'name', 'phone', 'contact_name', 'quote_type', 'delivery_date', 'cost');
                },
            ])
            ->where('valuation_id', $valuation->id)
            ->where('status', 'on_hold')
            ->paginate(15);
            
            return ApiResponseHelper::apiSuccess(200, 'Partes obtenidas exitosamente', ['vehicle' => $valuation['vehicle'], 'parts' => $parts]);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la búsqueda de partes', $e->getMessage(), 500, 'GET_PARTS_DETAIL_ERROR');
        }
    }


    public function updateParts( UpdateSparePartRequest  $request)
    {
        try {

            $data = $request->validated();

            // Obtener la parte mediante repair_uuid
            $valuation_part = ValuationPart::findByUuid($data['part_uuid']);

            // Generar las tablas intermedias

            $original_supplier = SpareSupplier::create([
                'name' => $data['supplier_original']
            ]);

            PartSupplier::create([
                'quote_type' => 'original',
                'delivery_date' => $data['delivery_original'],
                'cost' => $data['price_original'],
                'supplier_id' => $original_supplier->id,
                'part_id' => $valuation_part->id
            ]);

            if($data['price_generic'] && $data['delivery_generic'] && $data['supplier_generic']){
                
                $generic_supplier = SpareSupplier::create([
                    'name' => $data['supplier_generic']
                ]);

                PartSupplier::create([
                    'quote_type' => 'generic',
                    'delivery_date' => $data['delivery_generic'],
                    'cost' => $data['price_generic'],
                    'supplier_id' => $generic_supplier->id,
                    'part_id' => $valuation_part->id
                ]);
            }

            if($data['price_used'] && $data['delivery_used'] && $data['supplier_used']){
                
                $used_supplier = SpareSupplier::create([
                    'name' => $data['supplier_used']
                ]);

                PartSupplier::create([
                    'quote_type' => 'used',
                    'delivery_date' => $data['delivery_used'],
                    'cost' => $data['price_used'],
                    'supplier_id' => $used_supplier->id,
                    'part_id' => $valuation_part->id
                ]);
            }

            // Actualizar la parte cotizada
            $valuation_part->update([
                'status' => 'quoted_part'
            ]);

            // Verificar si todas las valuation parts estan cotizadas y actualizar status de valuación
            $valuation = $valuation_part->valuation;

            $parts_status = $this->valuationService->statusParts($valuation);

            if( $parts_status ){

                $valuation->update([
                    'status_parts' => 'parts_done'
                ]);
            }

            return ApiResponseHelper::apiSuccess(200, 'Partes actualizadas exitosamente', null);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar las partes a solicitar', $e->getMessage(), 500, 'UPDATE_PARTS_ERROR');
        }
    }


    /**
     * Encontrar checklist de valuacion
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checklist( ChecklistValuationRequest $request)
    {
        try {

            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid']);

            $checklist = $valuation->checkpoints()
                ->when(!empty($data['section_name']), function ($query) use ($data) {
                    return $query->where('section_name', $data['section_name']);
                })
                ->get();

            $response = $checklist->map(function($checkpoint) {
                return [
                    'uuid' => $checkpoint->uuid,
                    'name' => $checkpoint->name,
                    'description' => $checkpoint->description,
                    'values' => $checkpoint->values,
                    'value_type' => $checkpoint->value_type,
                    'section_name' => $checkpoint->section_name,
                    'image_path' => $checkpoint->image_path,
                    'selected_value' => $checkpoint->selected_value,
                    'created_at' => $checkpoint->created_at,
                ];
            });

            return ApiResponseHelper::apiSuccess(200, 'Checklist obtenida exitosamente', $response);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el checklist', $e->getMessage(), 500, 'GET_CHECKLIST_ERROR');
        }
    }

    /**
     * Actualizar checkpoint
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attatch(AttatchCheckpointRequest $request)
    {
        try {
            
            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid']);

            $checkpoint = ValuationCheckpoint::findByUuid($data['checkpoint_uuid']);

            if (!$valuation || !$checkpoint) {

                return ApiResponseHelper::apiError('No se encontró la información solicitada', 'No existe algun uuid: '. $data['valuation_uuid'] .' '.$data['checkpoint_uuid'] ,404, 'GET_VALUATION_CHEKPOINT_ERROR');
            }

            $checkpoint_value = CheckpointValue::firstOrCreate(['valuation_id' => $valuation->id, 'checkpoint_id' => $checkpoint->id]);

            $checkpoint_value->update(['selected_value' => $data['selected_value']]);

            $status = $this->valuationService->statusMechanicAndElectric($valuation);

            if( $status ){

                $valuation->update(['status' => 'checklist_ready']);
            }

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Punto de valuacion asociado correctamente al cliente:');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al vincular la valuacion con el checkpoint', $e->getMessage(), 500, 'CHECKPOINT_VALUATION_ATTATCH_ERROR');
        }
    }


    /**
     * Actualizar vehiculo a valuar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVehicle( StoreValuationVehicleRequest $request)
    {
        try {

            $data = $request->validated();

            $user = auth()->user();
            $user_profile = $user->getRoleProfile();

            $technician = User::findByUuid($data['technician_uuid']);
            $technician_profile = $technician->getRoleProfile();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid']);

            if (!$valuation || !$technician) {

                return ApiResponseHelper::apiError('No se encontró la información solicitada', 'No existe algun uuid: '. $data['technician_uuid'] .' '.$data['valuation_uuid'] ,404, 'GET_VALUATION_TECHNICIAN_ERROR');
            }

            if (!$valuation->valuator->contains($user->id)) {
                $valuation->valuator()->attach($user->id,['user_role_name' => $user_profile['role']]);

            }

            if (!$valuation->technician->contains($technician->id)) {
                $valuation->technician()->attach($technician->id, ['user_role_name' => $technician_profile['role']]);
            }

            if(!$valuation->vehicle_id) {
                $vehicle = $this->vehicleService->createOrUpdateVehicle($data, $user);
                $valuation->vehicle_id = $vehicle->id;
                $valuation->save();
            }

            $this->userService->valuationUpdate('Valuation controller: update vehicle', json_encode('') , json_encode($data), $user->id, $valuation->id);

            return ApiResponseHelper::apiSuccess(200, 'Vehiculo actualizado exitosamente');

        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la valuacion', $e->getMessage(), 500, 'GET_VEHICLE_VALUATION_ERROR');
        }
    }


    public function downloadPDF( DownloadValuationRequest $request){

        try{

            $nullable_checkpoints_ids = [60, 84, 107]; 

            $data = $request->validated();

            $valuation = VehicleValuation::findByUuid($data['valuation_uuid'], ['appointment.customer','vehicle.brand', 'vehicle.line','vehicle.model','vehicle.version','vehicle.body','vehicle.dealership','vehicle.specification', 'technician.userProfile','repairs', 'spareParts']);

            $sections = [
                'Mecánica y Eléctrica',
                'Revisión Exterior',
                'Revisión Interior',
                'Certificación de Vehículo',
            ];
            
            $checklists = [];
            $mapped_checklists = [];
            
            // Obtener y mapear los checkpoints para cada sección
            foreach ($sections as $section) {
                $checklists[$section] = $valuation->checkpoints()
                    ->where('section_name', $section)
                    ->whereNotIn('checkpoint_id', $nullable_checkpoints_ids)
                    ->orderBy('id', 'asc')
                    ->get();
            
                $mapped_checklists[$section] = $checklists[$section]->map(function ($checkpoint) {
                    return [
                        'uuid' => $checkpoint->uuid,
                        'name' => $checkpoint->name,
                        'description' => $checkpoint->description,
                        'values' => $checkpoint->values,
                        'value_type' => $checkpoint->value_type,
                        'section_name' => $checkpoint->section_name,
                        'image_path' => $checkpoint->image_path,
                        'selected_value' => $checkpoint->selected_value,
                        'created_at' => $checkpoint->created_at,
                    ];
                });
            }
            
            // Obtener y mapear los checkpoints nulos
            $checklist_nullable = $valuation->checkpoints()
                ->whereIn('checkpoint_id', $nullable_checkpoints_ids)
                ->get();
            
            $mapped_checklists['comments'] = $checklist_nullable->map(function ($checkpoint) {
                return [
                    'uuid' => $checkpoint->uuid,
                    'name' => $checkpoint->name,
                    'description' => $checkpoint->description,
                    'values' => $checkpoint->values,
                    'value_type' => $checkpoint->value_type,
                    'section_name' => $checkpoint->section_name,
                    'image_path' => $checkpoint->image_path,
                    'selected_value' => $checkpoint->selected_value,
                    'created_at' => $checkpoint->created_at,
                ];
            });

            $report = PDF::loadView('valuations.valuation_checklist', compact('valuation','mapped_checklists'));

            return ($report->stream());

            // return ApiResponseHelper::apiSuccess(200, 'Checklist obtenida exitosamente', ['valuation' => $valuation, 'mapped_checklists' => $mapped_checklists] );

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al descargar la valuacion', $e->getMessage(), 500, 'DOWNLOAD_VALUATION_ERROR');
        }

    }

}
