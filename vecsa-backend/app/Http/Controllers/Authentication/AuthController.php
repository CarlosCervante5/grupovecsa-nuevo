<?php

namespace App\Http\Controllers\Authentication;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\UserRegistrationRequest;
use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\InternallyUserRegistrationRequest;
use App\Http\Requests\Auth\ProfileUpdateRequest;
use App\Http\Requests\Auth\RecoverAccountRequest;
use App\Http\Requests\Files\UploadProfileImageRequest;
use App\Jobs\UploadProfileImage;
use App\Notifications\ResetPasswordNotification;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(UserRegistrationRequest $request)
    {
        try {

            $data = $request->validated();
            $user = User::where('email', $data['email'])->first();

            if ($user) {
                return ApiResponseHelper::authError('El usuario ya se encuentra registrado', null, 401, 'REGISTER_USER_ERROR');
            }

            $response = $this->userService->createNewCustomer($data);

            return ApiResponseHelper::authSuccess(201, 'Cliente registrado exitosamente', $response);

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::authError('Error al registrar el cliente', $e->getMessage(), 500, 'REGISTER_CLIENT_ERROR');
        }
    }

    public function internallyRegister(InternallyUserRegistrationRequest $request)
    {
        try {

            $data = $request->validated();

            $user = User::where('email', $data['email'])->first();

            if ($user) {
                $response = $this->userService->handleUser($user, $data);
                return ApiResponseHelper::authSuccess(201, 'Cliente existente', $response);
            }

            $response = $this->userService->createNewCustomer($data);

            return ApiResponseHelper::authSuccess(201, 'Cliente registrado exitosamente', $response);

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::authError('Error al registrar el cliente', $e->getMessage(), 500, 'REGISTER_CLIENT_ERROR');
        }
    }

    public function login(Request $request)
    {
        try {
            $email = strtolower(trim((string) $request->input('email', '')));
            $password = $request->input('password');

            if ($email === '' || $password === null || $password === '') {
                return ApiResponseHelper::authError('Credenciales inválidas', null, 401, 'INVALID_CREDENTIALS');
            }

            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user || !Hash::check($password, $user->password)) {
                return ApiResponseHelper::authError('Credenciales inválidas', null, 401, 'INVALID_CREDENTIALS');
            }
            auth()->login($user);

            // Solo rol + perfil en login; permisos en GET /api/auth/me (menos trabajo antes de emitir token)
            $user->loadMissing(['roles']);
            $primaryRole = $user->getRoleNames()->first();
            if ($primaryRole === 'client') {
                $user->loadMissing('customerProfile');
            } else {
                $user->loadMissing('userProfile');
            }

            $token = $user->getOrCreateToken();

            $profileData = $user->getRoleProfile();

            return ApiResponseHelper::authSuccess(200, 'Login correcto', [
                'token' => $token->plainTextToken,
                'user' => [
                    'uuid' => $user->uuid,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'role' => $profileData['role'],
                'profile' =>  $profileData['profile'],
            ]);

        } catch (\Exception $e) {
            return ApiResponseHelper::authError('Error al autenticar al usuario', $e->getMessage(), 500, 'LOGIN_ERROR');
        }
    }

    public function stregaLogin(Request $request)
    {
        try {
            $email = strtolower(trim((string) $request->input('email', '')));
            $password = $request->input('password');

            if ($email === '' || $password === null || $password === '') {
                return ApiResponseHelper::authError('Credenciales inválidas', null, 401, 'INVALID_CREDENTIALS');
            }

            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user || !Hash::check($password, $user->password)) {
                return ApiResponseHelper::authError('Credenciales inválidas', null, 401, 'INVALID_CREDENTIALS');
            }
            auth()->login($user);

            $user->loadMissing(['roles', 'userProfile']);

            $token = $user->getOrCreateStregaToken();

            $profileData = $user->getStregaRoleProfile();

            if($profileData['role'] == null || $profileData['profile'] == null ){
                return ApiResponseHelper::authError('Error al autenticar al usuario, no tiene el rol o perfil requerido', null, 500, 'ROLE_PROFILE_ERROR');
            }
            
            return ApiResponseHelper::authSuccess(200, 'Login correcto', [
                'token' => $token->plainTextToken,
                'user' => [
                    'uuid' => $user->uuid,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'role' => $profileData['role'],
                'profile' =>  $profileData['profile']
            ]);

        } catch (\Exception $e) {
            return ApiResponseHelper::authError('Error al autenticar al usuario', $e->getMessage(), 500, 'LOGIN_ERROR');
        }
    }

    /**
     * Sesión actual: permisos Spatie (tras login o para refrescar en el cliente).
     */
    public function me(Request $request)
    {
        try {
            $tokenUser = $request->user();

            if (! $tokenUser) {
                return ApiResponseHelper::authError('No autenticado', null, 401, 'UNAUTHENTICATED');
            }

            // Nueva consulta desde BD (evita instancia/token con relaciones «pegadas» y permisos de rol desactualizados).
            $user = User::query()
                ->whereKey($tokenUser->getAuthIdentifier())
                ->with(['roles.permissions', 'permissions', 'userProfile', 'customerProfile'])
                ->firstOrFail();

            $permissions = $user->getAllPermissions()->pluck('name')->values()->all();

            $profilePayload = null;
            $roleName = null;
            try {
                $profileData = $user->getRoleProfile();
                $roleName = $profileData['role'] ?? null;
                $p = $profileData['profile'] ?? null;
                if ($p && is_object($p)) {
                    $profilePayload = [
                        'name' => $p->name,
                        'last_name' => $p->last_name,
                    ];
                }
            } catch (\Exception $e) {
                // Sin perfil o tabla ausente: solo permisos
            }

            return ApiResponseHelper::apiSuccess(200, 'Sesión actual', [
                'permissions' => $permissions,
                'profile' => $profilePayload,
                'role' => $roleName,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener permisos de sesión', $e->getMessage(), 500, 'ME_ERROR');
        }
    }

    public function validateRole(Request $request)
    {
        try {
            
            $user = auth()->user();

            if (!$user) {
                return ApiResponseHelper::authError('No autenticado', null, 401, 'UNAUTHENTICATED');
            }

            try {
                $user->loadMissing(['roles', 'roles.permissions', 'permissions']);
            } catch (\Exception $e) {
                // Spatie/tablas no disponibles: se sigue con comprobaciones parciales
            }

            $stored_role = $request->input('stored_role');
            $expected_role = $request->input('expected_role');

            $userRoleNames = collect();
            $userRoleFirst = null;
            try {
                $userRoleNames = $user->getRoleNames();
                $userRoleFirst = $userRoleNames->first();
            } catch (\Exception $e) {
                // Spatie tables may not exist yet — fallback to role column
                $fallback = $user->role ?? null;
                if ($fallback !== null) {
                    $userRoleNames = collect([$fallback]);
                    $userRoleFirst = $fallback;
                }
            }

            // Coincide si el panel esperado es cualquiera de los roles asignados (evita 403 con varios roles si first() no es el del panel)
            if ($expected_role && $userRoleNames->contains($expected_role)) {
                return ApiResponseHelper::apiSuccess(200, 'Token y rol válidos', [$userRoleFirst, $expected_role]);
            }

            // Roles legacy: manager puede usar el panel gestor (URL /admin/gestor) si aplica en datos antiguos
            $legacyPanelRoles = [
                'gestor' => ['manager'],
            ];
            if ($expected_role && isset($legacyPanelRoles[$expected_role])) {
                foreach ($legacyPanelRoles[$expected_role] as $legacyName) {
                    if ($userRoleNames->contains($legacyName)) {
                        return ApiResponseHelper::apiSuccess(200, 'Token y rol válidos (rol legacy)', [$userRoleFirst, $expected_role]);
                    }
                }
            }

            // Administrator / developer: acceso a paneles internos (mismo criterio que antes, ampliado a developer)
            try {
                if ($user->hasRole('administrator') || $user->hasRole('developer')) {
                    $adminPanelRoles = [
                        'developer', 'marketing', 'vehicle_inventory', 'staff', 'gestor', 'manager', 'receptionist',
                        'valuator', 'appointment_manager', 'technician', 'bodywork_paint_technician',
                        'spare_parts', 'gerente', 'seller', 'strega-seller', 'strega-manager',
                        'strega-administrator',
                    ];
                    if (in_array($expected_role, $adminPanelRoles, true)) {
                        return ApiResponseHelper::apiSuccess(200, 'Acceso administrador a panel', [$userRoleFirst, $expected_role]);
                    }
                }
            } catch (\Exception $e) {
                // Spatie no disponible
            }

            // Permission-based access: user has "access {expected_role}" permission
            try {
                $accessPermission = 'access ' . $expected_role;
                if ($user->hasPermissionTo($accessPermission)) {
                    return ApiResponseHelper::apiSuccess(200, 'Acceso por permiso', [$userRoleFirst, $expected_role]);
                }
            } catch (\Exception $e) {
                // Permission check failed (table missing, etc.) — skip
            }

            // Inventario de vehículos: legacy con solo access marketing
            if ($expected_role === 'vehicle_inventory') {
                try {
                    if ($user->hasPermissionTo('access marketing')) {
                        return ApiResponseHelper::apiSuccess(200, 'Acceso inventario vía marketing', [$userRoleFirst, $expected_role]);
                    }
                } catch (\Exception $e) {
                    // skip
                }
            }

            return ApiResponseHelper::authError('Rol no autorizado', null, 403, 'UNAUTHORIZED_ROLE');
            
        } catch (\Exception $e) {
            return ApiResponseHelper::authError('Error al validar el token o rol', $e->getMessage(), 500, 'VALIDATION_ERROR');
        }
    }

    public function logout()
    {
        try {

            $user = auth()->user();
            $user->tokens()->delete();

            return ApiResponseHelper::authSuccess(200, 'Logout exitoso', null);

        } catch (\Exception $e) {

            return ApiResponseHelper::authError('Error al realizar el logout', $e->getMessage(), 500, 'LOGOUT_ERROR');
        }
    }

    /**
     * Obtener un cliente específico por UUID.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($uuid)
    {
        try {

            $user = auth()->user();

            if ($user->uuid !== $uuid) {

                return ApiResponseHelper::apiError('Usuario no autorizado para acceder a esta información', null, 401, 'UNAUTHORIZED_ACCESS');
            }

            $user = auth()->user();

            $profileData = $user->getRoleProfile();

            return ApiResponseHelper::authSuccess(200, 'Usuario encontrado', [
                'user' => [
                    'uuid' => $user->uuid,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
                'role' => $profileData['role'],
                'profile' =>  $profileData['profile']
            ]);

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al obtener el usuario', $e->getMessage(), 500, 'GET_USER_ERROR');
        }
    }


    /**
     * Obtener un usuario específico por UUID y actualizar su perfil.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile( ProfileUpdateRequest $request )
    {
        try {

            $data = $request->validated();

            $data = array_filter($data, function ($value) {
                return !is_null($value) && $value !== '';
            });

            $user = auth()->user();

            if ($user->uuid !== $data['uuid']) {

                return ApiResponseHelper::apiError('Usuario no autorizado para actualizar esta información', null, 401, 'UNAUTHORIZED_ACCESS');
            }

            $roleProfile = $user->getRoleProfile();
            $profile = $roleProfile['profile'];

            if (!$profile) {
                return ApiResponseHelper::apiError('Perfil no encontrado', null, 404, 'PROFILE_NOT_FOUND');
            }

            foreach ($data as $key => $value) {
                if (in_array($key, ['birthday','gender', 'phone_1', 'phone_2', 'email_2'])) {
                    $profile->$key = $value;
                }
            }
            
            $profile->save();

            if (isset($data['nickname'])) {
                $user->nickname = $data['nickname'];
                $user->save();
            }

            return ApiResponseHelper::apiSuccess(200, 'Perfil actualizado');

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al obtener el usuario', $e->getMessage(), 500, 'GET_USER_ERROR');
        }
    }

    /**
     * Actualizar imágen del perfil
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateImageProfile( UploadProfileImageRequest $request )
    {
        try {

            $user_uuid = $request->input('user_uuid');
            $image = $request->file('image');
            
            $user = User::findByUuid($user_uuid);

            if (!$user) {

                return ApiResponseHelper::apiError('El usuario no existe', 'No existe el id: '. $user_uuid ,404, 'UPDATE_PROFILE_IMAGE_ERROR');
            }

            if (!$image->isValid()) {

                return ApiResponseHelper::apiError('Error al actualizar la imagen de perfil',  'Archivo era inválido o corrupto: ' . $image->getClientOriginalName(), 500, 'UPDATE_PROFILE_IMAGE_ERROR');
            }

            $path = \App\Support\UploadableImage::storeTemp($image);

            UploadProfileImage::dispatchSync($path, $user, $image->getClientOriginalName());

            return ApiResponseHelper::apiSuccess(200, 'Perfil actualizado');

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al actualizar la imagen de perfil', $e->getMessage(), 500, 'UPDATE_PROFILE_IMAGE_ERROR');
        }
    }

    /**
     * Recuperar cuenta mediante correo electrónico.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function recoverAccount(RecoverAccountRequest $request)
    {
        $data = $request->validated();
        
        try {

            $user = User::where('email', $data['email'])
            ->orWhereHas('customerProfile', function ($query) use ($data) {
                $query->where('email_1', $data['email'])->orWhere('email_2', $data['email']);
            })->first();

            if (!$user) {

                return ApiResponseHelper::apiError('El usuario no existe', 'No existe el email: '. $data['email'] ,404, 'RECOVER_USER_ERROR');
            }


            $token_validate = Password::broker()->createToken($user);

            $token_user = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

            $user->notify(new ResetPasswordNotification($token_user->token, $token_validate ));

            return ApiResponseHelper::apiSuccess(200, 'Enlace de restablecimiento enviado.');

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al obtener el usuario', $e->getMessage(), 500, 'GET_USER_ERROR');
        }
    }


    public function resetPassword(Request $request)
    {
        try {

            $request->validate([
                'token_user' => 'required',
                'token_validate' => 'required',
                'password' => 'required|min:8|confirmed',
            ]);

            $token_user = urldecode($request->input('token_user'));
            $token_validate = urldecode($request->input('token_validate'));

            $passwordReset = DB::table('password_reset_tokens')->where('token', $token_user)->first();
    
            if (!$passwordReset) {
                return ApiResponseHelper::apiError('Token de restablecimiento inválido.', null, 400, 'TOKEN_ERROR');
            }
            
            $status = Password::reset(
                [
                    'email' => $passwordReset->email,
                    'password' => $request->input('password'),
                    'password_confirmation' => $request->input('password_confirmation'),
                    'token' => $token_validate,
                ],
                function ($user, $password) {
                    $user->update(['password' => Hash::make($password)]);
                }
            );
    
            if ($status === Password::PASSWORD_RESET) {
                return ApiResponseHelper::apiSuccess(200, 'Contraseña restablecida con éxito.');
            }

        } catch (\Exception $e) {

            return ApiResponseHelper::apiError('Error al restablecer la contraseña.', $e->getMessage(), 400, 'PASSWORD_RESET_ERROR');
        }
    }

}
