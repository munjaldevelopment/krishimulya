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
            dd($vendorAssign);
        }
    }
}
