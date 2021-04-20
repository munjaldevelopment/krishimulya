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
