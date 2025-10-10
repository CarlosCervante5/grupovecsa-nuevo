<?php

namespace App\Http\Controllers\Rewards;

use App\Helpers\ApiResponseHelper;
use App\Helpers\WoocomerceHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rewards\CategoryRewardRequest;
use App\Http\Requests\Rewards\DeleteRewardRequest;
use App\Http\Requests\Rewards\DetailRewardRequest;
use App\Http\Requests\Rewards\CustomerPointsRequest;
use App\Http\Requests\Rewards\NameRewardRequest;
use App\Http\Requests\Rewards\StoreRewardRequest;
use App\Http\Requests\Rewards\UpdateRewardRequest;
use App\Http\Requests\Rewards\UpdateSaleRewardRequest;
use App\Jobs\UploadRewardImage;
use App\Models\Customer;
use App\Models\CustomerCoupon;
use App\Models\CustomerReward;
use App\Models\Reward;
use App\Models\RewardPoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Monolog\Handler\IFTTTHandler;

class RewardController extends Controller
{
    /**
     * Obtener una lista de todas las recompensas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        try {
            
            $rewards = Reward::all();

            return ApiResponseHelper::apiSuccess(200, 'Recompensas obtenidas exitosamente', ['rewards' => $rewards]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de recompensas', $e->getMessage(), 500, 'GET_REWARDS_ERROR');
        }
    }

    /**
     * Almacenar recompensa
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRewardRequest $request)
    {
        try {
            
            $data = $request->validated();
            
            $rewardKeys = [
                'name',
                'description',
                'begin_date',
                'end_date',
                'type',
            ];
            
            $rewardSubset = array_intersect_key($data, array_flip($rewardKeys));
    
            $reward = Reward::create($rewardSubset);


            if( isset($data['image']) ){

                $image = $request->file('image');

                $path = $image->store('temp_images');

                UploadRewardImage::dispatchSync($path, $reward, $image->getClientOriginalName());
            }
                    
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Reward almacenado correctamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al subir crear el vehiculo', $e->getMessage(), 500, 'RIDE_CREATE_ERROR');
        }
    }

    /**
     * Detalle recompensa
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(DetailRewardRequest $request)
    {
        try {
            
            $data = $request->validated();

            $reward = Reward::findByUuid($data['reward_uuid']);

            if (!$reward) {

                return ApiResponseHelper::apiError('La recompensa no existe', 'No existe el uuid: '. $data['uuid'] ,404, 'RECOVER_REWARD_ERROR');
            }

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(200, 'Recompensa encontrada', $reward);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la recompensa', $e->getMessage(), 500, 'REWARD_GET_ERROR');
        }
    }


    /**
     * Detalle recompensa
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function byName(NameRewardRequest $request)
    {
        try {
            
            $data = $request->validated();

            $reward = Reward::where('name', $data['name'])->first();

            if (!$reward) {

                return ApiResponseHelper::apiError('La recompensa no existe', 'No existe el nombre: '. $data['name'] ,404, 'RECOVER_REWARD_ERROR');
            }

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(200, 'Recompensa(s) encontrada(s)', $reward);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la recompensa', $e->getMessage(), 500, 'REWARD_GET_ERROR');
        }
    }


    /**
     * Detalle recompensa
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function byCategory(CategoryRewardRequest $request)
    {
        try {
            
            $data = $request->validated();

            $rewards = Reward::where('category', $data['category'])->get();

            if (!$rewards) {

                return ApiResponseHelper::apiError('La(s) recompensa(s) no existe', 'No existe la categoría: '. $data['category'] ,404, 'GET_REWARDS_ERROR');
            }

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(200, 'Recompensa(s) encontrada(s)', $rewards);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la(s) recompensa(s)', $e->getMessage(), 500, 'REWARD_GET_ERROR');
        }
    }


    /**
     * Actualizar venta recompensa
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSale( UpdateSaleRewardRequest $request )
    {
        try {
            
            $data = $request->validated();

            $points = 0;

            $reward = Reward::findByUuid($data['reward_uuid']);

            $customer = Customer::findByUuid($data['customer_uuid']);

            if(!$reward && !$customer){
                return ApiResponseHelper::apiError('Error al actualizar el los puntos del reward', 'No existe el registro del cliente con el reward', 500, 'RIDE_REWARD_UPDATE_ERROR');
            }

            $customer_reward = CustomerReward::firstOrCreate([
                'customer_id' => $customer->id,
                'reward_id' => $reward->id,
                'vehicle_id' => null
            ]);

            $points = $reward->calculation * $data['quantity'] / 100;

            RewardPoint::create([
                'sale_id' => $data['sale_id'],
                'purchase_amount' => $data['quantity'],
                'earned_points' => $points,
                'detail' => $customer_reward->reward->name,
                'customer_reward_id' => $customer_reward->id
            ]);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Recompenza a cliente generada correctamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la(s) recompensa(s)', $e->getMessage(), 500, 'REWARD_GET_ERROR');
        }
    }


    /**
     * Actualizar recompensa
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRewardRequest $request)
    {
        try {
            
            $data = $request->validated();

            $reward = Reward::findByUuid($data['reward_uuid']);

            if (!$reward) {

                return ApiResponseHelper::apiError('La recompensa no existe', 'No existe el el uuid: '. $data['uuid'] ,404, 'RECOVER_REWARD_ERROR');
            }

            $rewardKeys = [
                'name',
                'description',
                'begin_date',
                'end_date',
                'type',
            ];
            
            $rewardSubset = array_intersect_key($data, array_flip($rewardKeys));

            $reward->update($rewardSubset);

            if( isset($data['image']) ){

                $image = $request->file('image');

                $path = $image->store('temp_images');

                UploadRewardImage::dispatchSync($path, $reward, $image->getClientOriginalName());
            }
                    
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Recompensa actualizada correctamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la recompensa', $e->getMessage(), 500, 'REWARD_UPDATE_ERROR');
        }
    }

    /**
     * Eliminar recompensa
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteRewardRequest $request)
    {
        try {
            
            $data = $request->validated();

            $reward = Reward::findByUuid($data['reward_uuid']);

            if (!$reward) {

                return ApiResponseHelper::apiError('La recompensa no existe', 'No existe el el uuid: '. $data['reward_uuid'] ,404, 'RECOVER_REWARD_ERROR');
            }

            $reward->delete();
                    
            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Recompensa eliminada correctamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar la recompensa', $e->getMessage(), 500, 'REWARD_DELETE_ERROR');
        }
    }


    public function customerPoints( CustomerPointsRequest $request)
    {
        try {
        
            $year = date('Y');

            $data = $request->validated();

            $customer = Customer::findByUuid($data['customer_uuid']);

            $points = DB::table('app_vecsa_customer_reward as cr')
                ->leftJoin('app_vecsa_reward_points as rp', function ($join) {
                    $join->on('cr.id', '=', 'rp.customer_reward_id')
                        ->where('rp.redeemed', '=', 0);
                })
                ->select(
                    DB::raw('COALESCE(SUM(CASE WHEN YEAR(rp.created_at) = ' . $year . ' THEN rp.earned_points ELSE 0 END), 0) as total_earned_points')
                )
                ->where('cr.customer_id', '=', $customer->id)
                ->first();

            return ApiResponseHelper::apiSuccess(200, 'Puntos obtenidos exitosamente', $points);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los puntos', $e->getMessage(), 500, 'GET_POINTS_ERROR');
        }
    }

    public function redeemCustomerPoints( CustomerPointsRequest $request)
    {
        try {
        
            $year = date('Y');

            $data = $request->validated();

            $customer = Customer::findByUuid($data['customer_uuid']);

            $points = RewardPoint::whereHas('customerReward.reward', function ($query) use ($customer) {
                $query->where('category', 'sale'); // Considerar si esta condición cumple al momento de dar puntos por rides
            })
            ->whereHas('customerReward', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id);
            })
            ->where('redeemed', 0)
            ->whereYear('created_at', $year)
            ->get();

            if( !$points->isEmpty() ){

                // Calcular total cupon
                $total_points = $points->sum('earned_points');
                $total_coupon = floor($total_points / 1000 * 100) / 100;

                // Generár código
                $uuid = $customer->uuid;
                $date = now()->format('Y-m-d_H-i-s');
                $rawCode = $uuid . '-' . $date;
                $code = substr(md5($rawCode), 0, 12);

                // Generar cupón en wordpress
                $consumer_key =  WoocomerceHelper::getEnvironmentKey('VB_WORDPRESS_CONSUMER_KEY');
                $consumer_secret =  WoocomerceHelper::getEnvironmentKey('VB_WORDPRESS_CONSUMER_SECRET');
                $wordpress_webhook = WoocomerceHelper::getEnvironmentKey('VB_WORDPRESS_WEBHOOK');

                $data = [
                    'code' => $code,
                    'discount_type' => 'fixed_cart',
                    'amount' => (string)$total_coupon,
                    'individual_use' => true,
                    'usage_limit' => 1
                ];

                // Crear cupón
                $response = WoocomerceHelper::createCoupon($consumer_key, $consumer_secret, $wordpress_webhook, $data);

                if( $response ){

                    $points->each(function ($point) {
                        $point->update(['redeemed' => 1]);
                    });

                    $coupon = CustomerCoupon::create($data);

                    return ApiResponseHelper::apiSuccess(200, 'Cupon generado exitosamente', $coupon);

                } else {

                    return ApiResponseHelper::apiError('Error al crear el cupón en wordpress', null, 500, 'CREATE_WORDPRESS_COUPON_ERROR');
                }

            } else {

                return ApiResponseHelper::apiError('Error al crear el cupón, no hay puntos por canjear', null, 500, 'CREATE_COUPON_ERROR');

            }

            

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al generar el cupon', $e->getMessage(), 500, 'CREATE_COUPON_ERROR');
        }
    }
}