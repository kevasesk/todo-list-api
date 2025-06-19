<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('index');

//Route::get('/login', function () {
//    return view('login');
//})->name('login');

Route::get('/tasks', function () {
    return view('tasks');
})->name('tasks');
