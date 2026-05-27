<?php

namespace App\Http\Controllers\Riders;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\CustomerPointsRequest;
use App\Http\Requests\Customers\DeleteCustomerVehicleRequest;
use App\Http\Requests\Customers\DetailCustomerRewardRequest;
use App\Http\Requests\Customers\UpdateCustomerRewardRequest;
use App\Http\Requests\Riders\MileageRiderRequest;
use App\Http\Requests\Riders\RegisterRiderRequest;
use App\Http\Requests\Riders\SearchRiderRequest;
use App\Http\Requests\Riders\StoreRiderRequest;
use App\Http\Requests\Riders\UpdateRiderRequest;
use App\Jobs\UploadRewardPointImage;
use App\Mail\RewardNotification;
use App\Models\Customer;
use App\Models\CustomerQuiz;
use App\Models\CustomerReward;
use App\Models\CustomerVehicle;
use App\Models\Quiz;
use App\Models\Reward;
use App\Models\RewardPoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RiderController extends Controller
{
    /**
     * Obtener una lista de todos los customers con vehiculos tipo moto
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search( SearchRiderRequest $request)
    {
        try {

            $data = $request->validated();

            $year = date('Y');
            
            $query = DB::table('app_vecsa_customer_reward')
            ->leftJoin('app_vecsa_rewards', 'app_vecsa_customer_reward.reward_id', '=', 'app_vecsa_rewards.id')
            ->leftJoin('app_vecsa_customers', 'app_vecsa_customer_reward.customer_id', '=', 'app_vecsa_customers.id')
            ->leftJoin('app_vecsa_customer_vehicles', 'app_vecsa_customer_reward.vehicle_id', '=', 'app_vecsa_customer_vehicles.id')
            ->leftJoin('users', 'app_vecsa_customers.user_id', '=', 'users.id')
            ->leftJoin('app_vecsa_reward_points', 'app_vecsa_reward_points.customer_reward_id', '=', 'app_vecsa_customer_reward.id')
            ->leftJoin('app_vecsa_customer_quiz', function($join) {
                $join->on('app_vecsa_customer_quiz.customer_id', '=', 'app_vecsa_customers.id')
                     ->where('app_vecsa_customer_quiz.quiz_id', '=', 2);
            })
            ->select(
                'app_vecsa_rewards.uuid as reward_uuid',
                'app_vecsa_customer_reward.created_at as reward_created_date',
                'app_vecsa_customer_reward.uuid as customer_reward_uuid',
                'app_vecsa_rewards.name as reward_name',
                'app_vecsa_rewards.description as reward_description',
                'app_vecsa_customer_reward.status as status',
                'app_vecsa_customers.uuid as customer_uuid',
                'users.nickname as user_nickname',
                'app_vecsa_customers.name as customer_name',
                'app_vecsa_customers.last_name as customer_last_name',
                'app_vecsa_customers.phone_1 as customer_phone',
                'app_vecsa_customers.email_1 as customer_email',
                'app_vecsa_customers.origin_agency as customer_dealership',
                'app_vecsa_customer_quiz.selected_value as customer_size',
                'app_vecsa_customer_vehicles.uuid as vehicle_uuid',
                'app_vecsa_customer_vehicles.vin as vehicle_vin',
                'app_vecsa_customer_vehicles.model_name as vehicle_model',
                'app_vecsa_customer_vehicles.year as vehicle_year',
                'app_vecsa_customer_vehicles.mileage as vehicle_mileage',
                'app_vecsa_reward_points.initial_mileage as initial_mileage',
                'app_vecsa_reward_points.final_mileage as final_mileage',
                DB::raw('COALESCE(SUM(CASE WHEN YEAR(app_vecsa_reward_points.created_at) = ' . $year . ' THEN app_vecsa_reward_points.earned_points ELSE 0 END), 0) as total_points')
            );

            if (isset($data['category']) && $data['category'] === 'ride') {
                $query->where('app_vecsa_rewards.category', 'ride');
            }

            if (!empty($data['keyword'])) {
                $keyword = '%' . $data['keyword'] . '%';
                
                $query->where(function ($query) use ($keyword) {

                    $keywords = explode(' ', trim($keyword));
                
                    $query->where(function ($subQuery) use ($keywords) {
                        foreach ($keywords as $word) {
                            $subQuery->orWhere('app_vecsa_customers.name', 'LIKE', '%' . $word . '%')
                                     ->orWhere('app_vecsa_customers.last_name', 'LIKE', '%' . $word . '%');
                        }
                    });
                
                    $query->orWhere('users.nickname', 'LIKE', '%' . $keyword . '%')
                          ->orWhere('app_vecsa_customers.phone_1', 'LIKE', '%' . $keyword . '%')
                          ->orWhere('app_vecsa_customers.email_1', 'LIKE', '%' . $keyword . '%')
                          ->orWhere('app_vecsa_customers.origin_agency', 'LIKE', '%' . $keyword . '%');
                });
                
            }

            $query->where('app_vecsa_customer_reward.deleted_at', null);

            $rewards = $query->groupBy(
                'app_vecsa_rewards.uuid',
                'app_vecsa_customer_reward.created_at',
                'app_vecsa_customer_reward.uuid',
                'app_vecsa_rewards.name',
                'app_vecsa_rewards.description',
                'app_vecsa_customer_reward.status',
                'app_vecsa_customers.uuid',
                'users.nickname',
                'app_vecsa_customers.name',
                'app_vecsa_customers.last_name',
                'app_vecsa_customers.phone_1',
                'app_vecsa_customers.email_1',
                'app_vecsa_customers.origin_agency',
                'app_vecsa_customer_quiz.selected_value',
                'app_vecsa_customer_vehicles.uuid',
                'app_vecsa_customer_vehicles.model_name',
                'app_vecsa_customer_vehicles.year',
                'app_vecsa_customer_vehicles.vin',
                'app_vecsa_customer_vehicles.mileage',
                'app_vecsa_reward_points.initial_mileage',
                'app_vecsa_reward_points.final_mileage'
            )
            ->paginate($data['paginate']);


            return ApiResponseHelper::apiSuccess(200, 'Riders obtenidos exitosamente', ['riders' => $rewards]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de riders', $e->getMessage(), 500, 'GET_RIDERS_ERROR');
        }
    }

    /**
     * Obtener una lista de todos los customers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search_customers( SearchRiderRequest $request)
    {
        try {

            $data = $request->validated();

            $year = date('Y');

            // Use strftime for SQLite compatibility, YEAR() for MySQL
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                $yearExpr = "COALESCE(SUM(CASE WHEN strftime('%Y', app_vecsa_reward_points.created_at) = '" . $year . "' THEN app_vecsa_reward_points.earned_points ELSE 0 END), 0) as total_points";
            } else {
                $yearExpr = 'COALESCE(SUM(CASE WHEN YEAR(app_vecsa_reward_points.created_at) = ' . $year . ' THEN app_vecsa_reward_points.earned_points ELSE 0 END), 0) as total_points';
            }
            
            $query = $query = DB::table('app_vecsa_customers')
            ->leftJoin('users', 'app_vecsa_customers.user_id', '=', 'users.id')
            ->leftJoin('app_vecsa_customer_reward', 'app_vecsa_customer_reward.customer_id', '=', 'app_vecsa_customers.id')
            ->leftJoin('app_vecsa_reward_points', function ($join) {
                $join->on('app_vecsa_customer_reward.id', '=', 'app_vecsa_reward_points.customer_reward_id')
                     ->where('app_vecsa_reward_points.redeemed', '=', 0);
            })
            ->select(
                'app_vecsa_customers.uuid as customer_uuid',
                'users.nickname as user_nickname',
                'app_vecsa_customers.name as customer_name',
                'app_vecsa_customers.last_name as customer_last_name',
                'app_vecsa_customers.phone_1 as customer_phone',
                'app_vecsa_customers.email_1 as customer_email',
                'app_vecsa_customers.created_at as register_date',
                DB::raw($yearExpr)
            );

            if (!empty($data['keyword'])) {
                $keyword = '%' . $data['keyword'] . '%';
                
                $query->where(function ($query) use ($keyword) {

                    $keywords = explode(' ', trim($keyword));
                
                    $query->where(function ($subQuery) use ($keywords) {
                        foreach ($keywords as $word) {
                            $subQuery->orWhere('app_vecsa_customers.name', 'LIKE', '%' . $word . '%')
                                     ->orWhere('app_vecsa_customers.last_name', 'LIKE', '%' . $word . '%');
                        }
                    });
                
                    $query->orWhere('users.nickname', 'LIKE', '%' . $keyword . '%')
                          ->orWhere('app_vecsa_customers.phone_1', 'LIKE', '%' . $keyword . '%')
                          ->orWhere('app_vecsa_customers.email_1', 'LIKE', '%' . $keyword . '%')
                          ->orWhere('app_vecsa_customers.origin_agency', 'LIKE', '%' . $keyword . '%');
                });
                
            }

            $query->where('app_vecsa_customers.deleted_at', null);
            $query->where('app_vecsa_customer_reward.deleted_at', null);

            $rewards = $query->groupBy(
                'app_vecsa_customers.uuid',
                'users.nickname',
                'app_vecsa_customers.name',
                'app_vecsa_customers.last_name',
                'app_vecsa_customers.phone_1',
                'app_vecsa_customers.email_1',
                'app_vecsa_customers.created_at',
            )
            ->paginate($data['paginate']);


            return ApiResponseHelper::apiSuccess(200, 'Clientes obtenidos exitosamente', ['clientes' => $rewards]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de clientes', $e->getMessage(), 500, 'GET_CUSTOMERS_SALES_ERROR');
        }
    }

    public function points()
    {
        try {
        
            $year = date('Y');

            $points = DB::table('app_vecsa_customers as c')
                ->leftJoin('app_vecsa_customer_reward as cr', 'c.id', '=', 'cr.customer_id')
                ->leftJoin('app_vecsa_reward_points as rp', function ($join) {
                    $join->on('cr.id', '=', 'rp.customer_reward_id')
                         ->where('rp.redeemed', '=', 0);
                })
                ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
                ->select(
                    'c.picture',
                    'u.nickname',
                    DB::raw("COALESCE(SUM(CASE WHEN strftime('%Y', rp.created_at) = '" . $year . "' THEN rp.earned_points ELSE 0 END), 0) as total_earned_points"),
                    DB::raw("ROW_NUMBER() OVER (ORDER BY COALESCE(SUM(CASE WHEN strftime('%Y', rp.created_at) = '" . $year . "' THEN rp.earned_points ELSE 0 END), 0) DESC) as position")
                )
                ->whereNull('c.deleted_at')
                ->groupBy('c.id', 'u.nickname', 'c.picture')
                ->orderBy('total_earned_points', 'DESC')
                ->limit(10)
                ->get();

            return ApiResponseHelper::apiSuccess(200, 'Tabla de puntajes obtenida exitosamente', $points);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de riders', $e->getMessage(), 500, 'GET_RIDERS_ERROR');
        }
    }

    public function customerPosition(CustomerPointsRequest $request)
    {
        try {
            
            $data = $request->validated();

            $year = date('Y');
            $month = date('m');
            $months = collect(range(1, 12));

            $points = DB::table('app_vecsa_customers as c')
                ->leftJoin('app_vecsa_customer_reward as cr', 'c.id', '=', 'cr.customer_id')
                ->leftJoin('app_vecsa_reward_points as rp', function ($join) {
                    $join->on('cr.id', '=', 'rp.customer_reward_id')
                         ->where('rp.redeemed', '=', 0);
                })
                ->leftJoin('users as u', 'c.user_id', '=', 'u.id')
                ->select(
                    'c.uuid',
                    DB::raw("COALESCE(SUM(CASE WHEN strftime('%Y', rp.created_at) = '" . $year . "' THEN rp.earned_points ELSE 0 END), 0) as total_points"),
                    DB::raw("COALESCE(SUM(CASE WHEN strftime('%Y', rp.created_at) = '" . $year . "' AND strftime('%m', rp.created_at) = '" . str_pad($month, 2, '0', STR_PAD_LEFT) . "' THEN rp.earned_points ELSE 0 END), 0) as month_points"),
                    DB::raw("ROW_NUMBER() OVER (ORDER BY COALESCE(SUM(CASE WHEN strftime('%Y', rp.created_at) = '" . $year . "' THEN rp.earned_points ELSE 0 END), 0) DESC) as position")
                )
                ->whereNull('c.deleted_at')
                ->groupBy('c.uuid')
                ->get();

            $customer = $points->where('uuid', $data['customer_uuid'])->first();

            $monthly_points = DB::table('app_vecsa_customers as c')
                ->leftJoin('app_vecsa_customer_reward as cr', 'c.id', '=', 'cr.customer_id')
                ->leftJoin('app_vecsa_reward_points as rp', function ($join) {
                    $join->on('cr.id', '=', 'rp.customer_reward_id')
                         ->where('rp.redeemed', '=', 0);
                })
                ->select(
                    DB::raw("CAST(strftime('%m', rp.created_at) AS INTEGER) as month"),
                    DB::raw("COALESCE(SUM(CASE WHEN strftime('%Y', rp.created_at) = '" . $year . "' THEN rp.earned_points ELSE 0 END), 0) as monthly_points")
                )
                ->where('c.uuid', $data['customer_uuid'])
                ->groupBy('month')
                ->get();

            $complete_monthly_points = $months->map(function ($month) use ($monthly_points) {
                
                $pointsForMonth = $monthly_points->firstWhere('month', $month);

                return $pointsForMonth ? $pointsForMonth->monthly_points : 0;
            });

            return ApiResponseHelper::apiSuccess(200, 'Puntos de cliente obtenidos exitosamente', ['profile' => $customer, 'months' => $complete_monthly_points]);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los puntos del cliente', $e->getMessage(), 500, 'GET_POINTS_ERROR');
        }
    }

    /**
     * Almacenar vehicle de rider
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRiderRequest $request)
    {
        try {
            
            $data = $request->validated();

            $customer = Customer::findByUuid($data['customer_uuid']);

            $reward = Reward::findByUuid($data['reward_uuid']);

            $customer_vehicle = CustomerVehicle::firstOrCreate(['vin' => $data['vin'], 'type' => 'moto', 'customer_id' => $customer->id]);

            $customer_vehicle->update(['mileage' => $data['mileage']]);

            CustomerReward::firstOrCreate([
                'customer_id' => $customer->id,
                'reward_id' => $reward->id,
                'vehicle_id' => $customer_vehicle->id
            ]);
        
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Ride almacenado correctamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el rider', $e->getMessage(), 500, 'RIDE_CREATE_ERROR');
        }
    }


    /**
     * Almacenar vehicle de rider
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vehicleRegister(RegisterRiderRequest $request)
    {
        try {
            
            $data = $request->validated();

            $customer = Customer::findByUuid($data['customer_uuid']);

            $reward = Reward::findByUuid($data['reward_uuid']);

            $customer_vehicle = CustomerVehicle::firstOrCreate(['model_name' => $data['model'], 'year' => $data['year'], 'type' => 'moto', 'customer_id' => $customer->id]);

            CustomerReward::firstOrCreate([
                'customer_id' => $customer->id,
                'reward_id' => $reward->id,
                'vehicle_id' => $customer_vehicle->id
            ]);
            
            Mail::to(env('CRM_MAIL_1', ''))->send(new RewardNotification($customer, $data['model'], $data['year']));
            Mail::to(env('CRM_MAIL_2', ''))->send(new RewardNotification($customer, $data['model'], $data['year']));
 
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Ride almacenado correctamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el vehiculo', $e->getMessage(), 500, 'RIDE_CREATE_ERROR');
        }
    }

    /**
     * Actualizar vehicle de rider
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRiderRequest $request)
    {
        try {
            
            $data = $request->validated();

            $customer = Customer::findByUuid($data['customer_uuid']);

            $reward = Reward::findByUuid($data['reward_uuid']);

            $customer_vehicle = CustomerVehicle::findByUuid($data['vehicle_uuid']);
            
            if($data['model'] && $data['year'] && $data['vin'])
                $customer_vehicle->update([
                    'model_name' => $data['model'],
                    'year' => $data['year'],
                    'type' => 'moto',
                    'vin' => $data['vin'],
                ]);

            $customer_reward = CustomerReward::where([
                'customer_id' => $customer->id,
                'reward_id' => $reward->id,
                'vehicle_id' => $customer_vehicle->id,
            ]);

            if($data['status'])
                $customer_reward->update([
                    'status' => $data['status'],
                ]);
        
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Ride actualizado correctamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el vehiculo', $e->getMessage(), 500, 'RIDE_CREATE_ERROR');
        }
    }


    /**
     * Detalle del reward del rider
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rewardRideDetail(DetailCustomerRewardRequest $request)
    {
        try {
            
            $data = $request->validated();

            $customer_reward = CustomerReward::findByUuid($data['customer_reward_uuid'], ['vehicle','reward','points.images','customer']);

            if(!$customer_reward){
                return ApiResponseHelper::apiError('Error al actualizar el los puntos del reward', 'No existe el registro del cliente con el reward', 500, 'RIDE_REWARD_UPDATE_ERROR');
            }

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Recompensa obtenida exitosamente', $customer_reward);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el detalle de la recompensa', $e->getMessage(), 500, 'CUSTOMER_REWARD_GET_ERROR');
        }
    }

    /**
     * Actualizar customer reward
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerRewardUpdate(UpdateCustomerRewardRequest $request)
    {
        try {
            
            $data = $request->validated();

            $customer_reward = CustomerReward::findByUuid($data['customer_reward_uuid'], ['customer','vehicle','reward','points.images']);

            if(!$customer_reward){
                return ApiResponseHelper::apiError('Error al actualizar el los puntos del reward', 'No existe el registro del cliente con el reward', 500, 'RIDE_REWARD_UPDATE_ERROR');
            }

            $gender_quiz = Quiz::findByUuid($data['gender_uuid']);

            $size_quiz = Quiz::findByUuid($data['size_uuid']);

            if(!$gender_quiz || !$size_quiz ){
                return ApiResponseHelper::apiError('Error al actualizar los quizzes', 'No existe el registro de quizzes con los uuids proporcionados', 500, 'QUIZZES_UPDATE_ERROR');
            }

            $customer_quiz_gender = CustomerQuiz::firstOrCreate(['quiz_id' => $gender_quiz->id, 'customer_id' => $customer_reward->customer->id]);

            $customer_quiz_size = CustomerQuiz::firstOrCreate(['quiz_id' => $size_quiz->id, 'customer_id' => $customer_reward->customer->id]);

            $customer_quiz_gender->update(['selected_value' => $data['gender']]);

            $customer_quiz_size->update(['selected_value' => $data['size']]);


            $customer_reward->customer->update([
                'origin_agency' => $data['origin_agency'],
                'email_1' => $data['email_1'],
                'phone_1' => $data['phone_1'],
                'last_name' => $data['last_name'],
                'name' => $data['name'],
            ]);

            $customer_reward->vehicle->update([
                'year' => $data['year'],
                'model_name' => $data['model'],
            ]);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Datos actualizados exitosamente', $customer_reward);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la recompensa', $e->getMessage(), 500, 'CUSTOMER_REWARD_GET_ERROR');
        }
    }

    /**
     * Actualizar rewards de rider
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rewardRideUpdate(MileageRiderRequest $request)
    {
        try {
            
            $data = $request->validated();

            $points = 0;

            $customer_reward = CustomerReward::findByUuid($data['customer_reward_uuid'], ['customer','vehicle','reward']);

            if(!$customer_reward){
                return ApiResponseHelper::apiError('Error al actualizar el los puntos del reward', 'No existe el registro del cliente con el reward', 500, 'RIDE_REWARD_UPDATE_ERROR');
            }

            $reward_point = RewardPoint::firstOrCreate([ 'detail' => $customer_reward->reward->name, 'customer_reward_id' => $customer_reward->id]);


            if(!$reward_point->initial_mileage && isset($data['initial_mileage'])) {
                
                $customer_reward->vehicle->update(['mileage'  => $data['initial_mileage']]);

                $reward_point->update([
                    'initial_mileage'  => $data['initial_mileage'],
                ]);
            }

            if( isset($data['final_mileage']) ){
                
                switch ($customer_reward->reward->category) {
                    case 'ride':
                        
                        $total_mileage = ($data['final_mileage'] - $reward_point->initial_mileage);

                        $points = $total_mileage * env('RIDES_POINT_100_KM' , 1);

                        break;
                }
                
                $customer_reward->vehicle->update(['mileage'  => $data['final_mileage']]);

                $reward_point->update([
                    'earned_points' => $points,
                    'final_mileage'  => $data['final_mileage'] ?? null
                ]);
            }

            if( isset($data['initial_image']) ){

                $initial_image = $request->file('initial_image');

                $path = \App\Support\UploadableImage::storeTemp($initial_image);

                UploadRewardPointImage::dispatch($path, 'kilometraje_inicial', $reward_point, $initial_image->getClientOriginalName());
            }

            if( isset($data['final_image']) ){

                $final_image = $request->file('final_image');

                $path = \App\Support\UploadableImage::storeTemp($final_image);

                UploadRewardPointImage::dispatch($path, 'kilometraje_final', $reward_point, $final_image->getClientOriginalName());
            }
                    
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Ride actualizado correctamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el los puntos del reward', $e->getMessage(), 500, 'RIDE_REWARD_UPDATE_ERROR');
        }
    }

    /**
     * Eliminar vehicle de rider
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteCustomerVehicleRequest $request)
    {
        try {
            
            $data = $request->validated();

            $customer_vehicle = CustomerVehicle::findByUuid($data['vehicle_uuid']);

            if($customer_vehicle)
                $customer_vehicle->delete();
                    
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Ride eliminado correctamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar el vehiculo', $e->getMessage(), 500, 'RIDE_DELETE_ERROR');
        }
    }
}