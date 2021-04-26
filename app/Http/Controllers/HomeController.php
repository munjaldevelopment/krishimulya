<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Console\Command;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use DB;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    public function sendNotification()
    {
        $customerNotify = DB::table('notifications')->where('is_sent', 0)->skip(0)->take(100)->get();
        if($customerNotify)
        {
            foreach($customerNotify as $row)
            {
                $title = $row->notification_title;
                $message = $row->notification_content;
                $customer_id = $row->customer_id;
                $user_type = $row->user_type;

                $optionBuilder = new OptionsBuilder();
                $optionBuilder->setTimeToLive(60*20);

                $image = "http://krishi.microcrm.in/uploads/logo/512-png-short.png";
                        
                $notificationBuilder = new PayloadNotificationBuilder($title);
                $notificationBuilder->setBody($message)->setIcon("xxxhdpi")->setImage($image)->setSound('default');
                
                $dataBuilder = new PayloadDataBuilder();
                $dataBuilder->addData(['title' => $title, 'content' => $message]);
                
                $option = $optionBuilder->build();


                $notification = $notificationBuilder->build();
                $data = $dataBuilder->build();
                dd($notification);

                if($user_type == "customer")
                {
                    $userDeviceRow = DB::table('customers')->where('id','=', $customer_id)->first();

                    $tokenData = array($userDeviceRow->fcmToken);
                                        
                    $downstreamResponse = FCM::sendTo($tokenData, $option, $notification, $data);
                                        
                    $success = $downstreamResponse->numberSuccess();
                    $fail = $downstreamResponse->numberFailure();
                    $total = $downstreamResponse->numberModification();
                }
                else if($user_type == "partner")
                {
                    $userDeviceRow = DB::table('vendors')->where('id','=', $customer_id)->first();

                    $tokenData = array($userDeviceRow->fcmToken);
                                        
                    $downstreamResponse = FCM::sendTo($tokenData, $option, $notification, $data);
                                        
                    $success = $downstreamResponse->numberSuccess();
                    $fail = $downstreamResponse->numberFailure();
                    $total = $downstreamResponse->numberModification();

                    echo $success.",".$fail.",".$total.'<br />';
                    dd($downstreamResponse);
                }

                $date   = date('Y-m-d H:i:s');
                DB::table('notifications')->where('id', '=', $row->id)->update(['is_sent' => '1', 'updated_at' => $date]);
            }
        }
    }

    public function assignVendor()
    {
        $vendors = \DB::table('vendors')->get();

        foreach($vendors as $vendor)
        {
            $vendor_id = $vendor->id;
            $vendorAssign = \DB::table('vendor_vendor_assign')->leftJoin('vendor_services', 'vendor_vendor_assign.vendor_service_id', '=', 'vendor_services.id')->where('vendor_id', $vendor_id)->get();

            if($vendorAssign)
            {
                foreach($vendorAssign as $assignData)
                {
                    $table_name = $assignData->table_name;
                    $table_name_vendor = $assignData->table_name."_vendor";
                    $table_name_vendor_history = $assignData->table_name."_vendor_history";

                    //echo $table_name; exit;

                    $vendorData = \DB::table($table_name)->get();

                    if($vendorData)
                    {
                        foreach($vendorData as $vendorRow)
                        {
                            $id = $vendorRow->id;

                            $isExists = \DB::table($table_name_vendor)->where($table_name."_id", $id)->where('vendor_id', $vendor_id)->count();

                            if($isExists == 0)
                            {
                                $table_id = \DB::table($table_name_vendor)->insertGetId([$table_name."_id" => $id, 'vendor_id' => $vendor_id, 'test_status' => 'Pending', 'status_time' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);


                                \DB::table($table_name_vendor_history)->insert([$table_name."_id" => $id, $table_name."_vendor_id" => $table_id, 'vendor_id' => $vendor_id, 'test_status' => 'Pending', 'status_time' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
                            }
                        }
                    }
                }
            }
        }
    }
}
