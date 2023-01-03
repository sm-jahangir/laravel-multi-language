<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\LanguageController;

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
Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear');
    return "Clear all routes";
});

Route::get('language', [LanguageController::class, 'langIndex'])->name('language.index');
Route::post('language/store', [LanguageController::class, 'langStore'])->name('language.store')->middleware('demo');
Route::get('language/destroy/{id}', [LanguageController::class, 'langDestroy'])->name('language.destroy');
Route::get('language/translate/{id}', [LanguageController::class, 'translate_create'])->name('language.translate');
Route::post('language/translate/store', [LanguageController::class, 'translate_store'])->name('language.translate.store')->middleware('demo');
Route::get('language/default/{id}', [LanguageController::class, 'defaultLanguage'])->name('language.default');

/**
 * Version 3.2.0
 */
Route::get('language/translate/categories/{id}', [LanguageController::class, 'translate_categories_create'])->name('language.categories.translate'); // version 3.2
Route::post('language/translate/categories/store', [LanguageController::class, 'translate_categories_store'])->name('language.categories.translate.store')->middleware('demo'); // version 3.2

Route::get('language/translate/products/{id}', [LanguageController::class, 'translate_products_create'])->name('language.products.translate'); // version 3.2
Route::post('language/translate/products/store', [LanguageController::class, 'translate_products_store'])->name('language.products.translate.store')->middleware('demo'); // version 3.2
/**
 * Version 3.2.0
 */


Route::post('language/change', [LanguageController::class, 'languagesChange'])->name('language.change');
