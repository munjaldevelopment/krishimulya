<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use DB;
use App;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use App\Models\Notification;

/**
 * Class NotificationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserNotificationController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitNotificationStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitNotificationUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function sendNotification()
    {
        $this->data['title'] = 'Send Notification';//trans('backpack::base.dashboard'); // set the page title

        return view('backpack::send_notification', $this->data);
    }

    public function sendNotificationMessage(Request $request)
    {
        //$user = Auth::user();
        //$user_id = $user->id;

        $customers = DB::table('customers')->whereIn('id', ['75', '208'])->get();

        foreach($customers as $cust)
        {
            $title = $request->notification_title;
            $message1 = $request->notification_message;
            Notification::sendNotification($cust->id, $title, $message1, '');
        }

        $success = 'Notification sent successfully.';
                
        return back()->with('success', $success);
    }
}
