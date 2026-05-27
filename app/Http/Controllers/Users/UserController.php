<?php

namespace App\Http\Controllers\Users;

use App\Models\Dealership;
use App\Models\User;
use App\Support\UserDealershipRules;
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
use Illuminate\Http\Request;
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
    public function index(Request $request)
    {
        try {
            $keyword = trim((string) $request->query('keyword', ''));
            $perPage = (int) $request->query('paginate', 15);
            if ($perPage < 1 || $perPage > 100) {
                $perPage = 15;
            }

            $query = User::whereHas('userProfile')->with(['dealerships', 'roles']);

            if ($keyword !== '') {
                $term = '%' . $keyword . '%';
                $words = array_values(array_filter(preg_split('/\s+/', $keyword)));

                $query->where(function ($q) use ($term, $words) {
                    $q->where('users.nickname', 'LIKE', $term)
                        ->orWhere('users.email', 'LIKE', $term);

                    $q->orWhereHas('userProfile', function ($profileQuery) use ($term, $words) {
                        $profileQuery->where(function ($pq) use ($term, $words) {
                            $pq->where('location', 'LIKE', $term);

                            foreach ($words as $word) {
                                $like = '%' . $word . '%';
                                $pq->orWhere('name', 'LIKE', $like)
                                    ->orWhere('last_name', 'LIKE', $like);
                            }
                        });
                    });

                    $q->orWhereHas('roles', function ($roleQuery) use ($term) {
                        $roleQuery->where('name', 'LIKE', $term);
                    });

                    $q->orWhereHas('dealerships', function ($dealershipQuery) use ($term) {
                        $dealershipQuery->where('name', 'LIKE', $term);
                    });
                });
            }

            $users = $query->paginate($perPage);

            $users->getCollection()->transform(function ($user) {
                
                $profile = $user->getRoleProfile();
                
                $user->role = $profile['role'];
                $user->profile = $profile['profile'];
                $user->dealership_names = $user->dealerships->pluck('name')->implode(', ') ?: 'Sin sucursal';
                $user->dealership_ids = $user->dealerships->pluck('id');

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

            $this->syncUserDealerships($user, $data, $data['role_name'] ?? null);

            if(isset($data['image'])){

                $image = $request->file('image');

                if ($image->isValid()) {

                    $path = \App\Support\UploadableImage::storeTemp($image);
    
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

            $user->load('dealerships');
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
                'profile' =>  $profile['profile'],
                'dealership_ids' => $user->dealerships->pluck('id')->values()->all(),
                'dealership_names' => $user->dealerships->pluck('name')->implode(', ') ?: null,
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
            if ($profile['profile']) {
                foreach ($data as $key => $value) {
                    if (in_array($key, ['name','last_name','gender', 'phone_1', 'phone_2', 'location'])) {
                        $profile['profile']->$key = $value;
                    }
                }
            }

            // Actualizar el usuario con los datos validados
            $user->save();
            
            if ($profile['profile']) {
                $profile['profile']->save();
            }

            if (isset($data['role_name']) && $data['role_name']) {
                $user->syncRoles($data['role_name']);
            }

            $this->syncUserDealerships(
                $user,
                $data,
                $data['role_name'] ?? $profile['role'] ?? null
            );

            if(isset($data['image'])){

                $image = $request->file('image');

                if ($image->isValid()) {

                    $path = \App\Support\UploadableImage::storeTemp($image);

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

    /**
     * Sincroniza sucursales (pivote) y refleja nombres en user_profiles.location.
     *
     * @param  array<string, mixed>  $data
     */
    protected function syncUserDealerships(User $user, array $data, ?string $roleName = null): void
    {
        if (! array_key_exists('dealership_ids', $data)) {
            return;
        }

        $role = $roleName ?? $user->getRoleNames()->first();
        $ids = array_values(array_unique(array_map('intval', $data['dealership_ids'] ?? [])));

        if (! UserDealershipRules::allowsMultipleDealerships($role) && count($ids) > 1) {
            $ids = array_slice($ids, 0, 1);
        }

        $user->dealerships()->sync($ids);

        $profileData = $user->getRoleProfile();
        $profile = $profileData['profile'] ?? null;
        if (! $profile) {
            return;
        }

        if ($ids === []) {
            return;
        }

        $names = Dealership::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->pluck('name')
            ->implode(', ');

        if ($names !== '') {
            $profile->location = $names;
            $profile->save();
        }
    }
}
