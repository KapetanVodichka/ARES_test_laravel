<?php

use App\Http\Controllers\DataParserController;
use Illuminate\Support\Facades\Route;

Route::get('/fetch-onu-data', [DataParserController::class, 'fetchData']);
