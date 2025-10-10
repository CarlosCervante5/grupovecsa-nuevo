<?php

namespace App\Services;

use App\Mail\ValuationNotification;
use App\Models\Customer;
use App\Models\CustomerAppointment;
use App\Models\CustomerVehicle;
use Illuminate\Support\Facades\Mail;

class AppointmentService
{   
    /**
     * Crea una cita.
     *
     * @param array $data Datos de la cita a crear.
     */
    public function createAppointment($data)
    {
        $customer = Customer::findByUuid($data['customer_uuid']);

        $customer_vehicle = CustomerVehicle::firstOrCreate([
            'mileage' => $data['mileage'],
            'brand_name' => $data['brand_name'],
            'model_name' => $data['model_name'],
            'year' => $data['year'],
            'customer_id' => $customer->id
        ]);

        $customer_appointment = CustomerAppointment::create([
            'type' => $data['type'],
            'scheduled_date' => $data['scheduled_date'],
            'dealership_name' => $data['dealership_name'],
            'vehicle_id' => $customer_vehicle->id,
            'customer_id' => $customer->id
        ]);

        if( $data['dealership_name'] != 'vecsa hidalgo') {
            Mail::to(env('VALUATION_PUEBLA_MAIL', ''))->send(new ValuationNotification($customer, $customer_vehicle, $customer_appointment));
        } else {
            Mail::to(env('VALUATION_HIDALGO_MAIL', ''))->send(new ValuationNotification($customer, $customer_vehicle, $customer_appointment));
        }

        return $customer_appointment;
    }

}
