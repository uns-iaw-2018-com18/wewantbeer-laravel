<?php

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

Route::get('/', function() {
    return redirect('login');
});

Route::get('/admin', function() {
    return view('admin');
}) -> middleware('auth');

Route::get('/add', function() {
    return view('crud');
}) -> middleware('auth');

Route::post('add','AddController@add');

// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm') -> name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout') -> name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');
