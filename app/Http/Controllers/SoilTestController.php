<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use DB;
use App;
use URL;
use File;
use Session;

class SoilTestController extends Controller
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

    public function KTLogin()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://soil.krishitantra.com/api/app/login',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "username": "kmapl",
            "password": "kmapl123"
        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

    public function createFarmer()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://soil.krishitantra.com/api/farmer/register',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "username":"truefriend84",
            "password":"truefriend@1234",
            "confirm-password":"truefriend@1234",
            "email":"truefriend84@gmail.com",
            "address":"D\\nDFDD",
            "contact_no":"9887501240",
            "longitude":28.98059049790038,
            "latitude":41.00519337980453,
            "name":"True Friend 83"
        }
        ',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, 1);

        if(isset($result['success']) && ($result['success'] == 1))
        {
            echo "Farmer has been created";
        }
        else
        {
            $message = "";
            foreach ($result['errors'] as $key => $value) {
                # code...
                $message.=$value['msg'].'<br />';
            }
            echo "Soemthing went wrong <br />".$message;
        }
    }

    public function getFarmer()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://soil.krishitantra.com/api/farmers?contact_no=9887501240',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6ImU0MDU0OWRjLWE2ZTctNDA2ZS1hMzRlLTJlNzkxN2U3ZTM4YiIsInRva2VuX2lkIjoiODAyODYzMmUtMDU2ZS00ZTA1LTlkMDMtY2QwNjYyODVlNGNjIiwidHlwZSI6IlVzZXIiLCJpYXQiOjE2MTYzOTU0NTYsImF1ZCI6ImV4YW1wbGUuY29tIiwiaXNzIjoiZXhhbXBsZS5jb20ifQ.SGXSr7tFfJb4AYNJHofb1pvIzLY1UbVRGsnHX8PIubc'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, 1);

        if(isset($result['data'][0]))
        {
            echo "Farmer Info";
        }
        else
        {
            echo "Soemthing went wrong";
        }
        echo '<pre>';print_r($result);
    }
}
