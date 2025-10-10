<?php

namespace App\Http\Controllers\Leads;

use App\Helpers\ApiResponseHelper;
use App\Helpers\GoogleSheetHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leads\StoreAskInfomationRequest;
use App\Http\Requests\Leads\StoreCarCareRequest;
use App\Http\Requests\Leads\StoreReceptionRequest;
use App\Http\Requests\Leads\StoreRidersQuiz;
use App\Mail\ReceptionNotification;
use App\Models\Leads\ReceptionForm;
use App\Models\Leads\RidersQuiz;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    public function askInfomation(StoreAskInfomationRequest $request){

        try {

            $data = $request->validated();

            $leadData = [
                'formType' => 'askInformation', // Identificador del formulario
                'nombre'   => $request->name,
                'apellido' => $request->lastname,
                'telefono' => $request->phone_1,
                'correo'   => $request->email,
            ];
        
            $additionalData = [
                'mensaje' => $request->comments,
                'fecha'   => now()->format('d-m-y H:i:s'),
            ];

            $webhookUrl = GoogleSheetHelper::getWebhookUrl('GOOGLE_SHEET_WEBHOOK_ASK_INFORMATION');

            $vehicles = Vehicle::vehiclesByUuids($data['vehicles_uuid'], ['brand']);

            $vehicles_uuid = $vehicles->pluck('uuid')->join(', ');
            $vehicleNames = $vehicles->pluck('name')->join(', ');
            $vehicleCategories = $vehicles->pluck('category')->join(', ');
            $brandNames = $vehicles->pluck('brand.name')->join(', ');
            
            $vehicleData = [
                'vehículos' => $vehicleNames,
                'marcas' => $brandNames,
                'Nuevo/Semi' => $vehicleCategories,
            ];

            $data['vehicles_uuid'] = $vehicles_uuid;

            // Crear una nueva líneas de marca
            // AskInformation::create($data);

            GoogleSheetHelper::sendToGoogleSheet($webhookUrl, $leadData, $vehicleData, $additionalData);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Solicitud de información almacenada correctamente.');

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear la solicitud de información', $e->getMessage(), 500, 'CREATE_ASK_INFORMATION_ERROR');
        }

    }

    public function receptionForm(StoreReceptionRequest $request){

        try {

            $data = $request->validated();

            // $reception_form = ReceptionForm::create($data);

            Carbon::setLocale('es');
            $mes_actual = strtoupper(Carbon::now()->isoFormat('MMMM'));
            $fecha = Carbon::parse($data['date']);

            $receptionFormOne = array(
                'formType'              => 'receptionForm', // Identificador del formulario
                // 'id'                    => $reception_form->id,
                'date'                  => $fecha->format('d/m/Y'),
                'hour'                  => $data['hour'],
                'salesAdvisor'          => $data['salesAdvisor'],
                'brand'                 => strtoupper($data['brand']),
                'departureTime'         => $data['departureTime'],
                'visitType'             => strtoupper($data['visitType']),
                'visitFirsTime'         => strtoupper($data['visitFirsTime']),
                'department'            => strtoupper($data['department']),
                'howFindOut'            => strtoupper($data['howFindOut']),
                'contactSub'            => $data['contactSub'],
                'clientName'            => strtoupper($data['clientName']),
                'clientAge'             => $data['clientAge'],
                'clientPhone'           => $data['clientPhone'],
                'clientEmail'           => $data['clientEmail'],
                'preferredMedium'       => strtoupper($data['preferredMedium']),
                'model'                 => strtoupper($data['model']),
                'year'                  => $data['year'],
                'version'               => strtoupper($data['version']),
                'color'                 => strtoupper($data['color']),
                'accessories'           => strtoupper($data['accessories']),
                'brandSecondOption'     => strtoupper($data['brandSecondOption']),
                'modelSecondOption'     => strtoupper($data['modelSecondOption']),
                'versionSecondOption'   => strtoupper($data['versionSecondOption']),
                'colorSecondOption'     => strtoupper($data['colorSecondOption']),
                'testDrive'             => strtoupper($data['testDrive']),
                'receivedQuote'         => strtoupper($data['receivedQuote']),
                'FAndI'                 => strtoupper($data['FAndI']),
                'leaveCarOnAccount'     => strtoupper($data['leaveCarOnAccount']),
                'customersCurrentCar'   => strtoupper($data['customersCurrentCar']),
                'hostes'                => $data['hostes'],
                'month'                 => $mes_actual,
                'saleType'              => strtoupper($data['saleType']),
                'wasClientProfile'      => strtoupper($data['wasClientProfile']),
                'wasApplicationTaken'   => strtoupper($data['wasApplicationTaken']),
                'financingType'         => strtoupper($data['financingType']),
                'initialInvestment'     => $data['initialInvestment'],
                'monthlyPayment'        => $data['monthlyPayment'],
                'termHowManyMonths'     => $data['termHowManyMonths'],
                'segment'               => strtoupper($data['segment']),
                'idCRM'                 => $data['idCRM'],
                'honorific'             => strtoupper($data['honorific']),
                'contactSalesplace' => $data['contactSalesplace'],
            );

            $receptionFormTwo = array(
                'formType'              => 'receptionForm', // Identificador del formulario
                // 'id'                     => $reception_form->id,
                'date'                   => $data['date'],
                'hour'                   => $data['hour'],
                'salesAdvisor'           => $data['salesAdvisor'],
                'salesAdvisorAssignment' => $data['salesAdvisor'] ? 'SI' : 'NO',
                'brand'                  => strtoupper($data['brand']),
                'visitType'              => $data['visitType'],
                'visitFirsTime'          => $data['visitFirsTime'],
                'department'             => $data['department'],
                'howFindOut'             => $data['howFindOut'],
                'contactSub'             => $data['contactSub'],
                'clientName'             => $data['clientName'],
                'clientAge'              => $data['clientAge'],
                'clientPhone'            => $data['clientPhone'],
                'clientEmail'            => $data['clientEmail'],
                'preferredMedium'        => $data['preferredMedium'],
                'model'                  => $data['model'],
                'year'                   => $data['year'],
                'version'                => $data['version'],
                'color'                  => $data['color'],
                'accessories'            => $data['accessories'],
                'brandSecondOption'      => $data['brandSecondOption'],
                'modelSecondOption'      => $data['modelSecondOption'],
                'versionSecondOption'    => $data['versionSecondOption'],
                'colorSecondOption'      => $data['colorSecondOption'],
                'testDrive'              => $data['testDrive'],
                'receivedQuote'          => $data['receivedQuote'],
                'FAndI'                  => $data['FAndI'],
                'leaveCarOnAccount'      => $data['leaveCarOnAccount'],
                'customersCurrentCar'    => $data['customersCurrentCar'],
                'channel'                => 'Control piso',
                'hostes'                 => $data['hostes'],
                'month'                  => $mes_actual,
                'saleType'               => strtoupper($data['saleType']),
                'wasClientProfile'       => strtoupper($data['wasClientProfile']),
                'wasApplicationTaken'    => strtoupper($data['wasApplicationTaken']),
                'financingType'          => strtoupper($data['financingType']),
                'initialInvestment'      => $data['initialInvestment'],
                'monthlyPayment'         => $data['monthlyPayment'],
                'termHowManyMonths'      => $data['termHowManyMonths'],
                'segment'                => strtoupper($data['segment']),
                'idCRM'                  => $data['idCRM'],
                'honorific'              => $data['honorific'],
            );

            $webhookUrl_1 = GoogleSheetHelper::getWebhookUrl('GOOGLE_SHEET_WEBHOOK_RECEPTION_1');

            GoogleSheetHelper::sendToGoogleSheet($webhookUrl_1, $receptionFormOne);

            $webhookUrl_2 = GoogleSheetHelper::getWebhookUrl('GOOGLE_SHEET_WEBHOOK_RECEPTION_2');

            GoogleSheetHelper::sendToGoogleSheet($webhookUrl_2, $receptionFormTwo);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Formulario de recepcion almacenado correctamente.');

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear el formulario de recepcion', $e->getMessage(), 500, 'CREATE_RECEPTION_FORM_ERROR');
        }
    }

    public function ridersQuiz(StoreRidersQuiz $request){

        try {

            $data = $request->validated();

            RidersQuiz::create($data);

            $fecha = Carbon::now()->format('Y-m-d H:i:s');

            $google_sheet = array(
                'fecha'    => $fecha,
                'nombre'   => $data['name'],
                'telefono' => $data['phone'],
                'correo'   => $data['email'],
                'modelo'   => $data['model'],
                'guantes'  => $data['gloves'],
                'chamarra'  => $data['jacket'],
                'calzado'  => $data['footwear'],
                'casco'  => $data['helmet'],
                'color'  => $data['color'],
            );

            $webhookUrl = GoogleSheetHelper::getWebhookUrl('GOOGLE_SHEET_WEBHOOK_RIDERS_QUIZ');

            GoogleSheetHelper::sendToGoogleSheet($webhookUrl, $google_sheet);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Cuestionario de riders almacenado correctamente.');

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear el cuestionario de riders', $e->getMessage(), 500, 'CREATE_RIDERS_QUIZ_ERROR');
        }
    }

    public function carCare(StoreCarCareRequest $request){

        try {

            $data = $request->validated();

            $fecha = Carbon::now()->format('Y-m-d H:i:s');

            $google_sheet = array(
                'fecha'    => $fecha,
                'nombre'   => $data['name'],
                'telefono' => $data['phone_1'],
                'correo'   => $data['email_1'],
                'modelo'   => $data['model_name'],
                'año'  => $data['year'],
                'sucursal'  => $data['dealership_name'],
                'servicio'  => $data['required_service'],
                'comentarios'  => $data['comments'],
                'marca'  => $data['brand_name'],
                'fecha_cita'  => $data['appointment_date'],
            );

            $webhookUrl = GoogleSheetHelper::getWebhookUrl('GOOGLE_SHEET_WEBHOOK_CAR_CARE');

            GoogleSheetHelper::sendToGoogleSheet($webhookUrl, $google_sheet);

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Cuestionario de care care almacenado correctamente.');

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear el cuestionario de care care', $e->getMessage(), 500, 'CREATE_CARE_CARE_ERROR');
        }
    }

    public function receptionNotification(){

        Mail::to('financiera.pachuca@bmwvecsa.com')->send(new ReceptionNotification);
        // Mail::to('olezama@codecreativity.mx')->send(new ReceptionNotification);

        return ApiResponseHelper::apiSuccess(201, 'Notificación enviada.');

    }
}
