<?php

namespace App\Http\Controllers\Appointments;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointments\AttatchAppointmentRequest;
use App\Http\Requests\Appointments\StoreAppointmentRequest;
use App\Http\Requests\Riders\SearchRiderRequest;
use App\Models\CustomerAppointment;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\ValuationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class AppointmentController extends Controller
{

    protected $appointmentService;
    protected $valuationService;

    public function __construct(AppointmentService $appointmentService, ValuationService $valuationService)
    {
        $this->appointmentService = $appointmentService;
        $this->valuationService = $valuationService;
    }

    /**
     * Obtener una lista de todas las citas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search( SearchRiderRequest $request)
    {
        try {

            $data = $request->validated();
            
            $query = DB::table('app_vecsa_customer_appointments')
            ->leftJoin('app_vecsa_customers', 'app_vecsa_customer_appointments.customer_id', '=', 'app_vecsa_customers.id')
            ->leftJoin('app_vecsa_customer_vehicles', 'app_vecsa_customer_appointments.vehicle_id', '=', 'app_vecsa_customer_vehicles.id')
            ->leftJoin('app_vecsa_vehicle_valuations', 'app_vecsa_customer_appointments.id', '=', 'app_vecsa_vehicle_valuations.appointment_id')
            ->leftJoin('app_vecsa_user_valuation', 'app_vecsa_vehicle_valuations.id', '=', 'app_vecsa_user_valuation.valuation_id')
            ->leftJoin('users', 'app_vecsa_user_valuation.user_id', '=', 'users.id')
            ->leftJoin('app_vecsa_user_profiles', 'app_vecsa_user_profiles.user_id', '=', 'users.id')
            ->select(
                'app_vecsa_customers.phone_1 as phone_1',
                'app_vecsa_customers.name as customer_name',
                'app_vecsa_customers.last_name as customer_lastname',
                'app_vecsa_customer_vehicles.brand_name as vehicle_brandname',
                'app_vecsa_customer_vehicles.model_name as vehicle_modelname',
                'app_vecsa_customer_vehicles.mileage as vehicle_mileage',
                'app_vecsa_customer_vehicles.year as vehicle_year',
                'app_vecsa_customer_appointments.uuid as appointment_uuid',
                'app_vecsa_customer_appointments.dealership_name as dealership_name',
                'app_vecsa_customer_appointments.type as appointment_type',
                'app_vecsa_customer_appointments.scheduled_date as appointment_scheduled_date',
                'app_vecsa_user_profiles.name as valuator_name',
                'app_vecsa_user_profiles.last_name as valuator_last_name',
                'users.uuid as valuator_uuid'
            );

            if (isset($data['type'])) {
                $query->where('app_vecsa_customer_appointments.type', $data['type']);
            }

            if (!empty($data['keyword'])) {
                $keyword = '%' . $data['keyword'] . '%';
                
                $query->orWhere('app_vecsa_customers.name', 'LIKE', $keyword)
                      ->orWhere('app_vecsa_customers.last_name', 'LIKE', $keyword)
                      ->orWhere('app_vecsa_customers.phone_1', 'LIKE', $keyword);
            }

            $appointments = $query->groupBy(
                'app_vecsa_customers.phone_1',
                'app_vecsa_customers.name',
                'app_vecsa_customers.last_name',
                'app_vecsa_customer_vehicles.brand_name',
                'app_vecsa_customer_vehicles.model_name',
                'app_vecsa_customer_vehicles.mileage',
                'app_vecsa_customer_vehicles.year',
                'app_vecsa_customer_appointments.type',
                'app_vecsa_customer_appointments.uuid',
                'app_vecsa_customer_appointments.dealership_name',
                'app_vecsa_customer_appointments.scheduled_date',
                'app_vecsa_user_profiles.name',
                'app_vecsa_user_profiles.last_name',
                'users.uuid',
            )
            ->paginate($data['paginate']);


            return ApiResponseHelper::apiSuccess(200, 'Citas obtenidas exitosamente', ['appointments' => $appointments]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de citas', $e->getMessage(), 500, 'GET_APPOINTMENTS_ERROR');
        }
    }


    /**
     * Crear cita para valuación
     *
     * @param  \App\Http\Requests\Users\StoreAppointmentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function valuationAppointment(StoreAppointmentRequest $request)
    {
        try {

            $data = $request->validated();

            $user = auth()->user();

            $appointment = $this->appointmentService->createAppointment($data);

            $this->valuationService->createValuation($appointment, $user);
            
            return ApiResponseHelper::apiSuccess(201, 'Cita de valuacion creada exitosamente');

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la cita de valuacion', $e->getMessage(), 500, 'CREATE_VALUATION_APPOINTMENT_ERROR');
        }
    }

    /**
     * Asociar cita con valuacion y valuador
     *
     * @param  \App\Http\Requests\Users\AttatchAppointmentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function attatchValuator(AttatchAppointmentRequest $request)
    {
        try {

            $data = $request->validated();

            $appointment = CustomerAppointment::findByUuid($data['appointment_uuid']);

            if (!$appointment) {
                return ApiResponseHelper::authError('La cita proporcionada no se encuentra', null, 401, 'GET_APPOINTMENT_ERROR');
            }

            $user = User::findByUuid($data['valuator_uuid']);

            if (!$user) {
                return ApiResponseHelper::authError('El usuario no se encuentra registrado', null, 401, 'GET_USER_ERROR');
            }

            $valuation = $appointment->valuation()->first();

            if(!$valuation){

              $valuation =  $this->valuationService->createValuation($appointment, $user);

            } else {

                $roleProfile = $user->getRoleProfile();
                $role = $roleProfile['role'];

                $valuation->valuator()->detach();

                $valuation->valuator()->attach($user->id, ['user_role_name' => $role]);
            }

            return ApiResponseHelper::apiSuccess(201, 'Cita de asociada exitosamente');

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la cita de valuacion', $e->getMessage(), 500, 'CREATE_VALUATION_APPOINTMENT_ERROR');
        }
    }

    /**
     * Crear cita
     *
     * @param  \App\Http\Requests\Users\StoreAppointmentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreAppointmentRequest $request)
    {
        try {

            $data = $request->validated();

            $this->appointmentService->createAppointment($data);

            return ApiResponseHelper::apiSuccess(201, 'Cita creada exitosamente');

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la cita', $e->getMessage(), 500, 'CREATE_APPOINTMENT_ERROR');
        }
    }
}
