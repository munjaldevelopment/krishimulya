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

class apiController extends Controller
{
    //START LOGIN
	public function customerLogin(Request $request)
    {
        try 
        {
            $mobile = $request->mobile;
            $device_id = $request->device_id;
            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($device_id == ""){
                $error = "Divice id not found";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($error == ""){
                $json = $userData = array();
                $mobile = $mobile;
                $date   = date('Y-m-d H:i:s');
                $customer = DB::table('customers')->where('telephone', $mobile)->first();
                if($customer) 
                {
                    
                    $customerid = $customer->id;
                    $deviceid = $customer->device_id;
                    $customer_status = $customer->status;


                    
                    if($device_id == $deviceid){
                        if($customer_status == 1){
                            $status_code = '1';
                            $message = 'Customer login successfully';
                            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customerid, 'mobile' => $mobile, "customer_type" => "already");
                        }else{
                            $otp = rand(111111, 999999);
                            $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
         
                            $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=rahul100gm&password=rahul100gm&senderid=IOOGMS&channel=trans&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=35");
                        
                     

                            DB::table('customers')->where('id', '=', $customerid)->update(['otp' => $otp, 'device_id' => $device_id, 'updated_at' => $date]);

                            $status_code = '1';
                            $message = 'Customer Otp Send, Please Process Next Step';
                            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customerid, 'mobile' => $mobile, "customer_type" => "new", 'otp' => "".$otp);
                        }
                        
                    }else{    
                        $otp = rand(111111, 999999);
                        $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
         
                         $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=rahul100gm&password=rahul100gm&senderid=IOOGMS&channel=trans&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=35");
                        
                     

                        DB::table('customers')->where('id', '=', $customerid)->update(['otp' => $otp, 'device_id' => $device_id, 'updated_at' => $date]);

                        $status_code = '1';
                        $message = 'Customer Otp Send, Please Process Next Step';
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customerid, 'mobile' => $mobile,"customer_type" => "new", 'otp' => "".$otp);
                    }
                }else{

                    $otp = rand(111111, 999999);
                    $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
     
                    $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=rahul100gm&password=rahul100gm&senderid=IOOGMS&channel=trans&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=35");

                    $customerid = DB::table('customers')->insertGetId(['telephone' => $mobile, 'otp' => $otp, 'device_id' => $device_id, 'created_at' => $date, 'updated_at' => $date]); 

                    $status_code = $success = '1';
                    $message = 'Customer Otp Send, Please Process Next Step';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customerid, 'mobile' => $mobile, "customer_type" => "new", 'otp' => "".$otp);
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
                $error = "otp not found";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($error == ""){
                $customer = DB::table('customers')->where('telephone', $mobile)->where('otp', $otp)->first();
                if($customer) 
                {
                    DB::table('customers')->where(['id' => $customer->id])->update(['status' => 1]);
                    $customerData= DB::table('customers')->where('id', $customer->id)->first();
                        
                    $status_code = '1';
                    $message = 'Customer activated sucessfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'customer_id' => (int)$customerData->id, 'mobile' => $mobile);
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
                $customer = DB::table('customers')->where('telephone', $mobile)->first();
                if($customer) 
                {
                    $customerid = $customer->id;
                    $otp = rand(111111, 999999);
                    $smsmessage = str_replace(" ", "%20", "Your OTP is ".$otp);
     
                    $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=rahul100gm&password=rahul100gm&senderid=IOOGMS&channel=trans&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=35");

                     DB::table('customers')->where('id', '=', $customerid)->update(['otp' => $otp, 'updated_at' => $date]);

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

    //Customer Update
    public function customerstep3(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $name = $request->name;
            $age = $request->age;

            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                
                DB::table('customers')->where('id', '=', $customer_id)->update(['name' => $name, 'age' => $age, 'updated_at' => $date]);

                $status_code = $success = '1';
                $message = 'Customer info added successfully';
                
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
    public function customer_profile(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
           
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $name = $customer->name;
                $email = $customer->email;
                $age = date("d-m-Y",strtotime($customer->age));
                $telephone = $customer->telephone;
                $address = $customer->address1;
                $city = $customer->city;
                $baseUrl = URL::to("/");
                $customer_image  = "";
                if($customer->image){
                    $customer_image  =  $baseUrl."/public/uploads/customer_image/".$customer->image;
                
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
            $address2 = '';
            $city = $request->city;


            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                $customer_image = '';
                if ($request->hasFile('customer_image')) {
                    $image = $request->file('customer_image'); 
                    if($image)
                    {
                        $customer_image = rand(10000, 99999).'-'.time().'.'.$image->getClientOriginalExtension();
                        $destinationPath = public_path('/uploads/customer_image/');
                        $image->move($destinationPath, $customer_image);
                        
                    }
                }
                DB::table('customers')->where('id', '=', $customer_id)->update(['name' => $name, 'age' => $age, 'email' => $email, 'telephone' => $telephone, 'address1' => $address1, 'address2' => $address2, 'city' => $city, 'image' => $customer_image, 'updated_at' => $date]);

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

    //START show cities 
    public function home_slider(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $sliderArr = array();
            $sliderList = DB::table('home_slider')->select('id','image')->where('isactive', '=', 1)->orderBy('id', 'DESC')->get();
            foreach ($sliderList as $hslider) {
                $sliderimage  =  $baseUrl."/public/".$hslider->image;
                $sliderArr[] = ['id' => (int)$hslider->id, 'slider_image' => $sliderimage]; //'planning_isprogress' => 
            }
            $status_code = '1';
            $message = 'All Slider list';
            $json = array('status_code' => $status_code,  'message' => $message, 'sliderList' => $sliderArr);
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
            
            $needList[] = array('name' => "Tractor");
            $needList[] = array('name' => "Equipment");
            
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
            
            $paymentList[] = array('name' => "Cash");
            $paymentList[] = array('name' => "Finance");
            
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
            
            
            $cityList = DB::table('cities')->select('id','name')->where('state_id', '=', 1)->where('isactive', '=', 1)->orderBy('name', 'DESC')->get();

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


     //START show feed list 
    public function tractor_company(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            
            //$insTypList = array('1' => "Tractor",'2' => "Equipment");
            
            $companyList = DB::table('company')->select('id','title')->where('isactive', '=', 1)->orderBy('id', 'DESC')->get();

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
                    
                    DB::table('tractor_rent_enquiry')->insert(['customer_id' => $customer_id, 'comment' => $comment, 'available_date' => $available_date, 'location' => $location,  'what_type' => $what_need, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

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
            $available_date = date("Y-m-d",strtotime($request->available_date));
            $error = "";
            if($location == ""){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 

                    
                    
                    $rentinList = DB::table('tractor_rent_enquiry')->select('id','customer_id','location','what_type','available_date','comment')->where('isactive', '=', 1);

                    if($what_need){
                        $rentinList = $rentinList->where('what_type',$what_need);    
                    }

                    if($location){
                        $rentinList = $rentinList->where('location','LIKE',$location);    
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
                            
                            $rscustomer = DB::table('customers')->where('id', $rlist->customer_id)->first();
                            $customer_name = $rscustomer->name;
                            $customer_telphone = $rscustomer->telephone;
                            $available_date = date("d-m-Y",strtotime($rlist->available_date));
                            $r_list[] = ['id' => (string)$rlist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'location' =>$rlist->location, 'what_type' => $rlist->what_type, 'available_date' => $available_date, 'comment' => $rlist->comment]; 
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


    //Tractor Sale Enquiry
    public function tractor_sale_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $sale_type = $request->sale_type;
            $location = $request->location;
            $company_name = $request->company_name;
            $model = $request->model;
            $hourse_power = $request->hourse_power;
            $hrs = $request->hrs;
           
            $exp_price = $request->exp_price;
            $comment = $request->comment;
            
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
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $tractor_image = '';
                    if ($request->hasFile('tractor_image')) {
                        $image = $request->file('tractor_image'); 
                        if($image)
                        {
                            $tractor_image = rand(10000, 99999).'-'.time().'.'.$image->getClientOriginalExtension();
                            $destinationPath = public_path('/uploads/tractor_image/');
                            $image->move($destinationPath, $tractor_image);
                            
                        }
                    }

                    DB::table('tractor_sell_enquiry')->insert(['customer_id' => $customer_id, 'company_name' => $company_name, 'comment' => $comment, 'model' => $model, 'hourse_power' => $hourse_power, 'hrs' => $hrs, 'exp_price' => $exp_price, 'image' => $tractor_image, 'sale_type' => $sale_type, 'location' => $location,  'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Tractor sale enquiry added successfully';
                    
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
    public function tractor_purchase_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $what_need = $request->what_need;
            $company_name = $request->company_name;
            $location = $request->location;
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
                    
                    DB::table('tractor_purchase_enquiry')->insert(['customer_id' => $customer_id, 'uses_type' => $what_need, 'company_name' => $company_name, 'hourse_power' => $hourse_power, 'payment_type' => $payment_type, 'location' => $location, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

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
    public function purchase_old_results(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $what_need = $request->what_need;
            $location = $request->location;
            $hourse_power = $request->hourse_power;
            $error = "";
            if($what_need == ""){
                $error = "Please select what need to search";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 

                    
                    
                    $purchaseOldList = DB::table('tractor_sell_enquiry')->select('id','customer_id','company_name','model','hourse_power','hrs', 'exp_price', 'image','sale_type')->where('isactive', '=', 1);

                    if($what_need){
                        $purchaseOldList = $purchaseOldList->where('sale_type',$what_need);    
                    }

                    if($location){
                        $purchaseOldList = $purchaseOldList->where('location',$location);    
                    }

                    if($hourse_power){
                        $purchaseOldList = $purchaseOldList->where('hourse_power','LIKE',$hourse_power);    
                    }

                   
                    $purchaseOldList = $purchaseOldList->orderBy('id', 'desc')->get(); 

                    if(count($purchaseOldList) >0){
                        $purchaseList = array();
                        foreach($purchaseOldList as $plist)
                        {
                            
                            $rscustomer = DB::table('customers')->where('id', $plist->customer_id)->first();
                            $customer_name = $rscustomer->name;
                            $customer_telphone = $rscustomer->telephone;
                            $baseUrl = URL::to("/");
                            $tractor_image  = "";
                            if($plist->image){
                                $tractor_image  =  $baseUrl."/public/uploads/tractor_image/".$plist->image;
                            
                            }
                            $purchaseList[] = ['id' => (string)$plist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'company_name' =>$plist->company_name, 'model' => $plist->model, 'hourse_power' => $plist->hourse_power, 'hrs' => $plist->hrs, 'exp_price' => $plist->exp_price, 'image' => $tractor_image]; 
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

    //Labour Enquiry
    public function labour_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $location = $request->location;
            $purpose = $request->purpose;
            $labour_no = $request->labour_no;
            $comments = $request->comments;
            $isactive = 1;
            $error = "";
            if($labour_no == ""){
                $error = "Please enter no of labour";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('labour_enquiry')->insert(['customer_id' => $customer_id, 'location' => $location, 'purpose' => $purpose, 'labour_no' => $labour_no, 'comments' => $comments,  'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

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
    public function labour_result(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $labour_no = $request->labour_no;
            $location = $request->location;
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

                    
                    
                    $labourList = DB::table('labour_enquiry')->select('id','customer_id','location','purpose','labour_no','comments')->where('isactive', '=', 1);

                    if($labour_no){
                        $labour_noto = 0;
                        //$labourList = $labourList->where('labour_no','<',$labour_no);    
                        $labourList = $labourList->whereBetween('labour_no', [$labour_noto, $labour_no]);
                    }

                    if($location){
                        $labourList = $labourList->where('location','LIKE',$location);    
                    }

                    if($purpose){
                        $labourList = $labourList->where('purpose','LIKE',$purpose);    
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
                            $customer_name = $rscustomer->name;
                            $customer_telphone = $rscustomer->telephone;
                            //$available_date = date("d-m-Y",strtotime($rlist->available_date));
                            $r_list[] = ['id' => (string)$rlist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'location' =>$rlist->location, 'labour_no' => $rlist->labour_no, 'purpose' => $rlist->purpose, 'comment' => $rlist->comments]; 
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
            
            $insTypList = DB::table('insurance_type')->select('id','title')->where('isactive', '=', 1)->orderBy('id', 'DESC')->get();

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
            $comments = $request->comments;
            $isactive = 1;
            $error = "";
            if($insurance_type == ""){
                $error = "Please enter insurance type";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('insurance_enquiry')->insert(['customer_id' => $customer_id, 'insurance_type' => $insurance_type, 'comments' => $comments, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

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
            
            $landTypeList[] = array('name' => "agriculture");
            $landTypeList[] = array('name' => "non-agriculture");
            
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
                    
                    DB::table('agriland_rent_enquiry')->insert(['customer_id' => $customer_id, 'location' => $location, 'land_type' => $land_type, 'size_in_acore' => $size_in_acre, 'how_much_time' => $how_much_time,   'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

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
    public function agriland_rent_results(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_type = $request->land_type;
            $location = $request->location;
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

                    
                    
                    $rentListquery = DB::table('agriland_rent_enquiry')->select('id','customer_id','land_type','size_in_acore','how_much_time','comment', 'location')->where('isactive', '=', 1);

                    if($land_type){
                        $rentListquery = $rentListquery->where('land_type',$land_type);    
                    }

                    if($location){
                        $rentListquery = $rentListquery->where('location',$location);    
                    }

                    if($size_in_acre){
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
                            $r_List[] = ['id' => (string)$rlist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'land_type' =>$rlist->land_type, 'location' => $rlist->location, 'size_in_acre' => $rlist->size_in_acore, 'rent_time' => $rlist->how_much_time, 'comment' => $rlist->comment]; 
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
    public function agri_land_sale_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $size_in_acre = $request->size;
            $comment = $request->comment;
            //$exp_price = $request->exp_price;
            $exp_price = 0;
            $isactive = 1;
            $error = "";
            if($location == ""){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('agriland_sale_enquiry')->insert(['customer_id' => $customer_id, 'location' => $location, 'land_type' => $land_type, 'size_in_acre' => $size_in_acre, 'exp_price' => $exp_price,   'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

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
    public function agriland_purchase_result(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $size_in_acre = $request->size;
            $error = "";
            if($location == ""){
                $error = "Please select location to search";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 

                    
                    
                    $purchaseOldList = DB::table('agriland_sale_enquiry')->select('id','customer_id','land_type','size_in_acre','comment', 'location')->where('isactive', '=', 1);

                    if($land_type){
                        $purchaseOldList = $purchaseOldList->where('land_type',$land_type);    
                    }

                    if($location){
                        $purchaseOldList = $purchaseOldList->where('location',$location);    
                    }

                    if($size_in_acre){
                        $purchaseOldList = $purchaseOldList->where('size_in_acre','LIKE',$size_in_acre);    
                    }

                   
                    $purchaseOldList = $purchaseOldList->orderBy('id', 'desc')->get(); 

                    if(count($purchaseOldList) >0){
                        $purchaseList = array();
                        foreach($purchaseOldList as $plist)
                        {
                            
                            $rscustomer = DB::table('customers')->where('id', $plist->customer_id)->first();
                            $customer_name = $rscustomer->name;
                            $customer_telphone = $rscustomer->telephone;
                            $pimage = '';
                            $purchaseList[] = ['id' => (string)$plist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'land_type' =>$plist->land_type, 'size_in_acre' => $plist->size_in_acre, 'location' => $plist->location, 'comment' => $plist->comment]; 
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
            //$user_id    =   $request->user_id;
            //$role_id    =   $request->role_id;
            //$is_emp     =   (int)$request->is_emp;
        
			// 2 = process one, 3 = process two, 4 = process three, 5= process four, 6 = process complete, 9 = planning
			//echo $role_id; exit;
			
            $feedList = array();
            $rsfeeds = DB::table('feeds')->where('language', $language)->where('status', '=', 'PUBLISHED')->orderBy('id', 'DESC')->get();
			
            if(count($rsfeeds) >0)
            {
                foreach($rsfeeds as $showFeed)
                {
					$feedimage  =  $baseUrl."/public/".$showFeed->image;
                    $feedcat = DB::table('feed_categories')->where('id', $showFeed->category_id)->first();
                    $feed_catname = $feedcat->name;
					$feedList[] = ['id' => (int)$showFeed->id, 'heading' =>$feed_catname, 'title' =>$showFeed->title, 'content' => strip_tags($showFeed->content), 'date' => $showFeed->date, 'feedimage' => $feedimage]; //'planning_isprogress' => $planning_isprogress, 
                }

                $status_code = '1';
                $message = 'Show feed list';
                $apptext = 'Krishimulya | कृषिमूल्य';
                $json = array('status_code' => $status_code,  'message' => $message, 'apptext' => $apptext, 'processList' => $feedList);
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

    
}
