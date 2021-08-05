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

Route::get('/assignVendor', 'HomeController@assignVendor');

Route::get('/sendNotification', 'HomeController@sendNotification');


Route::get('/app-popup', function () {
	$sliderList = DB::table('app_popups')->where('status', '=', 1)->orderBy('id', 'DESC')->first();
    return view('app-popup', compact('sliderList'));
});

Route::get('/update-notification', function () {
	$sliderList = DB::table('notifications')->where('id', '228899')->update(['is_sent' => '0']);
	$sliderList = DB::table('notifications')->where('id', '231358')->update(['is_sent' => '0']);
	$sliderList = DB::table('notifications')->where('id', '233879')->update(['is_sent' => '0']);
});

Route::get('/update-age', function () {
	/*$file = fopen("/home/krishi55/public_html/public/customers_email.csv","r");

	$k = 0;
	while(! feof($file))
	{
		$code = fgetcsv($file);
		$id = $code[0];
		//echo $id;
		if($id != "id")
		{
			$sql = "UPDATE customers SET email = '".str_replace(" ", ".", strtolower($code[1]))."@krishimulya.com' WHERE id = '".$code[0]."'";
			echo $sql."<br />";
			\DB::statement($sql);
		}
		$k++;
	}

	fclose($file);*/
});

Route::get('/play-store', function () {
	$gplay = new \Nelexa\GPlay\GPlayApps($defaultLocale = 'en_US', $defaultCountry = 'us');
	$appInfo = $gplay->getAppInfo('com.microprixs.krishimulya');

	echo $appInfo->getInstalls().",".$appInfo->getAppVersion();
});