<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use DB;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Hash;
use URL;
use File;
use Session;
use QR_Code\QR_Code;
use App\Models\Setting;

class apiPartnerController extends Controller
{
	public function httpGet($url)
    {
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $head = curl_exec($ch); 
        curl_close($ch);
        return $head;
    }

    public function sendNotification($customer_id, $lead_id, $title, $message, $image = '')
    {
        $date = date('Y-m-d H:i:s');
        $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $customer_id, 'lead_id' => $lead_id, 'notification_title' => $title, 'notification_content' => $message, 'notification_type' => 'customer_notification', 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);
        //echo $success.",".$fail.",".$total; exit;
    }

    //START LOGIN
	public function partnerLogin(Request $request)
    {
        try 
        {
            $mobile = $request->mobile;
            $password = $request->password;
            $device_id = $request->device_id;
            $fcmToken = $request->fcmToken;
            $refer_code = $request->refer_code;
            
            $error = "";

            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($password == ""){
                $error = "Please enter valid password";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($error == ""){
                $json = $userData = array();
                $mobile = $mobile;
                $date   = date('Y-m-d H:i:s');
                $vendors = DB::table('vendors')->where('phone', $mobile)->where('is_onboard', '1')->first();
                if($vendors) 
                {
                    if (Auth::attempt(array(
                        'phone' => $mobile,
                        'password' => $password,
                    ))) 
                    {
                        $partnerid = $vendors->id;
                        $deviceid = $vendors->device_id;
                        $refer_url = "https://play.google.com/store/apps/details?id=com.microprixs.krishivalu&referrer=krvprefer".$partnerid;
                   
                        DB::table('vendors')->where('id', '=', $partnerid)->update(['device_id' => $device_id, 'fcmToken' => $fcmToken, 'updated_at' => $date]);

                        if($refer_code != "")
	                    {
	                        $usertype = explode('refer',$refer_code);
	                        if($usertype[0]=='krvp'){
	                            $referal_customer_id = str_replace('krvprefer', '', $refer_code);
	                        } else {
	                            $referCustomerid = str_replace('krvrefer', '', $refer_code); 
	                            $referal_customer_id = $referCustomerid;
	                        }

	                        if($referal_customer_id != "")
	                        {
	                            DB::table('vendors')->where('id', '=', $partnerid)->update(['referal_partner_id' => $referal_customer_id, 'updated_at' => $date]);
	                        }
	                    }

                        $status_code = '1';
                        $message = 'Partner login successfully';
                        $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partnerid, 'name' =>  $vendors->name, 'phone' => $mobile, 'pincode' =>  $vendors->pincode, 'referurl' =>  $refer_url, "partner_type" => "already");
                    } else{
                    
                        $status_code = $success = '0';
                        $message = 'Incorrect login details. Please try again.';
                        $json = array('status_code' => $status_code, 'message' => $message);
                   }
                }else{

                    
                    $status_code = $success = '0';
                    $message = 'Please enter valid partner login detail';
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '', 'phone' => $mobile, "partner_type" => "not found");
               }
            }   
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    // End Login
    
    //Forget Password
    public function forgotPassword(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $mobile = $request->mobile;
            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
            
            if($error == ""){
                $vendors = DB::table('vendors')->where('phone', $mobile)->first();
                if($vendors) 
                {
                    $date   = date('Y-m-d H:i:s');
                    $vendorsid = $vendors->id;
                    $otp = rand(111111, 999999);

                    $message = str_replace(" ", "%20", "Thank you for registering on KRISHI MULYA AGRO PRIVATE LIMITED. ".$otp." is the OTP for your Login id. Please do not share with anyone.");
                    $result = $this->httpGet("http://sms.messageindia.in/v2/sendSMS?username=krishim&message=".$message."&sendername=KMAOTP&smstype=TRANS&numbers=".$mobile."&apikey=b82ccff1-85cc-4cd5-9401-beed47647ed0");//
                    //print_r($result); exit;

                     DB::table('vendors')->where('id', '=', $vendorsid)->update(['otp' => $otp, 'updated_at' => $date]);

                    $status_code = '1';
                    $message = 'OTP Send Successfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'partner_id' => (int)$vendors->id, 'otp' => "".$otp, 'phone' => $mobile);
                } 
                else 
                {
                    $status_code = $success = '0';
                    $message = 'Sorry! Partner does not exists or Incorrect OTP!';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '', 'phone' => $mobile);
               }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //START VERIFY
    public function partnerChangePassword(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $mobile = $request->mobile;
            $otp = $request->otp;
            $password = $request->password;

            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($otp == ""){
                $error = "otp not found";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($password == ""){
                $error = "Please enter valid password";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($error == ""){
                $partner = DB::table('vendors')->where('phone', $mobile)->where('otp', $otp)->first();
                if($partner) 
                {
                    $vendorsid = $partner->id;
                    $user_id = $partner->user_id;

                    $date   = date('Y-m-d H:i:s');
                    
                    DB::table('vendors')->where('id', '=', $vendorsid)->update(['password' => Hash::make($password), 'updated_at' => $date]);
                    DB::table('users')->where('id', '=', $user_id)->update(['password' => Hash::make($password), 'updated_at' => $date]);
                    $status_code = '1';
                    $message = 'Password changed successfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'partner_id' => (int)$partner->id, 'phone' => $mobile);
                } 
                else 
                {
                    $status_code = $success = '0';
                    $message = 'Sorry! Partner does not exists or Incorrect OTP!';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '', 'phone' => $mobile);
               }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }
    
    //START VERIFY
    public function resendSMS(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $mobile = $request->mobile;
            $date   = date('Y-m-d H:i:s');
            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
           
            if($error == ""){
                $partner = DB::table('vendors')->where('phone', $mobile)->first();
                if($partner) 
                {
                    $partnerid = $partner->id;
                    $otp = rand(111111, 999999);

                    $message = str_replace(" ", "%20", "Thank you for registering on KRISHI MULYA AGRO PRIVATE LIMITED. ".$otp." is the OTP for your Login id. Please do not share with anyone.");
                    $result = $this->httpGet("http://sms.messageindia.in/v2/sendSMS?username=krishim&message=".$message."&sendername=KMAOTP&smstype=TRANS&numbers=".$mobile."&apikey=b82ccff1-85cc-4cd5-9401-beed47647ed0");//

                    DB::table('vendors')->where('id', '=', $partnerid)->update(['otp' => $otp, 'updated_at' => $date]);

                    $status_code = '1';
                    $message = 'OTP Send sucessfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'partner_id' => (int)$partnerid,  'phone' => $mobile, 'otp' => "".$otp);
                } 
                else 
                {
                    $status_code = $success = '0';
                    $message = 'Sorry! Partner does not exists';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '', 'phone' => $mobile);
               }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function getPartnerType(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $partner_id = $request->partner_id;

            $app_version = "";

            if(isset($request->app_version))
            {
                $app_version = $request->app_version;
            }

            $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
            if($customer){
                $custname = $customer->name;

                // update app version in
                $date = date('Y-m-d H:i:s');
                DB::table('vendors')->where('id', '=', $partner_id)->update(['app_version' => $app_version, 'updated_at' => $date]);
            }else{
                $custname = "Guest";
            }

            $gplay = new \Nelexa\GPlay\GPlayApps($defaultLocale = 'en_US', $defaultCountry = 'us');
            $appInfo = $gplay->getAppInfo('com.microprixs.krishimulya');

            $live_version = $appInfo->getAppVersion();

            $same_version = 1;
            if($live_version != $app_version)
            {
                $same_version = 0;
            }

            $status_code = '1';
            $message = 'All Customer Type';
            $json = array('status_code' => $status_code,  'message' => $message, 'name' => $custname, 'same_version' => "".$same_version);
        }
        
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    //Partner Profile
    public function partner_dashboard(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
           
            $partner = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
            if($partner){ 
                
                if($partner->name){
                    $name = $partner->name;
                }else{
                    $name = "";
                }
                if($partner->email){
                    $email = $partner->email; 
                }else{
                    $email = "";
                }
                if($partner->age){
                   $age = $partner->age;
                }else{
                 $age = "";
                } 
                $mobile = $partner->phone;
                
                if($partner->pcode){
                    $pcode = $partner->pcode; 
                }else{
                    $pcode = "";
                }
                
                $baseUrl = URL::to("/");
                $partner_image  = "";
                if($partner->image){
                    $partner_image  =  $baseUrl."/public/uploads/partner_image/".$partner->image;
                
                }else{
                   $partner_image  =  $baseUrl."/public/uploads/profile.jpg";
                }
                
                $walletArr = array();
                /* Wallet Balance */
                    $partnerWalletAmt = DB::table('wallet_payments')->where('partner_id', $partner_id)->where('payment_status', '=', 'done')->sum('amount');

                    $partnerSoilOdrAmt = DB::table('soil_test_orders')->where('customer_id', $partner_id)->where('user_type', 'partner')->where('order_status', '=', 'done')->sum('amount');
                    $remain_balance = ($partnerWalletAmt-$partnerSoilOdrAmt);
                    $walletArr['wallet_balance'] = $partnerWalletAmt;
                    $walletArr['order_balance'] = $partnerSoilOdrAmt;
                    $walletArr['remain_balance'] = $remain_balance;
                /* End */
                
                /* Tractor Sale Commission Summary */
                    $partnerTractrSale = DB::table('tractor_sell_enquiry')->select('id','sale_type','exp_price','sale_commission')->where('customer_id', $partner_id)->where('user_type', 'partner')->where('is_converted', '=', 1)->get();
                     
                    $ttrodrCommissionArr = array();
                    $ttrodramt = 0;
                    $tractorOdrqty = 0;
                    $tractorOdrAmt = 0;
                    $implementOdrqty = 0;
                    $implementOdrAmt = 0;
                    foreach($partnerTractrSale as $tctrodrlist)
                    {
                        
                        if($tctrodrlist->sale_type == 'Tractor (ट्रैक्टर)'){
                            $tractorOdrqty ++;
                            $trctrcommission = $tctrodrlist->sale_commission;
                            $trctodrcommissionAmt = ($trctrcommission/100)*$tctrodrlist->exp_price;
                            $tractorOdrAmt += $trctodrcommissionAmt;
                        }
                        if($tctrodrlist->sale_type == 'Implement (उपकरण)'){
                            $implementOdrqty ++;
                            
                            $implementCommission = $tctrodrlist->sale_commission;
                            $implementodrcommissionAmt = ($implementCommission/100)*$tctrodrlist->exp_price;
                            $implementOdrAmt += $implementodrcommissionAmt;
                        }

                    }    

                    $totaltrtrCommission = ($tractorOdrAmt+$implementOdrAmt);
                    $ttrodrCommissionArr['tractor_commision_amount'] = "".$totaltrtrCommission;
                    $ttrodrCommissionArr['tractor_comission'] = "Tractor X ".$tractorOdrqty." = ".$tractorOdrAmt;
                    $ttrodrCommissionArr['implement_commission'] = "Implement X ".$implementOdrqty." = ".$implementOdrAmt;
                /* End */


                /* Soil Order Commission Summary */
                    $partnerSoilOdr = DB::table('soil_test_orders')->select('id','test_type','amount')->where('customer_id', $partner_id)->where('user_type', 'partner')->where('order_status', '=', 'done')->get();
                     
                    $odrCommissionArr = array();
                    $odramt = 0;
                    $singleOdrqty = 0;
                    $singleOdrAmt = 0;
                    $completeOdrqty = 0;
                    $completeOdrAmt = 0;
                    foreach($partnerSoilOdr as $odrlist)
                    {
                        
                        if($odrlist->test_type == 'Single Profile'){
                            $singleOdrqty ++;
                            $soilTypecommision = DB::table('soil_test_type')->where('title', $odrlist->test_type)->where('isactive', '=', '1')->first();
                            $singlecommission = $soilTypecommision->partner_commission;
                            $odrcommissionAmt = ($singlecommission/100)*$odrlist->amount;
                            $singleOdrAmt += $odrcommissionAmt;
                        }
                        if($odrlist->test_type == 'Complete Profile'){
                            $completeOdrqty ++;
                            $soilTypecommision = DB::table('soil_test_type')->where('title', $odrlist->test_type)->where('isactive', '=', '1')->first();
                            $completecommission = $soilTypecommision->partner_commission;
                            $compltodrcommissionAmt = ($completecommission/100)*$odrlist->amount;
                            $completeOdrAmt += $compltodrcommissionAmt;
                        }

                    }    

                    $totalCommission = ($singleOdrAmt+$completeOdrAmt);
                    $odrCommissionArr['commision_amount'] = "".$totalCommission;
                    $odrCommissionArr['single_profile'] = "Single Profile X ".$singleOdrqty." = ".$singleOdrAmt;
                    $odrCommissionArr['complete_profile'] = "Complete Profile X ".$completeOdrqty." = ".$completeOdrAmt;
                /* End */

                
               
                $status_code = $success = '1';
                $message = 'Partner Dashboard Info';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id , 'name' => $name, 'mobile' => $mobile, 'partner_code' => $pcode, 'partner_image' => $partner_image, 'wallet_data' => $walletArr, 'order_commission' => $odrCommissionArr, 'tractor_commission' => $ttrodrCommissionArr );


            } else{
                $status_code = $success = '0';
                $message = 'Partner not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }


    public function partner_order_commision(Request $request)
    {
        try 
        {   
             $baseUrl = URL::to("/");
            $json       =   array();
            $partner_id = $request->partner_id;
            $partner = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($partner){ 
                    $soilodrExists = DB::table('soil_test_orders')->where('customer_id', $partner_id)->where('user_type', 'partner')->where('order_status', 'done')->orderBy('id', 'DESC')->count();

                    if($soilodrExists >0){
                        $soilodrList = DB::table('soil_test_orders')->select('id','order_no','name','mobile','amount','land_size','location','khasra_no','test_type','report_file','order_status','created_at')->where('customer_id', $partner_id)->where('user_type', 'partner')->where('order_status', 'done')->orderBy('id', 'DESC')->get();

                        $odr_List = array();
                        $testype_name = "";
                        $totcommision = 0;
                        foreach($soilodrList as $odrlist)
                        {
                            $soilTypecommision = DB::table('soil_test_type')->where('title', $odrlist->test_type)->where('isactive', '=', '1')->first();
                            $orderCommission = $soilTypecommision->partner_commission;
                            $odrcommissionAmt = ($orderCommission/100)*$odrlist->amount;
                            
                            $testype_name = $odrlist->test_type; 

                            $totcommision += $odrcommissionAmt;
                           
                            if($odrlist->khasra_no != ''){
                                $khasra_no = $odrlist->khasra_no;
                            }else{
                                $khasra_no = "";
                            }
                            $odr_List[] = array('id' => "".$odrlist->id, 'order_no' => $odrlist->order_no, 'customer_name' => $odrlist->name, 'mobile' => $odrlist->mobile, 'testypeName' => $testype_name,'amount' => "".$odrlist->amount, 'land_size' => $odrlist->land_size, 'location' => $odrlist->location, 'khasra_no' => $khasra_no, 'commisionOdrAmt' => $odrcommissionAmt, 'date' => date('d-m-Y H:i:s', strtotime($odrlist->created_at)),'order_status' => $odrlist->order_status); 
                           
                        } 

                        //print_r($odr_List);
                        //exit;
                        $status_code = '1';
                        $message = 'Soil Order Commision List';
                        $json = array('status_code' => $status_code,  'message' => $message, 'odr_List' => $odr_List , 'totalOrderComission' => $totcommision);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Partner not valid';
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);

                }
        }

        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }


    //Purchase Old Enquiry
    public function partner_tractor_commision(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            if($partner_id != ""){
                $partner = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($partner){ 

                    $purchaseOldList = DB::table('tractor_sell_enquiry')->select('id','customer_id','name','mobile','company_name','other_company','model','hourse_power','hrs', 'exp_price', 'image','sale_type','location', 'other_city','user_type','sale_commission')->where('customer_id', $partner_id)->where('user_type', 'partner')->where('is_converted', '=', 1)->orderBy('id', 'desc')->get();


                    if(count($purchaseOldList) >0){
                        $purchaseList = array();
                        $tractorOdrAmt = 0;
                        foreach($purchaseOldList as $plist)
                        {
                            
                            $customer_name = $plist->name;
                            $customer_telphone = $plist->mobile;   
                            
                            $baseUrl = URL::to("/");
                            $tractor_image  = "";
                            if($plist->image){
                                $tractor_image  =  $baseUrl."/public/uploads/tractor_image/".$plist->image;
                            
                            }
                            $other_company = ($plist->other_company != '') ? $plist->other_company : "";
                            $othercity = ($plist->other_city != '') ? $plist->other_city : "";
                            
                            $trctrcommission = $plist->sale_commission;
                            $trctodrcommissionAmt = ($trctrcommission/100)*$plist->exp_price;
                            $tractorOdrAmt += $trctodrcommissionAmt;
                        
                            /*if($plist->sale_type == 'Tractor (ट्रैक्टर)'){
                                $trctrcommission = $plist->sale_commission;
                                $trctodrcommissionAmt = ($trctrcommission/100)*$tctrodrlist->exp_price;
                                $tractorOdrAmt += $trctodrcommissionAmt;
                            }
                            if($plist->sale_type == 'Implement (उपकरण)'){
                                $implementOdrqty ++;
                                
                                $implementCommission = $tctrodrlist->sale_commission;
                                $implementodrcommissionAmt = ($implementCommission/100)*$tctrodrlist->exp_price;
                                $implementOdrAmt += $implementodrcommissionAmt;
                            }*/


                            $purchaseList[] = ['id' => (string)$plist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'company_name' =>$plist->company_name, 'other_company' =>$other_company, 'what_need' =>$plist->sale_type, 'location' =>$plist->location, 'other_city' =>$othercity, 'model' => $plist->model, 'hourse_power' => $plist->hourse_power, 'hrs' => $plist->hrs, 'exp_price' => $plist->exp_price, 'image' => $tractor_image, 'trctodrcommissionAmt' => $trctodrcommissionAmt]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Tractor Commission List';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'purchase_list' => $purchaseList, 'tractorodrtotcomission' => $tractorOdrAmt);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Item for purchase not available right now';
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);    
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Partner not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //Partner Profile
    public function partnerLeadsStatusChange(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $lead_id = $request->lead_id;
            $lead_type = $request->lead_type;
            $test_status = $request->test_status;

           
            $partner = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
            if($partner){ 
                $vendor_service = DB::table('vendor_services')->where('service_code', $lead_type)->first();

                if($vendor_service)
                {
                    $table_name = $vendor_service->table_name;
                    $table_name_vendor = $vendor_service->table_name."_vendor";
                    $table_name_vendor_history = $vendor_service->table_name."_vendor_history";

                    $table_name_id = $vendor_service->table_name."_id";
                    $table_name_vendor_id = $vendor_service->table_name."_vendor_id";

                    $categoryName = json_decode($vendor_service->name);

                    \DB::table($table_name_vendor)->where('vendor_id', $partner_id)->where($table_name_id, $lead_id)->update(['test_status' => $test_status, 'updated_at' => date('Y-m-d H:i:s')]);

                    // Get Value

                    $vendorData1 = \DB::table($table_name_vendor)->where($table_name_id, $lead_id)->first();
                    //dd($vendorData1);

                    \DB::table($table_name_vendor_history)->insert([
                        $table_name_id => $vendorData1->$table_name_id, 
                        $table_name_vendor_id => $vendorData1->id,
                        'vendor_id' => $partner_id, 
                        'test_status' => $test_status,
                        'status_time' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    $status_code = $success = '1';
                    $message = "Status changed successfully";   

                    $json = array('status_code' => $status_code, 'message' => $message, 'lead_id' => $lead_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function partnerLeads(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $lead_type = $request->lead_type;
            $lead_id = $request->lead_id;
            $test_status = $request->test_status;

            $date_from = date("Y-m-d",strtotime($request->date_from));
            $date_to = date("Y-m-d",strtotime($request->date_to));
            
            $partner = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
            if($partner){ 
                $vendor_service = DB::table('vendor_services')->where('service_code', $lead_type)->first();

                if($vendor_service)
                {
                    $table_name = $vendor_service->table_name;
                    $table_name_vendor = $vendor_service->table_name."_vendor";

                    $categoryName = json_decode($vendor_service->name);

                    if($test_status == "" || $test_status == "All")
                    {
                        if($lead_id != "")
                        {
                            $vendorData = \DB::table($table_name_vendor)->leftJoin($table_name, $table_name_vendor.'.'.$table_name."_id", '=', $table_name.'.id')->where('vendor_id', $partner_id)->where('user_type', "customer")->where($table_name_vendor.".id", $lead_id)->whereDate('status_time', ">=", $date_from)->whereDate('status_time', "<=", $date_to)->orderBy($table_name_vendor.'.updated_at', 'DESC')->get();
                        }
                        else
                        {
                            $vendorData = \DB::table($table_name_vendor)->leftJoin($table_name, $table_name_vendor.'.'.$table_name."_id", '=', $table_name.'.id')->where('vendor_id', $partner_id)->where('user_type', "customer")->whereDate('status_time', ">=", $date_from)->whereDate('status_time', "<=", $date_to)->orderBy($table_name_vendor.'.updated_at', 'DESC')->get();
                        }
                    }
                    else
                    {
                        if($lead_id != "")
                        {
                            $vendorData = \DB::table($table_name_vendor)->leftJoin($table_name, $table_name_vendor.'.'.$table_name."_id", '=', $table_name.'.id')->where('test_status', $test_status)->where('vendor_id', $partner_id)->where('user_type', "customer")->where($table_name_vendor.".id", $lead_id)->whereDate('status_time', ">=", $date_from)->whereDate('status_time', "<=", $date_to)->orderBy($table_name_vendor.'.updated_at', 'DESC')->get();
                        }
                        else
                        {
                            $vendorData = \DB::table($table_name_vendor)->leftJoin($table_name, $table_name_vendor.'.'.$table_name."_id", '=', $table_name.'.id')->where('test_status', $test_status)->where('vendor_id', $partner_id)->where('user_type', "customer")->whereDate('status_time', ">=", $date_from)->whereDate('status_time', "<=", $date_to)->orderBy($table_name_vendor.'.updated_at', 'DESC')->get();
                        }
                    }

                    $leadData = array();
                    if($vendorData)
                    {
                        foreach($vendorData as $vendorRow)
                        {
                            $user_type = $vendorRow->user_type;
                            $details = "";

                            if($lead_type == "soil-test")
                            {
                                $details = "<strong>Order #: </strong>".$vendorRow->order_no.'<br />';
                                $details .= "<strong>Land Size: </strong>".$vendorRow->land_size.'<br />';
                                $details .= "<strong>Location: </strong>".$vendorRow->location.'<br />';
                                $details .= "<strong>Khasra No: </strong>".$vendorRow->khasra_no.'<br />';
                                $details .= "<strong>Test Type: </strong>".$vendorRow->test_type.'<br />';
                                $details .= "<strong>Amount: </strong>".$vendorRow->amount.'<br />';
                                $details .= "<strong>Comment: </strong>".$vendorRow->comments.'<br />';
                                $details .= "<strong>Status: </strong>".$vendorRow->order_status;
                            }
                            else if($lead_type == "tractor-purchase")
                            {
                                $details = "<strong>Company Name: </strong>".$vendorRow->company_name.'<br />';
                                $details .= "<strong>Location: </strong>".$vendorRow->location.'<br />';
                                $details .= "<strong>Horse Power: </strong>".$vendorRow->hourse_power.'<br />';
                                $details .= "<strong>Payment Type: </strong>".$vendorRow->payment_type.'<br />';
                                $details .= "<strong>Comment: </strong>".$vendorRow->comment.'<br />';
                                $details .= "<strong>Uses Type: </strong>".$vendorRow->uses_type;
                            }
                            else if($lead_type == "tractor-sale")
                            {
                                $details = "<strong>Company Name: </strong>".$vendorRow->company_name.'<br />';
                                $details .= "<strong>Model: </strong>".$vendorRow->model.'<br />';
                                $details .= "<strong>Year: </strong>".$vendorRow->year_manufacturer.'<br />';
                                $details .= "<strong>Horse Power: </strong>".$vendorRow->hourse_power.'<br />';
                                $details .= "<strong>Location: </strong>".$vendorRow->location.'<br />';
                                $details .= "<strong>Hrs: </strong>".$vendorRow->hrs.'<br />';
                                $details .= "<strong>Price: </strong>".$vendorRow->exp_price.'<br />';
                                $details .= "<strong>Commission: </strong>".$vendorRow->sale_commission.'<br />';
                                $details .= "<strong>Comment: </strong>".$vendorRow->comment.'<br />';
                                $details .= "<strong>Sale Type: </strong>".$vendorRow->sale_type;
                            }
                            else if($lead_type == "tractor-rental")
                            {
                                $details = "<strong>Available Date: </strong>".$vendorRow->available_date.'<br />';
                                $details .= "<strong>Comment: </strong>".$vendorRow->comment.'<br />';
                                $details .= "<strong>Model: </strong>".$vendorRow->model.'<br />';
                                $details .= "<strong>Location: </strong>".$vendorRow->location.'<br />';
                                $details .= "<strong>Type: </strong>".$vendorRow->what_type;
                            }
                            else if($lead_type == "tractor-refinance")
                            {
                                $details = "<strong>Company Name: </strong>".$vendorRow->company_name.'<br />';
                                $details .= "<strong>Location: </strong>".$vendorRow->location.'<br />';
                                $details .= "<strong>Horse Power: </strong>".$vendorRow->hourse_power.'<br />';
                                $details = "<strong>Payment Type: </strong>".$vendorRow->payment_type.'<br />';
                                $details .= "<strong>Comment: </strong>".$vendorRow->comment;
                            }

                            $customer_name = $customer_phone = "";

                            if($user_type == "customer")
                            {
                                $customerData = DB::table('customers')->where('id', $vendorRow->customer_id)->where('status', '=', '1')->first();
                                if($customerData) { 
                                    $customer_name = $customerData->name;
                                    $customer_phone = $customerData->telephone;
                                }
                                else if(isset($vendorRow->name))
                                {
                                    $customer_name = $vendorRow->name;
                                    $customer_phone = $vendorRow->mobile;
                                }
                            }
                            elseif($user_type == "partner")
                            {
                                if($vendorRow->contact_person_name != NULL)
                                {
                                    $customer_name = $vendorRow->contact_person_name;
                                    $customer_phone = $vendorRow->contact_person_phone;
                                }
                                else
                                {
                                    $customerData = DB::table('vendors')->where('id', $vendorRow->customer_id)->where('is_onboard', '=', '1')->first();
                                    if($customerData) { 
                                        $customer_name = $customerData->name;
                                        $customer_phone = $customerData->phone;
                                    }
                                }
                            }

                            //echo $user_type.">".$customer_name;

                            $leadData[] = array('id' => $vendorRow->id, 'lead_type' => $categoryName->en, 'details' => $details, 'name' => $customer_name,'phone' => $customer_phone, 'date' => $vendorRow->status_time, 'test_status' => $vendorRow->test_status, 'status_time' => $vendorRow->status_time, 'user_type' => $user_type);
                        }

                        if($leadData)
                        {
                            $status_code = $success = '1';
                            $message = $categoryName->en.' List';
                        }
                        else
                        {
                            $status_code = $success = '0';
                            $message = "No data found in ".$categoryName->en;   
                        }
                    }
                    else
                    {
                        $status_code = $success = '0';
                        $message = "No data found in ".$categoryName->en;   
                    }
                    
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'leadData' => $leadData);
                }
                           
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function partner_profile(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
           
            $partner = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
            if($partner){ 
                if($partner->name){
                    $name = $partner->name;
                }else{
                    $name = "";
                }
                if($partner->email){
                    $email = $partner->email; 
                }else{
                    $email = "";
                }
                if($partner->age){
                   $age = date('d-m-Y', strtotime($partner->age));
                }else{
                 $age = "";
                } 
                $mobile = $partner->phone;
                
                if($partner->pcode){
                    $pcode = $partner->pcode; 
                }else{
                    $pcode = "";
                }

                if($partner->address){
                    $address = $partner->address; 
                }else{
                    $address = "";
                }

                if($partner->city){
                    $city = $partner->city; 
                }else{
                    $city = "";
                }

                if($partner->state){
                    $state = $partner->state; 
                }else{
                    $state = "";
                }

                if($partner->pincode){
                    $pincode = $partner->pincode; 
                }else{
                    $pincode = "";
                }
                
                $baseUrl = URL::to("/");
                $partner_image  = "";
                if($partner->image){
                    $partner_image  =  $baseUrl."/".$partner->image;
                
                }else{
                   $partner_image  =  $baseUrl."/public/uploads/profile.jpg";
                }
                
                $assignService = array();

                $partnerAssign = DB::table('vendor_vendor_assign')->leftJoin('vendor_services', 'vendor_vendor_assign.vendor_service_id', '=', 'vendor_services.id')->where('vendor_id', $partner_id)->get();

                foreach ($partnerAssign as $key => $value) {
                    # code...
                    $table_name = $value->table_name;
                    $table_name_vendor = $value->table_name."_vendor";

                    $isExists = \DB::table($table_name_vendor)->where('vendor_id', $partner_id)->selectRaw('COUNT(id) as total')->groupBy('vendor_id')->first();
                    $stats = 0;
                    if($isExists)
                    {
                        $stats = $isExists->total;
                    }

                    $assignService[] = array('service_code' => $value->service_code, 'service_color' => $value->service_color, 'image' => $baseUrl."/".$value->image, 'name' => $value->name, 'stats' => $stats);
                }
                
                $status_code = $success = '1';
                $message = 'Partner Profile Info';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id , 'name' => $name, 'email' => $email, 'age' => $age, 'mobile' => $mobile, 'address' => $address, 'city' => $city, 'pincode' => $pincode, 'state' => $state, 'partner_code' => $pcode, 'partner_image' => $partner_image, 'assignService' => $assignService);


            } else{
                $status_code = $success = '0';
                $message = 'Partner not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function partnerDashboard(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $language = $request->language;

            //echo $language; exit;

            $baseUrl = URL::to("/");
           
            $partner = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
            if($partner){ 
                $assignService = array();

                $partnerAssign = DB::table('vendor_vendor_assign')->leftJoin('vendor_services', 'vendor_vendor_assign.vendor_service_id', '=', 'vendor_services.id')->where('vendor_id', $partner_id)->get();

                $stats_total  = $stats_pending_total  = 0;

                foreach ($partnerAssign as $key => $value) {
                    # code...
                    $categoryName = json_decode($value->name);
                    //print_r($categoryName); exit;
                    $table_name = $value->table_name;
                    $table_name_vendor = $value->table_name."_vendor";

                    $isExists = \DB::table($table_name_vendor)->where('vendor_id', $partner_id)->selectRaw('COUNT(id) as total')->groupBy('vendor_id')->first();
                    $stats = 0;
                    if($isExists)
                    {
                        $stats = $isExists->total;
                    }

                    $isExists1 = \DB::table($table_name_vendor)->where('vendor_id', $partner_id)->where('test_status', 'Pending')->selectRaw('COUNT(id) as total')->count();
                    $stats1 = $isExists1;

                    $stats_total+=$stats;
                    $stats_pending_total+=$stats1;

                    $assignService[] = array('service_code' => $value->service_code, 'service_color' => $value->service_color, 'image' => $baseUrl."/".$value->image, 'name' => $categoryName->$language, 'stats' => "".$stats);
                }

                //$assignService[] = array('service_code' => 'all-leads', 'service_color' => $value->service_color, 'image' => $baseUrl."/".$value->image,'name' => 'All Leads', 'stats' => "".$stats_total);

                //$assignService[] = array('service_code' => 'pending-leads', 'service_color' => $value->service_color, 'image' => $baseUrl."/".$value->image, 'name' => 'Pending Leads', 'stats' => "".$stats_pending_total);

                //array_reverse($assignService);
                
                $pincode = $partner->pincode;

                $appurl = 'api.openweathermap.org/data/2.5/weather?zip='.$pincode.',IN&units=metric&appid=acfd0186948c7adf0c9c87a2ebcc004b';
                $wheatherRespone = $this->httpGet($appurl);
                
                $wheather = json_decode($wheatherRespone);
                //print_r($wheather->main);
                //print_r($wheather->weather[0]);
                $mainval =  $wheather->weather[0]->main;
                $wheatherType =  $wheather->weather[0]->description;
                $wheathericon =  $wheather->weather[0]->icon;
                $todaytemp =  $wheather->main->temp;
                $todayhumidity =  $wheather->main->humidity;
                $todayhumidity =  $wheather->main->humidity;
                $locationName =  $wheather->name;
                $iconurl = "http://openweathermap.org/img/w/" . $wheathericon . ".png";
                
                $status_code = $success = '1';
                $message = 'Partner Dashboards';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id, 'pincode' => $pincode, 'wheatherType' => $wheatherType, 'wheathericon' => $iconurl, 'todaytemp' => "".$todaytemp."°C" , 'todayhumidity' => "".$todayhumidity, 'locationName' => "".$locationName, 'assignService' => $assignService);


            } else{
                $status_code = $success = '0';
                $message = 'Partner not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }


     //Partner Update
    public function update_profile(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $name = $request->name;
            $ageEntry = $request->age;
            $email = $request->email;
            $address = $request->address;
            $partner_image = $request->partner_image;
            $city = $request->city;

            $ageData = explode("-", $ageEntry);
            $age = $ageData[2]."/".$ageData[1]."/".$ageData[0];

            $partners = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
            if($partners){ 
                $partnerimage = '';
                if($partner_image != ''){
                    $image_parts = explode(";base64,", $partner_image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];

                    $partnerimage = rand(10000, 99999).'-'.time().'.'.$image_type;
                    $destinationPath = public_path('uploads/partner_image/').$partnerimage;


                    $data = base64_decode($image_parts[1]);
                   // $data = $image_parts[1];
                    file_put_contents($destinationPath, $data);

                    //echo $destinationPath; exit;
                }

                // check for email already exists
                $isExists = DB::table('users')->where('email', '=', $email)->where('id', '!=', $partners->user_id)->count();

                if($isExists > 0)
                {
                	$status_code = $success = '0';
	                $message = 'Email already exists';
	                
	                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);

                }
                else
                {
                	if($partnerimage != "")
                	{
	                	DB::table('vendors')->where('id', '=', $partner_id)->update(['name' => $name, 'age' => $age, 'email' => $email, 'address' => $address, 'city' => $city, 'image' => "uploads/partner_image/".$partnerimage, 'updated_at' => $date]);
	                }
	                else
	                {
	                	DB::table('vendors')->where('id', '=', $partner_id)->update(['name' => $name, 'age' => $age, 'email' => $email, 'address' => $address, 'city' => $city, 'updated_at' => $date]);
	                }

	                DB::table('users')->where('id', '=', $partners->user_id)->update(['name' => $name, 'email' => $email, 'updated_at' => $date]);

	                $status_code = $success = '1';
	                $message = 'Partner info updated successfully';
	                
	                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
				}
            } else{
                $status_code = $success = '0';
                $message = 'Partner not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //Partner logout
    public function partner_logout(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
           
            $partner = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
            if($partner){ 
                
                $status_code = $success = '1';
                $message = 'Partner logout successfully';
                
                $json = array('status_code' => $status_code, 'message' => $message);


            } else{
                $status_code = $success = '0';
                $message = 'Partner not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }


    //START VERIFY
    public function verifyOrderMobile(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $mobile = $request->mobile;
            $date   = date('Y-m-d H:i:s');
            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
           
            if($error == ""){
                $rsmobile = DB::table('tbl_mobile_verify')->where('mobile', $mobile)->first();
                if($rsmobile) 
                {
                    $verifyid = $rsmobile->id;
                    $otp = rand(111111, 999999);

                    $message = str_replace(" ", "%20", "Thank you for registering on KRISHI MULYA AGRO PRIVATE LIMITED. ".$otp." is the OTP for your Login id. Please do not share with anyone.");
                    $result = $this->httpGet("http://sms.messageindia.in/v2/sendSMS?username=krishim&message=".$message."&sendername=KMAOTP&smstype=TRANS&numbers=".$mobile."&apikey=b82ccff1-85cc-4cd5-9401-beed47647ed0");//

                    DB::table('tbl_mobile_verify')->where('id', '=', $verifyid)->update(['otp' => $otp]);

                    $status_code = '1';
                    $message = 'OTP Send successfully';
                    $json = array('status_code' => $status_code,  'message' => $message,  'mobile' => $mobile, 'otp' => $otp);
                } 
                else 
                {
                    $otp = rand(111111, 999999);
                    $message = str_replace(" ", "%20", "Thank you for registering on KRISHI MULYA AGRO PRIVATE LIMITED. ".$otp." is the OTP for your Login id. Please do not share with anyone.");
                    $result = $this->httpGet("http://sms.messageindia.in/v2/sendSMS?username=krishim&message=".$message."&sendername=KMAOTP&smstype=TRANS&numbers=".$mobile."&apikey=b82ccff1-85cc-4cd5-9401-beed47647ed0");//

                    DB::table('tbl_mobile_verify')->insertGetId(['mobile' => $mobile, 'otp' => $otp]);
                    $status_code = '1';
                    $message = 'OTP Send successfully';
                    $json = array('status_code' => $status_code,  'message' => $message,  'mobile' => $mobile, 'otp' => "".$otp);
               }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function notification_list(Request $request)
    {
        try 
        {
            $json       =   array();
            $partner_id = $request->partner_id;
            $partner = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
            if($partner){ 
                $soilnotificationExists = DB::table('notifications')->where('customer_id', $partner_id)->where('user_type', 'partner')->orderBy('id', 'DESC')->count();
                $notify_List = array();
                if($soilnotificationExists >0){
                    $soilNotifyList = DB::table('notifications')->select('id','lead_id','notification_title','notification_content','notification_type','created_at')->where('customer_id', $partner_id)->where('user_type', 'partner')->orderBy('id', 'DESC')->get();

                    
                    foreach($soilNotifyList as $notifylist)
                    {
                        $notification_type = str_replace("_", "-", $notifylist->notification_type);
                        if($notification_type == 'soil-order'){

                            $notification_type_code = 'Soil Order';
                            $notification_type = "soil-test";
                        }

                        $notify_List[] = array('id' => "".$notifylist->id, 'lead_id' => "".$notifylist->lead_id, 'notification_title' => $notifylist->notification_title,'notification_content' => "".$notifylist->notification_content, 'notification_code' => $notification_type_code,  'notification_type' => $notification_type, 'date' => date('d-m-Y H:i:s', strtotime($notifylist->created_at))); 
                       
                    } 

                    //print_r($odr_List);
                    //exit;
                    $status_code = '1';
                    $message = 'Notification List';
                    $json = array('status_code' => $status_code,  'message' => $message, 'notify_List' => $notify_List);
                }else{
                     $status_code = '0';
                    $message = 'No notification found.';
                    $json = array('status_code' => $status_code,  'message' => $message, 'partner_id' => $partner_id);
                }
            }else{
                $status_code = $success = '0';
                $message = 'Partner not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function appPopup(Request $request)
    {
        try 
        {
            Setting::assignSetting();

            $baseUrl = URL::to("/");
            $json       =   array();
            $customer_id = $request->partner_id;

            // To do
            $app_version = "";

            if(isset($request->app_version))
            {
                $app_version = $request->app_version;
            }

            $customer = DB::table('vendors')->where('id', $customer_id)->where('is_onboard', '=', '1')->first();
            if($customer){
                $custname = $customer->name;
                //$custcrn = ($customer->crn == NULL ? "" : $customer->crn);

                // update app version in
                $date = date('Y-m-d H:i:s');
                DB::table('customers')->where('id', '=', $customer_id)->update(['app_version' => $app_version, 'updated_at' => $date]);
            } else {
                $custname = "Guest";
                //$custcrn = "";
            }

            $sliderArr = array();
            $sliderList = DB::table('app_popups')->where('status', '=', 1)->orderBy('id', 'DESC')->first();
            $short_description = $sliderimage = $title = '';
            if($sliderList) {
                $title = $sliderList->title;
                $short_description = $sliderList->short_description;
                $sliderimage  =  $baseUrl."/public/".$sliderList->image;
            }

            $gplay = new \Nelexa\GPlay\GPlayApps($defaultLocale = 'en_US', $defaultCountry = 'us');
            $appInfo = $gplay->getAppInfo('com.microprixs.krishimulya');

            $live_version = $appInfo->getAppVersion();

            $same_version = '';
            if($live_version != $app_version)
            {
                $same_version = 'https://play.google.com/store/apps/details?id=com.microprixs.krishimulya';
            }
            
            $status_code = '1';
            $message = 'Popup list';
            $json = array('status_code' => $status_code,  'message' => $message, 'title' => $title, 'short_description' => $short_description, 'slider_image' => $sliderimage, 'app_url' => $same_version, 'slider_url' => $baseUrl.'/app-popup');
        }
        
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function leadStatus(Request $request)
    {
        try 
        {
            Setting::assignSetting();

            $json       =   array();

            $sliderArr = array();
            $sliderList = DB::table('lead_status')->where('status', '=', 1)->orderBy('id', 'DESC')->get();
            $statusData = array();
            if($sliderList) {
                foreach($sliderList as $row) {
                    $statusData[]['name'] = $row->name;
                }
            }
            
            $status_code = '1';
            $message = 'Status list';
            $json = array('status_code' => $status_code,  'message' => $message, 'statusData' => $statusData);
        }
        
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function leadStatusAll(Request $request)
    {
        try 
        {
            Setting::assignSetting();

            $json       =   array();

            $sliderArr = array();
            $sliderList = DB::table('lead_status')->where('status', '=', 1)->orderBy('id', 'DESC')->get();
            $statusData = array();
            if($sliderList) {
                $statusData[]['name'] = "All";

                foreach($sliderList as $row) {
                    $statusData[]['name'] = $row->name;
                }
            }
            
            $status_code = '1';
            $message = 'Status list';
            $json = array('status_code' => $status_code,  'message' => $message, 'statusData' => $statusData);
        }
        
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    // Services
    // Done
    public function agriTypeEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $agritype = $request->agritype;
            $city = $request->city;
            $comment = $request->comment;
            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;
            
            $isactive = 1;
            $error = "";
            if($agritype == ""){
                $error = "Please enter agri type";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->phone;
                    DB::table('agri_type_enquiry')->insert(['customer_id' => $partner_id, 'agri_type' => $agritype, 'city' => $city, 'comment' => $comment, 'isactive' => $isactive,  'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Agri Type enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function agrilandRentEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();

            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $comment = $request->comment;
            $how_much_time = $request->how_much_time;
            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            $isactive = 1;
            $error = "";
            if($location == "" || $location == "All"){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($size_in_acre == "" || $size_in_acre == "All"){
                $error = "Please enter size (acre) for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($how_much_time == "" || $how_much_time == "All"){
                $error = "Please enter time for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($land_type == "" || $land_type == "All"){
                $error = "Please enter land type for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($contact_person_phone != "")
            {
            	$customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
              	if($customer && $customer->phone == $contact_person_phone)
              	{
              		$error = "You can not enter your own mobile number.";
	                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
              	}
              	else
              	{
	                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
	                if($verifyOtp){ 
	                    $mobileverifyotp = $verifyOtp->otp;
	                    if($contact_person_otp != $mobileverifyotp){
	                        $error = "Please enter valid OTP to verify mobile.";
	                        $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
	                    }else{
	                        //$error = "Incorrect OTP.";
	                        //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                    }
	                } else {
	                    $error = "Please verify mobile.";
	                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                }
				}
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
                if($customer){ 
                    
                    $agriland_rent_enquiry_id = DB::table('agriland_rent_enquiry')->insertGetId(['customer_id' => $partner_id, 'location' => $location, 'other_city' => $other_city, 'land_type' => $land_type, 'size_in_acore' => $size_in_acre, 'how_much_time' => $how_much_time, 'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'is_edit' => '1', 'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Agriland Rent Enquiry";
                        $message1 = "Location: ".$location.", Land Type:".$land_type.", Size (Acre):".$size_in_acre.", Time:".$how_much_time.", Comments:".$comment;
                        $this->sendNotification($cust->id, $agriland_rent_enquiry_id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Agri land rent enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function agrilandSaleEnquiry(Request $request)
    {
        try 
        {
          // header('Content-Type: text/html; charset=UTF-8');
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $comment = $request->comment;

            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            //exit;
            //$exp_price = $request->exp_price;
            $exp_price = 0;
            $isactive = 1;
            $error = "";
            if($location == ""){
                $error = "Please enter location";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($contact_person_phone != "")
            {
            	$customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
              	if($customer && $customer->phone == $contact_person_phone)
              	{
              		$error = "You can not enter your own mobile number.";
	                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
              	}
              	else
              	{
	                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
	                if($verifyOtp){ 
	                    $mobileverifyotp = $verifyOtp->otp;
	                    if($contact_person_otp != $mobileverifyotp){
	                        $error = "Please enter valid OTP to verify mobile.";
	                        $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
	                    }else{
	                        //$error = "Incorrect OTP.";
	                        //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                    }
	                } else {
	                    $error = "Please verify mobile.";
	                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                }
				}
            }

            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
                if($customer){ 
                    
                    $agriland_sale_enquiry_id = DB::table('agriland_sale_enquiry')->insertGetId(['customer_id' => $partner_id, 'location' => $location, 'other_city' => $other_city, 'land_type' => $land_type, 'size_in_acre' => $size_in_acre, 'exp_price' => $exp_price, 'comment' => $comment, 'isactive' => $isactive, 'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'is_edit' => '1', 'created_at' => $date, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Agriland Sale Enquiry";
                        $message1 = "Location: ".$location.", Land Type:".$land_type.", Size (Acre):".$size_in_acre.", Exp. Price: ".$exp_price.", Comments:".$comment;
                        $this->sendNotification($cust->id, $agriland_sale_enquiry_id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Agri land sale enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function agriToolEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $agritool = $request->agritool;
            $city = $request->city;
            $comment = $request->comment;

            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            $isactive = 1;
            $error = "";
            if($agritool == ""){
                $error = "Please enter agri tool";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($contact_person_phone != "")
            {
            	$customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
              	if($customer && $customer->phone == $contact_person_phone)
              	{
              		$error = "You can not enter your own mobile number.";
	                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
              	}
              	else
              	{
	                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
	                if($verifyOtp){ 
	                    $mobileverifyotp = $verifyOtp->otp;
	                    if($contact_person_otp != $mobileverifyotp){
	                        $error = "Please enter valid OTP to verify mobile.";
	                        $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
	                    }else{
	                        //$error = "Incorrect OTP.";
	                        //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                    }
	                } else {
	                    $error = "Please verify mobile.";
	                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                }
				}
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer){ 
                    DB::table('agri_tool_enquiry')->insert(['customer_id' => $partner_id, 'agri_tool' => $agritool, 'city' => $city, 'comment' => $comment, 'isactive' => $isactive, 'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Agri Tool enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function insuranceEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $insurance_type = $request->insurance_type;
            $other_insurance_type = $request->other_insurance_type;
            $comments = $request->comment;
            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            $isactive = 1;
            $error = "";
            if($insurance_type == ""){
                $error = "Please enter insurance type";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($contact_person_phone != "")
            {
            	$customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
              	if($customer && $customer->phone == $contact_person_phone)
              	{
              		$error = "You can not enter your own mobile number.";
	                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
              	}
              	else
              	{
	                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
	                if($verifyOtp){ 
	                    $mobileverifyotp = $verifyOtp->otp;
	                    if($contact_person_otp != $mobileverifyotp){
	                        $error = "Please enter valid OTP to verify mobile.";
	                        $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
	                    }else{
	                        //$error = "Incorrect OTP.";
	                        //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                    }
	                } else {
	                    $error = "Please verify mobile.";
	                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                }
				}
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer) {
                    $name = $customer->name;
                    $mobile = $customer->phone;
                    DB::table('insurance_enquiry')->insert(['customer_id' => $partner_id, 'name' => $name, 'mobile' => $mobile, 'insurance_type' => $insurance_type, 'other_insurance_type' => $other_insurance_type, 'comments' => $comments, 'user_type' => 'customer', 'isactive' => $isactive, 'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Insurance enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function labourEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $location = $request->location;
            $other_city = $request->other_city;
            $purpose = $request->purpose;
            $need = $request->need;
            $labour_no = $request->labour_no;
            $comments = $request->comments;
            $isactive = 1;
            $error = "";

            // TO DO
            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            if($labour_no == ""){
                $error = "Please enter no of labour";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($contact_person_phone != "")
            {
            	$customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
              	if($customer && $customer->phone == $contact_person_phone)
              	{
              		$error = "You can not enter your own mobile number.";
	                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
              	}
              	else
              	{
	                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
	                if($verifyOtp){ 
	                    $mobileverifyotp = $verifyOtp->otp;
	                    if($contact_person_otp != $mobileverifyotp){
	                        $error = "Please enter valid OTP to verify mobile.";
	                        $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
	                    }else{
	                        //$error = "Incorrect OTP.";
	                        //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                    }
	                } else {
	                    $error = "Please verify mobile.";
	                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                }
				}
            }

            if($contact_person_phone != "")
            {
                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
                if($verifyOtp){ 
                    $mobileverifyotp = $verifyOtp->otp;
                    if($contact_person_otp != $mobileverifyotp){
                        $error = "Please enter valid OTP to verify mobile.";
                        $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
                    }else{
                        //$error = "Incorrect OTP.";
                        //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
                    }
                } else {
                    $error = "Please verify mobile.";
                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
                }
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('labour_enquiry')->insert(['customer_id' => $partner_id, 'location' => $location, 'other_city' => $other_city, 'purpose' => $purpose, 'need' => $need, 'labour_no' => $labour_no, 'comments' => $comments,  'isactive' => $isactive, 'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'is_edit' => '1', 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Labour enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //Tractor Purchase Enquiry
    public function tractorPurchaseEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $what_need = $request->what_need;
            $company_name = $request->company_name;
            $other_company = $request->other_company;
            $location = $request->location;
            $other_city = $request->other_city;
            $hourse_power = $request->hourse_power;
            $payment_type = $request->payment_type;
            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            $isactive = 1;
            $error = "";
            if($company_name == "" || $company_name == "All"){
                $error = "Please enter company name for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($location == "" || $location == "All"){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($payment_type == "" || $payment_type == "All"){
                $error = "Please enter payment type for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($hourse_power == "" || $hourse_power == "All"){
                $error = "Please enter horse power for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($contact_person_phone != "")
            {
            	$customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
              	if($customer && $customer->phone == $contact_person_phone)
              	{
              		$error = "You can not enter your own mobile number.";
	                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
              	}
              	else
              	{
	                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
	                if($verifyOtp){ 
	                    $mobileverifyotp = $verifyOtp->otp;
	                    if($contact_person_otp != $mobileverifyotp){
	                        $error = "Please enter valid OTP to verify mobile.";
	                        $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
	                    }else{
	                        //$error = "Incorrect OTP.";
	                        //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                    }
	                } else {
	                    $error = "Please verify mobile.";
	                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                }
				}
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->phone;
                    $tractor_purchase_enquiry_id = DB::table('tractor_purchase_enquiry')->insertGetId(['customer_id' => $partner_id, 'name' => $name, 'mobile' => $mobile, 'uses_type' => $what_need, 'company_name' => $company_name, 'other_company' => $other_company, 'hourse_power' => $hourse_power, 'payment_type' => $payment_type, 'location' => $location, 'other_city' => $other_city, 'isactive' => $isactive, 'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'is_edit' => '1', 'created_at' => $date, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor purchase";
                        $message1 = "Type: ".$what_need.", Company:".$company_name.", Location:".$location.", Horse Power:".$hourse_power.", Payment Type:".$payment_type;
                        $this->sendNotification($cust->id, $tractor_purchase_enquiry_id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Tractor purchase enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function tractorRefinanceEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $company_name = $request->company_name;
            $other_company = $request->other_company;
            $location = $request->location;
            $other_city = $request->other_city;
            $hourse_power = $request->hourse_power;
            $payment_type = $request->payment_type;
            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            $isactive = 1;
            $error = "";
            if($company_name == ""){
                $error = "Please enter company name for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($contact_person_phone != "")
            {
            	$customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
              	if($customer && $customer->phone == $contact_person_phone)
              	{
              		$error = "You can not enter your own mobile number.";
	                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
              	}
              	else
              	{
	                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
	                if($verifyOtp){ 
	                    $mobileverifyotp = $verifyOtp->otp;
	                    if($contact_person_otp != $mobileverifyotp){
	                        $error = "Please enter valid OTP to verify mobile.";
	                        $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
	                    }else{
	                        //$error = "Incorrect OTP.";
	                        //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                    }
	                } else {
	                    $error = "Please verify mobile.";
	                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                }
				}
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->phone;
                    $tractor_purchase_enquiry_id = DB::table('tractor_refinance_enquiry')->insertGetId(['customer_id' => $partner_id, 'name' => $name, 'mobile' => $mobile, 'company_name' => $company_name, 'other_company' => $other_company, 'hourse_power' => $hourse_power, 'payment_type' => $payment_type, 'location' => $location, 'other_city' => $other_city, 'isactive' => $isactive, 'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'created_at' => $date, 'is_edit' => '1', 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor Refinance";
                        $message1 = "Company: ".$company_name.", Location:".$location.", Horse Power:".$hourse_power.", Payment Type:".$payment_type;
                        $this->sendNotification($cust->id, $tractor_purchase_enquiry_id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Tractor re-finance enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function tractorRentEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $what_need = $request->what_need;
            $location = $request->location;
            $other_city = $request->other_city;
            $available_date = date("Y-m-d",strtotime($request->available_date));
            $comment = $request->comment;
            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            $model = '';
            $isactive = 1;
            $error = "";
            if($location == "" || $location == "All"){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($contact_person_phone != "")
            {
            	$customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
              	if($customer && $customer->phone == $contact_person_phone)
              	{
              		$error = "You can not enter your own mobile number.";
	                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
              	}
              	else
              	{
	                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
	                if($verifyOtp){ 
	                    $mobileverifyotp = $verifyOtp->otp;
	                    if($contact_person_otp != $mobileverifyotp){
	                        $error = "Please enter valid OTP to verify mobile.";
	                        $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
	                    }else{
	                        //$error = "Incorrect OTP.";
	                        //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                    }
	                } else {
	                    $error = "Please verify mobile.";
	                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                }
				}
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->phone;
                    $tractor_purchase_enquiry_id = DB::table('tractor_rent_enquiry')->insertGetId(['customer_id' => $partner_id, 'name' => $name, 'mobile' => $mobile, 'comment' => $comment, 'available_date' => $available_date, 'location' => $location, 'other_city' => $other_city, 'is_edit' => '1', 'what_type' => $what_need, 'isactive' => $isactive, 'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'created_at' => $date, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor Rent";
                        $message1 = "Type: ".$what_need.", Location:".$location.", Available Date:".$available_date.", Comment:".$comment;
                        $this->sendNotification($cust->id, $tractor_purchase_enquiry_id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Rent enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function tractorSaleEnquiry(Request $request)
    {
        try 
        {

            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $sale_type = $request->sale_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $company_name = $request->company_name;
            $other_company = $request->other_company;
            $model = $request->model;
            $year_manufacturer = $request->year_manufacturer;
            $hourse_power = $request->hourse_power;
            $hrs = $request->hrs;
           
            $exp_price = $request->exp_price;
            $comment = $request->comment;

            $payment_type = $request->payment_type;
            
            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            $tractor_image = $request->tractor_image;

            $isactive = 1;
            $error = "";
            if($location == "" || $location == "All"){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($company_name == "" || $company_name == "All"){
                $error = "Please enter company name for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            if($model == "" || $model == "All"){
                $error = "Please enter model name of tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($hourse_power == "" || $hourse_power == "All"){
                $error = "Please enter horse power of tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($payment_type == "" || $payment_type == "All"){
                //$error = "Please enter payment type of tractor";
                //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }

            if($contact_person_phone != "")
            {
            	$customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '1')->first();
              	if($customer && $customer->phone == $contact_person_phone)
              	{
              		$error = "You can not enter your own mobile number.";
	                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
              	}
              	else
              	{
	                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
	                if($verifyOtp){ 
	                    $mobileverifyotp = $verifyOtp->otp;
	                    if($contact_person_otp != $mobileverifyotp){
	                        $error = "Please enter valid OTP to verify mobile.";
	                        $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
	                    }else{
	                        //$error = "Incorrect OTP.";
	                        //$json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                    }
	                } else {
	                    $error = "Please verify mobile.";
	                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
	                }
				}
            }

            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->phone;

                  	if($tractor_image != ''){
                        $image_parts = explode(";base64,", $tractor_image);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];

                        $tractorimage = rand(10000, 99999).'-'.time().'.'.$image_type;
                        $destinationPath = public_path('/uploads/tractor_image/').$tractorimage;

                        $data = base64_decode($image_parts[1]);
                        // $data = $image_parts[1];
                        file_put_contents($destinationPath, $data);
                    } 

                    $tractor_sell_enquiry_id = DB::table('tractor_sell_enquiry')->insertGetId(['customer_id' => $partner_id, 'name' => $name, 'mobile' => $mobile, 'company_name' => $company_name, 'other_company' => $other_company, 'comment' => $comment, 'model' => $model, 'year_manufacturer' => $year_manufacturer, 'hourse_power' => $hourse_power, 'hrs' => $hrs, 'exp_price' => $exp_price, 'image' => $tractorimage, 'sale_type' => $sale_type, 'location' => $location, 'other_city' => $other_city, 'isactive' => $isactive, 'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'payment_type' => $payment_type, 'created_at' => $date, 'is_edit' => '1', 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor Sale";
                        $message1 = "Name: ".$name.", Phone:".$mobile.", Company:".$company_name.", Comment:".$comment.", Model:".$model.", Manufacturer Year:".$year_manufacturer.", Horse Power:".$hourse_power.", Horse Power:".$hourse_power.", Hours:".$hrs.", Exp. Price:".$exp_price.", Location:".$location;
                        $this->sendNotification($cust->id, $tractor_sell_enquiry_id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Tractor sale enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id, 'tractor_sell_enquiry_id' => $tractor_sell_enquiry_id);
                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function agriland_feedback(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $comment = $request->comment;
            $isactive = 1;
            $error = "";
            if($comment == ""){
                $error = "Please enter comment for feedback";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('feedback')->insert(['customer_id' => $partner_id, 'user_type' => 'partner', 'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Feedback added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    // Soil test
    public function soilTestEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $land_size = $request->land_size;
            $location = $request->location;
            $khasra_no = $request->khasra_no;
            $test_type = $request->test_type;
            $amount = $request->amount;

            /*$crop_type = $request->crop_type;
            $soil_type = $request->soil_type;
            $soil_density = $request->soil_density;
            $avg_yield = $request->avg_yield;*/

            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            //$comments = $request->comment;
            //$exp_price = $request->exp_price;
            $order_status = 'pending';
            $isactive = 1;
            $error = "";
            if($test_type == ""){
                $error = "Please enter valid data.";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer){ 
                    
                  
                   /* get order no */
                   $maxorderno = DB::table('soil_test_orders')->select('id','order_no')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'DESC')->first();
                   //print_r($maxorderno);
                  
                   if(!empty($maxorderno) && $maxorderno->id != '') {
                        
                        if($maxorderno->order_no != ''){
                            $orderno = $maxorderno->order_no;
                            
                            $orderno = $orderno+1;
                        }else{
                            $orderno = 1;
                        }
                        
                   }else{
                        $orderno = 1;
                   }
                  
                    $order_no = str_pad($orderno, 3, "0", STR_PAD_LEFT);
                    $name = $customer->name;
                    $mobile = $customer->phone;
                   $orderid = DB::table('soil_test_orders')->insertGetId(['customer_id' => $partner_id, 'order_no' => $order_no, 'name' => $name, 'mobile' => $mobile, 'land_size' => $land_size, 'location' => $location, 'khasra_no' => $khasra_no, 'test_type' => $test_type, 'amount' => $amount, 'order_status' => $order_status, 'isactive' => $isactive, 'user_type' => 'partner', 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'created_at' => $date, 'updated_at' => $date]); //'crop_type' => $crop_type, 'soil_type' => $soil_type, 'soil_density' => $soil_density, 'avg_yield' => $avg_yield, 
                   
                   /* FCM Notification */
                   $customerToken = $customer->fcmToken; 
                   //$customerToken = 'e2k1jCT_Ty2qOLk4gSX_Hz:APA91bHXhqvz5KlPW6EW9vDNeldzJR-yQcIarygjgn8fo2b08ihcEIFiu-NzHI-1A3L7MJMYyI4ehSWzBwimX5T0ExRbooa6-UxGrfckSdD-F49FzJxwWcU4M58qRu8yeRduTk62eBMW';
                   $customerName = $customer->name; 
                   $notification_title = "Soil Test Order";
                   $notification_body = $order_no." Your soil test order has been successfully created! Thanks for order with us.";
                   $notification_type = "soil_order";
                   $notif_data = array($notification_title,$customerName,$notification_body,"","");
                
                   //$customerNotify = $this->push_notification($notif_data,$customerToken);
                   $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $partner_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'partner', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                   /* End */
                    $status_code = $success = '1';
                    $message = 'Soil Test Order Added Successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id, 'order_id' => "".$orderid);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function enquiry_tracking(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $enquiry_type = $request->enquiry_type;
            $lead_id = $request->lead_id;
            $phone_number = $request->phone_number;
            
            $error = "";
            if($enquiry_type == ""){
                $error = "Please enter enquiry type";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            if($error == ""){
                $customer = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('enquiry_tracking')->insert(['customer_id' => $partner_id, 'enquiry_type' => $enquiry_type, 'user_type' => 'partner', 'lead_id' => $lead_id, 'phone_number' => $phone_number, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Enquiry Type added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => '');
        }
        
        return response()->json($json, 200);
    }
}
