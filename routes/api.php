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
use App\Http\Controllers\Users\UserDealershipController;
use App\Http\Controllers\Valuations\ValuationController;
use App\Http\Controllers\Valuations\ValuationImageController;
use App\Http\Controllers\Vehicles\BrandLineController;
use App\Http\Controllers\Vehicles\LineModelController;
use App\Http\Controllers\Vehicles\ModelVersionController;
use App\Http\Controllers\Vehicles\VehicleBodyController;
use App\Http\Controllers\Vehicles\VehicleBrandController;
use App\Http\Controllers\Vehicles\VehicleController;
use App\Http\Controllers\Vehicles\VehicleImageController;
use App\Http\Controllers\Home\HomePublicController;
use App\Http\Controllers\HomeSlides\HomeSlideController;
use App\Http\Controllers\HomeTestimonials\HomeTestimonialController;
use App\Http\Controllers\Boutique\BoutiqueCatalogController;
use App\Http\Controllers\Boutique\BoutiqueCartController;
use App\Http\Controllers\Boutique\BoutiqueCheckoutController;
use App\Http\Controllers\Boutique\BoutiqueOrderController;
use App\Http\Controllers\Boutique\BoutiqueShippingController;
use App\Http\Controllers\Boutique\BoutiquePaymentController;
use App\Http\Controllers\Boutique\BoutiqueCategoryController;
use App\Http\Controllers\Boutique\BoutiqueProductController;
use App\Http\Controllers\Boutique\BoutiqueProductImageController;
use App\Http\Controllers\Boutique\BoutiqueAdminOrderController;
use App\Http\Controllers\Boutique\BoutiqueInventoryController;
use App\Http\Controllers\Boutique\BoutiqueAttributeController;
use App\Http\Controllers\Boutique\BoutiqueBannerController;
use App\Http\Controllers\Experience\ExperienceController;
use App\Http\Controllers\Benchmark\BenchmarkAdsController;
use App\Http\Controllers\Assistant\AssistantController;
use App\Http\Controllers\StoreManagement\StoreManagementController;
use App\Http\Controllers\StoreManagement\StoreCustomerController;
use App\Http\Controllers\StoreManagement\StorePointsController;
use App\Http\Controllers\StoreManagement\StoreCouponController;
use App\Http\Controllers\AdminDashboard\AdminDashboardController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Boutique\IncadeaSyncController;
use App\Http\Controllers\Developer\ApiMonitorController;
use App\Http\Controllers\Boutique\WcImportController;
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

        Route::get('/me', [AuthController::class, 'me']);
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
    Route::post('/users', [DealershipController::class, 'users'])->middleware('auth:sanctum');
    Route::post('/store', [DealershipController::class, 'store'])->middleware(['auth:sanctum', 'role:administrator|developer|admin']);
    Route::post('/update', [DealershipController::class, 'update'])->middleware(['auth:sanctum', 'role:administrator|developer|admin']);
    Route::post('/delete', [DealershipController::class, 'destroy'])->middleware(['auth:sanctum', 'role:administrator|developer|admin']);
});

// Fin Sucursales

// Segmento Usuarios

Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    
    Route::get('/', [UserController::class, 'index'])->middleware(['role:administrator|developer', 'permission:list users']);
    Route::post('/', [UserController::class, 'store'])->middleware(['role:administrator|developer', 'permission:create users']);
    Route::post('/detail', [UserController::class, 'detail'])->middleware('role:administrator|developer');
    Route::post('/update', [UserController::class, 'update'])->middleware('role:administrator|developer', 'permission:update users');
    Route::post('/delete', [UserController::class, 'delete'])->middleware('role:administrator|developer', 'permission:delete users');
    Route::post('/by_role', [UserController::class, 'ByRole']);
    Route::post('/assign_dealerships', [UserDealershipController::class, 'assignDealerships'])->middleware('role:administrator|developer');
    Route::post('/dealerships', [UserDealershipController::class, 'getUserDealerships'])->middleware('role:administrator|developer');

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


// Segmento Home Público

Route::prefix('home')->middleware('bandwidth_usage')->group(function () {
    Route::post('/slides', [HomePublicController::class, 'slides']);
    Route::post('/testimonials', [HomePublicController::class, 'testimonials']);
});

// Fin Home Público


// Segmento Home Slides

Route::prefix('home_slides')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {
    Route::post('/', [HomeSlideController::class, 'store']);
    Route::post('/search', [HomeSlideController::class, 'search']);
    Route::post('/update', [HomeSlideController::class, 'update']);
    Route::post('/delete', [HomeSlideController::class, 'delete']);
    Route::post('/sort_update', [HomeSlideController::class, 'sortUpdate']);
    Route::post('/toggle', [HomeSlideController::class, 'toggle']);
});

// Fin Home Slides


// Segmento Home Testimonials

Route::prefix('home_testimonials')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {
    Route::post('/', [HomeTestimonialController::class, 'store']);
    Route::post('/search', [HomeTestimonialController::class, 'search']);
    Route::post('/delete', [HomeTestimonialController::class, 'delete']);
    Route::post('/sort_update', [HomeTestimonialController::class, 'sortUpdate']);
    Route::post('/toggle', [HomeTestimonialController::class, 'toggle']);
});

// Fin Home Testimonials


// Segmento Boutique

// Boutique Público
Route::prefix('boutique')->middleware('bandwidth_usage')->group(function () {
    Route::post('/catalog/search', [BoutiqueCatalogController::class, 'search']);
    Route::post('/catalog/detail', [BoutiqueCatalogController::class, 'detail']);
    Route::post('/catalog/categories', [BoutiqueCatalogController::class, 'categories']);
    Route::post('/checkout/create_guest_order', [BoutiqueCheckoutController::class, 'createGuestOrder']);
    Route::post('/checkout/shipping_quote_public', [BoutiqueCheckoutController::class, 'shippingQuote']);
    Route::post('/checkout/openpay_public_config', [SettingsController::class, 'openpayCheckoutPublic']);
    Route::post('/checkout/openpay_confirm_charge', [BoutiqueCheckoutController::class, 'confirmOpenPayCharge']);
    Route::post('/checkout/payment_methods_public', [SettingsController::class, 'boutiquePaymentMethodsPublic']);
    Route::post('/checkout/shipping_package_types_public', [SettingsController::class, 'boutiqueShippingPackageTypesPublic']);
});

// Boutique Cliente Autenticado
Route::prefix('boutique')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {
    Route::post('/cart/get', [BoutiqueCartController::class, 'get']);
    Route::post('/cart/add', [BoutiqueCartController::class, 'add']);
    Route::post('/cart/update', [BoutiqueCartController::class, 'update']);
    Route::post('/cart/remove', [BoutiqueCartController::class, 'remove']);

    Route::post('/checkout/shipping_quote', [BoutiqueCheckoutController::class, 'shippingQuote']);
    Route::post('/checkout/create_order', [BoutiqueCheckoutController::class, 'createOrder']);
    Route::post('/checkout/payment_intent', [BoutiqueCheckoutController::class, 'createPaymentIntent']);

    Route::post('/orders/search', [BoutiqueOrderController::class, 'search']);
    Route::post('/orders/detail', [BoutiqueOrderController::class, 'detail']);

    Route::post('/shipping/track', [BoutiqueShippingController::class, 'track']);
});

// Boutique Webhook Stripe (sin auth)
Route::prefix('boutique')->middleware('bandwidth_usage')->group(function () {
    Route::post('/webhook/stripe', [BoutiquePaymentController::class, 'stripeWebhook']);
});

// Boutique Admin
Route::prefix('boutique/admin')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {
    // Categories
    Route::post('/categories/search', [BoutiqueCategoryController::class, 'search']);
    Route::post('/categories/store', [BoutiqueCategoryController::class, 'store']);
    Route::post('/categories/update', [BoutiqueCategoryController::class, 'update']);
    Route::post('/categories/delete', [BoutiqueCategoryController::class, 'delete']);

    // Products
    Route::post('/products/search', [BoutiqueProductController::class, 'search']);
    Route::post('/products/store', [BoutiqueProductController::class, 'store']);
    Route::post('/products/update', [BoutiqueProductController::class, 'update']);
    Route::post('/products/delete', [BoutiqueProductController::class, 'delete']);
    Route::post('/products/generate_variants', [BoutiqueProductController::class, 'generateVariants']);
    Route::post('/products/update_variant', [BoutiqueProductController::class, 'updateVariant']);
    Route::post('/products/delete_variant', [BoutiqueProductController::class, 'deleteVariant']);

    // Product Images
    Route::post('/product_images/store', [BoutiqueProductImageController::class, 'store']);
    Route::post('/product_images/sort', [BoutiqueProductImageController::class, 'sortUpdate']);
    Route::post('/product_images/delete', [BoutiqueProductImageController::class, 'delete']);

    // Orders
    Route::post('/orders/search', [BoutiqueAdminOrderController::class, 'search']);
    Route::post('/orders/detail', [BoutiqueAdminOrderController::class, 'detail']);
    Route::post('/orders/update_status', [BoutiqueAdminOrderController::class, 'updateStatus']);
    Route::post('/orders/generate_label', [BoutiqueAdminOrderController::class, 'generateLabel']);
    Route::post('/orders/metrics', [BoutiqueAdminOrderController::class, 'metrics']);

    // Payments
    Route::post('/payments/confirm_manual', [BoutiquePaymentController::class, 'confirmManual']);

    // Checkout boutique: qué métodos mostrar (transferencia / sucursal; Stripe/OpenPay por llaves)
    Route::post('/checkout_payment_methods/config', [SettingsController::class, 'boutiqueCheckoutPaymentMethodsConfig']);
    Route::post('/checkout_payment_methods/update', [SettingsController::class, 'updateBoutiqueCheckoutPaymentMethods']);

    // OpenPay (config tienda; documentación https://documents.openpay.mx/docs/api/)
    Route::post('/openpay/config', [SettingsController::class, 'openpay']);
    Route::post('/openpay/update', [SettingsController::class, 'updateOpenpay']);

    // Inventory
    Route::post('/inventory/update', [BoutiqueInventoryController::class, 'update']);
    Route::post('/inventory/movements', [BoutiqueInventoryController::class, 'movements']);

    // Attributes
    Route::post('/attributes/list', [BoutiqueAttributeController::class, 'list']);
    Route::post('/attributes/store', [BoutiqueAttributeController::class, 'store']);
    Route::post('/attributes/update', [BoutiqueAttributeController::class, 'update']);
    Route::post('/attributes/delete', [BoutiqueAttributeController::class, 'delete']);

    // Attribute Values
    Route::post('/attribute-values/store', [BoutiqueAttributeController::class, 'storeValue']);
    Route::post('/attribute-values/update', [BoutiqueAttributeController::class, 'updateValue']);
    Route::post('/attribute-values/delete', [BoutiqueAttributeController::class, 'deleteValue']);

    // Banners
    Route::post('/banners/search', [BoutiqueBannerController::class, 'search']);
    Route::post('/banners/store', [BoutiqueBannerController::class, 'store']);
    Route::post('/banners/update', [BoutiqueBannerController::class, 'update']);
    Route::post('/banners/delete', [BoutiqueBannerController::class, 'delete']);
    Route::post('/banners/sort_update', [BoutiqueBannerController::class, 'sortUpdate']);
    Route::post('/banners/toggle', [BoutiqueBannerController::class, 'toggle']);
});

// Boutique Banners Público
Route::prefix('boutique')->middleware('bandwidth_usage')->group(function () {
    Route::post('/banners', [BoutiqueBannerController::class, 'publicList']);
});

// Fin Segmento Boutique


// Segmento Experience

Route::prefix('experience')->middleware('bandwidth_usage')->group(function () {
    Route::get('/upcoming_events', [ExperienceController::class, 'upcomingEvents']);
    Route::get('/past_events', [ExperienceController::class, 'pastEvents']);
    Route::post('/event_detail', [ExperienceController::class, 'eventDetail']);
    Route::get('/posts', [ExperienceController::class, 'posts']);
    Route::post('/post_detail', [ExperienceController::class, 'postDetail']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/events', [ExperienceController::class, 'storeEvent']);
        Route::post('/events/delete', [ExperienceController::class, 'deleteEvent']);

        Route::post('/admin/stories/search', [ExperienceController::class, 'adminStoriesSearch']);
        Route::get('/admin/stories/meta', [ExperienceController::class, 'adminStoriesMeta']);
        Route::post('/admin/stories/store', [ExperienceController::class, 'adminStoriesStore']);
        Route::post('/admin/stories/update', [ExperienceController::class, 'adminStoriesUpdate']);
        Route::post('/admin/stories/delete', [ExperienceController::class, 'adminStoriesDelete']);
        Route::post('/admin/stories/import_wordpress', [ExperienceController::class, 'adminStoriesImportWordpress']);
    });
});

// Fin Experience


// Segmento Benchmark ADS

Route::prefix('benchmark')->middleware(['bandwidth_usage', 'auth:sanctum', 'permission:access benchmark'])->group(function () {
    Route::get('/meta-token', [BenchmarkAdsController::class, 'metaTokenStatus']);
    Route::post('/meta-token', [BenchmarkAdsController::class, 'saveMetaToken']);
    Route::delete('/meta-token', [BenchmarkAdsController::class, 'clearMetaToken']);
    Route::post('/scan', [BenchmarkAdsController::class, 'scan']);
    Route::get('/search', [BenchmarkAdsController::class, 'search']);
    Route::get('/history', [BenchmarkAdsController::class, 'history']);
    Route::get('/history/{file}', [BenchmarkAdsController::class, 'historyDetail']);
    Route::get('/competitors', [BenchmarkAdsController::class, 'competitors']);
    Route::post('/competitors', [BenchmarkAdsController::class, 'addCompetitor']);
    Route::delete('/competitors/{name}', [BenchmarkAdsController::class, 'removeCompetitor']);
    Route::get('/reports', [BenchmarkAdsController::class, 'reports']);
});

// Fin Benchmark ADS


// Segmento Asistente Virtual

Route::prefix('assistant')->middleware('bandwidth_usage')->group(function () {
    Route::post('/chat', [AssistantController::class, 'chat']);
});

// Fin Asistente Virtual


// Segmento Store Management

Route::prefix('store-management')->middleware(['bandwidth_usage', 'auth:sanctum', 'permission:access store_management'])->group(function () {
    Route::post('/metrics', [StoreManagementController::class, 'dashboard']);

    Route::post('/orders/search', [StoreManagementController::class, 'searchOrders']);
    Route::post('/orders/detail', [StoreManagementController::class, 'orderDetail']);
    Route::post('/orders/update_status', [StoreManagementController::class, 'updateOrderStatus']);
    Route::post('/orders/generate_label', [StoreManagementController::class, 'generateLabel']);

    Route::post('/shipments/search', [StoreManagementController::class, 'searchShipments']);

    Route::post('/customers/search', [StoreCustomerController::class, 'search']);
    Route::post('/customers/detail', [StoreCustomerController::class, 'detail']);
    Route::post('/customers/orders', [StoreCustomerController::class, 'customerOrders']);

    Route::post('/points/search', [StorePointsController::class, 'search']);
    Route::post('/points/adjust', [StorePointsController::class, 'adjust']);
    Route::post('/points/customer_balance', [StorePointsController::class, 'customerBalance']);

    Route::post('/coupons/search', [StoreCouponController::class, 'search']);
    Route::post('/coupons/store', [StoreCouponController::class, 'store']);
    Route::post('/coupons/update', [StoreCouponController::class, 'update']);
    Route::post('/coupons/delete', [StoreCouponController::class, 'delete']);

    Route::post('/redemptions/search', [StorePointsController::class, 'searchRedemptions']);
    Route::post('/redemptions/update_status', [StorePointsController::class, 'updateRedemptionStatus']);
});

// Fin Store Management


// Segmento Admin Dashboard

Route::prefix('admin-dashboard')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {
    Route::post('/metrics', [AdminDashboardController::class, 'metrics']);
});

// Fin Admin Dashboard


// Segmento Settings

Route::prefix('settings')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {
    Route::post('/stripe', [SettingsController::class, 'stripe'])->middleware('role:developer|administrator');
    Route::post('/stripe/update', [SettingsController::class, 'updateStripe'])->middleware('role:developer|administrator');
    Route::post('/stripe/publishable_key', [SettingsController::class, 'publishableKey']);
    Route::post('/openpay', [SettingsController::class, 'openpay'])->middleware('role:developer|administrator');
    Route::post('/openpay/update', [SettingsController::class, 'updateOpenpay'])->middleware('role:developer|administrator');
});

// Fin Settings


// Segmento Incadea Sync

Route::prefix('boutique/admin/incadea')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {
    Route::post('/sync', [IncadeaSyncController::class, 'sync']);
    Route::post('/logs', [IncadeaSyncController::class, 'logs']);
    Route::post('/config', [IncadeaSyncController::class, 'getConfig']);
    Route::post('/update_config', [IncadeaSyncController::class, 'updateConfig']);
});

// Fin Incadea Sync


// Segmento WooCommerce Import

Route::prefix('boutique/admin/wc-import')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/upload', [WcImportController::class, 'upload']);
    Route::post('/sync-images', [WcImportController::class, 'syncImages']);
    Route::post('/sync-variant-attributes', [WcImportController::class, 'syncVariantAttributes']);
    Route::post('/cleanup', [WcImportController::class, 'cleanup']);
});

// Fin WooCommerce Import


// Segmento API Monitor

Route::prefix('developer/monitor')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(function () {
    Route::post('/logs', [ApiMonitorController::class, 'logs']);
    Route::post('/stats', [ApiMonitorController::class, 'stats']);
    Route::post('/health', [ApiMonitorController::class, 'health']);
});

// Fin API Monitor
