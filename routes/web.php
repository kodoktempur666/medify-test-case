<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MasterItemsController;
use App\Http\Controllers\CategoryItemsController;

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
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Master Items
Route::get('/master-items', [MasterItemsController::class, 'index'])->name('master-items.index');
Route::get('/master-items/search', [MasterItemsController::class, 'search'])->name('master-items.search');
Route::get('/master-items/export-excel', [MasterItemsController::class, 'exportExcel'])->name('master-items.export-excel');
Route::get('/master-items/download-excel', [MasterItemsController::class, 'exportExcel']);
Route::get('/master-items/form/{method}/{id?}', [MasterItemsController::class, 'formView'])->name('master-items.form');
Route::post('/master-items/form/{method}/{id?}', [MasterItemsController::class, 'formSubmit']);
Route::get('/master-items/view/{kode}', [MasterItemsController::class, 'singleView'])->name('master-items.view');
Route::get('/master-items/delete/{id}', [MasterItemsController::class, 'delete'])->name('master-items.delete');
Route::get('/master-items/update-random-data', [MasterItemsController::class, 'updateRandomData']);

// Categories (Kategori Items)
Route::get('/categories', [CategoryItemsController::class, 'index'])->name('categories.index');
Route::get('/categories/search', [CategoryItemsController::class, 'search'])->name('categories.search');
Route::get('/categories/form/{method}/{id?}', [CategoryItemsController::class, 'formView'])->name('categories.form');
Route::post('/categories/form/{method}/{id?}', [CategoryItemsController::class, 'formSubmit']);
Route::get('/categories/view/{id}', [CategoryItemsController::class, 'singleView'])->name('categories.view');
Route::get('/categories/delete/{id}', [CategoryItemsController::class, 'delete'])->name('categories.delete');
Route::get('/categories/pdf-download/{id}', [CategoryItemsController::class, 'pdfDownload'])->name('categories.pdf-download');
Route::get('/categories/pdf/{id}', [CategoryItemsController::class, 'pdfDownload']);

// Storage fallback route in case public/storage symlink is missing
Route::get('/storage/{path}', function ($path) {
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*');
