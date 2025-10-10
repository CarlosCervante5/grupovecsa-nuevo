<?php

namespace App\Http\Controllers\Customers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\DetailCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Http\Requests\Files\UploadCustomerImageRequest;
use App\Jobs\UploadProfileImage;
use App\Models\Customer;
use App\Services\UserService;

class CustomerController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    public function detail(DetailCustomerRequest $request)
    {
        try {

            $data = $request->validated();

            $customer = Customer::findByUuid($data['customer_uuid']);

            return ApiResponseHelper::authSuccess(200, 'Cliente encontrado', $customer);

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al obtener el cliente', $e->getMessage(), 500, 'GET_CUSTOMER_ERROR');
        }
    }

    public function update( UpdateCustomerRequest $request)
    {
        try {

            $data = $request->validated();

            $customer = Customer::findByUuid($data['customer_uuid']);

            $data = array_filter($data, function ($value) {
                return !is_null($value) && $value !== '';
            });

            foreach ($data as $key => $value) {
                if (in_array($key, ['honorific','bp_id','crm_id','rfc','tax_regime','name','last_name','age','birthday','gender','phone_1','phone_2','phone_3','cellphone','email_1','email_2','contact_method','zip_code','address','state','city','district','neighborhood','marital_status','educational_level','origin_agency',])) {
                    $customer->$key = $value;
                }
            }
            
            $customer->save();

            return ApiResponseHelper::apiSuccess(200, 'Cliente actualizado');

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al obtener el cliente', $e->getMessage(), 500, 'GET_CUSTOMER_ERROR');
        }
    }

    /**
     * Actualizar imágen del cliente
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateImage( UploadCustomerImageRequest $request )
    {
        try {

            $customer_uuid = $request->input('customer_uuid');
            $image = $request->file('image');
            
            $customer = Customer::findByUuid($customer_uuid, ['user']);

            if (!$customer) {

                return ApiResponseHelper::apiError('El cliente no existe', 'No existe el id: '. $customer_uuid ,404, 'UPDATE_PROFILE_IMAGE_ERROR');
            }

            if (!$image->isValid()) {

                return ApiResponseHelper::apiError('Error al actualizar la imagen de perfil',  'Archivo era inválido o corrupto: ' . $image->getClientOriginalName(), 500, 'UPDATE_PROFILE_IMAGE_ERROR');
            }

            $path = $image->store('temp_images');

            UploadProfileImage::dispatchSync($path, $customer->user, $image->getClientOriginalName());

            return ApiResponseHelper::apiSuccess(200, 'Perfil actualizado');

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al actualizar la imagen de perfil', $e->getMessage(), 500, 'UPDATE_PROFILE_IMAGE_ERROR');
        }
    }
}
