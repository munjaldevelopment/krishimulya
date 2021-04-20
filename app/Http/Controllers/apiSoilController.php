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
	//START LOGIN
    public function soilGetFarmer(Request $request)
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
          CURLOPT_POSTFIELDS =>'{"query":"query Query($getUsersOrganization: ID!, $getUsersPhone: PhoneNumber) {\\r\\n    getUsers(organization: $getUsersOrganization, phone: $getUsersPhone) {\\r\\n        id\\r\\n        latitude\\r\\n        longitude\\r\\n        name\\r\\n        address\\r\\n        phone\\r\\n        email\\r\\n        username\\r\\n        createdAt\\r\\n        updatedAt\\r\\n    }\\r\\n}\\r\\n","variables":{"getUsersOrganization":"6038ba9130a8cd001200110d","getUsersPhone":"+919999999999"}}',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.SOILTEST_TOKEN,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        echo '<pre>'; print_r($response); exit;
    }

    public function soilCreateFarmer(Request $request)
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
          CURLOPT_POSTFIELDS =>'{"query":"mutation CreateFarmerMutation($createFarmerFarmer: FarmerInput!) {\\r\\n    createFarmer(farmer: $createFarmerFarmer) {\\r\\n        id\\r\\n        latitude\\r\\n        longitude\\r\\n        name\\r\\n        address\\r\\n        phone\\r\\n        username\\r\\n        createdAt\\r\\n        updatedAt\\r\\n    }\\r\\n}","variables":{"createFarmerFarmer":{"name":"True Friend83", "address":"TestAddress", "phone":"+919999999999", "latitude":12.566465, "longitude":34.453666, "username":"truefriend832"}}}',

          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.SOILTEST_TOKEN,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        echo '<pre>'; print_r($response); exit;
    }

    public function soilMyInfo(Request $request)
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
          CURLOPT_POSTFIELDS =>'{"query":"query Query {\\r\\n  me {\\r\\n    organization {\\r\\n      id\\r\\n      slug\\r\\n      name\\r\\n    }\\r\\n    id\\r\\n    username\\r\\n    email\\r\\n    phone\\r\\n  }\\r\\n}","variables":{}}',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.SOILTEST_TOKEN,
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

        DB::table("settings")->where('id', '8')->update(['value' => $token]);
    }
}
