<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes

    Route::get('dashboard', 'AdminController@dashboard')->name('backpack.dashboard');

    Route::crud('customer', 'CustomerCrudController');
    Route::crud('feeds', 'FeedsCrudController');
    Route::crud('states', 'StatesCrudController');
    Route::crud('article', 'ArticleCrudController');
    Route::crud('category', 'CategoryCrudController');
    Route::crud('tag', 'TagCrudController');
    Route::crud('finance_enquiry', 'FinanceEnquiryCrudController');

    Route::crud('tractor_rent_enquiry', 'TractorRentEnquiryCrudController');
    
    Route::crud('tractor_sell_enquiry', 'TractorSellEnquiryCrudController');
    
    Route::crud('tractor_purchase_enquiry', 'TractorPurchaseEnquiryCrudController');
    
    Route::crud('tractor_refinance_enquiry', 'TractorRefinanceEnquiryCrudController');
    
    
    Route::crud('labour_enquiry', 'LabourEnquiryCrudController');
    Route::crud('labour_enquiry_partner', 'LabourEnquiryPartnerCrudController');
    
    Route::crud('insurancetype', 'InsuranceTypeCrudController');
    Route::crud('company', 'CompanyCrudController');
    Route::crud('hoursepower', 'HoursePowerCrudController');

    Route::crud('insuranceenquiry', 'InsuranceEnquiryCrudController');
    Route::crud('insuranceenquiry_partner', 'InsuranceEnquiryPartnerCrudController');
    
    Route::crud('agrilandrentenquiry', 'AgrilandRentEnquiryCrudController');
    Route::crud('agrilandsaleenquiry', 'AgrilandsaleEnquiryCrudController');

    Route::crud('agrilandrentenquiry_partner', 'AgrilandRentEnquiryPartnerCrudController');
    Route::crud('agrilandsaleenquiry_partner', 'AgrilandsaleEnquiryPartnerCrudController');
    
    Route::crud('city', 'CityCrudController');
    Route::crud('feedcategories', 'FeedCategoriesCrudController');
    Route::crud('homeslider', 'HomeSliderCrudController');
    
    Route::crud('feedback', 'FeedbackCrudController');
    Route::crud('feedback_partner', 'FeedbackPartnerCrudController');
    
    Route::crud('enquirytracking', 'EnquiryTrackingCrudController');
    Route::crud('paymenttype', 'PaymentTypeCrudController');
    Route::crud('purposetype', 'PurposeTypeCrudController');
    Route::crud('labourtype', 'LabourTypeCrudController');
    Route::crud('landtype', 'LandTypeCrudController');
    Route::crud('landsize', 'LandSizeCrudController');
    Route::crud('renttime', 'RentTimeCrudController');
    Route::crud('soiltesttype', 'SoilTestTypeCrudController');
    Route::crud('soiltestorders', 'SoilTestOrdersCrudController');
    Route::crud('sevakendra', 'SevaKendraCrudController');
    Route::crud('notification', 'NotificationCrudController');
    Route::crud('partners', 'PartnersCrudController');
    Route::crud('walletpayment', 'WalletPaymentCrudController');
    Route::crud('agri_type', 'Agri_typeCrudController');
    Route::crud('agri_type_enquiry', 'Agri_type_enquiryCrudController');
    Route::crud('agri_tool', 'Agri_toolCrudController');
    
    Route::crud('agri_tool_enquiry', 'AgriToolEnquiryCrudController');
    Route::crud('agri_tool_enquiry_partner', 'AgriToolEnquiryPartnerCrudController');
    
    Route::crud('pincode', 'PinCodeCrudController');
    Route::crud('app_popup', 'AppPopupCrudController');

    Route::get('sendNotification', 'UserNotificationController@sendNotification');
    Route::post('sendNotification', 'UserNotificationController@sendNotificationMessage');
    Route::crud('vendorservice', 'VendorServiceCrudController');
    Route::crud('vendor', 'VendorCrudController');
});