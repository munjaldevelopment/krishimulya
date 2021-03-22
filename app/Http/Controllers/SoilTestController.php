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
          CURLOPT_URL => 'https://soil-api-staging.krishitantra.com/api/app/login',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "username": "kmapl",
            "password": "123"
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
          CURLOPT_URL => 'https://soil-api-staging.krishitantra.com/api/farmer/register',
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
        CURLOPT_URL => 'https://soil-api-staging.krishitantra.com/api/farmers?contact_no=9887501240',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
        'Authorization: bearer:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvcmdhbml6YXRpb24iOiI2MDM4YmE5MTMwYThjZDAwMTIwMDExMGQiLCJ1c2VyIjoiNjAzOGJiNDgzMGE4Y2QwMDEyMDAxMTBlIiwiaWF0IjoxNjE1MjExODk2fQ.NWkhgpGIZY6Ty60CShFoJhGp5pbHgwjIJnziAJ06TBk'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, 1);

        if(isset($result['success']))
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
