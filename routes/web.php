<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Public\PostController as PublicPostController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Dashboard\DashboardHomeController;
use App\Http\Controllers\Dashboard\PostController as DashboardPostController;
use App\Http\Controllers\Dashboard\ImportController as DashboardImportController;
use App\Http\Controllers\Dashboard\ScrapeController;
use App\Http\Controllers\Dashboard\ScrapedJsonController;
use App\Http\Controllers\Dashboard\FacebookGroupController;
use App\Http\Controllers\Dashboard\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Guest Access)
|--------------------------------------------------------------------------
| Guest can only search and view search results via signed URLs.
| No direct access to /posts, /posts/{slug}
*/
Route::get('/', [PublicPostController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Search post with signed token - ONLY for viewing search results
Route::get('/search/post/{token}', [SearchController::class, 'viewPost'])->name('search.post.view');

// Deny direct access to posts for guests
Route::middleware(['auth'])->group(function () {
    Route::get('/posts', function () {
        abort(403, 'Direct access to posts is not allowed. Please use search.');
    })->name('posts.index');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES (Login Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('dashboard.')->group(function () {

        // Dashboard Home
        Route::get('/', [DashboardHomeController::class, 'index'])->name('index');
        Route::get('/dashboard', [DashboardHomeController::class, 'index'])->name('dashboard');
        Route::get('/home', [DashboardHomeController::class, 'index'])->name('home');

        // Posts Management
        Route::prefix('posts')->name('posts.')->group(function () {
            Route::get('/', [DashboardPostController::class, 'index'])->name('index');
            Route::get('/create', [DashboardPostController::class, 'create'])->name('create');
            Route::post('/', [DashboardPostController::class, 'store'])->name('store');
            Route::get('/{id}', [DashboardPostController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [DashboardPostController::class, 'edit'])->name('edit');
            Route::put('/{id}', [DashboardPostController::class, 'update'])->name('update');
            Route::delete('/{id}', [DashboardPostController::class, 'destroy'])->name('destroy');
        });

        // Import Management
        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/', [DashboardImportController::class, 'index'])->name('index');
            Route::get('/create', [DashboardImportController::class, 'create'])->name('create');
            Route::post('/', [DashboardImportController::class, 'store'])->name('store');
            Route::get('/{id}', [DashboardImportController::class, 'show'])->name('show');
        });

        // Scrape Management
        Route::prefix('scrape')->name('scrape.')->group(function () {
            Route::get('/', [ScrapeController::class, 'index'])->name('index');
            Route::post('/start-all', [ScrapeController::class, 'startAll'])->name('startAll');
            Route::post('/{id}/start', [ScrapeController::class, 'startGroup'])->name('start-group');
            Route::post('/{id}/stop', [ScrapeController::class, 'stopGroup'])->name('stop-group');
        });

        Route::prefix('scraped-json')->name('scraped-json.')->group(function () {
            Route::get('/', [ScrapedJsonController::class, 'index'])->name('index');
        });

        // Facebook Groups Management
        Route::prefix('facebook-groups')->name('facebook-groups.')->group(function () {
            Route::get('/', [FacebookGroupController::class, 'index'])->name('index');
            Route::get('/create', [FacebookGroupController::class, 'create'])->name('create');
            Route::post('/', [FacebookGroupController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [FacebookGroupController::class, 'edit'])->name('edit');
            Route::put('/{id}', [FacebookGroupController::class, 'update'])->name('update');
            Route::delete('/{id}', [FacebookGroupController::class, 'destroy'])->name('destroy');
        });

        // Settings Management
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('/', [SettingController::class, 'update'])->name('update');
            Route::post('/test-ai', [SettingController::class, 'testAiProvider'])->name('testAiProvider');
        });
    });
});

/*
|--------------------------------------------------------------------------
| GUEST AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    // Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    // Route::post('/register', [RegisteredUserController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| LOGOUT ROUTE (Available to all authenticated users)
|--------------------------------------------------------------------------
*/
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');
