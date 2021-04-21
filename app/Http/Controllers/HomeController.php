<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

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

    public function assignVendor()
    {
        $vendors = \DB::table('vendors')->get();

        foreach($vendors as $vendor)
        {
            $vendorAssign = \DB::table('vendor_vendor_assign')->leftJoin('vendor_services', 'vendor_vendor_assign.vendor_service_id', '=', 'vendor_services.id')->where('vendor_id', $vendor->id)->get();

            if($vendorAssign)
            {
                foreach($vendorAssign as $assignData)
                {
                    $table_name = $assignData->table_name;
                    $table_name_vendor = $assignData->table_name."_vendor";

                    //echo $table_name; exit;

                    $vendorData = \DB::table($table_name)->get();

                    if($vendorData)
                    {
                        foreach($vendorData as $vendorRow)
                        {
                            $id = $vendorRow->id;
                            echo $id; exit;
                        }
                    }
                }
            }
        }
    }
}
