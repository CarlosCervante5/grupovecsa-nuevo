<?php

namespace App\Http\Controllers\Strega;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Strega\Opportunities\AttatchOpportunityRequest;
use App\Http\Requests\Strega\Opportunities\DetailOpportunityRequest;
use App\Http\Requests\Strega\Opportunities\FirstAttemptRequest;
use App\Http\Requests\Strega\Opportunities\FollowAttemptRequest;
use App\Http\Requests\Strega\Opportunities\SearchAppointmentRequest;
use App\Http\Requests\Strega\Opportunities\SearchOpportunityRequest;
use App\Http\Requests\Strega\Opportunities\StoreOpportunityRequest;
use App\Http\Requests\Strega\Opportunities\UpdateOpportunityRequest;
use App\Imports\OpportunitiesImport;
use App\Models\Strega\Opportunity;
use App\Models\Strega\UserOpportunity;
use App\Models\User;
use App\Services\OpportunityService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class OpportunityController extends Controller
{
    protected $opportunityService;

    public function __construct(OpportunityService $opportunityService, UserService $userService)
    {
        $this->opportunityService = $opportunityService;
    }
     /**
     * Almacenar leads y cartera mediante un archivo csv
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function csvUpload(Request $request)
    {
        try {

            $user = auth()->user();

            $request->validate([
                'file' => 'required|mimes:csv,xlsx,xls',
            ]);

            $import = new OpportunitiesImport($this->opportunityService, $user->id);

            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            
            if ($failures->isNotEmpty()) {
                return ApiResponseHelper::apiSuccess(200, 'Se han subido las oportunidades pero el archivo contenía algunos errores.', $failures);
            }

            return ApiResponseHelper::apiSuccess(200, 'Oportunidades subidas exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al subir las oportunidades', $e->getMessage(), 500, 'UPLOAD_OPPORTUNITIES_ERROR');
        }
    }

    /**
     * Crear oportunidad
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(StoreOpportunityRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $this->opportunityService->createOpportunity($data, $user);

            return ApiResponseHelper::apiSuccess(200, 'Oportunidades creada exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la oportunidad', $e->getMessage(), 500, 'CREATE_OPPORTUNITY_ERROR');
        }
    }

     /**
     * Crear oportunidad
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function public_create(StoreOpportunityRequest $request)
    {
        try {

            $data = $request->validated();

            $this->opportunityService->createPublicOpportunity($data);

            // // Create zappier object
            // $zappier = array(
            //     'fecha'      => date_format(date_create(), "d-m-y H:i:s"),
            //     'tipo'       => 'Seminuevo',
            //     'campaña'    => 'Solicitud de financiamiento ABcars',
            //     'nombre'     => $financing->lastname,
            //     'apellido'   => $financing->mothername,
            //     'telefono'   => $client->phone1,
            //     'correo'     => $user->email,
            //     'auto'       => $request->brand_name.' '.$request->carmodel_name,
            //     'comentario' => $financing->monthly_fees.' meses',
            //     'inversion'  => $financing->hitch
            // );

            // // send content to webhook
            // $ch = curl_init();
            // curl_setopt($ch, CURLOPT_URL, "https://hooks.zapier.com/hooks/catch/8825119/3tw3rhj");
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_POST, true);
            // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($zappier));

            // $data = curl_exec($ch);
            // curl_close($ch);

            return ApiResponseHelper::apiSuccess(200, 'Oportunidades creada exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la oportunidad', $e->getMessage(), 500, 'CREATE_OPPORTUNITY_ERROR');
        }
    }


    /**
     * Buscar oportunidades
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAdministrator(SearchOpportunityRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $oportunities = $this->opportunityService->searchOpportunitiesAdministrator($data, $user->id);

            return ApiResponseHelper::apiSuccess(200, 'Oportunidades obtenidas exitosamente', $oportunities);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener las oportunidades', $e->getMessage(), 500, 'GET_OPPORTUNITIES_ERROR');
        }
    }

    /**
     * Buscar oportunidades
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchLeadsManager(SearchOpportunityRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $oportunities = $this->opportunityService->searchOpportunitiesManager($data, $user);

            return ApiResponseHelper::apiSuccess(200, 'Oportunidades obtenidas exitosamente', $oportunities);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener las oportunidades', $e->getMessage(), 500, 'GET_OPPORTUNITIES_ERROR');
        }
    }

    /**
     * Buscar oportunidades asignadas al ejecutivo Strega (strega-seller).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchSeller(SearchOpportunityRequest $request)
    {
        try {
            $user = auth()->user();
            $data = $request->validated();
            $oportunities = $this->opportunityService->searchOpportunitiesSeller($data, $user);

            return ApiResponseHelper::apiSuccess(200, 'Oportunidades obtenidas exitosamente', $oportunities);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener las oportunidades', $e->getMessage(), 500, 'GET_OPPORTUNITIES_ERROR');
        }
    }

    /**
     * Buscar citas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAppointmentsManager(SearchAppointmentRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $oportunities = $this->opportunityService->searchAppointmentsManager($data, $user);

            return ApiResponseHelper::apiSuccess(200, 'Citas obtenidas exitosamente', $oportunities);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener las citas', $e->getMessage(), 500, 'GET_APPOINTMENTS_ERROR');
        }
    }

    public function dealershipSearch()
    {
        try {

            $dealerships = Opportunity::distinct()->pluck('dealership_name');

            return ApiResponseHelper::authSuccess(200, 'Sucursales encontradas', $dealerships);

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al obtener las sucursales', $e->getMessage(), 500, 'GET_DEALERSHIPS_ERROR');
        }
    }

    public function typeSearch()
    {
        try {

            $types = Opportunity::distinct()->pluck('type');

            return ApiResponseHelper::authSuccess(200, 'Tipos de oportunides encontradas', $types);

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al obtener los tipos', $e->getMessage(), 500, 'GET_OPPORTUNITIES_TYPES_ERROR');
        }
    }

    /**
     * Asociar cita con valuacion y valuador
     *
     * @param  \App\Http\Requests\Strega\Opportunities\AttatchOpportunityRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function attatchManager(AttatchOpportunityRequest $request)
    {
        try {

            $data = $request->validated();

            $opportunity = Opportunity::findByUuid($data['opportunity_uuid']);

            if (!$opportunity) {
                return ApiResponseHelper::authError('La oportunidad proporcionada no se encuentra', null, 401, 'GET_OPPORTUNITY_ERROR');
            }

            $user = User::findByUuid($data['manager_uuid']);

            if (!$user) {
                return ApiResponseHelper::authError('El usuario no se encuentra registrado', null, 401, 'GET_USER_ERROR');
            }

            $roleProfile = $user->getStregaRoleProfile();
            $role = $roleProfile['role'];

            UserOpportunity::where('opportunity_id', $opportunity->id)
                            ->where('user_role_name', $role)
                            ->where('user_id', '!=', $user->id)
                            ->update(['deleted_at' => Carbon::now()]);


            UserOpportunity::updateOrCreate([
                'opportunity_id' => $opportunity->id,
                'user_id' => $user->id,
                'user_role_name' => $role,
                'activity' => 'assigned'
            ]);
            
            return ApiResponseHelper::apiSuccess(201, 'Opportunidad asociada exitosamente');

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la cita de valuacion', $e->getMessage(), 500, 'CREATE_VALUATION_APPOINTMENT_ERROR');
        }
    }


    public function detail(DetailOpportunityRequest $request)
    {
        try {

            $data = $request->validated();

            $oportunity = Opportunity::with([
                'forms' => function ($query) {
                    $query->select('uuid', 'name', 'selected_value');
                },
                'campaign',
                'customer',
                'appointments.stregaSeller.userProfile',
                'manager.userProfile'
            ])
            ->where('uuid', $data['opportunity_uuid'])
            ->first();

            return ApiResponseHelper::apiSuccess(200, 'Oportunidad obtenida exitosamente', $oportunity);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la oportunidad', $e->getMessage(), 500, 'GET_OPPORTUNITY_ERROR');
        }
    }

    public function update(UpdateOpportunityRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $oportunity = $this->opportunityService->updateOpportunity($data, $user);

            return ApiResponseHelper::apiSuccess(200, 'Oportunidad actualizada exitosamente', $oportunity);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la oportunidad', $e->getMessage(), 500, 'UPDATE_OPPORTUNITY_ERROR');
        }
    }

    public function firstAttempt(FirstAttemptRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $oportunity = $this->opportunityService->firstAttempt($data, $user);

            return ApiResponseHelper::apiSuccess(200, 'Intento de contacto enlazado exitosamente', $oportunity);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al vincular el intento de contacto', $e->getMessage(), 500, 'CREATE_CONTACT_ATTEMPT_ERROR');
        }
    }


    public function followAttempt(FollowAttemptRequest $request)
    {
        try {

            $user = auth()->user();

            $data = $request->validated();

            $oportunity = $this->opportunityService->followAttempt($data, $user);

            return ApiResponseHelper::apiSuccess(200, 'Intento de contacto enlazado exitosamente', $oportunity);

        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al vincular el intento de contacto', $e->getMessage(), 500, 'CREATE_CONTACT_ATTEMPT_ERROR');
        }
    }
}
