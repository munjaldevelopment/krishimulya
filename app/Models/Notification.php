<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class Notification extends Model
{
    use CrudTrait;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'notifications';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    
    protected $fillable = ['customer_id', 'notification_title', 'notification_content', 'notification_type', 'user_type', 'isactive'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id');
    }

    public function sendUserNotication() {
        return "<a class='btn btn-success ladda-button tooltipped' data-position='right' data-delay='50' data-tooltip='Send Notification' href='".backpack_url('sendNotification')."'><i class='fa fa-download'></i> Send Notification </a> &nbsp;&nbsp;"; 
    }

    public static function sendNotification($customer_id, $title, $message, $image = '')
    {
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

        $userDeviceRow = DB::table('customers')->where('id','=', $customer_id)->first();

        $tokenData = array($userDeviceRow->fcmToken);
                            
        $downstreamResponse = FCM::sendTo($tokenData, $option, $notification, $data);
                            
        $success = $downstreamResponse->numberSuccess();
        $fail = $downstreamResponse->numberFailure();
        $total = $downstreamResponse->numberModification();

        if($success > 0)
        {
            $date = date('Y-m-d H:i:s');
            $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $customer_id,'notification_title' => $title, 'notification_content' => $message, 'notification_type' => 'test', 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);
        }

        return $success;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
