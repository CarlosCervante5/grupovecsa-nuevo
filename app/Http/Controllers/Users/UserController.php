<?php

namespace App\Http\Controllers\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ByRoleUserRequest;
use App\Http\Requests\Users\DeleteUserRequest;
use App\Http\Requests\Users\DetailUserRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Jobs\UploadProfileImage;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Controlador para manejar operaciones relacionadas con usuarios.
 */
class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Obtener una lista de todos los usuarios.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Obtener todos los usuarios
            $users = User::whereHas('userProfile')->paginate(15);

            $users->getCollection()->transform(function ($user) {
                
                $profile = $user->getRoleProfile();
                
                $user->role = $profile['role'];
                $user->profile = $profile['profile'];

                return $user;
            });

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(200, 'Usuarios obtenidos exitosamente', $users);

        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener la lista de usuarios', $e->getMessage(), 500, 'GET_USERS_ERROR');
        }
    }

    /**
     * Crear un nuevo usuario.
     *
     * @param  \App\Http\Requests\Users\StoreUserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request)
    {
        try {
            // Validar los datos recibidos
            $data = $request->validated();

            $user = $this->userService->createNewUser($data);

            if(isset($data['image'])){

                $image = $request->file('image');

                if ($image->isValid()) {

                    $path = $image->store('temp_images');
    
                    UploadProfileImage::dispatchSync($path, $user, $image->getClientOriginalName());
                }
            }

            $user->getOrCreateToken();
            $profile = $user->getRoleProfile();

            // Retornar respuesta exitosa
            return ApiResponseHelper::apiSuccess(201, 'Usuario creado exitosamente', [
                'user' => [
                    'uuid' => $user->uuid,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'role' => $profile['role'],
                'profile' =>  $profile['profile']
            ]);

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);

        } catch (\Exception $e) {
            // Manejar otros errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al crear el usuario', $e->getMessage(), 500, 'CREATE_USER_ERROR');
        }
    }

    /**
     * Obtener un usuario específico por UUID.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(DetailUserRequest $request)
    {
        try {

            $data = $request->validated();

            $user = User::findByUuid($data['user_uuid']);

            if (!$user) {
                return ApiResponseHelper::authError('El usuario no se encuentra registrado', null, 401, 'GET_USER_ERROR');
            }

            $profile = $user->getRoleProfile();

            // Retornar el usuario encontrado
            return ApiResponseHelper::authSuccess(200, 'Usuario encontrado', [
                'user' => [
                    'uuid' => $user->uuid,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'role' => $profile['role'],
                'profile' =>  $profile['profile']
            ]);

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al obtener el usuario', $e->getMessage(), 500, 'GET_USER_ERROR');
        }
    }

    /**
     * Actualizar un usuario específico por UUID en la base de datos.
     *
     * @param  \App\Http\Requests\Users\UpdateUserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request)
    {

        try {

            $data = $request->validated();

            $user = User::findByUuid($data['user_uuid']);

            if (!$user) {
                return ApiResponseHelper::authError('El usuario no se encuentra registrado', null, 401, 'UPDATE_USER_ERROR');
            }

            $profile = $user->getRoleProfile();

            // Hashear la contraseña si se proporciona
            if (isset($data['password']) && $data['password']) {
                $data['password'] = Hash::make($data['password']);
                $user->password = $data['password'];
            }

            if (isset($data['email']) && $data['email']) {
                $user->email = $data['email'];
            }

            // Actualizar profile
            foreach ($data as $key => $value) {
                if (in_array($key, ['name','last_name','gender', 'phone_1', 'phone_2', 'location'])) {
                    $profile['profile']->$key = $value;
                }
            }

            // Actualizar el usuario con los datos validados
            $user->save();
            
            $profile['profile']->save();

            $user->syncRoles($data['role_name']);

            if(isset($data['image'])){

                $image = $request->file('image');

                if ($image->isValid()) {

                    $path = $image->store('temp_images');

                    UploadProfileImage::dispatchSync($path, $user, $image->getClientOriginalName());
                }

                $profile = $user->getRoleProfile();
            }

            // Devolver respuesta de éxito
            return ApiResponseHelper::apiSuccess(200, 'Usuario actualizado exitosamente', [
                'user' => [
                    'uuid' => $user->uuid,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'role' => $profile['role'],
                'profile' =>  $profile['profile']
            ]);
        
        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Error al actualizar el usuario', $e->getMessage(), 500, 'UPDATE_USER_ERROR');
        }
    }

    /**
     * Eliminar un usuario específico por UUID.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteUserRequest $request)
    {
        try {

            $data = $request->validated();

            // Buscar el usuario por UUID
            $user = User::findByUuid($data['user_uuid']);

            if (!$user) {
                // Retornar respuesta de error si el usuario no se encuentra
                return ApiResponseHelper::apiError('Usuario no encontrado', null, 404, 'USER_NOT_FOUND');
            }

            // Eliminar el usuario
            $user->delete();

            return ApiResponseHelper::apiSuccess(200, 'Usuario eliminado exitosamente');

        } catch (ValidationException $e) {
            // Manejar errores de validación y retornar respuesta de error
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            // Manejar errores y retornar respuesta de error
            return ApiResponseHelper::apiError('Hubo un error al eliminar el usuario', $e->getMessage(), 500, 'DELETE_USER_ERROR');
        }
    }

    public function byRole(ByRoleUserRequest $request){

        $users = User::role($request['role_name'])->with('userProfile')->get();

        return ApiResponseHelper::apiSuccess(200, 'Usuarios obtenidos exitosamente', ['users' => $users]);

    }
}
