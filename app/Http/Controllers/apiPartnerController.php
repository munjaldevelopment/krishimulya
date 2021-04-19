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

class apiPartnerController extends Controller
{
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

                        $status_code = '1';
                        $message = 'Partner login successfully';
                        $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partnerid, 'name' =>  $vendors->name, 'phone' => $mobile, 'pincode' =>  $vendors->pincode, 'refer_url' =>  $refer_url, "partner_type" => "already");
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
                    $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
     
                    $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=krishimulya&password=krishimulya&senderid=KMAPAY&channel=trans&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=35");

                     DB::table('vendors')->where('id', '=', $vendorsid)->update(['otp' => $otp, 'updated_at' => $date]);

                    $status_code = '1';
                    $message = 'OTP Send Successfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'partner_id' => (int)$vendors->id, 'phone' => $mobile);
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
                    $date   = date('Y-m-d H:i:s');
                    DB::table('vendors')->where('id', '=', $vendorsid)->update(['password' => $password, 'updated_at' => $date]);
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
                    $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
     
                    $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=krishimulya&password=krishimulya&senderid=KMAPAY&channel=trans&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=35");

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

    //Partner Profile
    public function partner_dashboard(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
           
            $partner = DB::table('vendors')->where('id', $partner_id)->where('status', '=', '1')->first();
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
                $mobile = $partner->mobile;
                
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
            $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
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
                $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
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
    public function partner_profile(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
           
            $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
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
                $mobile = $partner->mobile;
                
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
                    $partner_image  =  $baseUrl."/public/uploads/partner_image/".$partner->image;
                
                }else{
                   $partner_image  =  $baseUrl."/public/uploads/profile.jpg";
                }
                
                
                $status_code = $success = '1';
                $message = 'Partner Profile Info';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id , 'name' => $name, 'email' => $email, 'age' => $age, 'mobile' => $mobile, 'address' => $address, 'city' => $city, 'pincode' => $pincode, 'state' => $state, 'partner_code' => $pcode, 'partner_image' => $partner_image);


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
            $age = $request->age;
            $email = $request->email;
            //$mobile = $request->telephone;
            $address = $request->address;
            $partner_image = $request->partner_image;
            $city = $request->city;
            //$pincode = $request->pincode;

            $partners = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
            if($partners){ 
                $partnerimage = '';
                if($partner_image != ''){
                    $image_parts = explode(";base64,", $partner_image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];

                    $partnerimage = rand(10000, 99999).'-'.time().'.'.$image_type;
                    $destinationPath = public_path('/uploads/partner_image/').$partnerimage;

                    $data = base64_decode($image_parts[1]);
                   // $data = $image_parts[1];
                    file_put_contents($destinationPath, $data);
                }
                DB::table('partners')->where('id', '=', $partner_id)->update(['name' => $name, 'age' => $age, 'email' => $email, 'address' => $address, 'city' => $city, 'image' => $partnerimage, 'updated_at' => $date]);

                $status_code = $success = '1';
                $message = 'Partner info updated successfully';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


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
           
            $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
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

    //Rent Enquiry
    public function partner_rent_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $name = $request->name;
            $mobile = $request->mobile;
            //$email = $request->email;
            $what_need = $request->what_need;
            $location = $request->location;
            $other_city = $request->other_city;
            $available_date = date("Y-m-d",strtotime($request->available_date));
            $comment = $request->comment;
            $model = '';
            $isactive = 1;
            $error = "";
            if($location == ""){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            if($error == ""){
                $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 
                    
                    DB::table('tractor_rent_enquiry')->insert(['customer_id' => $partner_id, 'name' => $name, 'mobile' => $mobile, 'comment' => $comment, 'available_date' => $available_date, 'location' => $location, 'other_city' => $other_city,  'what_type' => $what_need, 'user_type' => 'partner', 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Rent enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


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



    //Rent IN Result
    public function partner_rent_result_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $name = $request->name;
            $mobile = $request->mobile;
            
            $what_need = $request->what_need;
            $location = $request->location;
            $other_city = $request->other_city;
            $available_date = date("Y-m-d",strtotime($request->available_date));
            $error = "";
            if($location == ""){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            if($error == ""){
                $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 

                    
                    
                    $rentinList = DB::table('tractor_rent_enquiry')->select('id','customer_id', 'name', 'mobile', 'location', 'other_city', 'what_type', 'user_type', 'available_date','comment')->where('isactive', '=', 1);

                    if($name){
                        $rentinList = $rentinList->where('name',$name);    
                    }
                    
                    if($mobile){
                        $rentinList = $rentinList->where('mobile',$mobile);    
                    }

                    if($what_need){
                        $rentinList = $rentinList->where('what_type',$what_need);    
                    }

                    if($location){
                        $rentinList = $rentinList->where('location','LIKE',$location);    
                    }

                    if($other_city){
                        $rentinList = $rentinList->where('other_city','LIKE',$other_city);    
                    }

                    if($available_date){
                          
                        $rentinList = $rentinList->where('available_date', '<=', $available_date.' 00:00:00'); 
                    }
                    $rentinList = $rentinList->orderBy('available_date', 'asc')->get(); 
                    if(count($rentinList) >0){
                        $r_list = array();
                        foreach($rentinList as $rlist)
                        {
                            
                            $customer_name = $rlist->name;
                            $customer_telphone = $rlist->mobile;
                            $available_date = date("d-m-Y",strtotime($rlist->available_date));
                            $othercity = ($rlist->other_city != '') ? $rlist->other_city : "";
                            $r_list[] = ['id' => (string)$rlist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'location' =>$rlist->location, 'other_city' =>$othercity, 'what_type' => $rlist->what_type, 'available_date' => $available_date, 'comment' => $rlist->comment]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Rent In enquiry result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'rentin_list' => $r_list);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Tractor for rent not available right now';
                    
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

    

    //Tractor Sale Enquiry
    public function tractor_sale_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $name = $request->name;
            $mobile = $request->mobile;
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
            $tractor_image = $request->tractor_image;
            $isactive = 1;
            $error = "";
            if($company_name == ""){
                $error = "Please enter company name for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            if($model == ""){
                $error = "Please enter model name of tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            if($error == ""){
                $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 
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

                    DB::table('tractor_sell_enquiry')->insert(['customer_id' => $partner_id, 'name' => $name, 'mobile' => $mobile, 'company_name' => $company_name, 'other_company' => $other_company, 'comment' => $comment, 'model' => $model, 'year_manufacturer' => $year_manufacturer, 'hourse_power' => $hourse_power, 'hrs' => $hrs, 'exp_price' => $exp_price, 'image' => $tractorimage, 'sale_type' => $sale_type, 'location' => $location, 'other_city' => $other_city, 'user_type' => 'partner', 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Tractor sale enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


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

    //Tractor Purchase Enquiry
    public function tractor_purchase_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $name = $request->name;
            $mobile = $request->mobile;
            $what_need = $request->what_need;
            $company_name = $request->company_name;
            $other_company = $request->other_company;
            $location = $request->location;
            $other_city = $request->other_city;
            $hourse_power = $request->hourse_power;
            $payment_type = $request->payment_type;
            $isactive = 1;
            $error = "";
            if($company_name == ""){
                $error = "Please enter company name for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            if($error == ""){
                $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 
                    
                    DB::table('tractor_purchase_enquiry')->insert(['customer_id' => $partner_id, 'name' => $name, 'mobile' => $mobile, 'uses_type' => $what_need, 'company_name' => $company_name, 'other_company' => $other_company, 'hourse_power' => $hourse_power, 'payment_type' => $payment_type, 'location' => $location, 'other_city' => $other_city, 'user_type' => 'partner', 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Tractor purchase enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


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

    //Purchase Old Enquiry
    public function purchase_old_results(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $name = $request->name;
            $mobile = $request->mobile;
            $what_need = $request->what_need;
            $location = $request->location;
            $other_city = $request->other_city;
            $company_name = $request->company_name;
            $other_company = $request->other_company;
            $hourse_power = $request->hourse_power;
            $model = $request->model;
            $year_manufacturer = $request->year_manufacturer;
            $error = "";
            if($what_need == ""){
                $error = "Please select what need to search";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            if($error == ""){
                $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 

                    
                    
                    $purchaseOldList = DB::table('tractor_sell_enquiry')->select('id','customer_id','name','mobile','company_name','other_company','model','hourse_power','hrs', 'exp_price', 'image','sale_type','location', 'other_city','user_type')->where('isactive', '=', 1);

                    if($name){
                        $purchaseOldList = $purchaseOldList->where('name',$name);    
                    }

                    if($mobile){
                        $purchaseOldList = $purchaseOldList->where('mobile',$mobile);    
                    }

                    if($what_need){
                        $purchaseOldList = $purchaseOldList->where('sale_type',$what_need);    
                    }

                    if($company_name){
                        $purchaseOldList = $purchaseOldList->where('company_name',$company_name);    
                    }

                    if($other_company){
                        $purchaseOldList = $purchaseOldList->where('other_company',$other_company);    
                    }

                    if($location){
                        $purchaseOldList = $purchaseOldList->where('location',$location);    
                    }

                    if($model){
                        $purchaseOldList = $purchaseOldList->where('model',$model);    
                    }

                    if($year_manufacturer){
                        $purchaseOldList = $purchaseOldList->where('year_manufacturer',$year_manufacturer);    
                    }

                    if($other_city){
                        $purchaseOldList = $purchaseOldList->where('other_city',$other_city);    
                    }

                    if($hourse_power){
                        
                        $purchaseOldList = $purchaseOldList->where('hourse_power','LIKE',$hourse_power);    
                    }

                   
                    $purchaseOldList = $purchaseOldList->orderBy('id', 'desc')->get(); 

                    if(count($purchaseOldList) >0){
                        $purchaseList = array();
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
                            $purchaseList[] = ['id' => (string)$plist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'company_name' =>$plist->company_name, 'other_company' =>$other_company, 'what_need' =>$plist->sale_type, 'location' =>$plist->location, 'other_city' =>$othercity, 'model' => $plist->model, 'hourse_power' => $plist->hourse_power, 'hrs' => $plist->hrs, 'exp_price' => $plist->exp_price, 'image' => $tractor_image]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Old Purchase enquiry result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'purchase_list' => $purchaseList);
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

    //Insurance Enquiry
    public function insurance_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $name = $request->name;
            $mobile = $request->mobile;
            $insurance_type = $request->insurance_type;
            $other_insurance_type = $request->other_insurance_type;
            $comments = $request->comments;
            $isactive = 1;
            $error = "";
            if($insurance_type == ""){
                $error = "Please enter insurance type";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            if($error == ""){
                $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 
                    
                    DB::table('insurance_enquiry')->insert(['customer_id' => $partner_id, 'insurance_type' => $insurance_type, 'name' => $name, 'mobile' => $mobile, 'other_insurance_type' => $other_insurance_type, 'comments' => $comments, 'user_type' => 'partner', 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Insurance enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


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

   
   //Agri Land Feedback
    public function enquiry_tracking(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $enquiry_type = $request->enquiry_type;
            $error = "";
            if($enquiry_type == ""){
                $error = "Please enter enquiry type";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            if($error == ""){
                $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 
                    
                    DB::table('partner_enquiry_tracking')->insert(['partner_id' => $partner_id, 'enquiry_type' => $enquiry_type, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Enquiry Type added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id);


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
                    $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
     
                    $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=krishimulya&password=krishimulya&senderid=KMAPAY&channel=trans&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=35");

                    DB::table('tbl_mobile_verify')->where('id', '=', $verifyid)->update(['otp' => $otp]);

                    $status_code = '1';
                    $message = 'OTP Send successfully';
                    $json = array('status_code' => $status_code,  'message' => $message,  'mobile' => $mobile, 'otp' => $otp);
                } 
                else 
                {
                    $otp = rand(111111, 999999);
                    $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
     
                    $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=krishimulya&password=krishimulya&senderid=KMAPAY&channel=trans&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=35");
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
    //Agri Land Sale Enquiry
    public function create_soiltest_order(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $partner_id = $request->partner_id;
            $name = $request->name;
            $mobile = $request->mobile;
            $otp = $request->otp;
            $land_size = $request->land_size;
            $location = $request->location;
            $khasra_no = $request->khasra_no;
            $test_type = $request->test_type;
            $amount = $request->amount;
            //$comments = $request->comment;
            //$exp_price = $request->exp_price;
            $order_status = 'pending';
            $isactive = 1;
            $error = "";
            if($test_type == ""){
                $error = "Please enter valid data.";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
            }
            
            $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $mobile)->first();
            if($verifyOtp){ 
                 $mobileverifyotp = $verifyOtp->otp;
                if($otp != $mobileverifyotp){
                    $error = "Please enter valid OTP to verify mobile.";
                    $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);
                }else{
                    $otpval = '';
                    DB::table('tbl_mobile_verify')->where('mobile', '=', $mobile)->update(['otp' => $otpval]);
                }
            } else {
                $error = "Please verify mobile.";
                $json = array('status_code' => '0', 'message' => $error, 'partner_id' => $partner_id);  
            }        
            
            if($error == ""){
                $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 
                    
                  
                   /* get order no */
                   $maxorderno = DB::table('soil_test_orders')->select('id','order_no')->where('isactive', '=', 1)->orderBy('id', 'DESC')->first();
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
                   $orderid = DB::table('soil_test_orders')->insertGetId(['customer_id' => $partner_id,'order_no' => $order_no, 'name' => $name, 'mobile' => $mobile, 'land_size' => $land_size, 'location' => $location, 'khasra_no' => $khasra_no, 'test_type' => $test_type, 'amount' => $amount, 'order_status' => $order_status,'user_type' => 'partner',  'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);
                   //DB::table('soil_test_orders')->where('id', '=', $orderid)->update(['order_no' => $order_no]);
                   
                   /* FCM Notification */
                   $partnerToken = $partner->fcmToken; 
                   //$customerToken = 'e2k1jCT_Ty2qOLk4gSX_Hz:APA91bHXhqvz5KlPW6EW9vDNeldzJR-yQcIarygjgn8fo2b08ihcEIFiu-NzHI-1A3L7MJMYyI4ehSWzBwimX5T0ExRbooa6-UxGrfckSdD-F49FzJxwWcU4M58qRu8yeRduTk62eBMW';
                   $partnerName = $partner->name; 
                   $notification_title = "Soil Test Order";
                   $notification_body = $order_no." Your soil test order has been successfully created! Thanks for order with us.";
                   $notification_type = "soil_order";
                   $notif_data = array($notification_title,$partnerName,$notification_body,"","");
                
                   $customerNotify = $this->push_notification($notif_data,$partnerToken);
                   $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $partner_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'partner', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                   /* End */
                    $status_code = $success = '1';
                    $message = 'Soil Test Order Added Successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'partner_id' => $partner_id, 'order_id' => "".$orderid);


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

    public function get_partner_soilOdr(Request $request)
    {
        try 
        {   
             $baseUrl = URL::to("/");
            $json       =   array();
            $partner_id = $request->partner_id;
            $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 
                    $soilodrExists = DB::table('soil_test_orders')->where('customer_id', $partner_id)->where('user_type', 'partner')->orderBy('id', 'DESC')->count();

                    if($soilodrExists >0){
                        $soilodrList = DB::table('soil_test_orders')->select('id','order_no','name','mobile','amount','land_size','location','khasra_no','test_type','report_file','order_status','created_at')->where('customer_id', $partner_id)->where('user_type', 'partner')->orderBy('id', 'DESC')->get();

                        $odr_List = array();
                        $testype_name = "";
                        foreach($soilodrList as $odrlist)
                        {
                            //$soiltest_type = DB::table('soil_test_type')->where('id', $odrlist->test_type)->first();
                            $testype_name = $odrlist->test_type; 
                            /*if($odrlist->report_file != ''){
                                
                                $report_file = $baseUrl."/public/order_report/".$odrlist->report_file;
                            }else{
                                $report_file =  $baseUrl."/public/order_report/dummy.pdf";
                            }*/
                            
                            if($odrlist->order_status == 'done'){
                                $report_file =  $baseUrl."/public/order_report/dummy.pdf";
                            }else{
                                $report_file = '';
                            }                           
                            if($odrlist->khasra_no != ''){
                                $khasra_no = $odrlist->khasra_no;
                            }else{
                                $khasra_no = "";
                            }
                            $odr_List[] = array('id' => "".$odrlist->id, 'order_no' => $odrlist->order_no, 'customer_name' => $odrlist->name, 'mobile' => $odrlist->mobile, 'testypeName' => $testype_name,'amount' => "".$odrlist->amount, 'land_size' => $odrlist->land_size, 'location' => $odrlist->location, 'khasra_no' => $khasra_no, 'report_file' => $report_file, 'date' => date('d-m-Y H:i:s', strtotime($odrlist->created_at)),'order_status' => $odrlist->order_status); 
                           
                        } 

                        //print_r($odr_List);
                        //exit;
                        $status_code = '1';
                        $message = 'Soil Order List';
                        $json = array('status_code' => $status_code,  'message' => $message, 'odr_List' => $odr_List);
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

    

    public function updateOdrTestType(Request $request)
    {
        try 
        {   
            
            $json      =   array();
            $partner_id = $request->partner_id;
            $order_id = $request->order_id;
            $test_type = $request->test_type;
            $amount = $request->amount;
            $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 
                    $soilodrList = DB::table('soil_test_orders')->select('id','order_no')->where('customer_id', $partner_id)->where('id', $order_id)->where('user_type', 'partner')->orderBy('id', 'DESC')->first();

                    if($soilodrList){
                        $date   = date('Y-m-d H:i:s');
                         DB::table('soil_test_orders')->where('id', '=', $order_id)->update(['test_type' => $test_type, 'amount' => $amount, 'updated_at' => $date]);
                        
                        /* FCM Notification */
                       $partnerToken = $partner->fcmToken; 
                       $partnerName = $partner->name; 
                       $notification_title = "Soil Test Order";
                       $notification_body = $soilodrList->order_no." Your soil test order has been successfully Updated! Thanks for order with us.";
                       $notification_type = "soil_order";
                       $notif_data = array($notification_title,$partnerName,$notification_body,"","");
                    
                        $customerNotify = $this->push_notification($notif_data,$partnerToken);
                        $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $partner_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'partner', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                       /* End */    
                        $status_code = '1';
                        $message = 'Soil Order Test Type Updated';
                        $json = array('status_code' => $status_code,  'message' => $message, 'test_type' => $test_type, 'amount' => $amount , 'order_id' => $order_id, 'partner_id' => $partner_id);
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

     public function orderReportCreated(Request $request)
    {
        try 
        {   
            
            $json      =   array();
            $partner_id = $request->partner_id;
            $order_id = $request->order_id;
            $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 
                    $soilodrList = DB::table('soil_test_orders')->select('id','order_no')->where('customer_id', $partner_id)->where('id', $order_id)->where('user_type', 'partner')->orderBy('id', 'DESC')->first();

                    if($soilodrList){
                        $date   = date('Y-m-d H:i:s');
                        $order_status = 'done';
                         DB::table('soil_test_orders')->where('id', '=', $order_id)->update(['order_status' => $order_status, 'updated_at' => $date]);
                        
                        /* FCM Notification */
                       $customerToken = $partner->fcmToken; 
                       $customerName = $partner->name; 
                       $notification_title = "Soil Test Report Created";
                       $notification_body = $soilodrList->order_no." Your soil test order report has been successfully generated! Thanks for order with us.";
                       $notification_type = "soil_order";
                       $notif_data = array($notification_title,$customerName,$notification_body,"","");
                    
                        $customerNotify = $this->push_notification($notif_data,$customerToken);
                       $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $partner_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'partner', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                       /* End */    
                        $status_code = '1';
                        $message = 'Soil Order Test Report Created';
                        $json = array('status_code' => $status_code,  'message' => $message, 'order_status' => $order_status, 'order_id' => $order_id, 'partner_id' => $partner_id);
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

    public function notification_list(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $partner_id = $request->partner_id;
            $partner = DB::table('partners')->where('id', $partner_id)->where('status', '=', '1')->first();
                if($partner){ 
                    $soilnotificationExists = DB::table('notifications')->where('customer_id', $partner_id)->where('user_type', 'partner')->orderBy('id', 'DESC')->count();
                    $notify_List = array();
                    if($soilnotificationExists >0){
                        $soilNotifyList = DB::table('notifications')->select('id','notification_title','notification_content','notification_type','created_at')->where('customer_id', $partner_id)->where('user_type', 'partner')->orderBy('id', 'DESC')->get();

                        
                        foreach($soilNotifyList as $notifylist)
                        {
                            $notification_type = "";
                            if($notifylist->notification_type == 'soil_order'){

                                $notification_type = 'Soil Order';
                            }

                            $notify_List[] = array('id' => "".$notifylist->id, 'notification_title' => $notifylist->notification_title,'notification_content' => "".$notifylist->notification_content, 'notification_type' => $notification_type, 'date' => date('d-m-Y H:i:s', strtotime($notifylist->created_at))); 
                           
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

    public function push_notification($data, $device_tokens)
        {
            $senderid = '789600431472';
            $msg = array
            (
                'title'  => $data[0],
                'name' => $data[1],
                'body' => $data[2],
                //'image' => $data[3],
                //'product_id' => $data[4],
                'vibrate' => 1,
                'sound'  => 'mySound',      
                'driverData'=>$data,        
            );

            $dataarr['data'] = array
            (
                'title'  => $data[0],
                'name' => $data[1],
                'body' => $data[2],
                //'image' => $data[3],
                //'product_id' => $data[4],
                        
            );

            $fields = array
            (
                'to'        => $device_tokens,
                'notification'  => $msg,
                'data'  => $dataarr
            ); 
            $serverKey = 'AAAAt9fabXA:APA91bFYHHT1fn136eJoJS2qNormp-KGZugqxTsSb859REUYAdVr9mWp7qsKgCeEmGVvygGIhybVOrc49S79DGknfMqVfvc_wi8piLb0TjjcKzIjJOY2snY763yCQeAEuDo32Wj6fA26'; 
            $headers = array
            (
                'Authorization: key=' . $serverKey,
                'Content-Type: application/json'
            );
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
            $result = curl_exec($ch );

            curl_close( $ch );
            return true;
        }
    
}
