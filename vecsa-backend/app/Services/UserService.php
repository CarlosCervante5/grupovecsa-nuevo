<?php

namespace App\Services;

use App\Helpers\PasswordHelper;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Valuations\ValuationUpdate;
use App\Models\VehicleUpdate;
use App\Notifications\InternallyRegisterNotification;
use App\Notifications\RegisterNotification;
use Faker\Factory as Faker;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{       

    protected $quiz_ids = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48];


    /**
     * Crea un vehicle update con la información proporcionada.
     *
     * @param string $api_name Nombre de la api consumida.
     * @param string $replaced_json Datos originales a reemplazar.
     * @param string $request_json Datos enviados al endpoint.
     * @param int $user_id id del usuario que envia la solicitud.
     * @param int $vehicle_id del vehículo.
     * @return void
    */
    public function vehicleUpdate( $api_name, $replaced_json = null ,$request_json,  $user_id = null, $vehicle_id = null) : void
    {
        VehicleUpdate::create([
            'api_name' => $api_name,
            'replaced_json' => $replaced_json,
            'request_json' => $request_json,
            'user_id' => $user_id,
            'vehicle_id' => $vehicle_id]);
    }


    /**
     * Crea un valuation update con la información proporcionada.
     *
     * @param string $api_name Nombre de la api consumida.
     * @param string $replaced_json Datos originales a reemplazar.
     * @param string $request_json Datos enviados al endpoint.
     * @param int $user_id id del usuario que envia la solicitud.
     * @param int $vehicle_id del vehículo.
     * @return void
    */
    public function valuationUpdate( $api_name, $replaced_json = null ,$request_json,  $user_id = null, $valuation_id = null) : void
    {
        ValuationUpdate::create([
            'api_name' => $api_name,
            'replaced_json' => $replaced_json,
            'request_json' => $request_json,
            'user_id' => $user_id,
            'valuation_id' => $valuation_id]);
    }

    public function handleUser(User $user, array $data)
    {
        $expiresAt = now()->addHour();
        $token = $user->createToken('Login: ' . $user->email, ['*'], $expiresAt);

        $user->customerProfile()->update([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'email_1' => $data['email']  ?? null,
            'phone_1' => $data['phone_1'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'origin_agency' => $data['origin_agency'] ?? null,
        ]);

        $role_profile = $user->getRoleProfile();

        return [
            'token' => $token->plainTextToken,
            'user' => [
                'uuid' => $user->uuid,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ],
            'role' => $role_profile['role'],
            'profile' => $role_profile['profile'],
        ];
    }

    public function createNewCustomer(array $data)
    {
        $faker = Faker::create();
        $userCreated = false;

        if( !isset($data['password']) ){

            $data['password'] = PasswordHelper::generateSecurePassword(15);

            $notification = new InternallyRegisterNotification($data['password']);

        } else {
            
            $notification = new RegisterNotification();
        }

        while (!$userCreated) {
            try {
                $nickname = $faker->unique()->userName;

                $user = User::create([
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'nickname' => $nickname,
                ]);

                $user->assignRole('client');
                $userCreated = true;
                
            } catch (QueryException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }

        $profile = $user->customerProfile()->create([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'email_1' => $data['email'],
            'phone_1' => $data['phone_1'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'origin_agency' => $data['origin_agency'] ?? null,
        ]);

        $quizzes = Quiz::whereIn('id', $this->quiz_ids)->get();

        $profile->quizzes()->attach($quizzes);

        $expiresAt = now()->addHour();
        $token = $user->createToken('Login: ' . $user->email, ['*'], $expiresAt);
        $role = $user->getRoleNames()->first();

        try {
            $user->notify($notification);
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar correo de bienvenida al registrarse', [
                'user_uuid' => $user->uuid,
                'email' => $user->email,
                'message' => $e->getMessage(),
            ]);
        }

        return [
            'token' => $token->plainTextToken,
            'user' => [
                'uuid' => $user->uuid,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ],
            'role' => $role,
            'profile' => $profile
        ];
    }

    public function createNewUser(array $data)
    {   

        $faker = Faker::create();
        $userCreated = false;

        while (!$userCreated) {
            try {
                $nickname = $faker->unique()->userName;

                $user = User::create([
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'nickname' => $nickname,
                ]);

                $user->assignRole($data['role_name']);
                $userCreated = true;
                
            } catch (QueryException $e) {
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }

        $user->userProfile()->create([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'phone_1' => $data['phone_1'] ?? null,
            'phone_2' => $data['phone_2'] ?? null,
            'gender' => $data['gender'] ?? null,
            'location' => $data['location'],
        ]);

        return $user;
    }
}
