<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class createSoiltestOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:soiltest_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Soiltest Order';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Setting::AssignSetting();
        exit;

        // Create existing customer as farmer
        $customers = \DB::table("customers")->whereNull('krishitantra_id')->skip(0)->take(40)->get();

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
              CURLOPT_POSTFIELDS => '{"query":"mutation CreateFarmerMutation($createFarmerFarmer: FarmerInput!) { createFarmer(farmer: $createFarmerFarmer) { id latitude longitude name address phone username createdAt updatedAt }}","variables":{"createFarmerFarmer":{"name":"'.$cust_name.'","address":"'.$address1.'","phone":"+919999999999","latitude":12.566465,"longitude":34.453666,"username":"KRISHIMULYA-'.$cust_name1.'"}}}',
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
                \DB::table("customers")->where('id', $cust_id)->update(['krishitantra_id' => $result['data']['createFarmer']['id'], 'krishitantra_username' => 'KRISHIMULYA-'.$cust_name1]);
            }
            else
            {
                echo $cust_name1.">".$result['errors'][0]['message'].'<br />';
                
            }
        }

        // vendors
        $customers = \DB::table("vendors")->whereNull('krishitantra_id')->skip(0)->take(40)->get();

        foreach ($customers as $key => $row) {

            $cust_id = $row->id;
            $cust_name = $row->name;
            $cust_name1 = str_replace(" ", "-", strtolower($row->name));
            $address1 = $row->address;

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
              CURLOPT_POSTFIELDS => '{"query":"mutation CreateFarmerMutation($createFarmerFarmer: FarmerInput!) { createFarmer(farmer: $createFarmerFarmer) { id latitude longitude name address phone username createdAt updatedAt }}","variables":{"createFarmerFarmer":{"name":"'.$cust_name.'","address":"'.$address1.'","phone":"+919999999999","latitude":12.566465,"longitude":34.453666,"username":"KRISHIMULYA-'.$cust_name1.'"}}}',
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
                \DB::table("vendors")->where('id', $cust_id)->update(['krishitantra_id' => $result['data']['createFarmer']['id'], 'krishitantra_username' => 'KRISHIMULYA-'.$cust_name1]);
            }
            else
            {
                echo $cust_name1.">".$result['errors'][0]['message'].'<br />';
            }
        }

        // Save existing Krishi Tantra ID
        $customers = \DB::table("customers")->whereNull('krishitantra_id')->skip(0)->take(40)->get();

        foreach ($customers as $key => $row) {

            $cust_id = $row->id;
            $cust_name = $row->name;
            $cust_name1 = str_replace(" ", "-", strtolower($row->name));

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
              CURLOPT_POSTFIELDS => '{"query":"query Query($getUsersOrganization: ID!, $getUsersUsername: String) { getUsers(organization: $getUsersOrganization, username: $getUsersUsername)  {     id     latitude     longitude     name     address     phone     email     username     createdAt     updatedAt }}","variables":{"getUsersOrganization":"6038ba9130a8cd001200110d","getUsersUsername":"KRISHIMULYA-'.$cust_name1.'"}}',
              CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.SOILTEST_TOKEN,
                'Content-Type: application/json'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $result = json_decode($response, 1);

            if(isset($result['data']['getUsers']))
            {
                foreach($result['data']['getUsers'] as $userRow)
                {
                    //echo $userRow['id'].",".$userRow['username']; exit;
                    ///echo $result['data']['createFarmer']['id'];
                    \DB::table("customers")->where('id', $row->id)->update(['krishitantra_id' => $userRow['id'], 'krishitantra_username' => $userRow['username']]);
                }
            }
        }
    }
}
