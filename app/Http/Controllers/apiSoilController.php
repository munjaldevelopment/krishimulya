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
    public function soilGetTest(Request $request)
    {
        Setting::AssignSetting();

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => SOILTEST_URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"query":"query Query($getExternalTestsByFarmerFarmer: ID!) {getExternalTestsByFarmer(farmer: $getExternalTestsByFarmerFarmer) {        id        test {            html            results        }        createdAt        updatedAt        status        expiresAt        latitude        area        cropType        soilType        soilDensity        surveyNo        sampleDate        longitude    }}","variables":{"getExternalTestsByFarmerFarmer":"607eacd24c0c1c001ae74693"}}',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.SOILTEST_TOKEN,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, 1);

        $soilResults = $result['data']['getExternalTestsByFarmer'];

		foreach($soilResults as $soil_result)
		{
			if($soil_result['status'] == "Completed")
			{
				echo $soil_result['test']['html']; exit;
			}
		}
    }

    public function soilCreateTest(Request $request)
    {
        Setting::AssignSetting();

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => SOILTEST_URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"query":"mutation CreateExternalTestMutation($createExternalTestExternalTest: ExternalTestInput!) {    createExternalTest(externalTest: $createExternalTestExternalTest) {        id        computedID        status    }}","variables":{"createExternalTestExternalTest":{"expiresAt":"2021-04-21T05:12:06.712Z","latitude":15.87453,"longitude":12.456356,"area":12.65466564,"cropType":["Paddy"],"soilType":"Gray","soilDensity":1.234,"surveyNo":"53455","sampleDate":"2021-04-20T05:12:06.712Z","farmers":"607eacd24c0c1c001ae74693","avgYield":32.5435453}}}',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.SOILTEST_TOKEN,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        echo '<pre>'; print_r($response); exit;
    }

    public function soilGetFarmer(Request $request)
    {
        Setting::AssignSetting();

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => SOILTEST_URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"query":"query Query($getUsersOrganization: ID!, $getUsersPhone: PhoneNumber) {    getUsers(organization: $getUsersOrganization, phone: $getUsersPhone) {        id        latitude        longitude        name        address        phone        email        username        createdAt        updatedAt    }}","variables":{"getUsersOrganization":"6038ba9130a8cd001200110d","getUsersPhone":"+919999999999"}}',
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

        // Create existing customer as farmer
        $customers = \DB::table("customers")->whereNull('krishitantra_id')->get();

        foreach ($customers as $key => $row) {

        	$cust_id = $row->id;
        	$cust_name = $row->name;
        	$cust_name1 = str_replace(" ", "-", strtolower($row->name));
        	$address1 = $row->address1;

	        $curl = curl_init();

	        curl_setopt_array($curl, array(
	          CURLOPT_URL => SOILTEST_URL,
	          CURLOPT_RETURNTRANSFER => true,
	          CURLOPT_ENCODING => '',
	          CURLOPT_MAXREDIRS => 10,
	          CURLOPT_TIMEOUT => 0,
	          CURLOPT_FOLLOWLOCATION => true,
	          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	          CURLOPT_CUSTOMREQUEST => 'POST',
	          CURLOPT_POSTFIELDS => '{"query":"mutation CreateFarmerMutation($createFarmerFarmer: FarmerInput!) { createFarmer(farmer: $createFarmerFarmer) { id latitude longitude name address phone username createdAt updatedAt }}","variables":{"createFarmerFarmer":{"name":"'.$cust_name.'","address":"'.$address1.'","phone":"+919999999999","latitude":12.566465,"longitude":34.453666,"username":"TEST-'.$cust_name1.'"}}}',
	          CURLOPT_HTTPHEADER => array(
	            'Authorization: Bearer '.SOILTEST_TOKEN,
	            'Content-Type: application/json'
	          ),
	        ));

	        $response = curl_exec($curl);

	        curl_close($curl);

	        $result = json_decode($response, 1);

	        if(isset($result['data']['createFarmer']['id']))
	        {
	        	///echo $result['data']['createFarmer']['id'];
	        	\DB::table("customers")->where('id', $cust_id)->update(['krishitantra_id' => $result['data']['createFarmer']['id'], 'krishitantra_username' => 'TEST-'.$cust_name1]);
	        }
	        else
	        {
	        	echo $cust_name1.">".$result['errors'][0]['message']);
	        }
		}
    }

    public function soilMyInfo(Request $request)
    {
        Setting::AssignSetting();

        $curl = curl_init();

        curl_setopt_array($curl, array(
			CURLOPT_URL => SOILTEST_URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => '{"query":"query Query {me {organization {    id slug name} id username email phone}}","variables":{}}',
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
			CURLOPT_URL => SOILTEST_URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => '{"query":"mutation LoginMutation($loginOrganization: String!,$loginUsername: String!, $loginPassword: String!) {login(organization: $loginOrganization, username: $loginUsername, password: $loginPassword)}","variables":{"loginOrganization":"'.SOILTEST_ORGANIZATION.'","loginUsername":"'.SOILTEST_USERNAME.'","loginPassword":"'.SOILTEST_PASSWORD.'"}}',
			CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
			),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, 1);

        //dd($result);

        $token = $result['data']['login'];

        DB::table("settings")->where('id', '8')->update(['value' => $token]);
    }
}
