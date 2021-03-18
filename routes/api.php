<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('getPincodeInfo/{pincode}', 'apiController@getPincodeInfo');

Route::get('home-slider', 'apiController@home_slider');

Route::post('today-wheather', 'apiController@todayWheateher');

Route::get('customer-token', 'apiController@getCustomerType');

Route::get('agri-type', 'apiController@agri_type');

Route::get('agri-tool', 'apiController@agri_tool');

Route::post('agri-type-enquiry', 'apiController@agri_type_enquiry');

Route::post('agri-tool-enquiry', 'apiController@agri_tool_enquiry');

Route::get('feed-list', 'apiController@feedList');

// Login API
Route::post('customer-login', 'apiController@customerLogin');

// Verify Customer
Route::post('customer-verify', 'apiController@customerVerify');
Route::post('resend-sms', 'apiController@resendSMS');

// Register
Route::post('customer-step3', 'apiController@customerRegister');

// Pin Codes
Route::get('pin-code', 'apiController@pinCode');
Route::get('setting-data', 'apiController@settingData');

Route::get('customer-profile', 'apiController@customer_profile');
Route::post('update-profile', 'apiController@update_profile');

Route::post('customer-logout', 'apiController@customer_logout');

Route::post('finance-enquiry', 'apiController@finance_enquiry');

Route::get('all-city', 'apiController@allCities');
Route::get('tractor-company', 'apiController@tractor_company');

Route::get('tractor-hp', 'apiController@all_hp');

Route::get('payment-type', 'apiController@payment_type');
Route::get('what-need-list', 'apiController@what_need_list');
Route::post('rent-enquiry', 'apiController@rent_enquiry');
Route::post('rent-in-enquiry', 'apiController@rent_in_enquiry');

Route::post('verify-customer-mobile', 'apiController@verifyOrderMobile');

Route::get('year-manufacturer', 'apiController@year_manufacturer');
Route::post('tractor-sale-enquiry', 'apiController@tractorSaleEnquiry');
Route::post('tractor-sale-enquiry-verify', 'apiController@tractorSaleEnquiryVerify');

Route::post('tractor-purchase-enquiry', 'apiController@tractorPurchaseEnquiry');
Route::post('purchase-old-result', 'apiController@purchaseOldResult');

Route::get('labour-need', 'apiController@all_labour_need');
Route::get('labour-purpose', 'apiController@all_purpose');
Route::post('labour-enquiry', 'apiController@labourEnquiry');
Route::post('labour-result', 'apiController@labour_result');

Route::get('insurance-type', 'apiController@insurance_type');
Route::post('insurance-enquiry', 'apiController@insurance_enquiry');

Route::get('land-type', 'apiController@land_type');
Route::get('land-size', 'apiController@all_land_size');
Route::get('rent-time', 'apiController@all_rent_time');

Route::post('agriland-rent-enquiry', 'apiController@agri_land_rent_enquiry');
Route::post('agriland-rent-result', 'apiController@agriland_rent_results');

Route::post('agriland-sale-enquiry', 'apiController@agrilandSaleEnquiry');
Route::post('agriland-purchase-result', 'apiController@agriland_purchase_result');

Route::post('agriland-feedback', 'apiController@agriland_feedback');

Route::get('enquiry-type', 'apiController@enquiry_type');
Route::post('enquiry-tracking', 'apiController@enquiry_tracking');

Route::get('soil-test-type', 'apiController@soiltest_type');
Route::get('seva-kendra', 'apiController@get_sevakendra');
Route::post('create-soiltest-order', 'apiController@create_soiltest_order');
Route::get('customer-soil-orderlist', 'apiController@get_customer_soilOdr');
Route::get('soil-orderdetail', 'apiController@get_soilOdrDetail');

Route::post('update_order-test-type', 'apiController@updateOdrTestType');

Route::post('report-created', 'apiController@orderReportCreated');

Route::get('create-soil-report', 'apiController@create_soil_report');

Route::get('notification-list', 'apiController@notification_list');

Route::get('birth-year-list', 'apiController@birth_year');





/* Partner API */
Route::post('partner-login', 'apiPartnerController@partnerLogin');
Route::post('forgot-password', 'apiPartnerController@forgotPassword');
Route::post('update-password', 'apiPartnerController@partnerChangePassword');
Route::post('resend-partner-sms', 'apiPartnerController@resendSMS');
Route::get('partner-profile', 'apiPartnerController@partner_profile');
Route::post('update-partner-profile', 'apiPartnerController@update_profile');
Route::post('partner-logout', 'apiPartnerController@partner_logout');
Route::post('partner-rent-enquiry', 'apiPartnerController@partner_rent_enquiry');
Route::post('partner-rent-in-result', 'apiPartnerController@partner_rent_result_enquiry');

Route::post('partner-tractor-sale-enquiry', 'apiPartnerController@tractor_sale_enquiry');
Route::post('partner-tractor_purchase-enquiry', 'apiPartnerController@tractor_purchase_enquiry');
Route::post('partner-tractor-old-enquiry', 'apiPartnerController@purchase_old_results');
Route::post('partner-insurance-enquiry', 'apiPartnerController@insurance_enquiry');
Route::post('partner-enquiry-tracking', 'apiPartnerController@enquiry_tracking');
Route::post('partner-soil-order', 'apiPartnerController@create_soiltest_order');
Route::get('partner-order-history', 'apiPartnerController@get_partner_soilOdr');
Route::post('partner-update-order', 'apiPartnerController@updateOdrTestType');

Route::post('verify-mobile', 'apiPartnerController@verifyOrderMobile');
Route::post('partner-order-report', 'apiPartnerController@orderReportCreated');
Route::get('partner-notification', 'apiPartnerController@notification_list');

Route::get('partner-dashboard', 'apiPartnerController@partner_dashboard');
Route::get('partner-order-commission', 'apiPartnerController@partner_order_commision');

Route::get('partner-trator-commission', 'apiPartnerController@partner_tractor_commision');
