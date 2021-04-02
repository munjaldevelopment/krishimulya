<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use App\Models\Setting;
use DB;
use App;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Hash;
use URL;
use File;
use Session;
use QR_Code\QR_Code;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class apiController extends Controller
{
    //START LOGIN
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

    public function sendNotification($customer_id, $title, $message, $image = '')
    {
        $date = date('Y-m-d H:i:s');
        $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $customer_id,'notification_title' => $title, 'notification_content' => $message, 'notification_type' => 'customer_notification', 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);
        //echo $success.",".$fail.",".$total; exit;
    }

    public function getPincodeInfo($pincode)
    {
        /*$result = $this->httpGet("https://api.postalpincode.in/pincode/".$pincode);
        $resultArr = json_decode($result, 1);

        $customerCodeData = array();
        $customerCode = $customerCity = $customerState = "";
        if($resultArr)
        {
            if($resultArr[0]['Status'] == 'Success')
            {
                $customerCode = substr($resultArr[0]['PostOffice'][0]['State'],0,3).substr($resultArr[0]['PostOffice'][0]['Block'],0,3);
                $customerCode = strtoupper($customerCode);

                $customerCodeData = array('customer_code' => $customerCode, 'customer_city' => $resultArr[0]['PostOffice'][0]['Block'], 'customer_state' => $resultArr[0]['PostOffice'][0]['State']);
            }
        }*/

        $customerCodeData = array();
        $pincodeData = DB::table('pincodes')->where('zip', $pincode)->where('active', '1')->first();
        if($pincodeData) 
        {
            $customerCode = substr($pincodeData->state, 0, 3).substr($pincodeData->city, 0, 3);
            $customerCodeData = array('customer_code' => $customerCode, 'customer_city' => $pincodeData->city, 'customer_state' => $pincodeData->state);
        }

        return $customerCodeData;
    }

    public function customerLogin(Request $request)
    {
        try 
        {
            $mobile = $request->mobile;
            $device_id = $request->device_id;
            $fcmToken = $request->fcmToken;
            $refer_code = $request->refer_code;
            $error = "";
            $json = array();

            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
            
            if($device_id == ""){
                $error = "Device ID not found";
                $json = array('status_code' => '0', 'message' => $error);
            }

            if($error == ""){
                $userData = array();
                
                $date   = date('Y-m-d H:i:s');
                $customer = DB::table('customers')->where('telephone', $mobile)->where('status', '1')->first();
                if($customer) 
                {
                    $customerid = $customer->id;
                    $deviceid = $customer->device_id;

                    DB::table('customers')->where('id', '=', $customerid)->update(['fcmToken' => $fcmToken, 'updated_at' => $date]);
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
                            DB::table('customers')->where('id', '=', $customerid)->update(['referal_partner_id' => $referal_customer_id, 'updated_at' => $date]);
                        }
                    }


                    $refer_url = "https://play.google.com/store/apps/details?id=com.microprixs.krishimulya&referrer=krvrefer".$customerid;
                    
                    $status_code = '1';
                    $message = 'Customer login successfully';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customerid, 'temp_customer_id' => '', 'mobile' => $mobile, 'name' => $customer->name, 'pincode' => $customer->pincode, 'referurl' => $refer_url, "customer_type" => "already");
                }else{
                	/* If device id already register with another mobile */
                    $otp = rand(111111, 999999);
                    $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
     
                    $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=krishimulya&password=krishimulya&senderid=KMAPAY&channel=TRANS&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=15");

                    $customerid = DB::table('customers_temp')->insertGetId(['telephone' => $mobile, 'otp' => $otp, 'device_id' => $device_id, 'fcmToken' => $fcmToken, 'created_at' => $date, 'updated_at' => $date]); 

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
                            DB::table('customers_temp')->where('id', '=', $customerid)->update(['referal_partner_id' => $referal_customer_id, 'updated_at' => $date]);
                        }
                    }

                    $status_code = $success = '1';
                    $message = 'Customer Otp Send, Please Process Next Step';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '', 'temp_customer_id' => $customerid, 'mobile' => $mobile, "customer_type" => "new", 'otp' => "".$otp);
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
    
    //START VERIFY
    public function customerVerify(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $mobile = $request->mobile;
            $otp = $request->otp;

            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($otp == ""){
                $error = "Please fill correct OTP";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($error == ""){
                $customer = DB::table('customers_temp')->where('telephone', $mobile)->where('otp', $otp)->orderBy('id', 'DESC')->first();
                if($customer) 
                {
                    DB::table('customers_temp')->where(['id' => $customer->id])->update(['status' => 1]);

                    $customerData= DB::table('customers_temp')->where('id', $customer->id)->first();
                    
                    $refer_url = "https://play.google.com/store/apps/details?id=com.microprixs.krishimulya&referrer=krvrefer".$customer->id;

                    $status_code = '1';
                    $message = 'Customer activated successfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'customer_id' => (int)$customer->id, 'mobile' => $mobile, 'pincode' => $customer->pincode, 'referurl' => $refer_url);
                }
                else 
                {
                    $status_code = $success = '0';
                    $message = 'Sorry! Customer does not exists or Incorrect OTP!';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '', 'mobile' => $mobile);
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
                $customer = DB::table('customers_temp')->where('telephone', $mobile)->orderBy('id', 'DESC')->first();
                if($customer) 
                {
                    $customerid = $customer->id;
                    $otp = rand(111111, 999999);
                    $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
     
                     $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=krishimulya&password=krishimulya&senderid=KMAPAY&channel=TRANS&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=15");

                    DB::table('customers_temp')->where('id', '=', $customerid)->update(['otp' => $otp, 'updated_at' => $date]);

                    $status_code = '1';
                    $message = 'OTP Send sucessfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'customer_id' => (int)$customerid,  'mobile' => $mobile, 'otp' => "".$otp);
                } 
                else 
                {
                    $status_code = $success = '0';
                    $message = 'Sorry! Customer does not exists';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '', 'mobile' => $mobile);
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

    public function customerRegister(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $name = $request->name;
            $age = $request->age;
            $pincode = $request->pincode;

            $customer = DB::table('customers_temp')->where('id', $customer_id)->where('status', '1')->first();
            if($customer){
                $otp = rand(111111, 999999);
                
                // Add entry in customer table
                $customerid = DB::table('customers')->insertGetId(['referal_partner_id' => $customer->referal_partner_id, 'temp_customer_id' => $customer_id, 'name' => $name, 'age' => $age, 'pincode' => $pincode, 'telephone' => $customer->telephone, 'otp' => $otp, 'device_id' => $customer->device_id, 'fcmToken' => $customer->fcmToken, 'created_at' => $date, 'status' => '1', 'updated_at' => $date]); 

                $customerCodeData = $this->getPincodeInfo($pincode);

                //$customerCodeData = array('customer_code' => $customerCode, 'customer_city' => $customerCity, 'customer_state' => $customerState);

                $newCustomerID = "00001";
                if($customerid > 9 && $customerid <= 99)
                {
                    $newCustomerID = "000".$customerid;
                }
                else if($customerid > 99 && $customerid <= 999)
                {
                    $newCustomerID = "00".$customerid;
                }
                else if($customerid > 999 && $customerid <= 9999)
                {
                    $newCustomerID = "0".$customerid;
                }
                else
                {
                    $newCustomerID = $customerid;
                }

                if($customerCodeData)
                {
                    $crn = $customerCodeData['customer_code'].$newCustomerID;
                    $customer_city = $customerCodeData['customer_city'];
                    $customer_state = $customerCodeData['customer_state'];

                    DB::table('customers')->where('id', '=', $customerid)->update(['crn' => $crn, 'city' => $customer_city, 'state' => $customer_state]);

                    DB::table('customers_temp')->where('id', '=', $customer_id)->update(['crn' => $crn, 'city' => $customer_city, 'state' => $customer_state]);
                }
                
                $status_code = $success = '1';
                $message = 'Customer info added successfully';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customerid, 'pincode' => $pincode);
            } else{
                $status_code = $success = '0';
                $message = 'Customer not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    /* Get customer detail */
    public function getCustomerType(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $customer_id = $request->customer_id;

            $app_version = "";

            if(isset($request->app_version))
            {
                $app_version = $request->app_version;
            }

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){
                $custname = $customer->name;

                // update app version in
                $date = date('Y-m-d H:i:s');
                DB::table('customers')->where('id', '=', $customer_id)->update(['app_version' => $app_version, 'updated_at' => $date]);
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
    //END 
    
    //START show feed list 
    /*public function birth_year(Request $request)
    {
        try 
        {   
            $json   =   array();
            $yearList       =   array();
            $year = date('Y');
            $year2 = date('Y')-60;
            for($y = $year; $y>$year2; $y--){
                $yearList[] = array('year' => "".$y."");
            }
            
            $status_code = '1';
            $message = 'Birth year list';
            $json = array('status_code' => $status_code,  'message' => $message, 'yearList' => $yearList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }*/
    //END 

    //Customer Update
    


    //Customer Update
    public function customer_profile(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
           
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                if($customer->name){
                    $name = $customer->name;
                }else{
                    $name = "";
                }
                if($customer->email){
                    $email = $customer->email; 
                }else{
                    $email = "";
                }
                if($customer->age){
                 //$age = date("d-m-Y",strtotime($customer->age));
                   $age = $customer->age;
                }else{
                 $age = "";
                } 
                $telephone = $customer->telephone;
                if($customer->address1){
                    $address = $customer->address1;    
                }else{
                    $address = "";
                }
                if($customer->city){
                    $city = $customer->city; 
                }else{
                    $city = "";
                }
                
                $baseUrl = URL::to("/");
                $customer_image  = "";
                if($customer->image){
                    $customer_image  =  $baseUrl."/public/uploads/customer_image/".$customer->image;
                
                }else{
                   $customer_image  =  $baseUrl."/public/uploads/customer_image/profile.jpg";
                }
                
                
                $status_code = $success = '1';
                $message = 'Customer Profile Info';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id , 'name' => $name, 'email' => $email, 'dob' => $age, 'telephone' => $telephone, 'address' => $address , 'city' => $city, 'customer_image' => $customer_image);


            } else{
                $status_code = $success = '0';
                $message = 'Customer not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //Customer Update
    public function update_profile(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $name = $request->name;
            $age = $request->age;
            $email = $request->email;
            $telephone = $request->telephone;
            $address1 = $request->address;
            //$address2 = $request->address2;
            $customer_image = $request->customer_image;
            $address2 = '';
            $city = $request->city;


            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $customerimage = '';
                /*if ($request->hasFile('customer_image')) {
                    $image = $request->file('customer_image'); 
                    if($image)
                    {
                        $customer_image = rand(10000, 99999).'-'.time().'.'.$image->getClientOriginalExtension();
                        $destinationPath = public_path('/uploads/customer_image/');
                        $image->move($destinationPath, $customer_image);
                        
                    }
                }*/
                if($customer_image != ''){
                    $image_parts = explode(";base64,", $customer_image);
                    $image_type_aux = explode("image/", $image_parts[0]);
                    $image_type = $image_type_aux[1];

                    $customerimage = rand(10000, 99999).'-'.time().'.'.$image_type;
                    $destinationPath = public_path('/uploads/customer_image/').$customerimage;

                    $data = base64_decode($image_parts[1]);
                   // $data = $image_parts[1];
                    file_put_contents($destinationPath, $data);
                }
                DB::table('customers')->where('id', '=', $customer_id)->update(['name' => $name, 'age' => $age, 'email' => $email, 'telephone' => $telephone, 'address1' => $address1, 'address2' => $address2, 'city' => $city, 'image' => $customerimage, 'updated_at' => $date]);

                $status_code = $success = '1';
                $message = 'Customer info updated successfully';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


            } else{
                $status_code = $success = '0';
                $message = 'Customer not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }


    //Customer Update
    public function customer_logout(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
           
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                
                $status_code = $success = '1';
                $message = 'Customer logout successfully';
                
                $json = array('status_code' => $status_code, 'message' => $message);


            } else{
                $status_code = $success = '0';
                $message = 'Customer not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }


    //Finance Enquiry
    public function finance_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $purpose = $request->purpose;
            $usesof = $request->usesof;
            $brandname = $request->brandname;
            //$model = $request->model;
            $year_of_manufacture = $request->year_of_manufacture;
            $name = $request->name;
            $mobile = $request->mobile;
            $ftype = $request->ftype;
            $isactive = 1;
            $error = "";
            if($purpose == ""){
                $error = "Please enter purpose for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            if($usesof == ""){
                $error = "Please enter uses of tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('finance_enquiry')->insert(['customer_id' => $customer_id, 'purpose' => $purpose, 'usesof' => $usesof, 'brandname' => $brandname, 'year_of_manufacture' => $year_of_manufacture, 'name' => $name, 'mobile' => $mobile, 'ftype' => $ftype, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Finance enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

    //START show popup 
    public function appPopup(Request $request)
    {
        try 
        {
            Setting::assignSetting();

            $baseUrl = URL::to("/");
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer) {
                $custname = $customer->name;
                $custcrn = ($customer->crn == NULL ? "" : $customer->crn);
            } else {
                $custname = "Guest";
                $custcrn = "";
            }

            $sliderArr = array();
            $sliderList = DB::table('app_popups')->where('status', '=', 1)->orderBy('id', 'DESC')->first();
            $short_description = $sliderimage = $title = '';
            if($sliderList) {
                $title = $sliderList->title;
                $short_description = $sliderList->short_description;
                $sliderimage  =  $baseUrl."/public/".$sliderList->image;
            }
            
            $status_code = '1';
            $message = 'Popup list';
            $json = array('status_code' => $status_code,  'message' => $message, 'title' => $title, 'short_description' => $short_description, 'slider_image' => $sliderimage, 'slider_url' => $baseUrl.'/app-popup');
        }
        
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    //START show home slider 
    public function homeSlider(Request $request)
    {
        try 
        {
            Setting::assignSetting();

            $baseUrl = URL::to("/");
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer) {
                $custname = $customer->name;
                $custcrn = ($customer->crn == NULL ? "" : $customer->crn);
            } else {
                $custname = "Guest";
                $custcrn = "";
            }

            $sliderArr = array();
            $sliderList = DB::table('home_slider')->select('id','image')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'DESC')->get();
            foreach ($sliderList as $hslider) {
                $sliderimage  =  $baseUrl."/public/".$hslider->image;
                $sliderArr[] = ['id' => (int)$hslider->id, 'slider_image' => $sliderimage]; //'planning_isprogress' => 
            }
            
            $status_code = '1';
            $message = 'All Slider list';
            $json = array('status_code' => $status_code,  'message' => $message, 'name' => $custname, 'crn' => $custcrn, 'marque' => HOMEPAGE_MARQUEE, 'sliderList' => $sliderArr);
        }
        
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

     //START show feed list 
    public function what_need_list(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            
            $needList[] = array('name' => "Tractor (ट्रैक्टर)");
            $needList[] = array('name' => "Implement (उपकरण)");
            
            $status_code = '1';
            $message = 'What need list';
            $json = array('status_code' => $status_code,  'message' => $message, 'needList' => $needList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    //START show feed list 
    public function payment_type(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;


            $paymentList[] = array('name' => 'All');
            $paymentList1 = DB::table('payment_type')->select('title as name')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();

            if($paymentList1)
            {
                foreach($paymentList1 as $row)
                {
                   $paymentList[] = array('name' => $row->name); 
                }
            }

            
            $status_code = '1';
            $message = 'Payment Type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'paymentList' => $paymentList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    //START show cities 
    public function allCities(Request $request)
    {
        try 
        {   
            $json       =   array();
            

            $cityList[] = array('id' => 0, 'name' => 'All');

            $cityList1 = DB::table('cities')->select('id','name')->where('state_id', '=', 1)->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();

            if($cityList1)
            {
                foreach($cityList1 as $row)
                {
                   $cityList[] = array('id' => $row->id, 'name' => $row->name); 
                }
            }

            $status_code = '1';
            $message = 'All City list';
            $json = array('status_code' => $status_code,  'message' => $message, 'cityList' => $cityList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    //START show agri type 
    public function agriType(Request $request)
    {
        try 
        {   
            $json  =   array();
            
            $cityList[] = array('id' => 0, 'typename' => 'All');

            $cityList1 = DB::table('agri_type')->select('id','typename')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();

            if($cityList1)
            {
                foreach ($cityList1 as $key => $value) {
                    # code...
                    $cityList[] = array('id' => $value->id, 'typename' => $value->typename);
                }
            }

            $status_code = '1';
            $message = 'All Agri Type';
            $json = array('status_code' => $status_code,  'message' => $message, 'agritype' => $cityList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END

     //Rent Enquiry
    public function agriTypeEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $agritype = $request->agritype;
            $city = $request->city;
            $comment = $request->comment;
            $isactive = 1;
            $error = "";
            if($agritype == ""){
                $error = "Please enter agri type";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->telephone;
                    DB::table('agri_type_enquiry')->insert(['customer_id' => $customer_id, 'agri_type' => $agritype, 'city' => $city, 'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Agri Type enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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


    //START show agri type 
    public function pinCode(Request $request)
    {
        try 
        {   
            $json  =   array();
            
            
            $toolList = DB::table('pincodes')->select('id','zip')->where('active', '=', 1)->orderBy('id', 'ASC')->get();

            $status_code = '1';
            $message = 'All Pincode List';
            $json = array('status_code' => $status_code,  'message' => $message, 'pincodes' => $toolList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function settingData(Request $request)
    {
        try 
        {
            $json  =   array();
            Setting::assignSetting();
            
            $status_code = '1';
            $json = array('status_code' => $status_code, 'setting' => array(array('name' => YES_DATA), array('name' => NO_DATA)));
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function agriTool(Request $request)
    {
        try 
        {   
            $json  =   array();
     
            $toolList[] = array('id' => 0, 'title' => 'All');

            $toolList1 = DB::table('agri_tool_type')->select('id','title')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();

            if($toolList1)
            {
                foreach ($toolList1 as $key => $value) {
                    # code...
                    $toolList[] = array('id' => $value->id, 'title' => $value->title);
                }
            }

            $status_code = '1';
            $message = 'All Agri Type';
            $json = array('status_code' => $status_code,  'message' => $message, 'agritool' => $toolList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END

     //Rent Enquiry
    public function agriToolEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $agritool = $request->agritool;
            $city = $request->city;
            $comment = $request->comment;
            $isactive = 1;
            $error = "";
            if($agritool == ""){
                $error = "Please enter agri tool";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->telephone;
                    DB::table('agri_tool_enquiry')->insert(['customer_id' => $customer_id, 'agri_tool' => $agritool, 'city' => $city, 'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Agri Tool enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

     //START show feed list 
    public function tractor_company(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            
            //$insTypList = array('1' => "Tractor",'2' => "Equipment");
            
            $companyList1 = DB::table('company')->select('id','title')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();

            $companyList[] = array('id' => 0, 'title' => 'All');

            if($companyList1)
            {
                foreach ($companyList1 as $key => $value) {
                    # code...
                    $companyList[] = array('id' => $value->id, 'title' => $value->title);
                }
            }

            $status_code = '1';
            $message = 'Company list';
            $json = array('status_code' => $status_code,  'message' => $message, 'companyList' => $companyList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    public function all_hp(Request $request)
    {
        try 
        {   
            $json       =   array();
            $language = $request->language;
            $tractorHpList1 = DB::table('hpower')->select('title as name')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('title', 'ASC')->get();

            $tractorHpList[] = array('name' => 'All');

            if($tractorHpList1)
            {
                foreach ($tractorHpList1 as $key => $value) {
                    # code...
                    $tractorHpList[] = array('name' => $value->name);
                }
            }

            $status_code = '1';
            $message = 'Tractor HP list';
            $json = array('status_code' => $status_code,  'message' => $message, 'hptractor' => $tractorHpList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    //Rent Enquiry
    public function rent_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
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
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->telephone;
                    DB::table('tractor_rent_enquiry')->insert(['customer_id' => $customer_id, 'name' => $name, 'mobile' => $mobile, 'comment' => $comment, 'available_date' => $available_date, 'location' => $location, 'other_city' => $other_city, 'is_edit' => '1', 'what_type' => $what_need, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor Rent";
                        $message1 = "Type: ".$what_need.", Location:".$location.", Available Date:".$available_date.", Comment:".$comment;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Rent enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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



    //Rent IN Result
    public function rent_in_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $what_need = $request->what_need;
            $location = $request->location;
            $other_city = $request->other_city;
            $available_date = date("Y-m-d",strtotime($request->available_date));
            $error = "";
            if($location == ""){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){
                    $rentinList = DB::table('tractor_rent_enquiry')->select('id','customer_id','name','mobile','location', 'other_city', 'what_type','available_date','comment')->where('isactive', '=', 1)->whereNull('deleted_at');

                    if($what_need){
                        $rentinList = $rentinList->where('what_type',$what_need);    
                    }

                    if($location != "All"){
                        $rentinList = $rentinList->where('location','LIKE',$location);    
                    }

                    if($other_city){
                        $rentinList = $rentinList->where('other_city','LIKE',$other_city);    
                    }

                    if($available_date){
                        //$rentinList = $rentinList->wheredate('available_date',' > ',$available_date);   
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
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);    
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

     //START show feed list 
    public function year_manufacturer(Request $request)
    {
        try 
        {   
            $json       =   array();
            $yearList       =   array();
            $year = date('Y');
            $year2 = date('Y')-10;
            for($y = $year; $y>$year2; $y--){
                $yearList[] = array('year' => "".$y."");
            }
            
            $status_code = '1';
            $message = 'year manufacturer list';
            $json = array('status_code' => $status_code,  'message' => $message, 'yearList' => $yearList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    //Tractor Sale Enquiry
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
     
                    $resultData = $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=krishimulya&password=krishimulya&senderid=KMAPAY&channel=TRANS&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=15");

                    //print_r($resultData); exit;

                    DB::table('tbl_mobile_verify')->where('id', '=', $verifyid)->update(['otp' => $otp]);

                    $status_code = '1';
                    $message = 'OTP Send successfully';
                    $json = array('status_code' => $status_code,  'message' => $message,  'mobile' => $mobile, 'otp' => $otp);
                } 
                else 
                {
                    $otp = rand(111111, 999999);
                    $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
     
                    $resultData = $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=krishimulya&password=krishimulya&senderid=KMAPAY&channel=TRANS&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=15");

                    //print_r($resultData); exit;

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



    public function tractorSaleEnquiry(Request $request)
    {
        try 
        {

            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
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
            if($company_name == ""){
                $error = "Please enter company name for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            if($model == ""){
                $error = "Please enter model name of tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }

            if($contact_person_phone != "")
            {
                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
                if($verifyOtp){ 
                    $mobileverifyotp = $verifyOtp->otp;
                    if($contact_person_otp != $mobileverifyotp){
                        $error = "Please enter valid OTP to verify mobile.";
                        $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
                    }else{
                        //$error = "Incorrect OTP.";
                        //$json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);  
                    }
                } else {
                    $error = "Please verify mobile.";
                    $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);  
                }
            }

            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
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

                    $name = $customer->name;
                    $mobile = $customer->telephone;

                    $tractor_sell_enquiry_id = DB::table('tractor_sell_enquiry')->insertGetId(['customer_id' => $customer_id, 'name' => $name, 'mobile' => $mobile, 'company_name' => $company_name, 'other_company' => $other_company, 'comment' => $comment, 'model' => $model, 'year_manufacturer' => $year_manufacturer, 'hourse_power' => $hourse_power, 'hrs' => $hrs, 'exp_price' => $exp_price, 'image' => $tractorimage, 'sale_type' => $sale_type, 'location' => $location, 'other_city' => $other_city, 'isactive' => $isactive, 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'payment_type' => $payment_type, 'created_at' => $date, 'is_edit' => '1', 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor Sale";
                        $message1 = "Name: ".$name.", Phone:".$mobile.", Company:".$company_name.", Comment:".$comment.", Model:".$model.", Manufacturer Year:".$year_manufacturer.", Horse Power:".$hourse_power.", Horse Power:".$hourse_power.", Hours:".$hrs.", Exp. Price:".$exp_price.", Location:".$location;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Tractor sale enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'tractor_sell_enquiry_id' => $tractor_sell_enquiry_id);
                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

    // TO DO
    public function tractorRefinanceEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
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
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->telephone;
                    DB::table('tractor_refinance_enquiry')->insert(['customer_id' => $customer_id, 'name' => $name, 'mobile' => $mobile, 'company_name' => $company_name, 'other_company' => $other_company, 'hourse_power' => $hourse_power, 'payment_type' => $payment_type, 'location' => $location, 'other_city' => $other_city, 'isactive' => $isactive, 'created_at' => $date, 'is_edit' => '1', 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor Refinance";
                        $message1 = "Company: ".$company_name.", Location:".$location.", Horse Power:".$hourse_power.", Payment Type:".$payment_type;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Tractor re-finance enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

    //Tractor Purchase Enquiry
    public function tractorPurchaseEnquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
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
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->telephone;
                    DB::table('tractor_purchase_enquiry')->insert(['customer_id' => $customer_id, 'name' => $name, 'mobile' => $mobile, 'uses_type' => $what_need, 'company_name' => $company_name, 'other_company' => $other_company, 'hourse_power' => $hourse_power, 'payment_type' => $payment_type, 'location' => $location, 'other_city' => $other_city, 'isactive' => $isactive, 'is_edit' => '1', 'created_at' => $date, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor purchase";
                        $message1 = "Type: ".$what_need.", Company:".$company_name.", Location:".$location.", Horse Power:".$hourse_power.", Payment Type:".$payment_type;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Tractor purchase enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

    //Purchase Old Enquiry
    public function purchaseOldResult(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $what_need = $request->what_need;
            $location = $request->location;
            $other_city = $request->other_city;
            $company_name = $request->company_name;
            $other_company = $request->other_company;
            $hourse_power = $request->hourse_power;
            $model = $request->model;
            $year_manufacturer = $request->year_manufacturer;
            $payment_type = $request->payment_type;

            $error = "";
            if($what_need == ""){
                $error = "Please select what need to search";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){
                    $purchaseOldList = DB::table('tractor_sell_enquiry')->select('id','customer_id','name','mobile','company_name','other_company','model','hourse_power','hrs', 'exp_price', 'image','payment_type','sale_type','location', 'other_city', 'is_contact', 'contact_person_name', 'contact_person_phone')->where('isactive', '=', 1)->where('is_edit','1')->whereNull('deleted_at'); //where('customer_id', '=', $customer_id)->

                    if($what_need){
                        $purchaseOldList = $purchaseOldList->where('sale_type',$what_need);    
                    }

                    if($company_name != "All"){
                        $purchaseOldList = $purchaseOldList->where('company_name',$company_name);    
                    }

                    if($other_company){
                        $purchaseOldList = $purchaseOldList->where('other_company',$other_company);    
                    }

                    if($location != "All"){
                        $purchaseOldList = $purchaseOldList->where('location',$location);    
                    }

                    if($model){
                        //$purchaseOldList = $purchaseOldList->where('model',$model);    
                    }

                    if($year_manufacturer){
                        //$purchaseOldList = $purchaseOldList->where('year_manufacturer',$year_manufacturer);    
                    }

                    if($payment_type){
                        $purchaseOldList = $purchaseOldList->where('payment_type',$payment_type);    
                    }
                    else
                    {
                        $purchaseOldList = $purchaseOldList->where('payment_type', 'Cash (नकद भुगतान)');
                        
                    }

                    if($other_city){
                        $purchaseOldList = $purchaseOldList->where('other_city',$other_city);    
                    }

                    if($hourse_power == "" || $hourse_power == "All"){
                    }
                    else
                    {
                        $hparr = explode('-', $hourse_power);
                        $hpfrom = $hparr[0];
                        $hpto = $hparr[1];
                        //$purchaseOldList = $purchaseOldList->whereBetween('hourse_power', [$hpfrom, $hpto]);
                        $purchaseOldList = $purchaseOldList->where('hourse_power','LIKE',$hourse_power);    
                    }

                   
                    $purchaseOldList = $purchaseOldList->orderBy('hrs', 'ASC')->orderBy('hourse_power', 'DESC')->get(); 
                    //dd($purchaseOldList);

                    if(count($purchaseOldList) > 0){
                        $purchaseList = array();
                        foreach($purchaseOldList as $plist)
                        {
                            $is_contact = $plist->is_contact;   

                            if($is_contact == "No")
                            {
                                $customer_name = $plist->contact_person_name;
                                $customer_telphone = $plist->contact_person_phone;
                            }
                            else
                            {
                                $customer_name = $plist->name;
                                $customer_telphone = $plist->mobile;
                            }

                            $baseUrl = URL::to("/");
                            $tractor_image  = "";
                            if($plist->image){
                                $tractor_image  =  $baseUrl."/public/uploads/tractor_image/".$plist->image;
                            
                            }
                            $other_company = ($plist->other_company != '') ? $plist->other_company : "";
                            $othercity = ($plist->other_city != '') ? $plist->other_city : "";
                            $purchaseList[] = ['id' => (string)$plist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'company_name' =>$plist->company_name, 'other_company' =>$other_company, 'what_need' =>$plist->sale_type, 'location' =>$plist->location, 'other_city' =>$othercity, 'model' => $plist->model, 'hourse_power' => $plist->hourse_power, 'hrs' => $plist->hrs, 'exp_price' => $plist->exp_price, 'payment_type' => $plist->payment_type, 'image' => $tractor_image]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Old Purchase enquiry result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'purchase_list' => $purchaseList);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Item for purchase not available right now';
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);    
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

    public function all_purpose(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            

            $purposeType1 = DB::table('purpose_type')->select('title as name')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();

            $purposeType[] = array('name' => 'All');
            if($purposeType1)
            {
                foreach($purposeType1 as $row)
                {
                    $purposeType[] = array('name' => $row->name);   
                }
            }
            
            
            $status_code = '1';
            $message = 'purpose type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'purposeType' => $purposeType);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function all_labour_need(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            
            $needType = DB::table('labour_type')->select('title as name')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();
           
           /* $needType[] = array('name' => "Normal");
            $needType[] = array('name' => "Urgent");*/
            
            
            $status_code = '1';
            $message = 'labour need type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'needType' => $needType);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    //Labour Enquiry
    public function labourEnquiry(Request $request)
    {
        header('Content-Type: text/html; charset=utf-8');
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
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
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }

            if($contact_person_phone != "")
            {
                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
                if($verifyOtp){ 
                    $mobileverifyotp = $verifyOtp->otp;
                    if($contact_person_otp != $mobileverifyotp){
                        $error = "Please enter valid OTP to verify mobile.";
                        $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
                    }else{
                        //$error = "Incorrect OTP.";
                        //$json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);  
                    }
                } else {
                    $error = "Please verify mobile.";
                    $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);  
                }
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('labour_enquiry')->insert(['customer_id' => $customer_id, 'location' => $location, 'other_city' => $other_city, 'purpose' => $purpose, 'need' => $need, 'labour_no' => $labour_no, 'comments' => $comments,  'isactive' => $isactive, 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'is_edit' => '1', 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Labour enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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


     //Rent IN Result
    public function labourResult(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $labour_no = $request->labour_no;
            $need = $request->need;
            $location = $request->location;
            $other_city = $request->other_city;
            $purpose = $request->purpose;
            
            //$available_date = date("Y-m-d",strtotime($request->available_date));
            $error = "";
            if($location == ""){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){
                    $labourList = DB::table('labour_enquiry')->select('id','customer_id','location', 'other_city', 'purpose','labour_no','comments')->whereNull('deleted_at')->where('customer_id', '=', $customer_id)->where('isactive', '=', 1);

                    if($labour_no){
                        $labour_noto = 0;
                        $labourList = $labourList->where('labour_no','>=',$labour_no);    
                        //$labourList = $labourList->whereBetween('labour_no', [$labour_noto, $labour_no]);
                    }

                    if($location != "All"){
                        $labourList = $labourList->where('location','LIKE',$location);    
                    }
                    if($other_city){
                        $labourList = $labourList->where('other_city','LIKE',$other_city);    
                    }

                    if($purpose){
                        $labourList = $labourList->where('purpose','LIKE',$purpose);    
                    }

                    if($need){
                        $labourList = $labourList->where('need','LIKE',$need);    
                    }

                    /*if($available_date){
                        $rentinList = $rentinList->wheredate('available_date',$available_date);    
                    }*/
                    $labourList = $labourList->orderBy('id', 'desc')->get(); 
                    if(count($labourList) >0){
                        $r_list = array();
                        foreach($labourList as $rlist)
                        {                            
                            $rscustomer = DB::table('customers')->where('id', $rlist->customer_id)->first();
                            $customer_name = $customer_telphone = "";
                            if($rscustomer)
                            {
                                $customer_name = $rscustomer->name;
                                $customer_telphone = $rscustomer->telephone;
                            }
                            
                            $othercity = ($rlist->other_city != '') ? $rlist->other_city : "";
                            //$available_date = date("d-m-Y",strtotime($rlist->available_date));
                            $r_list[] = ['id' => (string)$rlist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'location' =>$rlist->location, 'other_city' =>$othercity, 'labour_no' => $rlist->labour_no, 'purpose' => $rlist->purpose, 'comment' => $rlist->comments]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Labour result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'rentin_list' => $r_list);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Labour not available right now';
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);    
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

     //START show feed list 
    public function insurance_type(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            
            //$insTypList = array('1' => "Tractor",'2' => "Equipment");
            
            $insTypList = DB::table('insurance_type')->select('id','title')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();

            $status_code = '1';
            $message = 'Insurance Type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'insTypList' => $insTypList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    //Labour Enquiry
    public function insurance_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $insurance_type = $request->insurance_type;
            $other_insurance_type = $request->other_insurance_type;
            $comments = $request->comments;
            $isactive = 1;
            $error = "";
            if($insurance_type == ""){
                $error = "Please enter insurance type";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer) {
                    $name = $customer->name;
                    $mobile = $customer->telephone;
                    DB::table('insurance_enquiry')->insert(['customer_id' => $customer_id, 'name' => $name, 'mobile' => $mobile, 'insurance_type' => $insurance_type, 'other_insurance_type' => $other_insurance_type, 'comments' => $comments, 'user_type' => 'customer', 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Insurance enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

     //START show feed list 
    public function land_type(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            
            $landTypeList1 = DB::table('land_type')->select('title as name')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();

            $landTypeList[] = array('name' => 'All');

            if($landTypeList1)
            {
                foreach ($landTypeList1 as $key => $value) {
                    # code...
                    $landTypeList[] = array('name' => $value->name);
                }
            }

           /* $landTypeList[] = array('name' => "agriculture");
            $landTypeList[] = array('name' => "non-agriculture");
            */
            $status_code = '1';
            $message = 'Land Type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'landTypeList' => $landTypeList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

     public function all_land_size(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            
            $landsize1 = DB::table('land_size')->select('title as name')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();

            $landsize[] = array('name' => 'All');

            if($landsize1)
            {
                foreach ($landsize1 as $key => $value) {
                    # code...
                    $landsize[] = array('name' => $value->name);
                }
            }
            
            $status_code = '1';
            $message = 'land Size list';
            $json = array('status_code' => $status_code,  'message' => $message, 'landsize' => $landsize);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function all_rent_time(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            $rent_time1 = DB::table('rent_time')->select('title as name')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();

            $rent_time[] = array('name' => 'All');

            if($rent_time1)
            {
                foreach ($rent_time1 as $key => $value) {
                    # code...
                    $rent_time[] = array('name' => $value->name);
                }
            }
            
            $status_code = '1';
            $message = 'Rent Time list';
            $json = array('status_code' => $status_code,  'message' => $message, 'rent_time' => $rent_time);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    //Agri Land Rent Enquiry
    public function agri_land_rent_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $comment = $request->comment;
            $how_much_time = $request->how_much_time;
            $isactive = 1;
            $error = "";
            if($location == ""){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('agriland_rent_enquiry')->insert(['customer_id' => $customer_id, 'location' => $location, 'other_city' => $other_city, 'land_type' => $land_type, 'size_in_acore' => $size_in_acre, 'how_much_time' => $how_much_time, 'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Agriland Rent Enquiry";
                        $message1 = "Location: ".$location.", Land Type:".$land_type.", Size (Acre):".$size_in_acre.", Time:".$how_much_time.", Comments:".$comment;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Agri land rent enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

    //Purchase Old Enquiry
    public function agrilandRentResults(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $rent_time = $request->rent_time;

            $error = "";
            if($location == ""){
                $error = "Please select location to search";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){
                    $rentListquery = DB::table('agriland_rent_enquiry')->select('id','customer_id','land_type','size_in_acore','how_much_time','comment', 'location','other_city')->where('isactive', '=', 1)->where('customer_id', '=', $customer_id)->whereNull('deleted_at');

                    if($land_type){
                        $rentListquery = $rentListquery->where('land_type',$land_type);    
                    }

                    if($location != "All"){
                        $rentListquery = $rentListquery->where('location',$location);    
                    }

                    if($other_city){
                        $rentListquery = $rentListquery->where('other_city',$other_city);    
                    }

                    if($size_in_acre != "All"){
                        $rentListquery = $rentListquery->where('size_in_acore',$size_in_acre);    
                    }

                    if($rent_time){
                        $rentListquery = $rentListquery->where('how_much_time','LIKE',$rent_time);    
                    }

                   
                    $rentListquery = $rentListquery->orderBy('id', 'desc')->get(); 

                    if(count($rentListquery) >0){
                        $r_List = array();
                        foreach($rentListquery as $rlist)
                        {
                            
                            $rscustomer = DB::table('customers')->where('id', $rlist->customer_id)->first();
                            $customer_name = $rscustomer->name;
                            $customer_telphone = $rscustomer->telephone;
                            $pimage = '';
                            $othercity = ($rlist->other_city != '') ? $rlist->other_city : "";
                            $r_List[] = ['id' => (string)$rlist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'land_type' =>$rlist->land_type, 'location' => $rlist->location, 'other_city' => $othercity, 'size_in_acre' => $rlist->size_in_acore, 'rent_time' => $rlist->how_much_time, 'comment' => $rlist->comment]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Agri land Rent enquiry result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'sale_rent_list' => $r_List);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Agri land for rent not available right now';
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);    
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

    //Agri Land Sale Enquiry
    public function agrilandSaleEnquiry(Request $request)
    {
        try 
        {
          // header('Content-Type: text/html; charset=UTF-8');
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
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
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }

            if($contact_person_phone != "")
            {
                $verifyOtp = DB::table('tbl_mobile_verify')->where('mobile', $contact_person_phone)->first();
                if($verifyOtp){ 
                    $mobileverifyotp = $verifyOtp->otp;
                    if($contact_person_otp != $mobileverifyotp){
                        $error = "Please enter valid OTP to verify mobile.";
                        $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
                    }else{
                        //$error = "Incorrect OTP.";
                        //$json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);  
                    }
                } else {
                    $error = "Please verify mobile.";
                    $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);  
                }
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('agriland_sale_enquiry')->insert(['customer_id' => $customer_id, 'location' => $location, 'other_city' => $other_city, 'land_type' => $land_type, 'size_in_acre' => $size_in_acre, 'exp_price' => $exp_price, 'comment' => $comment, 'isactive' => $isactive, 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'is_edit' => '1', 'created_at' => $date, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Agriland Sale Enquiry";
                        $message1 = "Location: ".$location.", Land Type:".$land_type.", Size (Acre):".$size_in_acre.", Exp. Price: ".$exp_price.", Comments:".$comment;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = $success = '1';
                    $message = 'Agri land sale enquiry added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

    //Agri land Purchase Old Enquiry
    public function agrilandPurchaseResult(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $error = "";
            if($location == ""){
                $error = "Please select location to search";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $purchaseOldList = DB::table('agriland_sale_enquiry')->select('id','customer_id','land_type','size_in_acre','comment', 'location','other_city', 'is_contact', 'contact_person_name', 'contact_person_phone')->where('customer_id', '=', $customer_id)->where('isactive', '=', 1)->whereNull('deleted_at');

                    if($land_type != "All"){
                        $purchaseOldList = $purchaseOldList->where('land_type',$land_type);    
                    }

                    if($location != "All"){
                        $purchaseOldList = $purchaseOldList->where('location',$location);    
                    }

                    if($other_city){
                        $purchaseOldList = $purchaseOldList->where('other_city',$other_city);    
                    }

                    if($size_in_acre != "All"){
                        $purchaseOldList = $purchaseOldList->where('size_in_acre','LIKE',$size_in_acre);    
                    }

                   
                    $purchaseOldList = $purchaseOldList->orderBy('id', 'desc')->get(); 

                    if(count($purchaseOldList) >0){
                        $purchaseList = array();
                        foreach($purchaseOldList as $plist)
                        {
                            $is_contact = $plist->is_contact;   

                            if($is_contact == "No")
                            {
                                $customer_name = $plist->contact_person_name;
                                $customer_telphone = $plist->contact_person_phone;
                            }
                            else
                            {
                                $rscustomer = DB::table('customers')->where('id', $plist->customer_id)->first();

                                if($rscustomer)
                                {
                                    $customer_name = $rscustomer->name;
                                    $customer_telphone = $rscustomer->telephone;
                                }
                                else
                                {
                                    $customer_name = "Guest";
                                    $customer_telphone = "";
                                }
                            }

                            $pimage = '';
                            $othercity = ($plist->other_city != '') ? $plist->other_city : "";

                            $purchaseList[] = ['id' => (string)$plist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'land_type' =>$plist->land_type, 'size_in_acre' => $plist->size_in_acre, 'location' => $plist->location, 'other_city' => $othercity, 'comment' => $plist->comment]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Agri land Purchase enquiry result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'purchase_list' => $purchaseList);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Agri land for purchase not available right now';
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);    
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

    //START show feed list 
    public function feedList(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            $customer_id = $request->customer_id;
            $pincode = '';
            if($customer_id){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $pincode = $customer->pincode;
                }
            }       
                    
            if($pincode ==""){
                $pincode = '302022';
            }
            //$user_id    =   $request->user_id;
            //$role_id    =   $request->role_id;
            //$is_emp     =   (int)$request->is_emp;
        
			// 2 = process one, 3 = process two, 4 = process three, 5= process four, 6 = process complete, 9 = planning
			//echo $role_id; exit;
			
            $feedList = array();
            $rsfeeds = DB::table('feeds')->where('language', $language)->where('status', '=', 'PUBLISHED')->whereNull('deleted_at')->orderBy('id', 'DESC')->get();
			
            if(count($rsfeeds) >0)
            {
                foreach($rsfeeds as $showFeed)
                {
					$feedimage  =  $baseUrl."/public/".$showFeed->image;
                    $feedcat = DB::table('feed_categories')->where('id', $showFeed->category_id)->whereNull('deleted_at')->first();
                    $feed_catname = $feedcat->name;
					$feedList[] = ['id' => (int)$showFeed->id, 'heading' =>$feed_catname, 'title' =>$showFeed->title, 'content' => strip_tags($showFeed->content), 'date' => date("d-m-Y",strtotime($showFeed->date)), 'feedimage' => $feedimage]; //'planning_isprogress' => $planning_isprogress, 
                }
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
                $status_code = '1';
                $message = 'Show feed list';
                $apptext = 'Krishimulya | कृषिमूल्य';
                $json = array('status_code' => $status_code,  'message' => $message, 'apptext' => $apptext, 'processList' => $feedList, 'pincode' => $pincode, 'wheatherType' => $wheatherType, 'wheathericon' => $iconurl, 'todaytemp' => "".$todaytemp."°C" , 'todayhumidity' => "".$todayhumidity, 'locationName' => "".$locationName);
            }
            else
            {
                $status_code = '0';
                $message = 'Sorry! no feed exists .';
                $json = array('status_code' => $status_code,  'message' => $message);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    //Agri Land Feedback
    public function agriland_feedback(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $comment = $request->comment;
            $isactive = 1;
            $error = "";
            if($comment == ""){
                $error = "Please enter comment for feedback";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('feedback')->insert(['customer_id' => $customer_id, 'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Feedback added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

     public function enquiry_type(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            
            $enquiryTypeList[] = array('name' => "Rent");
            $enquiryTypeList[] = array('name' => "Sale");
            $enquiryTypeList[] = array('name' => "Purchase");
            $enquiryTypeList[] = array('name' => "Agriland Rent");
            $enquiryTypeList[] = array('name' => "Agriland Purchase");
            $enquiryTypeList[] = array('name' => "Labour");
            $enquiryTypeList[] = array('name' => "Insurance");
            
            $status_code = '1';
            $message = 'Enquiry Type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'enquiryTypeList' => $enquiryTypeList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
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
            $customer_id = $request->customer_id;
            $enquiry_type = $request->enquiry_type;
            $error = "";
            if($enquiry_type == ""){
                $error = "Please enter enquiry type";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('enquiry_tracking')->insert(['customer_id' => $customer_id, 'enquiry_type' => $enquiry_type, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Enquiry Type added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

     public function soiltest_type(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            
            $soiltestTypList = DB::table('soil_test_type')->select('id','title','price')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();


            $status_code = '1';
            $message = 'Enquiry Type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'soiltestTypList' => $soiltestTypList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    } 

     public function get_sevakendra(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            
            $sevaKendraList = DB::table('seva_kendra')->select('id','name','contact_no','location','city','email' ,'latitude','langitude')->where('isactive', '=', 1)->whereNull('deleted_at')->orderBy('id', 'ASC')->get();


            $status_code = '1';
            $message = 'Seva Kendra list';
            $json = array('status_code' => $status_code,  'message' => $message, 'sevakendraList' => $sevaKendraList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
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
            $customer_id = $request->customer_id;
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
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
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
                    $mobile = $customer->telephone;
                   $orderid = DB::table('soil_test_orders')->insertGetId(['customer_id' => $customer_id,'order_no' => $order_no, 'name' => $name, 'mobile' => $mobile, 'land_size' => $land_size, 'location' => $location, 'khasra_no' => $khasra_no, 'test_type' => $test_type, 'amount' => $amount, 'order_status' => $order_status, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);
                   //DB::table('soil_test_orders')->where('id', '=', $orderid)->update(['order_no' => $order_no]);
                   
                   /* FCM Notification */
                   $customerToken = $customer->fcmToken; 
                   //$customerToken = 'e2k1jCT_Ty2qOLk4gSX_Hz:APA91bHXhqvz5KlPW6EW9vDNeldzJR-yQcIarygjgn8fo2b08ihcEIFiu-NzHI-1A3L7MJMYyI4ehSWzBwimX5T0ExRbooa6-UxGrfckSdD-F49FzJxwWcU4M58qRu8yeRduTk62eBMW';
                   $customerName = $customer->name; 
                   $notification_title = "Soil Test Order";
                   $notification_body = $order_no." Your soil test order has been successfully created! Thanks for order with us.";
                   $notification_type = "soil_order";
                   $notif_data = array($notification_title,$customerName,$notification_body,"","");
                
                   $customerNotify = $this->push_notification($notif_data,$customerToken);
                   $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $customer_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                   /* End */
                    $status_code = $success = '1';
                    $message = 'Soil Test Order Added Successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'order_id' => "".$orderid);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

    public function get_customer_soilOdr(Request $request)
    {
        try 
        {   
             $baseUrl = URL::to("/");
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $soilodrExists = DB::table('soil_test_orders')->where('customer_id', $customer_id)->whereNull('deleted_at')->orderBy('id', 'DESC')->count();

                    if($soilodrExists >0){
                        $soilodrList = DB::table('soil_test_orders')->select('id','order_no','name', 'mobile', 'amount','land_size','location','khasra_no','test_type','report_file','order_status','created_at')->where('customer_id', $customer_id)->orderBy('id', 'DESC')->get();

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
                                $report_file =  $baseUrl."/public/order_report/".$odrlist->report_file;
                            }else{
                                $report_file = '';
                            }                           
                            if($odrlist->khasra_no != ''){
                                $khasra_no = $odrlist->khasra_no;
                            }else{
                                $khasra_no = "";
                            }
                            $odr_List[] = array('id' => "".$odrlist->id, 'order_no' => $odrlist->order_no, 'name' => $odrlist->name, 'mobile' => $odrlist->mobile, 'testypeName' => $testype_name,'amount' => "".$odrlist->amount, 'land_size' => $odrlist->land_size, 'location' => $odrlist->location, 'khasra_no' => $khasra_no, 'report_file' => $report_file, 'date' => date('d-m-Y H:i:s', strtotime($odrlist->created_at)),'order_status' => $odrlist->order_status); 
                           
                        } 

                        //print_r($odr_List);
                        //exit;
                        $status_code = '1';
                        $message = 'Soil Order List';
                        $json = array('status_code' => $status_code,  'message' => $message, 'odr_List' => $odr_List);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);

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
            $customer_id = $request->customer_id;
            $order_id = $request->order_id;
            $test_type = $request->test_type;
            $amount = $request->amount;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $soilodrList = DB::table('soil_test_orders')->select('id','order_no')->where('customer_id', $customer_id)->where('id', $order_id)->whereNull('deleted_at')->orderBy('id', 'DESC')->first();

                    if($soilodrList){
                        $date   = date('Y-m-d H:i:s');
                        DB::table('soil_test_orders')->where('id', '=', $order_id)->update(['test_type' => $test_type, 'amount' => $amount, 'updated_at' => $date]);
                        
                        /* FCM Notification */
                       $customerToken = $customer->fcmToken; 
                       $customerName = $customer->name; 
                       $notification_title = "Soil Test Order";
                       $notification_body = $soilodrList->order_no." Your soil test order has been successfully Updated! Thanks for order with us.";
                       $notification_type = "soil_order";
                       $notif_data = array($notification_title,$customerName,$notification_body,"","");
                    
                        $customerNotify = $this->push_notification($notif_data,$customerToken);
                       $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $customer_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                       /* End */    
                        $status_code = '1';
                        $message = 'Soil Order Test Type Updated';
                        $json = array('status_code' => $status_code,  'message' => $message, 'test_type' => $test_type, 'amount' => $amount , 'order_id' => $order_id, 'customer_id' => $customer_id);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);

                }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function create_soil_report(Request $request)
    {

         $soilodr = DB::table('soil_test_orders')->join('customers', 'customers.id', '=', 'soil_test_orders.customer_id')->where('soil_test_orders.kt_report_id', "!=", '')->where('soil_test_orders.order_status', 'pending')->select('soil_test_orders.*', 'customers.fcmToken')->whereNull('deleted_at')->get();
         
        if(count($soilodr) > 0){
            foreach ($soilodr as $order) {
                $kt_report_id = $order->kt_report_id; 
                $order_no = $order->order_no; 
                $order_id = $order->id; 
                $customer_id = $order->customer_id; 
                if($kt_report_id != ''){
                  

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                      CURLOPT_URL => 'https://soil-api-staging.krishitantra.com/graphql',
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'POST',
                      CURLOPT_POSTFIELDS =>'{"query":"query Query {\\r\\n  getTest(id:\\"'.$kt_report_id.'\\"){id,html}\\r\\n}\\r\\n","variables":{}}',
                      CURLOPT_HTTPHEADER => array(
                        'authorization: bearer:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvcmdhbml6YXRpb24iOiI2MDM4YmE5MTMwYThjZDAwMTIwMDExMGQiLCJ1c2VyIjoiNjAzOGJiNDgzMGE4Y2QwMDEyMDAxMTBlIiwiaWF0IjoxNjE1MjExODk2fQ.NWkhgpGIZY6Ty60CShFoJhGp5pbHgwjIJnziAJ06TBk',
                        'Content-Type: application/json'
                      ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);
                    
                    $res =  json_decode($response);
                   /* echo '<pre>';
                    print_r($res);*/
                    $replace = '<img src="http://krishimulya.com/uploads/logo/512-png-short.png" width="50" height="50">';
                    $reporthtml =  str_replace('krishimulya', $replace, $res->data->getTest->html);
                   if($reporthtml != ''){ 
                       $pdf = App::make('dompdf.wrapper');
                       $pdf->loadHTML($reporthtml);
                       $pdf->setPaper('a4', '')->setWarnings(false);
                       $filename = $order_no.'_report.pdf';
                       $pdf->save(public_path().'/order_report/'.$filename);

                       /*Update Soil order */ 
                        $date   = date('Y-m-d H:i:s');
                        $order_status = 'done';
                        DB::table('soil_test_orders')->where('id', '=', $order_id)->update(['report_file' => $filename, 'order_status' => $order_status, 'report_date' => $date]);
                       /* End */
                      /* FCM Notification */
                       $customerToken = $order->fcmToken; 
                       
                       //$customerToken = 'e2k1jCT_Ty2qOLk4gSX_Hz:APA91bHXhqvz5KlPW6EW9vDNeldzJR-yQcIarygjgn8fo2b08ihcEIFiu-NzHI-1A3L7MJMYyI4ehSWzBwimX5T0ExRbooa6-UxGrfckSdD-F49FzJxwWcU4M58qRu8yeRduTk62eBMW';
                       $customerName = $order->name; 
                       $notification_title = "Soil Test Report";
                       $notification_body = $order_no." Your soil test report has been successfully created! Thanks for soil test with us.";
                       $notification_type = "soil_order_report";
                       $notif_data = array($notification_title,$customerName,$notification_body,"","");
                    
                       $customerNotify = $this->push_notification($notif_data,$customerToken);
                       $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $customer_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                       /* End */
                    }
                }
            }
            $status_code = $success = '1';
            $message = 'Successfully sent soil report to customers';
            $json = array('status_code' => $status_code, 'message' => $message); 
            
        }else{
            $status_code = $success = '0';
            $message = 'Soil report not found for any customers';
            $json = array('status_code' => $status_code, 'message' => $message);
        }
        return response()->json($json, 200);
    }  



    public function orderReportCreated(Request $request)
    {
        try 
        {   
            
            $json      =   array();
            $customer_id = $request->customer_id;
            $order_id = $request->order_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $soilodrList = DB::table('soil_test_orders')->select('id','order_no')->where('customer_id', $customer_id)->where('id', $order_id)->whereNull('deleted_at')->orderBy('id', 'DESC')->first();

                    if($soilodrList){
                        $date   = date('Y-m-d H:i:s');
                        $order_status = 'done';
                         DB::table('soil_test_orders')->where('id', '=', $order_id)->update(['order_status' => $order_status, 'updated_at' => $date]);
                        
                        /* FCM Notification */
                       $customerToken = $customer->fcmToken; 
                       $customerName = $customer->name; 
                       $notification_title = "Soil Test Report Created";
                       $notification_body = $soilodrList->order_no." Your soil test order report has been successfully generated! Thanks for order with us.";
                       $notification_type = "soil_order";
                       $notif_data = array($notification_title,$customerName,$notification_body,"","");
                    
                        $customerNotify = $this->push_notification($notif_data,$customerToken);
                       $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $customer_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                       /* End */    
                        $status_code = '1';
                        $message = 'Soil Order Test Report Created';
                        $json = array('status_code' => $status_code,  'message' => $message, 'order_status' => $order_status, 'order_id' => $order_id, 'customer_id' => $customer_id);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);

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
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $soilnotificationExists = DB::table('notifications')->where('customer_id', $customer_id)->where('user_type', 'customer')->whereNull('deleted_at')->orderBy('id', 'DESC')->count();
                    $notify_List = array();
                    if($soilnotificationExists >0){
                        $soilNotifyList = DB::table('notifications')->select('id','notification_title','notification_content','notification_type','created_at')->where('customer_id', $customer_id)->whereNull('deleted_at')->orderBy('id', 'DESC')->get();

                        
                        foreach($soilNotifyList as $notifylist)
                        {
                            $notification_type = "";
                            if($notifylist->notification_type == 'soil_order'){

                                $notification_type = 'Soil Order';
                            } else if($notifylist->notification_type == 'agriland'){

                                $notification_type = 'Agri Land';
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
                        $json = array('status_code' => $status_code,  'message' => $message, 'customer_id' => $customer_id);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);

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


        public function todayWheateher(Request $request)
        {
            try 
            {   
                
                $json = array();
                $customer_id = $request->customer_id;
                $pincode = $request->pincode;
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                    if($customer){

                        $appurl = 'api.openweathermap.org/data/2.5/weather?zip='.$pincode.',IN&units=metric&appid=acfd0186948c7adf0c9c87a2ebcc004b';
                        $wheatherRespone = $this->httpGet($appurl);
                        
                        $wheather = json_decode($wheatherRespone);
                        
                        //print_r($wheather->weather[0]);
                        $mainval =  $wheather->weather[0]->main;
                        $wheatherType =  $wheather->weather[0]->description;
                        $todaytemp =  $wheather->main->temp;
                        $todayhumidity =  $wheather->main->humidity;
                        $locationName =  $wheather->name;
                        $status_code = $success = '1';
                        $message = 'Today Wheather';
                        $json = array('status_code' => $status_code, 'message' => $message, 'wheatherType' => $wheatherType, 'todaytemp' => "".$todaytemp."°C" , 'todayhumidity' => "".$todayhumidity,'locationName' => "".$locationName);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Customer not valid';
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);

                    }
            }
            catch(\Exception $e) {
                $status_code = '0';
                $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
        
                $json = array('status_code' => $status_code, 'message' => $message);
            }
        
            return response()->json($json, 200);
    }


    public function tractorPurchaseHistory(Request $request)
    {
        try 
        {
            $json = $tractorPurchaseData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_purchase_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists)
                {
                    $tractorSellEnquiry = DB::table('tractor_purchase_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->orderBy('id', 'DESC')->get();
                    foreach($tractorSellEnquiry as $row)
                    {
                        $tractorPurchaseData[] = array('id' => "".$row->id, 'name' => $row->name, 'mobile' => $row->mobile, 'company_name' => $row->company_name, 'other_company' => ($row->other_company == NULL ? "" : $row->other_company), 'location' => $row->location, 'other_city' => ($row->other_city ==  NULL ? "" : $row->other_city), 'hourse_power' => $row->hourse_power, 'payment_type' => $row->payment_type, 'comment' => ($row->comment == NULL ? "" : $row->comment), 'uses_type' => $row->uses_type, 'user_type' => $row->user_type, 'is_edit' => "".$row->is_edit);
                    }

                    $status_code = '1';
                    $message = 'Tractor Purchase history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'tractorPurchaseData' => $tractorPurchaseData);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Purchase history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function tractorRentHistory(Request $request)
    {
        try 
        {
            $json = $tractorRentData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_rent_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists)
                {
                    $tractorSellEnquiry = DB::table('tractor_rent_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->orderBy('id', 'DESC')->get();
                    foreach($tractorSellEnquiry as $row)
                    {
                        $tractorRentData[] = array('id' => "".$row->id, 'name' => $row->name, 'mobile' => $row->mobile, 'available_date' => $row->available_date, 'comment' => $row->comment, 'model' => ($row->model == NULL ? "" : $row->model), 'location' => $row->location, 'other_city' => ($row->other_city == NULL ? "" : $row->other_city), 'what_type' => $row->what_type, 'user_type' => $row->user_type, 'is_edit' => "".$row->is_edit);
                    }

                    $status_code = '1';
                    $message = 'Tractor Rent history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'tractorRentData' => $tractorRentData);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Rent history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function tractorRefinanceHistory(Request $request)
    {
        try 
        {
            $json = $tractorRefinanceData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_refinance_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists)
                {
                    $tractorSellEnquiry = DB::table('tractor_refinance_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->orderBy('id', 'DESC')->get();
                    foreach($tractorSellEnquiry as $row)
                    {
                        $tractorRefinanceData[] = array('id' => "".$row->id, 'name' => $row->name, 'mobile' => $row->mobile, 'company_name' => $row->company_name, 'other_company' => ($row->other_company == NULL ? "" : $row->other_company), 'location' => $row->location, 'other_city' => ($row->other_city == NULL ? "" : $row->other_city), 'hourse_power' => $row->hourse_power, 'payment_type' => $row->payment_type, 'comment' => ($row->comment == NULL ? "" : $row->comment), 'user_type' => $row->user_type, 'is_edit' => "".$row->is_edit);
                    }

                    $status_code = '1';
                    $message = 'Tractor Refinance history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'tractorRefinanceData' => $tractorRefinanceData);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Refinance history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function tractorSaleHistory(Request $request)
    {
        try 
        {
            $json = $tractorSaleData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_sell_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $tractorSellEnquiry = DB::table('tractor_sell_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->orderBy('id', 'DESC')->get();
                    foreach($tractorSellEnquiry as $row)
                    {
                        $tractorSaleData[] = array('id' => "".$row->id, 'name' => $row->name, 'mobile' => $row->mobile, 'company_name' => $row->company_name, 'other_company' => ($row->other_company == NULL ? "" : $row->other_company), 'comment' => $row->comment, 'model' => $row->model, 'year_manufacturer' => $row->year_manufacturer, 'hourse_power' => $row->hourse_power, 'hrs' => $row->hrs, 'exp_price' => $row->exp_price, 'image' => asset('/uploads/tractor_image/')."/".$row->image, 'sale_type' => $row->sale_type, 'location' => $row->location, 'other_city' => ($row->other_city == NULL ? "" : $row->other_city), 'is_contact' => $row->is_contact, 'is_edit' => "".$row->is_edit, 'contact_person_name' => ($row->contact_person_name == NULL ? "" : $row->contact_person_name), 'contact_person_phone' => ($row->contact_person_phone == NULL ? "" : $row->contact_person_phone), 'contact_person_otp' => ($row->contact_person_otp == NULL ? "" : $row->contact_person_otp), 'payment_type' => $row->payment_type);
                    }

                    $status_code = '1';
                    $message = 'Tractor Sale history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'tractorSaleData' => $tractorSaleData);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Sale history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function labourEnquiryHistory(Request $request)
    {
        try 
        {
            $json = $labourEnquiryData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('labour_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $tractorSellEnquiry = DB::table('labour_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->orderBy('id', 'DESC')->get();
                    foreach($tractorSellEnquiry as $row)
                    {
                        $labourEnquiryData[] = array('id' => "".$row->id, 'location' => $row->location, 'other_city' => ($row->other_city == NULL ? "" : $row->other_city), 'purpose' => $row->purpose, 'need' => ($row->need == NULL ? "" : $row->need), 'labour_no' => $row->labour_no, 'comments' => ($row->comments == NULL ? "" : $row->comments), 'is_contact' => ($row->is_contact == NULL ? "" : $row->is_contact), 'contact_person_name' => ($row->contact_person_name == NULL ? "" : $row->contact_person_name), 'contact_person_phone' => ($row->contact_person_phone == NULL ? "" : $row->contact_person_phone), 'contact_person_otp' => ($row->contact_person_otp == NULL ? "" : $row->contact_person_otp), 'is_edit' => "".$row->is_edit);
                    }

                    $status_code = '1';
                    $message = 'Labor Enquiry history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'labourEnquiryData' => $labourEnquiryData);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Labor Enquiry history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function agriRentEnquiryHistory(Request $request)
    {
        try 
        {
            $json = $labourEnquiryData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('agriland_rent_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $tractorSellEnquiry = DB::table('agriland_rent_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->orderBy('id', 'DESC')->get();
                    foreach($tractorSellEnquiry as $row)
                    {
                        $labourEnquiryData[] = array('id' => "".$row->id, 'location' => $row->location, 'other_city' => ($row->other_city == NULL ? "" : $row->other_city), 'comment' => ($row->comment == NULL ? "" : $row->comment), 'size_in_acore' => $row->size_in_acore, 'how_much_time' => $row->how_much_time, 'land_type' => $row->land_type, 'is_edit' => "".$row->is_edit);
                    }

                    $status_code = '1';
                    $message = 'Rent Enquiry history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'agrilandRentEnquiryData' => $labourEnquiryData);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Rent Enquiry history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function agriSaleEnquiryHistory(Request $request)
    {
        try 
        {
            $json = $labourEnquiryData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('agriland_sale_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists)
                {
                    $tractorSellEnquiry = DB::table('agriland_sale_enquiry')->where('customer_id', '=', $customer_id)->where('isactive', '1')->orderBy('id', 'DESC')->get();
                    foreach($tractorSellEnquiry as $row)
                    {
                        $labourEnquiryData[] = array('id' => "".$row->id, 'location' => $row->location, 'other_city' => ($row->other_city == NULL ? "" : $row->other_city), 'comment' => $row->comment, 'size_in_acre' => $row->size_in_acre, 'exp_price' => $row->exp_price, 'land_type' => $row->land_type, 'is_contact' => $row->is_contact, 'contact_person_name' => ($row->contact_person_name == NULL ? "" : $row->contact_person_name), 'contact_person_phone' => ($row->contact_person_phone == NULL ? "" : $row->contact_person_phone), 'contact_person_otp' => ($row->contact_person_otp == NULL ? "" : $row->contact_person_otp), 'is_edit' => "".$row->is_edit);
                    }

                    $status_code = '1';
                    $message = 'Agriland Sale Enquiry history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'agrisaleEnquiryData' => $labourEnquiryData);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Agriland Sale Enquiry history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    

    // DETAIL PAGE
    public function tractorPurchaseDetail(Request $request)
    {
        try 
        {
            $json = $tractorPurchaseData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id; $tractor_purchase_id = $request->tractor_purchase_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_purchase_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_purchase_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $tractorSellEnquiry = DB::table('tractor_purchase_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_purchase_id)->where('isactive', '1')->first();

                    $status_code = '1';
                    $message = 'Tractor Purchase history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'id' => "".$tractorSellEnquiry->id, 'name' => $tractorSellEnquiry->name, 'mobile' => $tractorSellEnquiry->mobile, 'company_name' => $tractorSellEnquiry->company_name, 'other_company' => ($tractorSellEnquiry->other_company == NULL ? "" : $tractorSellEnquiry->other_company), 'location' => $tractorSellEnquiry->location, 'other_city' => ($tractorSellEnquiry->other_city ==  NULL ? "" : $tractorSellEnquiry->other_city), 'hourse_power' => $tractorSellEnquiry->hourse_power, 'payment_type' => $tractorSellEnquiry->payment_type, 'comment' => ($tractorSellEnquiry->comment == NULL ? "" : $tractorSellEnquiry->comment), 'uses_type' => $tractorSellEnquiry->uses_type, 'user_type' => $tractorSellEnquiry->user_type, 'is_edit' => "".$tractorSellEnquiry->is_edit);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Purchase history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function tractorRentDetail(Request $request)
    {
        try 
        {
            $json = $tractorRentData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id; $tractor_rent_id = $request->tractor_rent_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_rent_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_rent_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists)
                {
                    $tractorSellEnquiry = DB::table('tractor_rent_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_rent_id)->where('isactive', '1')->first();

                    $status_code = '1';
                    $message = 'Tractor Rent history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'id' => "".$tractorSellEnquiry->id, 'name' => $tractorSellEnquiry->name, 'mobile' => $tractorSellEnquiry->mobile, 'available_date' => $tractorSellEnquiry->available_date, 'comment' => $tractorSellEnquiry->comment, 'model' => ($tractorSellEnquiry->model == NULL ? "" : $tractorSellEnquiry->model), 'location' => $tractorSellEnquiry->location, 'other_city' => ($tractorSellEnquiry->other_city == NULL ? "" : $tractorSellEnquiry->other_city), 'what_type' => $tractorSellEnquiry->what_type, 'user_type' => $tractorSellEnquiry->user_type, 'is_edit' => "".$tractorSellEnquiry->is_edit);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Rent history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function tractorRefinanceDetail(Request $request)
    {
        try 
        {
            $json = $tractorRefinanceData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id; $tractor_refinance_id = $request->tractor_refinance_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_refinance_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_refinance_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $tractorSellEnquiry = DB::table('tractor_refinance_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_refinance_id)->where('isactive', '1')->first();

                    $status_code = '1';
                    $message = 'Tractor Refinance history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'id' => "".$tractorSellEnquiry->id, 'name' => $tractorSellEnquiry->name, 'mobile' => $tractorSellEnquiry->mobile, 'company_name' => $tractorSellEnquiry->company_name, 'other_company' => ($tractorSellEnquiry->other_company == NULL ? "" : $tractorSellEnquiry->other_company), 'location' => $tractorSellEnquiry->location, 'other_city' => ($tractorSellEnquiry->other_city == NULL ? "" : $tractorSellEnquiry->other_city), 'hourse_power' => $tractorSellEnquiry->hourse_power, 'payment_type' => $tractorSellEnquiry->payment_type, 'comment' => ($tractorSellEnquiry->comment == NULL ? "" : $tractorSellEnquiry->comment), 'user_type' => $tractorSellEnquiry->user_type, 'is_edit' => "".$tractorSellEnquiry->is_edit);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Refinance history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function tractorSaleDetail(Request $request)
    {
        try 
        {
            $json = $tractorSaleData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id; $tractor_sale_id = $request->tractor_sale_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_sell_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_sale_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $tractorSellEnquiry = DB::table('tractor_sell_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_sale_id)->where('isactive', '1')->first();

                    $status_code = '1';
                    $message = 'Tractor Sale history';

                    $json = array('status_code' => $status_code, 'message' => $message, 'id' => "".$tractorSellEnquiry->id, 'name' => $tractorSellEnquiry->name, 'mobile' => $tractorSellEnquiry->mobile, 'company_name' => $tractorSellEnquiry->company_name, 'other_company' => ($tractorSellEnquiry->other_company == NULL ? "" : $tractorSellEnquiry->other_company), 'comment' => $tractorSellEnquiry->comment, 'model' => $tractorSellEnquiry->model, 'year_manufacturer' => $tractorSellEnquiry->year_manufacturer, 'hourse_power' => $tractorSellEnquiry->hourse_power, 'hrs' => $tractorSellEnquiry->hrs, 'exp_price' => $tractorSellEnquiry->exp_price, 'image' => asset('/uploads/tractor_image/')."/".$tractorSellEnquiry->image, 'sale_type' => $tractorSellEnquiry->sale_type, 'location' => $tractorSellEnquiry->location, 'other_city' => ($tractorSellEnquiry->other_city == NULL ? "" : $tractorSellEnquiry->other_city), 'is_contact' => $tractorSellEnquiry->is_contact, 'is_edit' => "".$tractorSellEnquiry->is_edit, 'contact_person_name' => ($tractorSellEnquiry->contact_person_name == NULL ? "" : $tractorSellEnquiry->contact_person_name), 'contact_person_phone' => ($tractorSellEnquiry->contact_person_phone == NULL ? "" : $tractorSellEnquiry->contact_person_phone), 'contact_person_otp' => ($tractorSellEnquiry->contact_person_otp == NULL ? "" : $tractorSellEnquiry->contact_person_otp), 'payment_type' => $tractorSellEnquiry->payment_type);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Sale history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function labourEnquiryDetail(Request $request)
    {
        try 
        {
            $json = $labourEnquiryData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id; $labour_enquiry_id = $request->labour_enquiry_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('labour_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $labour_enquiry_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $tractorSellEnquiry = DB::table('labour_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $labour_enquiry_id)->where('isactive', '1')->first();

                    $status_code = '1';
                    $message = 'Labor Enquiry history';

                    $json = array('status_code' => $status_code, 'message' => $message, 'id' => "".$tractorSellEnquiry->id, 'location' => $tractorSellEnquiry->location, 'other_city' => ($tractorSellEnquiry->other_city == NULL ? "" : $tractorSellEnquiry->other_city), 'purpose' => $tractorSellEnquiry->purpose, 'need' => ($tractorSellEnquiry->need == NULL ? "" : $tractorSellEnquiry->need), 'labour_no' => $tractorSellEnquiry->labour_no, 'comments' => ($tractorSellEnquiry->comments == NULL ? "" : $tractorSellEnquiry->comments), 'is_contact' => ($tractorSellEnquiry->is_contact == NULL ? "" : $tractorSellEnquiry->is_contact), 'contact_person_name' => ($tractorSellEnquiry->contact_person_name == NULL ? "" : $tractorSellEnquiry->contact_person_name), 'contact_person_phone' => ($tractorSellEnquiry->contact_person_phone == NULL ? "" : $tractorSellEnquiry->contact_person_phone), 'contact_person_otp' => ($tractorSellEnquiry->contact_person_otp == NULL ? "" : $tractorSellEnquiry->contact_person_otp), 'is_edit' => "".$tractorSellEnquiry->is_edit);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Labor Enquiry history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function agriRentEnquiryDetail(Request $request)
    {
        try 
        {
            $json = $labourEnquiryData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id; $agri_rent_enquiry_id = $request->agri_rent_enquiry_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('agriland_rent_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $agri_rent_enquiry_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $tractorSellEnquiry = DB::table('agriland_rent_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $agri_rent_enquiry_id)->where('isactive', '1')->first();


                    $status_code = '1';
                    $message = 'Rent Enquiry history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'id' => "".$tractorSellEnquiry->id, 'location' => $tractorSellEnquiry->location, 'other_city' => ($tractorSellEnquiry->other_city == NULL ? "" : $tractorSellEnquiry->other_city), 'comment' => ($tractorSellEnquiry->comment == NULL ? "" : $tractorSellEnquiry->comment), 'size_in_acore' => $tractorSellEnquiry->size_in_acore, 'how_much_time' => $tractorSellEnquiry->how_much_time, 'land_type' => $tractorSellEnquiry->land_type, 'is_edit' => "".$tractorSellEnquiry->is_edit);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Rent Enquiry history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function agriSaleEnquiryDetail(Request $request)
    {
        try 
        {
            $json = $labourEnquiryData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id; 
            $agri_sale_enquiry_id = $request->agri_sale_enquiry_id;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('agriland_sale_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $agri_sale_enquiry_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists)
                {
                    $tractorSellEnquiry = DB::table('agriland_sale_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $agri_sale_enquiry_id)->where('isactive', '1')->first();

                    $status_code = '1';
                    $message = 'Agriland Sale Enquiry history';
                    $json = array('status_code' => $status_code, 'message' => $message, 'id' => "".$tractorSellEnquiry->id, 'location' => $tractorSellEnquiry->location, 'other_city' => ($tractorSellEnquiry->other_city == NULL ? "" : $tractorSellEnquiry->other_city), 'comment' => $tractorSellEnquiry->comment, 'size_in_acre' => $tractorSellEnquiry->size_in_acre, 'exp_price' => $tractorSellEnquiry->exp_price, 'land_type' => $tractorSellEnquiry->land_type, 'is_contact' => $tractorSellEnquiry->is_contact, 'contact_person_name' => ($tractorSellEnquiry->contact_person_name == NULL ? "" : $tractorSellEnquiry->contact_person_name), 'contact_person_phone' => ($tractorSellEnquiry->contact_person_phone == NULL ? "" : $tractorSellEnquiry->contact_person_phone), 'contact_person_otp' => ($tractorSellEnquiry->contact_person_otp == NULL ? "" : $tractorSellEnquiry->contact_person_otp), 'is_edit' => "".$tractorSellEnquiry->is_edit);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Agriland Sale Enquiry history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    // Save PAGE
    public function tractorPurchaseDetailSave(Request $request)
    {
        try 
        {
            $json = $tractorPurchaseData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $tractor_purchase_id = $request->tractor_purchase_id;

            $what_need = $request->what_need;
            $company_name = $request->company_name;
            $other_company = $request->other_company;
            $location = $request->location;
            $other_city = $request->other_city;
            $hourse_power = $request->hourse_power;
            $payment_type = $request->payment_type;
            $is_edit = $request->is_edit;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_purchase_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_purchase_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $status_code = '1';
                    $message = 'Tractor Purchase updated successfully';

                    $name = $customer->name;
                    $mobile = $customer->telephone;
                    $date = date('Y-m-d H:i:s');

                    DB::table('tractor_purchase_enquiry')->where('id', '=', $tractor_purchase_id)->update(['name' => $name, 'mobile' => $mobile, 'uses_type' => $what_need, 'company_name' => $company_name, 'other_company' => $other_company, 'hourse_power' => $hourse_power, 'payment_type' => $payment_type, 'location' => $location, 'other_city' => $other_city, 'updated_at' => $date, 'is_edit' => $is_edit]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor purchase";
                        $message1 = "Type: ".$what_need.", Company:".$company_name.", Location:".$location.", Horse Power:".$hourse_power.", Payment Type:".$payment_type;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $json = array('status_code' => $status_code, 'message' => $message);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Purchase history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function tractorRentDetailSave(Request $request)
    {
        try 
        {
            $json = $tractorRentData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $tractor_rent_id = $request->tractor_rent_id;
            $what_need = $request->what_need;
            $location = $request->location;
            $other_city = $request->other_city;
            $available_date = date("Y-m-d",strtotime($request->available_date));
            $comment = $request->comment;
            $is_edit = $request->is_edit;


            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_rent_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_rent_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists)
                {
                    $name = $customer->name;
                    $mobile = $customer->telephone;
                    $date = date('Y-m-d H:i:s');

                    DB::table('tractor_rent_enquiry')->where('id', $tractor_rent_id)->update(['name' => $name, 'mobile' => $mobile, 'comment' => $comment, 'available_date' => $available_date, 'location' => $location, 'other_city' => $other_city,  'what_type' => $what_need, 'is_edit' => $is_edit, 'updated_at' => $date]);  

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor Rent";
                        $message1 = "Type: ".$what_need.", Location:".$location.", Available Date:".$available_date.", Comment:".$comment;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = '1';
                    $message = 'Tractor Rent updated successfully';
                    $json = array('status_code' => $status_code, 'message' => $message);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Rent history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function tractorRefinanceDetailSave(Request $request)
    {
        try 
        {
            $json = $tractorRefinanceData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id; 
            $tractor_refinance_id = $request->tractor_refinance_id;
            $company_name = $request->company_name;
            $other_company = $request->other_company;
            $location = $request->location;
            $other_city = $request->other_city;
            $hourse_power = $request->hourse_power;
            $payment_type = $request->payment_type;
            $is_edit = $request->is_edit;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_refinance_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_refinance_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $date   = date('Y-m-d H:i:s');
            
                    $name = $customer->name;
                    $mobile = $customer->telephone;

                    DB::table('tractor_refinance_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_refinance_id)->update(['name' => $name, 'mobile' => $mobile, 'company_name' => $company_name, 'other_company' => $other_company, 'hourse_power' => $hourse_power, 'payment_type' => $payment_type, 'location' => $location, 'other_city' => $other_city, 'is_edit' => $is_edit, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor Refinance";
                        $message1 = "Company: ".$company_name.", Location:".$location.", Horse Power:".$hourse_power.", Payment Type:".$payment_type;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = '1';
                    $message = 'Tractor Refinance updated successfully';
                    $json = array('status_code' => $status_code, 'message' => $message);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Refinance history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function tractorSaleDetailSave(Request $request)
    {
        try 
        {
            $json = $tractorSaleData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $tractor_sale_id = $request->tractor_sale_id;

            $company_name = $request->company_name;
            $other_company = $request->other_company;
            $comment = $request->comment;
            $model = $request->model;
            $year_manufacturer = $request->year_manufacturer;
            $hourse_power = $request->hourse_power;
            $hrs = $request->hrs;
            $exp_price = $request->exp_price;
            $sale_type = $request->sale_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;
            $payment_type = $request->payment_type;

            $is_edit = $request->is_edit;

            $tractor_image = $request->tractor_image;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('tractor_sell_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $tractor_sale_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $status_code = '1';
                    $message = 'Tractor Sale updated successfully.';

                    $date = date('Y-m-d H:i:s');

                    $name = $customer->name;
                    $mobile = $customer->telephone;

                    $tractorimage = "";
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

                    if($tractorimage != "")
                    {
                        DB::table('tractor_sell_enquiry')->where('id', $tractor_sale_id)->update(['name' => $name, 'mobile' => $mobile, 'company_name' => $company_name, 'other_company' => $other_company, 'comment' => $comment, 'model' => $model, 'year_manufacturer' => $year_manufacturer, 'hourse_power' => $hourse_power, 'hrs' => $hrs, 'exp_price' => $exp_price, 'image' => $tractorimage, 'sale_type' => $sale_type, 'location' => $location, 'other_city' => $other_city, 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'payment_type' => $payment_type, 'is_edit' => $is_edit, 'updated_at' => $date]);
                    }
                    else
                    {
                        DB::table('tractor_sell_enquiry')->where('id', $tractor_sale_id)->update(['name' => $name, 'mobile' => $mobile, 'company_name' => $company_name, 'other_company' => $other_company, 'comment' => $comment, 'model' => $model, 'year_manufacturer' => $year_manufacturer, 'hourse_power' => $hourse_power, 'hrs' => $hrs, 'exp_price' => $exp_price, 'sale_type' => $sale_type, 'location' => $location, 'other_city' => $other_city, 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'payment_type' => $payment_type, 'is_edit' => $is_edit, 'updated_at' => $date]);
                    }

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Tractor Sale";
                        $message1 = "Name: ".$name.", Phone:".$mobile.", Company:".$company_name.", Comment:".$comment.", Model:".$model.", Manufacturer Year:".$year_manufacturer.", Horse Power:".$hourse_power.", Horse Power:".$hourse_power.", Hours:".$hrs.", Exp. Price:".$exp_price.", Location:".$location;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $json = array('status_code' => $status_code, 'message' => $message);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Sale history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function labourEnquiryDetailSave(Request $request)
    {
        try 
        {
            $json = $labourEnquiryData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $labour_enquiry_id = $request->labour_enquiry_id;

            $location = $request->location;
            $other_city = $request->other_city;
            $purpose = $request->purpose;
            $need = $request->need;
            $labour_no = $request->labour_no;
            $comments = $request->comments;
            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;
            $is_edit = $request->is_edit;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('labour_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $labour_enquiry_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $date = date('Y-m-d H:i:s');

                    DB::table('labour_enquiry')->where('id', $labour_enquiry_id)->update(['location' => $location, 'other_city' => $other_city, 'purpose' => $purpose, 'need' => $need, 'labour_no' => $labour_no, 'comments' => $comments, 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'is_edit' => $is_edit, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Labor Enquiry";
                        $message1 = "Location: ".$location.", Purpose:".$purpose.", Need:".$need.", Labor No:".$labour_no.", Comments:".$comments;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = '1';
                    $message = 'Labor Enquiry updated successfully';

                    $json = array('status_code' => $status_code, 'message' => $message);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Labor Enquiry history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function agriRentEnquiryDetailSave(Request $request)
    {
        try 
        {
            $json = $labourEnquiryData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $agri_rent_enquiry_id = $request->agri_rent_enquiry_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $comment = $request->comment;
            $how_much_time = $request->how_much_time;
            $is_edit = $request->is_edit;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('agriland_rent_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $agri_rent_enquiry_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists > 0)
                {
                    $date = date('Y-m-d H:i:s');

                    DB::table('agriland_rent_enquiry')->where('id', '=', $agri_rent_enquiry_id)->update(['location' => $location, 'other_city' => $other_city, 'land_type' => $land_type, 'size_in_acore' => $size_in_acre, 'how_much_time' => $how_much_time,   'comment' => $comment, 'updated_at' => $date, 'is_edit' => $is_edit]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Agriland Rent Enquiry";
                        $message1 = "Location: ".$location.", Land Type:".$land_type.", Size (Acre):".$size_in_acre.", Time:".$how_much_time.", Comments:".$comment;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = '1';
                    $message = 'Rent Enquiry updated sucessfully';
                    $json = array('status_code' => $status_code, 'message' => $message);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Rent Enquiry history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function agriSaleEnquiryDetailSave(Request $request)
    {
        try 
        {
            $json = $labourEnquiryData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;

            $agri_sale_enquiry_id = $request->agri_sale_enquiry_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $comment = $request->comment;

            $is_contact = $request->is_contact;
            $contact_person_name = $request->contact_person_name;
            $contact_person_phone = $request->contact_person_phone;
            $contact_person_otp = $request->contact_person_otp;

            $is_edit = $request->is_edit;
            $exp_price = 0;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $tractorSellEnquiryExists = DB::table('agriland_sale_enquiry')->where('customer_id', '=', $customer_id)->where('id', '=', $agri_sale_enquiry_id)->where('isactive', '1')->count();
                if($tractorSellEnquiryExists)
                {
                    $date = date('Y-m-d H:i:s');

                    DB::table('agriland_sale_enquiry')->where('id', '=', $agri_sale_enquiry_id)->update(['location' => $location, 'other_city' => $other_city, 'land_type' => $land_type, 'size_in_acre' => $size_in_acre, 'exp_price' => $exp_price, 'comment' => $comment, 'is_contact' => $is_contact, 'contact_person_name' => $contact_person_name, 'contact_person_phone' => $contact_person_phone, 'contact_person_otp' => $contact_person_otp, 'is_edit' => $is_edit, 'updated_at' => $date]);

                    $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

                    foreach($customers as $cust)
                    {
                        $title = "Agriland Sale Enquiry";
                        $message1 = "Location: ".$location.", Land Type:".$land_type.", Size (Acre):".$size_in_acre.", Exp. Price: ".$exp_price.", Comments:".$comment;
                        $this->sendNotification($cust->id, $title, $message1, '');
                    }

                    $status_code = '1';
                    $message = 'Agriland Sale Enquiry updated successfully';
                    $json = array('status_code' => $status_code, 'message' => $message);
                }
                else
                {
                    $status_code = $success = '0';
                    $message = 'Agriland Sale Enquiry history not exists';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
            else
            {
                $status_code = $success = '0';
                $message = 'Customer not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
}
