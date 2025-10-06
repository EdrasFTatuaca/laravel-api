<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ClientController;

Route::apiResource('clients', ClientController::class);
