<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'landing');

Route::view('/docs/api', 'api-docs')->name('docs.api');
