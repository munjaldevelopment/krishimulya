<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use DB;
use App;

use App\Mail\SendMail;
use App\Models\Customer;

class DailyQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Respectively send soil report to everyone daily';

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
     
    
   $soilodr = DB::table('soil_test_orders')->join('customers', 'customers.id', '=', 'soil_test_orders.customer_id')->where('soil_test_orders.kt_report_id', "!=", '')->where('soil_test_orders.order_status', 'pending')->select('soil_test_orders.*', 'customers.fcmToken')->get();
        if($soilodr){
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
                       $saveNotification = DB::table('tbl_notification')->insertGetId(['customer_id' => $customer_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                       /* End */
                    }
                }
            }
             
            $this->info('Successfully sent soil report to customers.');
        }
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

