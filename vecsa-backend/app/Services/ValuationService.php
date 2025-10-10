<?php

namespace App\Services;

use App\Models\CustomerAppointment;
use App\Models\Dealership;
use App\Models\User;
use App\Models\Valuations\AcquisitionCheckpoint;
use App\Models\Valuations\UserValuation;
use App\Models\Valuations\ValuationCheckpoint;
use App\Models\Valuations\VehicleValuation;

class ValuationService
{   
    protected $valuation_checkpoints_ids = [
        1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,
        21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,
        41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,
        61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,
        81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,
        101,102,103,104,105,106,107,108,109,110,111,112,113,114,115
    ];

    protected $acquisition_checkpoints_ids = [
        1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,
        21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,
        41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,
        61,62,63,64,65,66,67,68,69
    ];

    protected $nullable_checkpoints_ids = [60, 84, 107]; // Checkpoints que pueden ser nulll 


    /**
     * Crea una valuación limpia.
     *
     * @param CustomerAppointment $appointment - Cita para crear la valuación
     */
    public function createValuation(CustomerAppointment $appointment, User $user)
    {

        $roleProfile = $user->getRoleProfile();
        $role = $roleProfile['role'];

        $dealership = Dealership::where([
            'name' => $appointment->dealership_name,
        ])->first();
        
        if (!$dealership) {
            throw new \Exception("Concesionaria no encontrada para el nombre: {$appointment->dealership_name}");
        }

        $valuation = VehicleValuation::create([
            'appointment_id' => $appointment->id,
            'dealership_id' => $dealership->id
        ]);

        if (!$valuation) {
            throw new \Exception("Error al crear la valuación para la cita con ID: {$appointment->id}");
        }

        $checkpoints = ValuationCheckpoint::whereIn('id', $this->valuation_checkpoints_ids)->get();

        $valuation->checkpoints()->attach($checkpoints);

        $acquisition_checkpoints = AcquisitionCheckpoint::whereIn('id', $this->acquisition_checkpoints_ids)->get();

        $valuation->acquisition_checkpoints()->attach($acquisition_checkpoints);
        
        UserValuation::firstOrCreate([
            'user_role_name' => $role,
            'user_id' => $user->id,
            'valuation_id' => $valuation->id
        ]);

        return $valuation;
    }

    public function statusAcquisition( VehicleValuation $valuation) {

        $hasNullSelectedValue = $valuation->acquisition_checkpoints() // Usar despues nullableCheckpoints
            ->wherePivot('selected_value', null)
            ->whereNotIn('checkpoint_id', [69])
            ->exists();

        return !$hasNullSelectedValue;

    }

    public function statusMechanicAndElectric( VehicleValuation $valuation) {

        $hasNullSelectedValue = $valuation->checkpoints() // Usar despues nullableCheckpoints
            ->wherePivot('selected_value', null)
            ->whereNotIn('checkpoint_id', $this->nullable_checkpoints_ids)
            ->exists();

        return !$hasNullSelectedValue;

    }

    public function statusRepairs( VehicleValuation $valuation) {

        $hasOnHoldStatus = $valuation->repairs()
            ->where('status', 'on_hold')
            ->exists();

        return !$hasOnHoldStatus;

    }

    public function statusParts( VehicleValuation $valuation) {

        $hasOnHoldStatus = $valuation->spareParts()
            ->where('status', 'on_hold')
            ->exists();

        return !$hasOnHoldStatus;

    }

}
