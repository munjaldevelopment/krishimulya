<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use DB;

class SendCustomerNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:customer_notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $customerNotify = DB::table('notifications')->where('is_sent', 0)->skip(0)->take(100)->get();
        if($customerNotify)
        {
            foreach($customerNotify as $row)
            {
                $title = $row->notification_title;
                if($title != 'Tractor Refinance'){
                    $message = $row->notification_content;
                    $customer_id = $row->customer_id;
                    $user_type = $row->user_type;
                    $mobile = $row->mobile;
                    $lead_id = $row->lead_id;

                    $optionBuilder = new OptionsBuilder();
                    $optionBuilder->setTimeToLive(60*20);

                    $image = "https://krishimulya.com/uploads/logo/512-png-short.png";
                            
                    $notificationBuilder = new PayloadNotificationBuilder($title);
                    $notificationBuilder->setBody($message)->setIcon("xxxhdpi")->setImage($image)->setSound('default');
                    
                    $dataBuilder = new PayloadDataBuilder();
                    $dataBuilder->addData(['title' => $title, 'lead_id' => $lead_id, 'mobile' => $mobile]);
                    
                    $option = $optionBuilder->build();

                    $notification = $notificationBuilder->build();
                    $data = $dataBuilder->build();

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
                                            
                        $downstreamResponse = FCM::sendToPartner($tokenData, $option, $notification, $data);
                                            
                        $success = $downstreamResponse->numberSuccess();
                        $fail = $downstreamResponse->numberFailure();
                        $total = $downstreamResponse->numberModification();
                    }

                    $date   = date('Y-m-d H:i:s');
                    DB::table('notifications')->where('id', '=', $row->id)->update(['is_sent' => '1', 'updated_at' => $date]);
                }
            }
        }
    }
}
