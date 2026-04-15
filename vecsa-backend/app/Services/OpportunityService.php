<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerAppointment;
use App\Models\Strega\Opportunity;
use App\Models\Strega\Campaign;
use App\Models\Strega\ContactAttempt;
use App\Models\Strega\FollowUpSurvey;
use App\Models\Strega\Form;
use App\Models\Strega\OpportunityAppointment;
use App\Models\Strega\UserOpportunity;
use App\Models\User;
use App\Models\UserAppointment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class OpportunityService
{   
    protected $userService;

    protected $customer_keys = [
        'name', 'last_name', 'email', 'phone', 'educational_level', 'contact_method', 'honorific', 'crm_id', 'gender', 'rfc', 'marital_status', 'educational_level', 'address', 'neighborhood', 'zip_code', 'state', 'district'
    ];

    protected $questions_keys = [
        'q_model_interest', 'q_brand_interest', 'q_time_to_buy', 'q_type_of_buy', 'q_initial_investment', 'q_comments'
    ];

    protected $opportunity_keys = [
        'opportunity_type', 'opportunity_date', 'opportunity_id', 'opportunity_category', 'dealership_name', 'opportunity_status'
    ];

    protected $campaign_keys = [
        'campaign_name', 'campaign_channel', 'campaign_source',
    ];

    protected $contact_keys = [
        'contact_station_name', 'contact_take_date', 'contact_comment', 'contact_attempt_status', 'contact_attempt_description', 'contact_transfer_attempt_status','contact_transfer_attempt_description', 'contact_channel',
    ];

    protected $appointment_keys = [
        'appointment_assignment', 'appointment_date', 'appointment_seller', 'appointment_comments',
    ];

    protected $followup_attempt_keys = [
        'fu_survey_status', 'fu_survey_comment', 'fu_survey_ticket_complain_comment', 'fu_survey_satisfaction_comments',
    ];

    protected $followup_survey_keys = [
        'fu_survey_ticket_complain', 'fu_survey_csi', 'fu_survey_satisfaction',
    ];
    
    protected $form_ids = [1,2,3,4,5,6];

    protected $follow_up_ids = [1,2,3];

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Crea o actualiza una oportunidad en la base de datos.
     *
     * @param array $data Datos de la oportunidad a crear o actualizar.
     * @return Opportunity La oportunidad creada o actualizada.
     */
    public function createOrUpdateOpportunity($data, $user_id)
    {
        // Extraer los datos del customer      
        $customerSubset = array_intersect_key($data, array_flip($this->customer_keys));

        // Crear o actualizar el customer
        $customer = Customer::firstOrNew([
            'name' => $customerSubset['name'],
            'last_name' => $customerSubset['last_name'] ?? '_',
            'email_1' => $customerSubset['email'] ?? null,
            'phone_1' => $customerSubset['phone'] ?? null,
        ]);

        // Guardar el cliente 
        $customer->save();

        if ($customer->wasRecentlyCreated) {
            $query = Customer::query();
        
            // Optimizar la construcción de la consulta para evitar redundancia.
            $query->orWhere('name', $customerSubset['name'] ?? null)
                  ->orWhere('last_name', $customerSubset['last_name'] ?? '_');
        
            if (!empty($customerSubset['email'])) {
                $query->orWhere('email_1', $customerSubset['email'])
                      ->orWhere('email_2', $customerSubset['email']);
            }
        
            if (!empty($customerSubset['phone'])) {
                $query->orWhere('phone_1', $customerSubset['phone'])
                      ->orWhere('phone_2', $customerSubset['phone']);
            }
            
            $query->where('id', '!=', $customer->id);

            $existingCustomers = $query->get();
        
            // Vincular relaciones de manera optimizada.
            $customer->relations()->syncWithoutDetaching($existingCustomers->pluck('id')->unique());
        
            // Log::alert($existingCustomers);
        }

        // Extraer los datos del campaign      
        $campaignSubset = array_intersect_key($data, array_flip($this->campaign_keys));

        $campaign = Campaign::firstOrNew([
            'source' => $campaignSubset['campaign_source'],
            'name' => isset($campaignSubset['campaign_name']) ? $campaignSubset['campaign_name'] : 'Sin Nombre',
            'channel' => $campaignSubset['campaign_channel'],
        ]);

        $campaign->save();

        // Extraer los datos del campaign      
        $opportunitySubset = array_intersect_key($data, array_flip($this->opportunity_keys));

        // Crear lead del customer
        $opportunity = Opportunity::create([
            'type' => $opportunitySubset['opportunity_type'],
            'status' => $opportunitySubset['opportunity_status'],
            'category' => $opportunitySubset['opportunity_category'] ?? null,
            'dealership_name' => $opportunitySubset['dealership_name'],
            'created_at' => $this->formatFecha($opportunitySubset['opportunity_date']),
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id
        ]);

        // Vincular preguntas y respuestas obtenidas
        $forms = Form::whereIn('id', $this->form_ids)->get();

        $opportunity->forms()->attach($forms);

        $opportunity_forms = $opportunity->forms()->get();

        $questionsSubset = array_intersect_key($data, array_flip($this->questions_keys));

        foreach ($opportunity_forms as $index => $opportunity_form) {

            // Utilizar el ID del formulario para obtener el valor correcto
            if (isset($questionsSubset[$this->questions_keys[$index]])) {
                
                $form_value = (string) $questionsSubset[$this->questions_keys[$index]];

                $opportunity_form->pivot->selected_value = $form_value;

                $opportunity_form->pivot->save();

            }
        }

        // Extraer los datos del intento de contactación      
        $contactSubset = array_intersect_key($data, array_flip($this->contact_keys));
            
        ContactAttempt::create([
            'status' => $contactSubset['contact_attempt_status'],
            'description' => isset($contactSubset['contact_attempt_description']) ? $contactSubset['contact_attempt_description'] : 'on_hold',
            'transfer_status' => $contactSubset['contact_transfer_attempt_status'],
            'transfer_description'  => $contactSubset['contact_transfer_attempt_description'],
            'contact_channel' => $contactSubset['contact_channel'],
            'comments' => $contactSubset['contact_comment'],
            'created_at' => $this->formatFecha($contactSubset['contact_take_date']),
            'opportunity_id' => $opportunity->id
        ]);

        $lead_manager = User::findByName($contactSubset['contact_station_name'], 'strega-manager');

        if($lead_manager){

            UserOpportunity::create([
                'user_role_name' => 'strega-manager',
                'activity' => 'assigned',
                'user_id' => $lead_manager->id,
                'opportunity_id' => $opportunity->id,
                'customer_id' => $customer->id
            ]);

            // Extraer los datos de la cita o actividad
            $appointmentSubset = array_intersect_key($data, array_flip($this->appointment_keys));

            if(isset($appointmentSubset['appointment_assignment']) && $appointmentSubset['appointment_assignment'] == 'SI'  && isset($appointmentSubset['appointment_seller']) && ($appointmentSubset['appointment_seller'] != '' || $appointmentSubset['appointment_seller'] != null) ){

                $seller = User::findByName($appointmentSubset['appointment_seller'], 'strega-seller');

                $customer_appointment = CustomerAppointment::create([
                    'type' => 'call',
                    'description' => $contactSubset['contact_attempt_description'],
                    'contact_channel' => $contactSubset['contact_channel'],
                    'scheduled_date' => $this->formatFecha($appointmentSubset['appointment_date']),
                    'dealership_name' => $opportunitySubset['dealership_name'],
                    'customer_id' => $customer->id,
                ]);

                // Unir customer_appointment con user (opportunity y appointment)
                OpportunityAppointment::create([
                    'opportunity_id' => $opportunity->id,
                    'appointment_id' => $customer_appointment->id,
                ]);

                // Unir customer_appointment con user (seller y contact)
                UserAppointment::create([
                    'user_role_name' => 'strega-manager',
                    'activity' => 'Asignacion de cita',
                    'user_id' => $lead_manager->id,
                    'appointment_id' => $customer_appointment->id,
                ]);

                // Condicionar si existe el seller
                UserAppointment::create([
                    'user_role_name' => 'strega-seller',
                    'activity' => 'Cita asignada',
                    'user_id' => $seller->id,
                    'appointment_id' => $customer_appointment->id,
                ]);

                $followUpAttemptSubset = array_intersect_key($data, array_flip($this->followup_attempt_keys));

                if( isset($followUpAttemptSubset['fu_survey_status']) ){

                    $comment = '';
                    $description = '';
                    
                    if ( isset($followUpAttemptSubset['fu_survey_comment']) ){
                        $description = $followUpAttemptSubset['fu_survey_comment'];
                    } elseif ( isset($followUpAttemptSubset['fu_survey_ticket_complain_comment']) ){
                        $description = $followUpAttemptSubset['fu_survey_ticket_complain_comment'];
                    } 
                    
                    if ( isset($followUpAttemptSubset['fu_survey_satisfaction_comments']) ) {
                        $comment = $followUpAttemptSubset['fu_survey_satisfaction_comments'];
                    }

                    ContactAttempt::create([
                        'type' => 'follow_attempt',
                        'status' => $followUpAttemptSubset['fu_survey_status'],
                        'description' => $description,
                        'comments' => $comment,
                        'appointment_id' => $customer_appointment->id,
                    ]);

                    $followUpSubset = array_intersect_key($data, array_flip($this->followup_survey_keys));

                    $follow_ups = FollowUpSurvey::whereIn('id', $this->follow_up_ids)->get();

                    $customer_appointment->surveys()->attach($follow_ups);

                    $appointment_surveys = $customer_appointment->surveys()->get();

                    foreach ($appointment_surveys as $index => $appointment_survey) {

                        if (isset($followUpSubset[$this->followup_survey_keys[$index]])) {
                            
                            $survey_value = (string) $followUpSubset[$this->followup_survey_keys[$index]];

                            $appointment_survey->pivot->selected_value = $survey_value;

                            $appointment_survey->pivot->save();

                        }
                    }
                }
            }
        }
        
        return $opportunity;
    }


    /**
     * Crea una oportunidad en la base de datos.
     *
     * @param array $data Datos de la oportunidad a crear.
     * @return Opportunity La oportunidad creada o actualizada.
     */
    public function createOpportunity($data, $user)
    {
        // Extraer los datos del customer      
        $customerSubset = array_intersect_key($data, array_flip($this->customer_keys));

        // Crear o actualizar el customer
        $customer = Customer::create([
            'honorific' => isset($customerSubset['honorific']) ? $customerSubset['honorific'] : '',
            'name' => $customerSubset['name'],
            'last_name' => $customerSubset['last_name'],
            'email_1' => $customerSubset['email'],
            'phone_1' => $customerSubset['phone'],
        ]);

        $query = Customer::query();
    
        $query->orWhere('name', $customerSubset['name'] ?? null)
                ->orWhere('last_name', $customerSubset['last_name'] ?? '_');
    
        if (!empty($customerSubset['email'])) {
            $query->orWhere('email_1', $customerSubset['email'])
                    ->orWhere('email_2', $customerSubset['email']);
        }
    
        if (!empty($customerSubset['phone'])) {
            $query->orWhere('phone_1', $customerSubset['phone'])
                    ->orWhere('phone_2', $customerSubset['phone']);
        }
        
        $query->where('id', '!=', $customer->id);

        $existingCustomers = $query->get();
    
        $customer->relations()->syncWithoutDetaching($existingCustomers->pluck('id')->unique());
        
        $campaignSubset = array_intersect_key($data, array_flip($this->campaign_keys));

        $campaign = Campaign::firstOrNew([
            'source' => isset($campaignSubset['campaign_source']) ? $campaignSubset['campaign_source'] : 'Sin Fuente',
            'name' => isset($campaignSubset['campaign_name']) ? $campaignSubset['campaign_name'] : 'Sin Nombre',
            'channel' => isset($campaignSubset['campaign_channel'])  ? $campaignSubset['campaign_channel'] : 'Sin Canal' ,
        ]);

        $campaign->save();

        // Extraer los datos del campaign
        $opportunitySubset = array_intersect_key($data, array_flip($this->opportunity_keys));

        // Crear lead del customer
        $opportunity = Opportunity::create([
            'type' => $opportunitySubset['opportunity_type'],
            'status' => 'Prospeccion',
            'category' => $opportunitySubset['opportunity_category'] ?? null,
            'dealership_name' => $opportunitySubset['dealership_name'],
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id
        ]);

        // Vincular preguntas y respuestas obtenidas
        $forms = Form::whereIn('id', $this->form_ids)->get();

        $opportunity->forms()->attach($forms);

        $opportunity_forms = $opportunity->forms()->get();

        $questionsSubset = array_intersect_key($data, array_flip($this->questions_keys));

        foreach ($opportunity_forms as $index => $opportunity_form) {
            if (isset($questionsSubset[$this->questions_keys[$index]])) {
                $form_value = (string) $questionsSubset[$this->questions_keys[$index]];
                $opportunity_form->pivot->selected_value = $form_value;
                $opportunity_form->pivot->save();
            }
        }
        
        UserOpportunity::create([
            'user_role_name' => 'strega-manager',
            'activity' => 'assigned',
            'user_id' => $user->id,
            'opportunity_id' => $opportunity->id,
        ]);

        return $opportunity;
    }


    /**
     * Crea una oportunidad en la base de datos.
     *
     * @param array $data Datos de la oportunidad a crear.
     * @return Opportunity La oportunidad creada o actualizada.
     */
    public function createPublicOpportunity($data)
    {
        // Extraer los datos del customer      
        $customerSubset = array_intersect_key($data, array_flip($this->customer_keys));

        // Crear o actualizar el customer  // Encontrar Customer por email y poner la información del lead solamente
        // Loggear al usuario para evitar duplicidad

        $customer = Customer::create([
            'honorific' => isset($customerSubset['honorific']) ? $customerSubset['honorific'] : null,
            'name' => $customerSubset['name'],
            'last_name' => $customerSubset['last_name'],
            'email_1' => $customerSubset['email'],
            'phone_1' => $customerSubset['phone'],
            'gender' => isset($customerSubset['gender']) ? $customerSubset['gender'] : null,
            'rfc' => isset($customerSubset['rfc']) ? $customerSubset['rfc'] : null,
            'marital_status' => isset($customerSubset['marital_status']) ? $customerSubset['marital_status'] : null,
            'educational_level' => isset($customerSubset['educational_level']) ? $customerSubset['educational_level'] : null,
            'address' => isset($customerSubset['address']) ? $customerSubset['address'] : null,
            'neighborhood' => isset($customerSubset['neighborhood']) ? $customerSubset['neighborhood'] : null,
            'zip_code' => isset($customerSubset['zip_code']) ? $customerSubset['zip_code'] : null,
            'state' => isset($customerSubset['state']) ? $customerSubset['state'] : null,
            'district' => isset($customerSubset['district']) ? $customerSubset['district'] : null,
        ]);

        $query = Customer::query();
    
        $query->orWhere('name', $customerSubset['name'] ?? null)
                ->orWhere('last_name', $customerSubset['last_name'] ?? '_');
    
        if (!empty($customerSubset['email'])) {
            $query->orWhere('email_1', $customerSubset['email'])
                    ->orWhere('email_2', $customerSubset['email']);
        }
    
        if (!empty($customerSubset['phone'])) {
            $query->orWhere('phone_1', $customerSubset['phone'])
                    ->orWhere('phone_2', $customerSubset['phone']);
        }
        
        $query->where('id', '!=', $customer->id);

        $existingCustomers = $query->get();
    
        $customer->relations()->syncWithoutDetaching($existingCustomers->pluck('id')->unique());
        
        $campaignSubset = array_intersect_key($data, array_flip($this->campaign_keys));

        $campaign = Campaign::firstOrNew([
            'source' => isset($campaignSubset['campaign_source']) ? $campaignSubset['campaign_source'] : 'Sin Fuente',
            'name' => isset($campaignSubset['campaign_name']) ? $campaignSubset['campaign_name'] : 'Sin Nombre',
            'channel' => isset($campaignSubset['campaign_channel'])  ? $campaignSubset['campaign_channel'] : 'Sin Canal' ,
        ]);

        $campaign->save();

        // Extraer los datos del campaign
        $opportunitySubset = array_intersect_key($data, array_flip($this->opportunity_keys));

        // Crear lead del customer
        $opportunity = Opportunity::create([
            'type' => $opportunitySubset['opportunity_type'],
            'status' => 'Prospeccion',
            'category' => $opportunitySubset['opportunity_category'] ?? null,
            'dealership_name' => $opportunitySubset['dealership_name'],
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id
        ]);

        // Vincular preguntas y respuestas obtenidas
        $forms = Form::whereIn('id', $this->form_ids)->get();

        $opportunity->forms()->attach($forms);

        $opportunity_forms = $opportunity->forms()->get();

        $questionsSubset = array_intersect_key($data, array_flip($this->questions_keys));

        foreach ($opportunity_forms as $index => $opportunity_form) {
            if (isset($questionsSubset[$this->questions_keys[$index]])) {
                $form_value = (string) $questionsSubset[$this->questions_keys[$index]];
                $opportunity_form->pivot->selected_value = $form_value;
                $opportunity_form->pivot->save();
            }
        }

        return $opportunity;
    }


    /**
     * Actualiza una oportunidad en la base de datos.
     *
     * @param array $data Datos de la oportunidad a crear.
     * @return Opportunity La oportunidad creada o actualizada.
     */
    public function updateOpportunity($data, $user)
    {
        // Extraer los datos del customer      
        $customerSubset = array_intersect_key($data, array_flip($this->customer_keys));

        $customer = Customer::findByUuid($data['customer_uuid']);

        // Crear o actualizar el customer
        $customer->update([
            'honorific' => $customerSubset['honorific'],
            'name' => $customerSubset['name'],
            'last_name' => $customerSubset['last_name'],
            'email_1' => $customerSubset['email'],
            'phone_1' => $customerSubset['phone'],
        ]);

        $campaignSubset = array_intersect_key($data, array_flip($this->campaign_keys));

        $campaign = Campaign::firstOrNew([
            'source' => isset($campaignSubset['campaign_source']) ? $campaignSubset['campaign_source'] : 'Sin Fuente',
            'name' => isset($campaignSubset['campaign_name']) ? $campaignSubset['campaign_name'] : 'Sin Nombre',
            'channel' => isset($campaignSubset['campaign_channel'])  ? $campaignSubset['campaign_channel'] : 'Sin Canal' ,
        ]);

        $campaign->save();

        $opportunity = Opportunity::findByUuid($data['opportunity_uuid']);

        $opportunitySubset = array_intersect_key($data, array_flip($this->opportunity_keys));

        $opportunity->update([
            'type' => $opportunitySubset['opportunity_type'],
            'category' => $opportunitySubset['opportunity_category'] ?? null,
            'dealership_name' => $opportunitySubset['dealership_name'],
            'campaign_id' => $campaign->id,
        ]);

        $opportunity_forms = $opportunity->forms()->get();

        $questionsSubset = array_intersect_key($data, array_flip($this->questions_keys));

        foreach ($opportunity_forms as $index => $opportunity_form) {
            
            $form_value = (string) $questionsSubset[$this->questions_keys[$index]];
            $opportunity_form->pivot->selected_value = $form_value;
            $opportunity_form->pivot->save();
        }

        return $opportunity;
    }


    /**
     * Enlazar una oportunidad con un intento de contactación.
     *
     * @param array $data Datos de la oportunidad y el intento de contactación.
     * @return Opportunity La oportunidad creada o actualizada.
     */
    public function firstAttempt($data, $user)
    {
        $opportunity = Opportunity::findByUuid($data['opportunity_uuid']);

        // Actualizar status de la oportunidad

        ContactAttempt::create([
            'status' => $data['contact_attempt_status'],
            'description' => $data['contact_attempt_description'],
            'contact_channel' => $data['contact_channel'],
            'comments' => $data['comments'],

            'transfer_status' => $data['contact_transfer_attempt_status'],
            // 'transfer_description'  => $data['contact_transfer_attempt_description'],
            
            'opportunity_id' => $opportunity->id
        ]);
 
        if( $data['contact_transfer_attempt_status'] != 'No' && $data['contact_transfer_attempt_status'] != null){

            $seller = User::findByUuid($data['seller_uuid']);

            $customer = Customer::findByUuid($data['customer_uuid']);

            $customer_appointment = CustomerAppointment::create([
                'type' => 'call',
                'description' => $data['contact_attempt_description'],
                'contact_channel' => $data['contact_channel'],
                'scheduled_date' => $data['appointment_date'],
                'dealership_name' => $data['dealership_name'],
                'customer_id' => $customer->id,
            ]);

            // Unir customer_appointment con user (opportunity y appointment)
            OpportunityAppointment::create([
                'opportunity_id' => $opportunity->id,
                'appointment_id' => $customer_appointment->id,
            ]);

            // Unir customer_appointment con user (seller y contact)
            UserAppointment::create([
                'user_role_name' => 'strega-manager',
                'activity' => 'Asignacion de cita',
                'user_id' => $user->id,
                'appointment_id' => $customer_appointment->id,
            ]);

            // Condicionar si existe el seller
            UserAppointment::create([
                'user_role_name' => 'strega-seller',
                'activity' => 'Cita asignada',
                'user_id' => $seller->id,
                'appointment_id' => $customer_appointment->id,
            ]);

        }
    }

    /**
     * Enlazar una oportunidad con un intento de contactación de seguimiento (experiencia).
     *
     * @param array $data Datos de la oportunidad y el intento de contactación.
     * @return Opportunity La oportunidad creada o actualizada.
     */
    public function followAttempt($data, $user)
    {
        $appointment = CustomerAppointment::findByUuid($data['appointment_uuid']);

        // Actualizar status de la oportunidad

        ContactAttempt::create([
            'type' => 'follow_attempt',
            'status' => $data['contact_attempt_status'],
            'description' => $data['contact_attempt_description'] ?? null,
            'contact_channel' => $data['contact_channel'] ?? null,
            'comments' => $data['comments'],
            'appointment_id' => $appointment->id
        ]);

        $followUpSubset = array_intersect_key($data, array_flip($this->followup_survey_keys));

        if(!empty($followUpSubset)){

            $appointment_surveys = $appointment->surveys()->get();

            if($appointment_surveys->isEmpty()){

                $follow_ups = FollowUpSurvey::whereIn('id', $this->follow_up_ids)->get();

                $appointment->surveys()->attach($follow_ups);

                $appointment_surveys = $appointment->surveys()->get();

            }

            foreach ($appointment_surveys as $index => $appointment_survey) {

                if (isset($followUpSubset[$this->followup_survey_keys[$index]])) {
                    
                    $survey_value = (string) $followUpSubset[$this->followup_survey_keys[$index]];

                    $appointment_survey->pivot->selected_value = $survey_value;

                    $appointment_survey->pivot->save();

                }
            }
        }
    }


    /**
     * Busca oportunidades en base a los criterios proporcionados.
     *
     * @param array $data Datos de búsqueda que incluyen condiciones y paginación.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Oportunidades encontradas.
     */
    public function searchOpportunitiesAdministrator($data)
    {
        // Crear la consulta base
        $query = Opportunity::query();
        
        // Aplicar las condiciones
        $query->with($data['relationship_names']);
        $query->where($this->statusCondition($data['status']));
        
        // Aplicar la condición de keyword si no es null
        
        $dealershipCondition = $this->dealershipCondition($data['by_dealership']);
        if ($dealershipCondition) {
            $query->where($dealershipCondition);
        }

        $typeCondition = $this->typeCondition($data['by_type']);
        if ($typeCondition) {
            $query->where($typeCondition);
        }

        $managerCondition = $this->managerCondition($data['by_manager']);
        if ($managerCondition) {
            $query->where($managerCondition);
        }
        
        $keywordCondition = $this->keywordCondition($data['keyword']);
        if ($keywordCondition) {
            $query->where($keywordCondition);
        }

        $query->orderBy('created_at', 'desc');

        $opportunities = $query->paginate($data['paginate']);

        // Obtener las oportunidades
        return $opportunities;
    }


    /**
     * Busca oportunidades en base a los criterios proporcionados.
     *
     * @param array $data Datos de búsqueda que incluyen condiciones y paginación.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Oportunidades encontradas.
     */
    public function searchOpportunitiesManager($data, $user)
    {
        // Crear la consulta base
        $query = $user->opportunities();

        if ($data['has_appointments']) {
            $query->whereHas('appointments');
        } 
        else {
            $query->doesntHave('appointments');
        }

        // Aplicar las condiciones
        $query->with($data['relationship_names']);
        
        if($data['attempts_type'] == 'first_contact'){
            $query->withCount('firstContactAttempts');
        } else {
            $query->withCount('experienceContactAttempts');
        }

        $query->where($this->statusCondition($data['status']));
        
        // Aplicar la condición de keyword si no es null
        
        $dealershipCondition = $this->dealershipCondition($data['by_dealership']);
        if ($dealershipCondition) {
            $query->where($dealershipCondition);
        }

        $typeCondition = $this->typeCondition($data['by_type']);
        if ($typeCondition) {
            $query->where($typeCondition);
        }
        
        $keywordCondition = $this->keywordCondition($data['keyword']);
        if ($keywordCondition) {
            $query->where($keywordCondition);
        }

        $oportunities = $query->paginate($data['paginate']);

        // Obtener las oportunidades
        return $oportunities;
    }

    /**
     * Busca oportunidades asignadas al vendedor Strega (mismo criterio que gerente, distinto pivot).
     *
     * @param  array  $data  Datos de búsqueda que incluyen condiciones y paginación.
     * @param  \App\Models\User  $user  Usuario con rol strega-seller.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchOpportunitiesSeller($data, $user)
    {
        $query = $user->stregaSellerOpportunities();

        if ($data['has_appointments']) {
            $query->whereHas('appointments');
        } else {
            $query->doesntHave('appointments');
        }

        $query->with($data['relationship_names']);

        if ($data['attempts_type'] == 'first_contact') {
            $query->withCount('firstContactAttempts');
        } else {
            $query->withCount('experienceContactAttempts');
        }

        $query->where($this->statusCondition($data['status']));

        $dealershipCondition = $this->dealershipCondition($data['by_dealership']);
        if ($dealershipCondition) {
            $query->where($dealershipCondition);
        }

        $typeCondition = $this->typeCondition($data['by_type']);
        if ($typeCondition) {
            $query->where($typeCondition);
        }

        $keywordCondition = $this->keywordCondition($data['keyword']);
        if ($keywordCondition) {
            $query->where($keywordCondition);
        }

        return $query->paginate($data['paginate']);
    }

    /**
     * Busca oportunidades en base a los criterios proporcionados.
     *
     * @param array $data Datos de búsqueda que incluyen condiciones y paginación.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator Oportunidades encontradas.
     */
    public function searchAppointmentsManager($data, $user)
    {

        $query = $user->opportunities();

        $query->whereHas('appointments');
        
        $dealershipCondition = $this->dealershipCondition($data['by_dealership']);
        if ($dealershipCondition) {
            $query->where($dealershipCondition);
        }

        $typeCondition = $this->typeCondition($data['by_type']);
        if ($typeCondition) {
            $query->where($typeCondition);
        }

        $managerCondition = $this->managerCondition($data['by_manager']);
        if ($managerCondition) {
            $query->where($managerCondition);
        }

        $keywordCondition = $this->keywordCondition($data['keyword']);
        if ($keywordCondition) {
            $query->where($keywordCondition);
        }

        $opportunityIds = $query->pluck('app_strega_opportunities.id');



        $appointments = CustomerAppointment::whereHas('opportunities', function ($query) use ($opportunityIds) {
            $query->whereIn('app_strega_opportunities.id', $opportunityIds);
        })
        ->where('status', 'on_hold')
        ->with([
            'opportunities.campaign',
            'customer'
        ])
        ->withCount('followAttempts')
        ->paginate($data['paginate']);

        // Modifica los resultados después de obtener la paginación
        $appointments->getCollection()->transform(function ($appointment) {
            $appointment->setRelation('opportunity', $appointment->opportunities->first());
            unset($appointment->opportunities);
            return $appointment;
        });

        return $appointments;
    }

    /**
     * Crea una condición para filtrar oportunidades por status.
     *
     * @param array $status Estatus de la oportunidad para filtrar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function statusCondition(array $status)
    {
        return function ($query) use ($status) {
            if (!empty($status)) {
                $query->whereIn('status', $status);
            } else {
                $query->where('status', '!=', 'closed');
            }
        };
    }

    /**
     * Crea una condición para buscar oportunidades por una palabra clave en múltiples campos.
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
            $query->whereHas('customer', function ($query) use ($keyword) {
                $query->where('name', 'LIKE', $keyword)
                      ->orWhere('last_name', 'LIKE', $keyword)
                      ->orWhere('email_1', 'LIKE', $keyword);
            })->orWhere('uuid', 'LIKE', $keyword);
        };
    }

    /**
     * Crea una condición para buscar oportunidades por surcursal.
     *
     * @param string $by_dealership Palabra clave para buscar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function dealershipCondition($by_dealership)
    {
        // Si el by_dealership está vacío, no retornar una condición
        if (empty($by_dealership)) {
            return null;
        }

        // Formatear el keyword para uso en LIKE
        $by_dealership = '%' . $by_dealership . '%';

        return function ($query) use ($by_dealership) {
                $query->where('dealership_name', 'LIKE', $by_dealership);
        };
    }

    /**
     * Crea una condición para buscar oportunidades por tipo.
     *
     * @param string $keyword Palabra clave para buscar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function typeCondition($by_type)
    {
        // Si el by_dealership está vacío, no retornar una condición
        if (empty($by_type)) {
            return null;
        }

        // Formatear el keyword para uso en LIKE
        $by_type = '%' . $by_type . '%';

        return function ($query) use ($by_type) {
                $query->where('type', 'LIKE', $by_type);
        };
    }

    /**
     * Crea una condición para buscar oportunidades por manager uuid.
     *
     * @param string $keyword Palabra clave para buscar.
     * @return \Closure Función de condición para usar en la consulta.
     */
    public function managerCondition($by_manager)
    {
        // Si el by_dealership está vacío, no retornar una condición
        if (empty($by_manager)) {
            return null;
        };

        return function ($query) use ($by_manager) {
            $query->whereHas('manager', function ($subQuery) use ($by_manager) {
                $subQuery->where('uuid', $by_manager)
                         ->where('app_strega_user_opportunity.user_role_name', 'strega-manager')
                         ->orderBy('app_strega_user_opportunity.created_at', 'desc')
                         ->limit(1);
            });
        };
    }

    function formatFecha($fecha)
    {
        // Configura el idioma de Carbon a español
        Carbon::setLocale('es');

        // Array de formatos esperados
        $formatos = [
            'l, F j, Y g:i A',  // Ej: "Martes, Noviembre 5, 2024 7:05 PM"
            'j/n/Y',            // Ej: "1/11/2024"
            'd/m/Y H:i',        // Ej: "01/11/2024 10:41"
            'Y-m-d H:i:s',      // Ej: "2024-11-01 16:14:18"
            'l, F j, Y',        // Para fechas solo con día de la semana y mes
        ];

        foreach ($formatos as $formato) {
            try {
                // Intenta crear una instancia de Carbon usando el formato actual
                return Carbon::createFromFormat($formato, $fecha)->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                // Ignorar excepciones y continuar con el siguiente formato
            }
        }

        // Si no coincide con ninguno de los formatos, devuelve null o lanza una excepción
        return Carbon::now()->format('Y-m-d H:i:s');
    }

}
