<?php
use App\Http\Controllers\Site\AccountController;
use App\Http\Controllers\Site\AuthController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ExportController;
use App\Http\Controllers\Site\InvoiceController;
use App\Http\Controllers\Site\QuoteController;
use App\Http\Controllers\Site\SiteController;
use App\Http\Controllers\Site\TrackController;
use App\Http\Middleware\StaffOnly;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// ---- Site public (clients) ----
Route::get('/', [SiteController::class, 'home'])->name('home');
Route::post('/demande', [QuoteController::class, 'store'])->middleware('throttle:5,1')->name('quote.store');
Route::get('/suivi', [TrackController::class, 'form'])->name('track');
Route::post('/suivi', [TrackController::class, 'lookup'])->middleware('throttle:10,1');

// ---- Connexion unique : chacun est dirigé vers son interface (admin, caisse, atelier, espace client) ----
Route::get('/connexion', [AuthController::class, 'form'])->name('login');
Route::post('/connexion', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::redirect('/equipe', '/connexion');
Route::get('/inscription', [AuthController::class, 'registerForm'])->name('register');
Route::post('/inscription', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');

// ---- Espace client : compte, export et suppression des données ----
Route::middleware('auth')->prefix('mon-compte')->group(function () {
    Route::get('/', [AccountController::class, 'show'])->name('account');
    Route::put('/', [AccountController::class, 'update'])->name('account.update');
    Route::put('/mot-de-passe', [AccountController::class, 'password'])->name('account.password');
    Route::get('/donnees.json', [AccountController::class, 'export'])->name('account.export');
    Route::delete('/', [AccountController::class, 'destroy'])->name('account.destroy');
});

// ---- Pages légales (obligatoires pour Google Play) ----
Route::get('/confidentialite', [PageController::class, 'privacy'])->name('privacy');
Route::get('/conditions', [PageController::class, 'terms'])->name('terms');
Route::get('/suppression-compte', [AccountController::class, 'deletionInfo'])->name('deletion');
Route::post('/suppression-compte', [AccountController::class, 'deletionRequest'])->middleware('throttle:3,1')->name('deletion.request');

// ---- Documents (équipe connectée) ----
Route::get('/factures/{sale}', [InvoiceController::class, 'show'])->middleware(['auth', StaffOnly::class])->name('invoice');
Route::get('/recus/{sale}', [InvoiceController::class, 'receipt'])->middleware(['auth', StaffOnly::class])->name('receipt');
Route::get('/export/{type}', [ExportController::class, 'csv'])->whereIn('type', ['ventes', 'charges', 'achats'])->middleware(['auth', StaffOnly::class])->name('export');
Route::get('/modele-articles.csv', [ExportController::class, 'template'])->middleware(['auth', StaffOnly::class])->name('products.template');
Route::get('/rapport.pdf', [ExportController::class, 'pdf'])->middleware(['auth', StaffOnly::class])->name('report.pdf');

// ---- Fiche d'un article ----
Route::get('/article/{product}', [SiteController::class, 'product'])->name('product');

// ---- Recherche dans tout le site ----
Route::get('/recherche', [SiteController::class, 'search'])->name('search');

// ---- Photos des articles (dossier storage/app/public, sans lien symbolique) ----
Route::get('/media/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $disk = Storage::disk('public');
    abort_unless($disk->exists($path), 404);
    return $disk->response($path, null, ['Cache-Control' => 'public, max-age=604800']);
})->where('path', '.*')->name('media');

// ---- Pages des sous-menus : /boutique/sacs , /imprimerie/baches ----
Route::get('/{menu}/{category:slug}', [SiteController::class, 'category'])
    ->whereIn('menu', ['boutique', 'imprimerie'])->name('category');
