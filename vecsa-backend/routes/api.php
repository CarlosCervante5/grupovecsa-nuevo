<?php

use App\Http\Controllers\Acquisitions\AcquisitionController;
use App\Http\Controllers\Appointments\AppointmentController;
use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\Blogs\BlogController;
use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Campaigns\CampaignController;
use App\Http\Controllers\Dealerships\DealershipController;
use App\Http\Controllers\Quizzes\QuizController;
use App\Http\Controllers\Events\EventController;
use App\Http\Controllers\Promotions\PromotionController;
use App\Http\Controllers\Leads\LeadController;
use App\Http\Controllers\Multimedia\MultimediaController;
use App\Http\Controllers\Repairs\RepairController;
use App\Http\Controllers\Rewards\RewardController;
use App\Http\Controllers\Riders\RiderController;
use App\Http\Controllers\Roles_Permissions\PermissionController;
use App\Http\Controllers\Roles_Permissions\RoleController;
use App\Http\Controllers\SpareParts\SparePartController;
use App\Http\Controllers\Strega\OpportunityController;
use App\Http\Controllers\Tests\TestController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Valuations\ValuationController;
use App\Http\Controllers\Valuations\ValuationImageController;
use App\Http\Controllers\Vehicles\BrandLineController;
use App\Http\Controllers\Vehicles\LineModelController;
use App\Http\Controllers\Vehicles\ModelVersionController;
use App\Http\Controllers\Vehicles\VehicleBodyController;
use App\Http\Controllers\Vehicles\VehicleBrandController;
use App\Http\Controllers\Vehicles\VehicleController;
use App\Http\Controllers\Vehicles\VehicleImageController;
use Illuminate\Support\Facades\Route;


// Segmento Autenticación

Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/iternally_register', [AuthController::class, 'internallyRegister']);
    Route::post('/recover_account', [AuthController::class, 'recoverAccount']);
    Route::post('/reset_password', [AuthController::class, 'resetPassword']);
    Route::post('/update_image', [AuthController::class, 'updateImageProfile']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/validate_role', [AuthController::class, 'validateRole']);
        Route::post('/update_profile', [AuthController::class, 'updateProfile']);
        Route::post('/update_image_profile', [AuthController::class, 'updateImageProfile']);
        Route::get('/profile/{uuid}', [AuthController::class, 'show']);

    });

});

// Fin Segmento Autenticación

// Segmento Sucursales

Route::prefix('dealerships')->middleware('bandwidth_usage')->group(function () {

    Route::post('/search', [DealershipController::class, 'search']);
});

// Fin Sucursales

// Segmento Usuarios

Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    
    Route::get('/', [UserController::class, 'index'])->middleware(['role:administrator', 'permission:list users']);
    Route::post('/', [UserController::class, 'store'])->middleware(['role:administrator', 'permission:create users']);
    Route::post('/detail', [UserController::class, 'detail'])->middleware('role:administrator');
    Route::post('/update', [UserController::class, 'update'])->middleware('role:administrator', 'permission:update users');
    Route::post('/delete', [UserController::class, 'delete'])->middleware('role:administrator', 'permission:delete users');
    Route::post('/by_role', [UserController::class, 'ByRole']);

});

// Fin Segmento Usuarios


// Fin Segmento Autenticación


// Segmento Customers

Route::prefix('customers')->middleware('auth:sanctum')->group(function () {
    Route::post('/detail', [CustomerController::class, 'detail'])->middleware('role:staff');
    Route::post('/update', [CustomerController::class, 'update'])->middleware('role:staff');
    Route::post('/update_image', [CustomerController::class, 'updateImage'])->middleware('role:staff');
});

// Fin Segmento Customers


// Segmento Roles y Permisos

Route::apiResource('roles', RoleController::class)->middleware('auth:sanctum');
Route::apiResource('permissions', PermissionController::class)->middleware('auth:sanctum');

// Fin Segmento Roles y Permisos


// Segmento Marcas de vehículos

Route::prefix('vehicle_brands')->group(function () {
    
    Route::get('/', [VehicleBrandController::class, 'index']);
    Route::get('/{id}', [VehicleBrandController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [VehicleBrandController::class, 'store']);
        Route::put('/{id}', [VehicleBrandController::class, 'update']);
        Route::delete('/{id}', [VehicleBrandController::class, 'destroy']);
    });
});

// Fin Segmento Marcas de vehículos


// Segmento Lineas de Marcas

Route::prefix('brand_lines')->group(function () {
    
    Route::get('/', [BrandLineController::class, 'index']);
    Route::get('/{id}', [BrandLineController::class, 'show']);
    Route::get('/by_brand/{brand}', [BrandLineController::class, 'byBrand']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [BrandLineController::class, 'store']);
        Route::put('/{id}', [BrandLineController::class, 'update']);
        Route::delete('/{id}', [BrandLineController::class, 'destroy']);
    });
});

// Fin Segmento Lineas de Marcas


// Segmento Modelos de Lineas

Route::prefix('line_models')->group(function () {
    
    Route::get('/', [LineModelController::class, 'index']);
    Route::get('/{id}', [LineModelController::class, 'show']);
    Route::get('/by_line/{line}', [LineModelController::class, 'byLine']);
    Route::get('/by_brand/{brand}', [LineModelController::class, 'byBrand']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [LineModelController::class, 'store']);
        Route::put('/{id}', [LineModelController::class, 'update']);
        Route::delete('/{id}', [LineModelController::class, 'destroy']);
    });
});

// Fin Segmento Modelos de Lineas


// Segmento Versiones de Modelos

Route::prefix('model_versions')->group(function () {
    
    Route::get('/', [ModelVersionController::class, 'index']);
    Route::get('/{id}', [ModelVersionController::class, 'show']);
    Route::get('/by_model/{model}', [ModelVersionController::class, 'byModel']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [ModelVersionController::class, 'store']);
        Route::put('/{id}', [ModelVersionController::class, 'update']);
        Route::delete('/{id}', [ModelVersionController::class, 'destroy']);
    });
});

// Fin Segmento Versiones de Modelos


// Segmento Carrocerías de Vehículos

Route::prefix('vehicle_bodies')->group(function () {
    
    Route::get('/', [VehicleBodyController::class, 'index']);
    Route::get('/{id}', [VehicleBodyController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [VehicleBodyController::class, 'store']);
        Route::put('/{id}', [VehicleBodyController::class, 'update']);
        Route::delete('/{id}', [VehicleBodyController::class, 'destroy']);
    });
});

// Fin Segmento Carrocerías de Vehículos


// Segmento Vehículos

Route::prefix('vehicles')->middleware('bandwidth_usage')->group(function () {
    
    Route::post('/detail', [VehicleController::class, 'detail']);
    Route::get('/search', [VehicleController::class, 'search']);
    Route::post('/random', [VehicleController::class, 'randomSearch']);
    Route::get('/min_max', [VehicleController::class, 'minMax']);

    Route::get('preowned_xml', [VehicleController::class, 'preownedXML']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [VehicleController::class, 'store']);
        Route::post('/create', [VehicleController::class, 'create']);
        Route::post('/update', [VehicleController::class, 'update']);
        Route::post('/status', [VehicleController::class, 'status']);
        Route::post('/delete', [VehicleController::class, 'delete']);
        Route::post('/restore', [VehicleController::class, 'restore']);
        Route::post('/csv_upload', [VehicleController::class, 'csvUpload']);
        Route::post('/delete-batch', [VehicleController::class, 'deleteBatch']);
        Route::post('/inverse_delete_batch', [VehicleController::class, 'inverseDeleteBatch']);
        Route::post('/status-batch', [VehicleController::class, 'statusBatch']);
    });

});

// Fin Segmento Vehículos


// Segmento Imágenes de Vehículos

Route::prefix('vehicle_images')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {
    
    Route::post('/', [VehicleImageController::class, 'store']);
    Route::post('/sort_update', [VehicleImageController::class, 'sortUpdate']);
    Route::post('/delete', [VehicleImageController::class, 'delete']);
    Route::post('/delete_batch', [VehicleImageController::class, 'deleteBatch']);
    Route::post('/update_storage', [VehicleImageController::class, 'updateStorage']);

});

// Fin Segmento Imágenes de Vehículos


// Segmento Leads

Route::prefix('leads')->middleware('bandwidth_usage')->group(function () {
    
    Route::post('/ask_information', [LeadController::class, 'askInfomation']);
    Route::post('/reception_notification',  [LeadController::class, 'receptionNotification']);
    Route::post('/reception_form',  [LeadController::class, 'receptionForm']);
    Route::post('/riders_quiz',  [LeadController::class, 'ridersQuiz']);
    Route::post('/car_care',  [LeadController::class, 'carCare']);

});

// Fin Segmento Leads


// Segmento Campaigns

Route::prefix('campaigns')->middleware('bandwidth_usage')->group(function () {
    
    Route::post('/active', [CampaignController::class, 'active']);
    Route::post('/active_by_name', [CampaignController::class, 'activeByName']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [CampaignController::class, 'store']);
        Route::post('/search', [CampaignController::class, 'search']);
        Route::post('/search_category', [CampaignController::class, 'searchCategory']);
        Route::post('/delete', [CampaignController::class, 'delete']);
        Route::post('/attach_vehicle', [CampaignController::class, 'attachVehicle']);
        Route::post('/update_storage', [CampaignController::class, 'updateStorage']);
    });

});

// Fin Segmento Campaigns


// Segmento Promotions

Route::prefix('promotions')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {
   
    Route::post('/', [PromotionController::class, 'store']);
    Route::post('/search', [PromotionController::class, 'search']);
    Route::post('/sort_update', [PromotionController::class, 'sortUpdate']);
    Route::post('/delete', [PromotionController::class, 'delete']);

});

// Fin Segmento Promotions


// Segmento Events

Route::prefix('events')->middleware('bandwidth_usage')->group(function () {

    Route::post('/search', [EventController::class, 'search']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [EventController::class, 'store']);
        Route::post('/delete', [EventController::class, 'delete']);
    });

});

// Fin Segmento Events


// Segmento Event Multimedia

Route::prefix('event_multimedia')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {

    Route::post('/', [MultimediaController::class, 'store']);
    Route::post('/search', [MultimediaController::class, 'search']);
    Route::post('/sort_update', [MultimediaController::class, 'sortUpdate']);
    Route::post('/delete', [MultimediaController::class, 'delete']);

});

// Fin Event Multimedia


// Segmento Rewards

Route::prefix('rewards')->middleware('bandwidth_usage')->group(function () {

    Route::post('/search', [RewardController::class, 'search']);

    Route::post('/by_name', [RewardController::class, 'byName']);

    Route::post('/by_category', [RewardController::class, 'byCategory']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [RewardController::class, 'store']);
        Route::post('/update', [RewardController::class, 'update']);
        Route::post('/detail', [RewardController::class, 'detail']);
        Route::post('/delete', [RewardController::class, 'delete']);

        Route::post('/update_sale', [RewardController::class, 'updateSale']);

        Route::post('/customer_points', [RewardController::class, 'customerPoints']);

        Route::post('/redeem_customer_points', [RewardController::class, 'redeemCustomerPoints']);

    });

});

// Fin Rewards

// Segmento Riders

Route::prefix('riders')->middleware('bandwidth_usage')->group(function () {

    Route::post('/points', [RiderController::class, 'points']);
    Route::post('/vehicle_register', [RiderController::class, 'vehicleRegister']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [RiderController::class, 'store']);
        
        Route::get('/search', [RiderController::class, 'search']);

        Route::get('/search_customers', [RiderController::class, 'search_customers']);

        Route::post('/customer_position', [RiderController::class, 'customerPosition']);
        Route::post('/customer_reward_update', [RiderController::class, 'customerRewardUpdate']);
        Route::post('/reward_detail', [RiderController::class, 'rewardRideDetail']);
        Route::post('/reward_update', [RiderController::class, 'rewardRideUpdate']);
        Route::post('/update', [RiderController::class, 'update']);
        Route::post('/delete', [RiderController::class, 'delete']);

    });

});

// Fin Riders

// Segmento Customer Quizzes

Route::prefix('quizzes')->middleware('bandwidth_usage')->group(function () {

    Route::post('/search', [QuizController::class, 'search']);
    Route::post('/search_by_customer', [QuizController::class, 'searchByCustomer']);
    Route::post('/search_profile', [QuizController::class, 'searchProfile']);
    Route::post('/attatch', [QuizController::class, 'attatch']);
    Route::post('/attatch_batch', [QuizController::class, 'attatchBatch']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [QuizController::class, 'store']);
    });
});

// Fin Customer Quizzes

// Segmento Appointments

Route::prefix('appointment')->middleware('bandwidth_usage')->group(function () {

    Route::post('/', [AppointmentController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/valuation_appointment', [AppointmentController::class, 'valuationAppointment']);
        Route::post('/attatch_valuator', [AppointmentController::class, 'attatchValuator']);
        Route::post('/search', [AppointmentController::class, 'search']);
    });
});

// Fin Appointments

// Segmento Valuaciones

Route::prefix('valuations')->middleware('bandwidth_usage')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/search', [ValuationController::class, 'search']);
        Route::get('/search_bodyworks', [ValuationController::class, 'searchBodyworks']);
        Route::get('/search_repairs', [ValuationController::class, 'searchRepairs']);
        Route::get('/search_parts', [ValuationController::class, 'searchParts']);
        Route::post('/detail', [ValuationController::class, 'detail']);
        Route::post('/checklist', [ValuationController::class, 'checklist']);
        Route::post('/attatch', [ValuationController::class, 'attatch']);
        Route::post('/update', [ValuationController::class, 'update']);
        Route::post('/update_vehicle', [ValuationController::class, 'updateVehicle']);
        Route::post('/detail_parts', [ValuationController::class, 'detailParts']);
        Route::post('/update_parts', [ValuationController::class, 'updateParts']);

        Route::post('/update_images', [ValuationImageController::class, 'store']);
        Route::post('/search_images', [ValuationImageController::class, 'search']);

        Route::get('download_pdf',[ValuationController::class, 'downloadPDF']);

    });
});

// Fin Valuaciones


// Segmento Toma Vehículo

Route::prefix('acquisitions')->middleware('bandwidth_usage')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/checklist', [AcquisitionController::class, 'checklist']);
        Route::post('/attatch', [AcquisitionController::class, 'attatch']);
        Route::post('upload_pdf',[AcquisitionController::class, 'uploadPDF']);

    });
});

// Fin Toma Vehículo

// Segmento Refacciones

Route::prefix('spare_parts')->middleware('bandwidth_usage')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('/', [SparePartController::class, 'store']);
        Route::post('/delete', [SparePartController::class, 'delete']);
    
    });
});

// Fin Refacciones


// Segmento Hojalatería y pintura

Route::prefix('bodyworks')->middleware('bandwidth_usage')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('/', [RepairController::class, 'store']);
        Route::post('/update', [RepairController::class, 'update']);
        Route::post('/delete', [RepairController::class, 'delete']);
    });
});

// Fin Hojalatería y pintura


// Segmento Blog

Route::prefix('blogs')->middleware('bandwidth_usage')->group(function () {

    Route::get('/search', [BlogController::class, 'searchPublic']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/search_manager', [BlogController::class, 'searchManager']);
        
        Route::post('/', [BlogController::class, 'store']);
        Route::post('/update', [BlogController::class, 'update']);
        Route::post('/delete', [BlogController::class, 'delete']);

        Route::post('/create_content', [BlogController::class, 'createContent']);
        Route::post('/delete_content', [BlogController::class, 'deleteContent']);

    });
});

// Fin Blog


// Segmento Strega

Route::prefix('strega')->middleware('bandwidth_usage')->group(function () {

    Route::post('/login', [AuthController::class, 'stregaLogin']);

    Route::post('/public_create', [OpportunityController::class, 'public_create']);

    // Segmento leads
    Route::prefix('leads')->middleware('auth:sanctum')->group(function () {
    
        Route::get('/search_administrator', [OpportunityController::class, 'searchAdministrator']);
        Route::get('/search_manager', [OpportunityController::class, 'searchLeadsManager']);
        Route::get('/search_seller', [OpportunityController::class, 'searchSeller']);

        Route::post('/create', [OpportunityController::class, 'create']);
        Route::post('/detail', [OpportunityController::class, 'detail']);
        Route::post('/update', [OpportunityController::class, 'update']);

        Route::post('/csv_upload', [OpportunityController::class, 'csvUpload']);
        Route::get('/dealership_search', [OpportunityController::class, 'dealershipSearch']);
        Route::get('/type_search', [OpportunityController::class, 'typeSearch']);

        Route::post('/attatch_manager', [OpportunityController::class, 'attatchManager']);

        Route::post('/first_attempt', [OpportunityController::class, 'firstAttempt']);

    });

    Route::prefix('appointments')->middleware('auth:sanctum')->group(function () {
    
        Route::get('/search_manager', [OpportunityController::class, 'searchAppointmentsManager']);

        Route::post('/follow_attempt', [OpportunityController::class, 'followAttempt']);

    });

    // Fin segmento leads

});

Route::prefix('pruebas')->group(function () {

    Route::get('/time_zone', [TestController::class, 'timeZone']);

});

// Fin Strega