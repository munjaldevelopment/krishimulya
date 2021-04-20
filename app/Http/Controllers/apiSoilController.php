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
          CURLOPT_URL => 'https://soil-api-staging.krishitantra.com/graphql',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"query":"query Query($getExternalTestsByFarmerFarmer: ID!) {\\r\\ngetExternalTestsByFarmer(farmer: $getExternalTestsByFarmerFarmer) {\\r\\n        id\\r\\n        test {\\r\\n            html\\r\\n            results\\r\\n        }\\r\\n        createdAt\\r\\n        updatedAt\\r\\n        status\\r\\n        expiresAt\\r\\n        latitude\\r\\n        area\\r\\n        cropType\\r\\n        soilType\\r\\n        soilDensity\\r\\n        surveyNo\\r\\n        sampleDate\\r\\n        longitude\\r\\n    }\\r\\n}","variables":{"getExternalTestsByFarmerFarmer":"607eacd24c0c1c001ae74693"}}',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.SOILTEST_TOKEN,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, 1);

        $soilResults = $result['data']['getExternalTestsByFarmer']);

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
          CURLOPT_URL => 'https://soil-api-staging.krishitantra.com/graphql',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"query":"mutation CreateExternalTestMutation($createExternalTestExternalTest: ExternalTestInput!) {\\r\\n    createExternalTest(externalTest: $createExternalTestExternalTest) {\\r\\n        id\\r\\n        computedID\\r\\n        status\\r\\n    }\\r\\n}","variables":{"createExternalTestExternalTest":{"expiresAt":"2021-04-21T05:12:06.712Z","latitude":15.87453,"longitude":12.456356,"area":12.65466564,"cropType":["Paddy"],"soilType":"Gray","soilDensity":1.234,"surveyNo":"53455","sampleDate":"2021-04-20T05:12:06.712Z","farmers":"607eacd24c0c1c001ae74693","avgYield":32.5435453}}}',
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
