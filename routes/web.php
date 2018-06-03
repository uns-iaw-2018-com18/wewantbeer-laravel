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

Route::group(['prefix' => '/admin'], function() {

  Route::get('/', function() {
    return view('admin');
  }) -> middleware('auth');

  Route::get('/add', function() {
    return view('add');
  }) -> middleware('auth');

  Route::post('/add', 'AddController@add');

  Route::get('/edit', function() {
    return view('edit');
  }) -> middleware('auth');

  Route::get('/edit/{id}', 'EditController@getEdit') -> middleware('auth');

  Route::post('/edit/{id}', 'EditController@edit') -> middleware('auth');

  Route::get('/delete', function() {
    return view('admin');
  }) -> middleware('auth');

  // Cambiar a POST
  Route::get('/delete/{id}', 'DeleteController@delete') -> middleware('auth');

});

// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm') -> name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout') -> name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');
