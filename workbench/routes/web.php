<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Models\User;

Route::get('/', function () {
    User::where(User::email(), 1)->get();

    return view('welcome');
});
