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

Route::get('app-popup', 'apiController@appPopup');

Route::get('home-slider', 'apiController@homeSlider');

Route::post('today-wheather', 'apiController@todayWheateher');

Route::get('customer-token', 'apiController@getCustomerType');

Route::get('agri-type', 'apiController@agriType');

Route::get('agri-tool', 'apiController@agriTool');

Route::post('agri-type-enquiry', 'apiController@agriTypeEnquiry');

Route::post('agri-tool-enquiry', 'apiController@agriToolEnquiry');

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
Route::post('rent-in-enquiry', 'apiController@rent_in_enquiry'); // Search

Route::post('verify-customer-mobile', 'apiController@verifyOrderMobile');

Route::get('year-manufacturer', 'apiController@year_manufacturer');
Route::post('tractor-sale-enquiry', 'apiController@tractorSaleEnquiry');
Route::post('tractor-sale-enquiry-multi-images', 'apiController@tractorSaleMultimage');
Route::post('tractor-sale-enquiry-verify', 'apiController@tractorSaleEnquiryVerify');

Route::post('tractor-refinance-enquiry', 'apiController@tractorRefinanceEnquiry');

Route::post('tractor-purchase-enquiry', 'apiController@tractorPurchaseEnquiry');
Route::post('purchase-old-result', 'apiController@purchaseOldResult'); // Search

Route::post('all-tractor-images', 'apiController@all_tractor_sale_enquiry_images'); 

Route::get('labour-need', 'apiController@all_labour_need');
Route::get('labour-purpose', 'apiController@all_purpose');
Route::post('labour-enquiry', 'apiController@labourEnquiry');
Route::post('labour-result', 'apiController@labourResult'); // Search

Route::get('insurance-type', 'apiController@insurance_type');
Route::post('insurance-enquiry', 'apiController@insurance_enquiry');

Route::get('land-type', 'apiController@land_type');
Route::get('land-size', 'apiController@all_land_size');
Route::get('rent-time', 'apiController@all_rent_time');


Route::get('crop-type', 'apiController@cropType');
Route::get('soil-type', 'apiController@soilType');

Route::post('agriland-rent-enquiry', 'apiController@agri_land_rent_enquiry');
Route::post('agriland-rent-result', 'apiController@agrilandRentResults'); // Search

Route::post('agriland-sale-enquiry', 'apiController@agrilandSaleEnquiry');
Route::post('agriland-purchase-result', 'apiController@agrilandPurchaseResult'); // Search

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


// Listing API
Route::get('tractor-sale-history', 'apiController@tractorSaleHistory');
Route::get('tractor-purchase-history', 'apiController@tractorPurchaseHistory');
Route::get('tractor-rent-history', 'apiController@tractorRentHistory');
Route::get('tractor-refinance-history', 'apiController@tractorRefinanceHistory');

Route::get('labour-enquiry-history', 'apiController@labourEnquiryHistory');

Route::get('agriland-rent-enquiry-history', 'apiController@agriRentEnquiryHistory');
Route::get('agriland-sale-enquiry-history', 'apiController@agriSaleEnquiryHistory');


// Detail
Route::post('tractor-sale-detail', 'apiController@tractorSaleDetail');
Route::post('tractor-purchase-detail', 'apiController@tractorPurchaseDetail');
Route::post('tractor-rent-detail', 'apiController@tractorRentDetail');
Route::post('tractor-refinance-detail', 'apiController@tractorRefinanceDetail');

Route::post('labour-enquiry-detail', 'apiController@labourEnquiryDetail');

Route::post('agriland-rent-enquiry-detail', 'apiController@agriRentEnquiryDetail');
Route::post('agriland-sale-enquiry-detail', 'apiController@agriSaleEnquiryDetail');


// Update
Route::post('tractor-sale-detail-save', 'apiController@tractorSaleDetailSave');
Route::post('tractor-sale-update-multi-image', 'apiController@tractorSaleUpdateMultimage');
Route::post('tractor-purchase-detail-save', 'apiController@tractorPurchaseDetailSave');
Route::post('tractor-rent-detail-save', 'apiController@tractorRentDetailSave');
Route::post('tractor-refinance-detail-save', 'apiController@tractorRefinanceDetailSave');

Route::post('labour-enquiry-detail-save', 'apiController@labourEnquiryDetailSave');

Route::post('agriland-rent-enquiry-detail-save', 'apiController@agriRentEnquiryDetailSave');
Route::post('agriland-sale-enquiry-detail-save', 'apiController@agriSaleEnquiryDetailSave');

// Send notification
Route::get('send-notification/{customer_id}', 'apiController@sendNotification');



/* Partner API */
Route::post('partner-login', 'apiPartnerController@partnerLogin');
Route::post('forgot-password', 'apiPartnerController@forgotPassword');
Route::post('update-password', 'apiPartnerController@partnerChangePassword');
Route::post('resend-partner-sms', 'apiPartnerController@resendSMS');
Route::get('partner-profile', 'apiPartnerController@partner_profile');
Route::get('partner-dashboard', 'apiPartnerController@partnerDashboard');
Route::post('partner-leads', 'apiPartnerController@partnerLeads');
Route::post('partner-leads-status-change', 'apiPartnerController@partnerLeadsStatusChange');

Route::post('update-partner-profile', 'apiPartnerController@update_profile');
Route::post('partner-logout', 'apiPartnerController@partner_logout');
Route::get('partner-token', 'apiPartnerController@getPartnerType');

// Service API
Route::post('agriland-rent-enquiry-partner', 'apiPartnerController@agrilandRentEnquiry');
Route::post('agriland-sale-enquiry-partner', 'apiPartnerController@agrilandSaleEnquiry');
Route::post('agri-tool-enquiry-partner', 'apiPartnerController@agriToolEnquiry');
Route::post('insurance-enquiry-partner', 'apiPartnerController@insuranceEnquiry');
Route::post('labour-enquiry-partner', 'apiPartnerController@labourEnquiry');
Route::post('tractor-purchase-enquiry-partner', 'apiPartnerController@tractorPurchaseEnquiry');
Route::post('tractor-refinance-enquiry-partner', 'apiPartnerController@tractorRefinanceEnquiry');
Route::post('tractor-rent-enquiry-partner', 'apiPartnerController@tractorRentEnquiry');
Route::post('tractor-sale-enquiry-partner', 'apiPartnerController@tractorSaleEnquiry');
Route::post('tractor-sale-enquiry-multi-images-partner', 'apiPartnerController@tractorSaleMultimage');
Route::post('soiltest-order-partner', 'apiPartnerController@soilTestEnquiry');
Route::post('agri-type-enquiry-partner', 'apiPartnerController@agriTypeEnquiry');

Route::post('agriland-feedback-partner', 'apiPartnerController@agriland_feedback');

Route::post('enquiry-tracking-partner', 'apiPartnerController@enquiry_tracking');

Route::post('verify-mobile', 'apiPartnerController@verifyOrderMobile');
//Route::get('partner-dashboard', 'apiPartnerController@partner_dashboard');
Route::get('partner-notification', 'apiPartnerController@notification_list');

Route::get('app-popup-partner', 'apiPartnerController@appPopup');
Route::get('lead-status', 'apiPartnerController@leadStatus');
Route::get('lead-status-all', 'apiPartnerController@leadStatusAll');

Route::get('partner-order-commission', 'apiPartnerController@partner_order_commision');
Route::get('partner-trator-commission', 'apiPartnerController@partner_tractor_commision');
Route::post('partner-tractor-sale-enquiry-images', 'apiPartnerController@all_tractor_sale_enquiry_images');
Route::get('crop-material', 'apiPartnerController@cropMaterial');
Route::post('crop-material-enquiry', 'apiPartnerController@cropmaterialEnquiry');


/// soil testing
Route::get('soil-login', 'apiSoilController@soilLogin');
Route::get('soil-myinfo', 'apiSoilController@soilMyInfo');
Route::get('soil-create-farmer', 'apiSoilController@soilCreateFarmer');
Route::get('soil-get-farmer', 'apiSoilController@soilGetFarmer');
Route::get('soil-create-test', 'apiSoilController@soilCreateTest');
Route::get('soil-get-test', 'apiSoilController@soilGetTest');


Route::post('update-enquiry-contact', 'apiController@update_contact_info_enquiry');

Route::post('missed-call', 'apiController@missedCall');

// Punch-in / out
Route::get('call-type', 'apiPartnerController@callType');
Route::get('tractor-make', 'apiPartnerController@tractorMake');
Route::get('tractor-model', 'apiPartnerController@tractorModel');
Route::get('proposed-crop', 'apiPartnerController@proposedCrop');


Route::get('partner-checkin', 'apiPartnerController@partnerCheckin');
Route::get('partner-checkout', 'apiPartnerController@partnerCheckout');
Route::get('partner-checkin-latlong', 'apiPartnerController@partnerCheckinLatLong');
Route::post('partner-questionairre-activity', 'apiPartnerController@partnerQuestionairreActivity');
Route::post('partner-questionairre-record', 'apiPartnerController@partnerQuestionairreRecord');
Route::post('partner-questionairre-soiltest', 'apiPartnerController@partnerQuestionairreSoilTest');

Route::get('cultivation-no', 'apiPartnerController@cultivation_no');
Route::get('irrigation-source', 'apiPartnerController@irrigation_source');
Route::get('irrigation-frequency', 'apiPartnerController@irrigation_frequncy');
Route::get('crop-type-list', 'apiPartnerController@crop_type');

Route::post('questioner-enquiry', 'apiPartnerController@questionerEnquiry');