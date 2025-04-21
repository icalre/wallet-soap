<?php
use App\Http\Controllers\SoapWalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para el servicio SOAP
Route::prefix('soap')->middleware('api')->group(function () {
    Route::get('wallet/wsdl', [SoapWalletController::class, 'wsdl']);
    Route::any('wallet', [SoapWalletController::class, 'handleRequest']);
});
