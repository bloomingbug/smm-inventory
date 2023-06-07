<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Apps\RoleController;
use App\Http\Controllers\Apps\UserController;
use App\Http\Controllers\Apps\ProductController;
use App\Http\Controllers\Apps\CategoryController;
use App\Http\Controllers\Apps\CustomerController;
use App\Http\Controllers\Apps\DashboardController;
use App\Http\Controllers\Apps\PermissionController;
use App\Http\Controllers\Apps\ProfitController;
use App\Http\Controllers\Apps\SaleController;
use App\Http\Controllers\Apps\TransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route("login");
})->middleware("guest");

Route::get("/login", function () {
    return Inertia::render("Auth/Login");
})->middleware("guest")->name("login");

Route::prefix("apps")->group(function () {
    Route::middleware(["auth"])->group(function () {
        Route::get("/dashboard", DashboardController::class)->name("apps.dashboard");

        Route::get("/permissions", PermissionController::class)->name("apps.permissions.index");

        Route::resource("/roles", RoleController::class, ["as" => "apps", "except" => "show"]);

        Route::resource("/users", UserController::class, ["as" => "apps", "except" => "show"]);

        Route::resource("/categories", CategoryController::class, ["as" => "apps", "except" => "show"]);

        Route::resource("/products", ProductController::class, ["as" => "apps", "except" => "show"]);

        Route::resource("/customers", CustomerController::class, ["as" => "apps", "except" => "show"]);

        Route::get('/transactions', [TransactionController::class, 'index'])->name('apps.transactions.index');
        Route::post('/transactions/searchProduct', [TransactionController::class, 'searchProduct'])->name('apps.transactions.searchProduct');
        Route::post('/transactions/addToCart', [TransactionController::class, 'addToCart'])->name('apps.transactions.addToCart');
        Route::post('/transactions/destroyCart', [TransactionController::class, 'destroyCart'])->name('apps.transactions.destroyCart');
        Route::post('/transactions/store', [TransactionController::class, 'store'])->name('apps.transactions.store');
        Route::get('/transactions/print', [TransactionController::class, 'print'])->name('apps.transactions.print');

        Route::get('/sales', [SaleController::class, 'index'])->name('apps.sales.index');
        Route::get('/sales/filter', [SaleController::class, 'filter'])->name('apps.sales.filter');
        Route::get('/sales/export', [SaleController::class, 'export'])->name('apps.sales.export');
        Route::get('/sales/pdf', [SaleController::class, 'pdf'])->name('apps.sales.pdf');

        Route::get('/profits', [ProfitController::class, 'index'])->name('apps.profits.index');
        Route::get('/profits/filter', [ProfitController::class, 'filter'])->name('apps.profits.filter');
        Route::get('/profits/export', [ProfitController::class, 'export'])->name('apps.profits.export');
        Route::get('/profits/pdf', [ProfitController::class, 'pdf'])->name('apps.profits.pdf');
    });
});
