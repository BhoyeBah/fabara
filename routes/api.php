<?php
use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

// API pour la future application mobile (authentification par jeton Sanctum).
Route::get('/categories', [ApiController::class, 'categories']);           // catalogue public
Route::post('/login', [ApiController::class, 'login'])->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [ApiController::class, 'me']);
    Route::post('/logout', [ApiController::class, 'logout']);
    Route::get('/account/export', [ApiController::class, 'exportAccount']);    // client
    Route::delete('/account', [ApiController::class, 'deleteAccount']);        // client : suppression dans l'application
    Route::get('/sales', [ApiController::class, 'sales']);                    // admin, gérante
    Route::post('/sales', [ApiController::class, 'storeSale']);               // admin, gérante
    Route::get('/print-orders', [ApiController::class, 'printOrders']);       // admin, agent
    Route::post('/print-orders/{order}/advance', [ApiController::class, 'advance']);
    Route::post('/print-orders/{order}/deliver', [ApiController::class, 'deliver']);
});
