<?php

use App\Http\Controllers\ComprovantesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('pdf/{id}',[ComprovantesController::class, 'geraPdf'])->name('comprovanteNormal');
Route::get('pdfPdv/{id}',[ComprovantesController::class, 'geraPdfPDV'])->name('comprovantePDV');

