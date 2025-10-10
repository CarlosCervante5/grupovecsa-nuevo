<?php

namespace App\Services;

use App\Models\VehicleBrand;
use App\Models\BrandLine;
use App\Models\LineModel;
use App\Models\VehicleBody;
use App\Models\ModelVersion;
use App\Models\Dealership;
use App\Models\Vehicle;
use App\Models\VehicleSpecification;
use Illuminate\Support\Facades\DB;

class VehicleService
{   
    protected $userService;

    protected $vehicleKeys = [
        'name', 'description', 'vin', 'page_status', 'purchase_date', 'sale_price', 
        'list_price', 'offer_price', 'mileage', 'type', 'category', 'cylinders', 
        'interior_color', 'exterior_color', 'transmission', 'drive_train', 'fuel_type', 'spec_sheet'
    ];

    protected $specificationKeys = [
        'keys_number', 'wheel_locks', 'spare_wheel', 'hydraulic_jack', 'fire_extinguisher',
        'reflectors', 'jumper_cables', 'engine_type', 'plates', 'country_of_origin',
        'auto_start_stop', 'tools', 'antenna', 'stud_wrench', 'security_film', 'warranty_policy',
        'warranty_manual', 'intake_engine'
    ];

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Crea un vehículo en la base de datos.
     *
     * @param array $data Datos del vehículo a crear o actualizar.
     * @return Vehicle El vehículo creado o actualizado.
     */
    public function createVehicle($data, $user_id)
    {
        // Buscar o crear el vehículo
        $vehicle = Vehicle::firstOrNew(['vin' => $data['vin']]);
        
        $exists = 'existente';

        if (!$vehicle->exists) {

            $exists = 'no existente';

            // Crear o obtener las entidades relacionadas
            $dealership = Dealership::firstOrCreate(['name' => strtolower($data['dealership_name']), 'location' => strtolower($data['location'])]);
            $brand = VehicleBrand::firstOrCreate(['name' => strtolower($data['brand'])]);
            $line = BrandLine::firstOrCreate(['name' => strtolower($data['line']), 'brand_id' => $brand->id]);
            $model = LineModel::firstOrCreate(['name' => strtolower($data['model']), 'year' => $data['year'], 'line_id' => $line->id]);
            $version = ModelVersion::firstOrCreate(['name' => strtolower($data['version']), 'model_id' => $model->id]);
            $body = VehicleBody::firstOrCreate(['name' => strtolower($data['body'])]);

            // Crear la relación muchos a muchos si no existe
            if (!$model->bodies->contains($body->id)) {
                $model->bodies()->attach($body->id);
            }

            // Extraer los datos del vehículo
            $vehicleSubset = array_intersect_key($data, array_flip($this->vehicleKeys));

            // Verificar los cambios en los datos del vehículo
            $changedData = array_diff_assoc($vehicleSubset, $vehicle->toArray());

            if (!empty($changedData)) {
                $vehicle->fill($changedData);
            }

            // Asignar las IDs de las relaciones
            $vehicle->dealership_id = $dealership->id;
            $vehicle->brand_id = $brand->id;
            $vehicle->line_id = $line->id;
            $vehicle->model_id = $model->id;
            $vehicle->version_id = $version->id;
            $vehicle->body_id = $body->id;

            // Guardar el vehículo
            $vehicle->save();

            // Extraer las especificaciones del vehículo
            $specificationSubset = array_intersect_key($data, array_flip($this->specificationKeys));

            if (!empty($specificationSubset)) {
                // Buscar el registro de especificaciones existente para el vehículo
                $existingSpecification = $vehicle->specification()->get();

                if ($existingSpecification->count() > 1) {
                    // Si hay más de un registro, eliminar los adicionales
                    $existingSpecification->slice(1)->each->delete();
                }

                $vehicleSpecification = $existingSpecification->first();

                if ($vehicleSpecification) {
                    // Si existe, actualizar las especificaciones
                    $vehicleSpecification->fill($specificationSubset);
                    $vehicleSpecification->save();
                } else {
                    // Si no existe, crear un nuevo registro de especificaciones
                    $specificationSubset['vehicle_id'] = $vehicle->id;
                    $vehicleSpecification = VehicleSpecification::create($specificationSubset);
                }
            }
        }

        $this->userService->vehicleUpdate('Vehicle Controller: Create vehicle('.$exists.')', null, json_encode($data), $user_id, $vehicle->id);

        return $vehicle;
    }

    /**
     * Crea o actualiza un vehículo en la base de datos.
     *
     * @param array $data Datos del vehículo a crear o actualizar.
     * @return Vehicle El vehículo creado o actualizado.
     */
    public function createOrUpdateVehicle($data, $user_id)
    {
        // Crear o obtener las entidades relacionadas
        $dealership = Dealership::firstOrCreate(['name' => strtolower($data['dealership_name']), 'location' => strtolower($data['location'])]);
        $brand = VehicleBrand::firstOrCreate(['name' => strtolower($data['brand'])]);
        $line = BrandLine::firstOrCreate(['name' => strtolower($data['line']), 'brand_id' => $brand->id]);
        $model = LineModel::firstOrCreate(['name' => strtolower($data['model']), 'year' => $data['year'], 'line_id' => $line->id]);
        $version = ModelVersion::firstOrCreate(['name' => strtolower($data['version']), 'model_id' => $model->id]);
        $body = VehicleBody::firstOrCreate(['name' => strtolower($data['body'])]);

        // Crear la relación muchos a muchos si no existe
        if (!$model->bodies->contains($body->id)) {
            $model->bodies()->attach($body->id);
        }

        // Extraer los datos del vehículo      
        $vehicleSubset = array_intersect_key($data, array_flip($this->vehicleKeys));

        // Crear o actualizar el vehículo
        $vehicle = Vehicle::firstOrNew(['vin' => $vehicleSubset['vin']]);

        // // Verificar los cambios en los datos del vehículo
        $changedData = array_diff_assoc($vehicleSubset, $vehicle->toArray());

        if (!empty($changedData)) {
            $vehicle->fill($changedData);
        }

        // Asignar las IDs de las relaciones
        $vehicle->dealership_id = $dealership->id;
        $vehicle->brand_id = $brand->id;
        $vehicle->line_id = $line->id;
        $vehicle->model_id = $model->id;
        $vehicle->version_id = $version->id;
        $vehicle->body_id = $body->id;

        // Guardar el vehículo
        $vehicle->save();

        // Extraer las especificaciones del vehículo
        $specificationSubset = array_intersect_key($data, array_flip($this->specificationKeys));

        if (!empty($specificationSubset)) {
            // Buscar el registro de especificaciones existente para el vehículo
            $existingSpecification = $vehicle->specification()->get();

            if ($existingSpecification->count() > 1) {
                // Si hay más de un registro, eliminar los adicionales
                $existingSpecification->slice(1)->each->delete();
            }

            $vehicleSpecification = $existingSpecification->first();

            if ($vehicleSpecification) {
                // Si existe, actualizar las especificaciones
                $vehicleSpecification->fill($specificationSubset);
                $vehicleSpecification->save();
            } else {
                // Si no existe, crear un nuevo registro de especificaciones
                $specificationSubset['vehicle_id'] = $vehicle->id;
                $vehicleSpecification = VehicleSpecification::create($specificationSubset);
            }
        }

        $exists = 'Update';

        if (!$vehicle->exists) {
            $exists = 'Create';
        }
        
        $this->userService->vehicleUpdate('Vehicle Controller: '.$exists.' vehicle', json_encode([$vehicle, ( $vehicleSpecification ?? null )]) , json_encode($data), $user_id, $vehicle->id);

        return $vehicle;
    }

        /**
     * Actualiza status del vehículo en la base de datos.
     *
     * @param array $data Datos del vehículo a crear o actualizar.
     * @return Vehicle El vehículo actualizado.
     */
    public function updateStatus($data, $user_id)
    {
    
        // Crear o actualizar el vehículo
        $vehicle = Vehicle::findByUuid([$data['uuid']]);

        $vehicle->update(['page_status' => $data['page_status']]);

        $this->userService->vehicleUpdate('Vehicle Controller: Update vehicle', json_encode([$vehicle, null]) , json_encode($data), $user_id, $vehicle->id);

        return $vehicle;
    }

    /**
     * Elimina un vehículo basado en su UUID.
     *
     * @param string $uuid UUID del vehículo a eliminar.
     * @return bool Verdadero si el vehículo fue eliminado, falso si no existe.
     */
    public function deleteVehicle($uuid, $user_id)
    {
        $vehicle = Vehicle::findByUuid($uuid);
        
        $this->userService->vehicleUpdate('Vehicle Controller: Delete vehicle', null, json_encode($uuid), $user_id, $vehicle->id);

        if ($vehicle) {
            $vehicle->delete();
            return true;
        }

        return false;
    }

    /**
     * Elimina un batch de vehículos basado en sus UUID.
     *
     * @param string [] $uuids UUIDs de los vehículos a eliminar.
     * @return bool Verdadero si el vehículo fue eliminado, falso si no existe.
     */
    public function deleteVehicleBatch($uuids, $user_id)
    {
        $vehiclesExist = Vehicle::whereIn('uuid', $uuids)->exists();
        
        if ($vehiclesExist) {

            Vehicle::whereIn('uuid', $uuids)->delete();

            $this->userService->vehicleUpdate('Vehicle Controller: Delete vehicle batch', null, json_encode($uuids), $user_id);

            return true;
        }

        return false;
    }

    /**
     * Elimina un batch de vehículos basado en sus UUID.
     *
     * @param string [] $uuids UUIDs de los vehículos a conservar.
     * @return bool Verdadero si el vehículo fue eliminado, falso si no existe.
     */
    public function inverseDeleteVehicleBatch($uuids, $user_id)
    {
        $vehiclesExist = Vehicle::whereNotIn('uuid', $uuids)->exists();
        
        if ($vehiclesExist) {

            Vehicle::whereNotIn('uuid', $uuids)->delete();

            $this->userService->vehicleUpdate('Vehicle Controller: Inverse delete vehicle batch', null, json_encode($uuids), $user_id);

            return true;
        }

        return false;
    }

    /**
     * Elimina un batch de vehículos basado en sus UUID.
     *
     * @param string [] $uuids UUIDs de los vehículos a eliminar.
     * @param string $page_status nuevo status de los vehículos.
     * @return bool Verdadero si el vehículo fue eliminado, falso si no existe.
     */
    public function statusVehicleBatch($uuids, $page_status, $user_id)
    {
        $vehiclesExist = Vehicle::whereIn('uuid', $uuids)->exists();
        
        if ($vehiclesExist) {
            
            Vehicle::whereIn('uuid', $uuids)->update(['page_status' => $page_status]);

            $this->userService->vehicleUpdate('Vehicle Controller: Status vehicle batch ('.$page_status.')', null, json_encode($uuids), $user_id);

            return true;
        }

        return false;
    }


    /**
     * Restaura un vehículo eliminado basado en su UUID.
     *
     * @param string $uuid UUID del vehículo a restaurar.
     * @return Vehicle|null El vehículo restaurado, o null si no fue encontrado.
     */
    public function restoreVehicle($uuid, $user_id)
    {
        // Buscar el vehículo eliminado por UUID en la tabla de vehículos eliminados
        $vehicle = Vehicle::onlyTrashed()->where('uuid', $uuid)->first();

        // Verificar si el vehículo fue encontrado
        if (!$vehicle) {
            return null;
        }

        // Restaurar el vehículo a la tabla de vehículos activos
        $vehicle->restore();

        $this->userService->vehicleUpdate('Vehicle Controller: Restore vehicle', null, json_encode($uuid), $user_id, $vehicle->id );

        return $vehicle;
    }

    /**
     * Busca vehículos en base a los criterios proporcionados.
     *
     * @param array $data Datos de búsqueda que incluyen condiciones y paginación.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Vehículos encontrados.
     */
    public function searchVehicles($data)
    {
        // Crear la consulta base
        $query = Vehicle::query();
        
        // Aplicar las condiciones
        $query->with($data['relationship_names']);
        $query->where($this->statusCondition($data['status']));
        $query->where($this->categoriesCondition($data['categories']));
        $query->where($this->brandsCondition($data['brand_names']));
        $query->where($this->linesCondition($data['line_names']));
        $query->where($this->modelsCondition($data['model_names'], $data['years']));
        $query->where($this->bodiesCondition($data['body_names']));
        $query->where($this->versionsCondition($data['version_names']));
        $query->where($this->dealershipCondition($data['location_names']));
        $query->where($this->transmissionsCondition($data['transmission_names']));
        $query->where($this->interiorColorsCondition($data['interior_colors']));
        $query->where($this->exteriorColorsCondition($data['exterior_colors']));
        $query->where($this->pricesCondition($data['prices']));
        
        // Aplicar la condición de keyword si no es null
        $keywordCondition = $this->keywordCondition($data['keyword']);
        if ($keywordCondition) {
            $query->where($keywordCondition);
        }

        if( $data['has_images'] ){
            $query->whereHas('images');
        }
                
        // Agregar la condición de ordenación
        $orderByCondition = $this->orderByCondition($data['order_by']);
        $query->orderBy($orderByCondition['field'], $orderByCondition['direction']);


        $filters = [];

        if ($data['filters']) {

            $vehicles = $query->get();
            $filters['categories'] = $vehicles->pluck('category')->unique()->values();
            $filters['brands'] = $vehicles->pluck('brand.name')->unique()->values();
            $filters['lines'] = $vehicles->pluck('line.name')->unique()->values();
            $filters['models'] = $vehicles->pluck('model.name')->unique()->values();
            $filters['versions'] = $vehicles->pluck('version.name')->unique()->values();
            $filters['bodies'] = $vehicles->pluck('body.name')->unique()->values();
            $filters['transmissions'] = $vehicles->pluck('transmission')->unique()->values();
            $filters['interior_colors'] = $vehicles->pluck('interior_color')->unique()->values();
            $filters['exterior_colors'] = $vehicles->pluck('exterior_color')->unique()->values();
            $filters['years'] = $vehicles->pluck('model.year')->unique()->values();
            $filters['locations'] = $vehicles->pluck('dealership.name')->unique()->values();
            $filters['max_price'] = $vehicles->max('sale_price');
            $filters['min_price'] = $vehicles->min('sale_price');

            return $filters;
        }

        $vehicles = $query->paginate($data['paginate']);

        // Obtener los vehículos
        return $vehicles;
    }


    /**
     * Busca vehículos y retornar XML
     *
     */
    public function searchVehiclesXML()
    {
        $data = array(                        
            "channel" => array(
                "title" => "Inventario de Vecsa Hidalgo",
                "description" => "Inventario de Vecsa Hidalgo para Facebook",
            )        
        );

        $vehicles = Vehicle::with(['brand', 'model','images','firstImage'])
            ->where('page_status', 'active')
            ->where('category', 'pre_owned')
            ->whereHas('images')
            ->get();

        foreach ($vehicles as $key => $vehicle){                 
            
            $product = array(
                'id' => $vehicle->id,
                'title' => $vehicle->name,
                'description' => $vehicle->description,
                'availability' => 'in stock',
                'inventory' => '1',
                'condition' => 'used',
                'price' => $vehicle->sale_price,
                'link' => "https://grupovecsa.com/inventory/compra-tu-auto/detail/$vehicle->uuid",                
                'brand' => $vehicle->brand->name,                        
                'color' => $vehicle->exterior_color,
                'google_product_category' => '916',
                'custom_label_0' => $vehicle->model->name,
                'custom_label_1' => $vehicle->model->year,                
                'custom_label_2' => $vehicle->mileage . ' kms',                               
            );

            $product['image_link'] = $vehicle->firstImage->service_image_url;

            foreach( $vehicle->images as $index => $image ){

                if ($index === 0) {
                    continue;
                }
                
                $product["additional_image_link$index"] = $image->service_image_url;
                
            }

            $data['channel']['product'.$key] = $product;
        }

        $xml = $this->arrayToXml($data);

        return $xml;
    }

    /**
     * Retorna el máximo y el mínimo de los autos activos
     *
     * @param array $data Datos de búsqueda que incluyen condiciones y paginación.
     * @return array Máximos y mínimos.
     */
    public function minMaxPrices($data)
    {
        // Crear la consulta base
        $vehicles = Vehicle::where($this->statusCondition($data['status']))->get();      

        $minMax = [];

        $minMax['max_price'] = $vehicles->max('sale_price');
        $minMax['min_price'] = $vehicles->min('sale_price');

        // Obtener los vehículos
        return $minMax;
    }

    /**
     * Busca vehículos en base a los criterios proporcionados.
     *
     * @param array $data Datos de búsqueda que incluyen condiciones y paginación.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Vehículos encontrados.
     */
    public function randomSearchVehicles($data)
    {
        // Crear la consulta base
        $query = Vehicle::query();
        
        // Aplicar las condiciones
        $query->with($data['relationship_names']);
        $query->where($this->statusCondition($data['status']));
        $query->where($this->pricesCondition($data['prices']));
        $query->whereHas('images');
        
        // Obtener los vehículos con paginación
        $vehicles = $query->inRandomOrder()->limit(10)->get();


        if($vehicles->count() == 10){
            return $vehicles;
        }

        return Vehicle::with($data['relationship_names'])
        ->where($this->statusCondition($data['status']))
        ->where($this->pricesCondition($data['prices']))
        ->whereHas('images')->inRandomOrder()->limit(10)->get();

    }

    private function arrayToXml($array, $xml = null)
    {
        if ($xml === null) {
            $xml = new \SimpleXMLElement('<rss/>');
        }

        foreach ($array as $key => $value) {

            if (is_array($value) && isset($value['__attributes'])) {

                $element = $xml->addChild($key, $value['value']);

                foreach ($value['__attributes'] as $attrKey => $attrValue) {
                    $element->addAttribute($attrKey, $attrValue);
                }

                unset($value['__attributes']);           

            } elseif (is_array($value)) {                
                
                if(str_contains($key, 'product')){
                    $key = 'item';
                }

                $this->arrayToXml($value, $xml->addChild($key));

            } else { 

                if(str_contains($key, 'additional_image_link')){
                    $key = 'additional_image_link';
                }

                $xml->addChild($key, $value);
            }
        }

        return $xml->asXML();
    }

    /**
     * Crea una condición para filtrar vehículos por marcas.
     *
     * @param array $brand_names Nombres de las marcas para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function brandsCondition(array $brand_names)
    {
        $brand_ids = VehicleBrand::whereIn('name', $brand_names)->pluck('id')->toArray();

        return function ($query) use ($brand_ids) {
            if (!empty($brand_ids)) {
                $query->whereIn('brand_id', $brand_ids);
            } else {
                $query->where('brand_id', '!=', 0);
            }
        };
    }

    /**
     * Crea una condición para filtrar vehículos por líneas.
     *
     * @param array $line_names Nombres de las líneas para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function linesCondition(array $line_names)
    {
        $lines_ids = BrandLine::whereIn('name', $line_names)->pluck('id')->toArray();

        return function ($query) use ($lines_ids) {
            if (!empty($lines_ids)) {
                $query->whereIn('line_id', $lines_ids);
            } else {
                $query->where('line_id', '!=', 0);
            }
        };
    }

    /**
     * Crea una condición para filtrar vehículos por modelos y años.
     *
     * @param array $model_names Nombres de los modelos para filtrar.
     * @param array $years Años de los vehículos para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function modelsCondition(array $model_names, array $years)
    {
        $model_ids_by_names = [];
        $model_ids_by_years = [];

        if (!empty($model_names)) {
            $model_ids_by_names = LineModel::whereIn('name', $model_names)
                                        ->pluck('id')
                                        ->toArray();
        }

        if (!empty($years)) {
            $model_ids_by_years = LineModel::whereIn('year', $years)
                                        ->pluck('id')
                                        ->toArray();
        }

        $merged_model_ids = array_unique(array_merge($model_ids_by_names, $model_ids_by_years));

        return function ($query) use ($merged_model_ids) {
            if (!empty($merged_model_ids)) {
                $query->whereIn('model_id', $merged_model_ids);
            } else {
                $query->where('model_id', '!=', 0);
            }
        };
    }

    /**
     * Crea una condición para filtrar vehículos por cuerpos.
     *
     * @param array $body_names Nombres de los cuerpos para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function bodiesCondition(array $body_names)
    {
        $bodies_ids = VehicleBody::whereIn('name', $body_names)->pluck('id')->toArray();

        return function ($query) use ($bodies_ids) {
            if (!empty($bodies_ids)) {
                $query->whereIn('body_id', $bodies_ids);
            } else {
                $query->where('body_id', '!=', 0);
            }
        };
    }

    /**
     * Crea una condición para filtrar vehículos por versiones.
     *
     * @param array $version_names Nombres de las versiones para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function versionsCondition(array $version_names)
    {
        $versions_ids = ModelVersion::whereIn('name', $version_names)->pluck('id')->toArray();

        return function ($query) use ($versions_ids) {
            if (!empty($versions_ids)) {
                $query->whereIn('version_id', $versions_ids);
            } else {
                $query->where('version_id', '!=', 0);
            }
        };
    }

    /**
     * Crea una condición para filtrar vehículos por transmisiones.
     *
     * @param array $transmission_names Nombres de las transmisiones para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function transmissionsCondition(array $transmission_names)
    {
        return function ($query) use ($transmission_names) {
            if (!empty($transmission_names)) {
                $query->whereIn('transmission', $transmission_names);
            } else {
                $query->where('transmission', '!=', '');
            }
        };
    }

    /**
     * Crea una condición para filtrar vehículos por categoría (new o pre_owned).
     *
     * @param array $categories Nombres de las transmisiones para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function categoriesCondition(array $categories)
    {
        return function ($query) use ($categories) {
            if (!empty($categories)) {
                $query->whereIn('category', $categories);
            } else {
                $query->where('category', '!=', '');
            }
        };
    }

    /**
     * Crea una condición para filtrar vehículos por color interior.
     *
     * @param array $interior_colors Colores interiores para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function interiorColorsCondition(array $interior_colors)
    {
        return function ($query) use ($interior_colors) {
            if (!empty($interior_colors)) {
                $query->whereIn('interior_color', $interior_colors);
            } else {
                $query->where('interior_color', '!=', '');
            }
        };
    }


    /**
     * Crea una condición para filtrar vehículos por color exterior.
     *
     * @param array $interior_colors Colores interiores para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function exteriorColorsCondition(array $exterior_colors)
    {
        return function ($query) use ($exterior_colors) {
            if (!empty($exterior_colors)) {
                $query->whereIn('exterior_color', $exterior_colors);
            } else {
                $query->where('exterior_color', '!=', '');
            }
        };
    }

    /**
     * Crea una condición para filtrar vehículos por concesionarios.
     *
     * @param array $location_names Nombres de las ubicaciones de concesionarios para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function dealershipCondition(array $location_names)
    {
        $locations_ids = Dealership::whereIn('name', $location_names)->pluck('id')->toArray();

        return function ($query) use ($locations_ids) {
            if (!empty($locations_ids)) {
                $query->whereIn('dealership_id', $locations_ids);
            } else {
                $query->where('dealership_id', '!=', 0);
            }
        };
    }

    /**
     * Crea una condición para filtrar vehículos por estado.
     *
     * @param array $status Estatus del vehículo para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function statusCondition(array $status)
    {
        return function ($query) use ($status) {
            if (!empty($status)) {
                $query->whereIn('page_status', $status);
            } else {
                $query->where('page_status', '!=', 'sale');
            }
        };
    }


    /**
     * Crea una condición para filtrar vehículos por estado.
     *
     * @param array $status Estatus del vehículo para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function pricesCondition(array $prices)
    {
        return function ($query) use ($prices) {
            if (empty($prices)) {
                $query->where('sale_price', '>=', 0);
            } elseif (count($prices) == 1) {
                $query->where('sale_price', '>=', $prices[0]);
            } elseif (count($prices) == 2) {
                $query->whereBetween('sale_price', [$prices[0]-1, $prices[1]+1]);
            }
        };
    }

    
    /**
     * Obtiene los parámetros de ordenación según el valor de entrada.
     *
     * @param string $order_by Valor de entrada para determinar el campo y el orden.
     * @return array Parámetros de ordenación con el campo y el orden.
     */
    public function orderByCondition($order_by)
    {
        switch ($order_by) {
            case "precio_mas":
                return ['field' => 'sale_price', 'direction' => 'DESC'];
            case "precio_menos":
                return ['field' => 'sale_price', 'direction' => 'ASC'];
            case "vacio":
                return ['field' => DB::raw('RAND()'), 'direction' => 'ASC'];
            default:
                return ['field' => 'id', 'direction' => 'DESC'];
        }
    }


    /**
     * Crea una condición para buscar vehículos por una palabra clave en múltiples campos.
     *
     * @param string $keyword Palabra clave para buscar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function keywordCondition($keyword)
    {
        // Si el keyword está vacío, no retornar una condición
        if (empty($keyword)) {
            return null;
        }

        // Formatear el keyword para uso en LIKE
        $keyword = '%' . $keyword . '%';

        return function ($query) use ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('name', 'LIKE', $keyword)
                    ->orWhere('uuid', 'LIKE', $keyword)
                    ->orWhere('vin', 'LIKE', $keyword)
                    ->orWhereHas('model', function ($query) use ($keyword) {
                        $query->where('name', 'LIKE', $keyword);
                    })
                    ->orWhereHas('line', function ($query) use ($keyword) {
                        $query->where('name', 'LIKE', $keyword);
                    })
                    ->orWhereHas('brand', function ($query) use ($keyword) {
                        $query->where('name', 'LIKE', $keyword);
                    })
                    ->orWhereHas('version', function ($query) use ($keyword) {
                        $query->where('name', 'LIKE', $keyword);
                    })
                    ->orWhereHas('body', function ($query) use ($keyword) {
                        $query->where('name', 'LIKE', $keyword);
                    });
            });
        };
    }

}
