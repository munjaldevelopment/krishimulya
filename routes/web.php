<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/terms', 'StaticPageController@termsPage');
Route::get('/privacy', 'StaticPageController@privacyPage');

Route::get('/terms', 'StaticPageController@termsPage');

// Soil Test
Route::get('/kt_login', 'SoilTestController@KTLogin');
Route::get('/create_farmer', 'SoilTestController@createFarmer');
Route::get('/get_farmer', 'SoilTestController@getFarmer');

Route::get('/create_area', 'SoilTestController@createArea');
Route::get('/get_area', 'SoilTestController@getArea');

Route::get('/farmer_area_report', 'SoilTestController@farmerAreaReport');

Route::get('/app-popup', function () {
	$sliderList = DB::table('app_popups')->where('status', '=', 1)->orderBy('id', 'DESC')->first();
    return view('app-popup', compact('sliderList'));
});