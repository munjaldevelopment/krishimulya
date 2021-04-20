<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use DB;
use Illuminate\Support\Facades\Hash;
use URL;
use File;
use Session;
use App\Models\Setting;

class apiSoilController extends Controller
{
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

    //START LOGIN
    public function soilCreateFarmer(Request $request)
    {
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
          CURLOPT_POSTFIELDS =>'{"query":"mutation CreateFarmerMutation($createFarmerFarmer: FarmerInput!) {\\r\\n    createFarmer(farmer: $createFarmerFarmer) {\\r\\n        id\\r\\n        latitude\\r\\n        longitude\\r\\n        name\\r\\n        address\\r\\n        phone\\r\\n        username\\r\\n        createdAt\\r\\n        updatedAt\\r\\n    }\\r\\n}","variables":{"createFarmerFarmer":{"name":"True Friend83", "address":"TestAddress", "phone":"+919999999999", "latitude":12.566465, "longitude":34.453666, "username":"truefriend832"}}}',

          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvcmdhbml6YXRpb24iOiI2MDM4YmE5MTMwYThjZDAwMTIwMDExMGQiLCJ1c2VyIjoiNjAzOGJiNDgzMGE4Y2QwMDEyMDAxMTBlIiwiaWF0IjoxNjE4OTE0NDA1fQ.qxprKH7lH9k24dDnwhCvMd0aDx4B2Rr-SfCS-eIhqIo',
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        echo '<pre>'; print_r($response); exit;
    }

    public function soilMyInfo(Request $request)
    {
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
          CURLOPT_POSTFIELDS =>'{"query":"query Query {\\r\\n  me {\\r\\n    organization {\\r\\n      id\\r\\n      slug\\r\\n      name\\r\\n    }\\r\\n    id\\r\\n    username\\r\\n    email\\r\\n    phone\\r\\n  }\\r\\n}","variables":{}}',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvcmdhbml6YXRpb24iOiI2MDM4YmE5MTMwYThjZDAwMTIwMDExMGQiLCJ1c2VyIjoiNjAzOGJiNDgzMGE4Y2QwMDEyMDAxMTBlIiwiaWF0IjoxNjE4OTE0NDA1fQ.qxprKH7lH9k24dDnwhCvMd0aDx4B2Rr-SfCS-eIhqIo',
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        echo '<pre>'; print_r($response); exit;
    }

	public function soilLogin(Request $request)
    {
        Setting::AssignSetting();

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
          CURLOPT_POSTFIELDS =>'{"query":"mutation LoginMutation($loginOrganization: String!, $loginUsername: String!, $loginPassword: String!) {\\r\\n        login(organization: $loginOrganization, username: $loginUsername, password: $loginPassword)\\r\\n}","variables":{"loginOrganization":"krishimulya","loginUsername":"krishimulya","loginPassword":"krishimulya"}}',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, 1);
        $token = $result['data']['login'];
        echo $token;
    }
}
