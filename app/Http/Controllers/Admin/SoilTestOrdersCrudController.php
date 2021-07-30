<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SoilTestOrdersRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use App\Models\Setting;

use File;
use Illuminate\Support\Facades\Storage;

/**
 * Class SoilTestOrdersCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SoilTestOrdersCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public static function add_directory( $directory, $cache_path ) {

        if (!File::exists( $cache_path.'/'.$directory)) {
            File::makeDirectory($cache_path . '/' . $directory, 0775, true, true);
        }
    }

    public function setup()
    {
        CRUD::setModel(\App\Models\SoilTestOrders::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/soiltestorders');
        CRUD::setEntityNameStrings('Soil Test Orders', 'Soil Test Orders');

        $this->crud->addClause("where", "user_type", "=", "customer");

        $this->crud->addButtonFromView('line', 'download_pdf', 'download_pdf', 'end');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //CRUD::setFromDb(); // columns

        $this->crud->addColumn([
            'label'     => 'Customer Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allCustomers', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Customer", //name of Models

         ]);

        //$this->crud->addColumn('name');
        $this->crud->addColumn('mobile');
         
        $this->crud->addColumn('order_no');
        $this->crud->addColumn('land_size');
        $this->crud->addColumn('khasra_no');
        $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Date',
            'type' => 'datetime',
        ]);
        $this->crud->addColumn('amount');
        $this->crud->addColumn('contact_person_name');
        $this->crud->addColumn('contact_person_phone');
        /*$this->crud->addColumn([
            'name' => 'kt_report_id',
            'label' => 'Krishitantra Report ID',
            'type' => 'text',
            'hint' => '',                                                                           
        ]);*/
         //$this->crud->addColumn('land_size');
         //$this->crud->addColumn('location');
        //$this->crud->addColumn('test_type');
        //$this->crud->addColumn('amount');
        //$this->crud->addColumn('order_status');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(SoilTestOrdersRequest::class);

        //CRUD::setFromDb(); // fields

        $all_customers = array();
        
        $all_customers[0] = 'Select';
        $customers = \DB::table('customers')->orderBy('name')->get();
        if($customers)
        {
            foreach($customers as $row)
            {
                $all_customers[$row->id] = ($row->name != '') ? $row->name : $row->telephone;
            }
        }

        $all_city = array();
        
        $all_city[0] = 'Select';
        $cities = \DB::table('cities')->orderBy('name')->get();
        if($cities)
        {
            foreach($cities as $row)
            {
                $all_city[$row->name] = $row->name;
            }
        }

        $all_landSize = array();

        $all_landSize[0] = 'Select';
        $landSize = \DB::table('land_size')->orderBy('id')->get();
        if($landSize)
        {
            foreach($landSize as $row)
            {
                $all_landSize[$row->title] = $row->title;
            }
        }

         $all_testType = array();

        $all_testType[0] = 'Select';
        $test_type = \DB::table('soil_test_type')->orderBy('id')->get();
        if($test_type)
        {
            foreach($test_type as $row)
            {
                $all_testType[$row->title] = $row->title;
            }
        }

         $this->crud->addField([
                'label'     => 'Customer',
                'type'      => 'select2_from_array',
                'name'      => 'customer_id',
                'options'   => $all_customers
                
         ]);

         $this->crud->addField([
                'name' => 'order_no',
                'label' => 'Order No',
                'type' => 'text',
                'placeholder' => 'Order No.',
            ]); 

        /*$this->crud->addField([
            'name' => 'kt_report_id',
            'label' => 'Krishitantra Report ID',
            'type' => 'text',
            'hint' => '',
        ]);*/

         $this->crud->addField([
                'label'     => 'Size',
                'type'      => 'select2_from_array',
                'name'      => 'land_size',
                'options'   => $all_landSize
                
         ]);

         $this->crud->addField([
                'label'     => 'Address Location',
                'type'      => 'textarea',
                'name'      => 'location',
                'placeholder' => 'Your location here',
                
         ]);

         $this->crud->addField([
                'name' => 'khasra_no',
                'label' => 'Khasra No',
                'type' => 'text',
                'placeholder' => 'Khasra No.',
            ]); 

         $this->crud->addField([
                'label'     => 'Test Type',
                'type'      => 'select2_from_array',
                'name'      => 'test_type',
                'options'   => $all_testType
                
         ]);

          $this->crud->addField([
                'name' => 'amount',
                'label' => 'Amount',
                'type' => 'text',
                'placeholder' => 'Amount',
            ]); 


        $this->crud->addField([
                'name' => 'crop_type',
                'label' => 'Crop Type',
                'type' => 'text',
                'placeholder' => 'Crop Type',
            ]); 
        $this->crud->addField([
                'name' => 'soil_type',
                'label' => 'soil_type',
                'type' => 'text',
                'placeholder' => 'Soil Type',
            ]); 
        $this->crud->addField([
                'name' => 'soil_density',
                'label' => 'Soil Density',
                'type' => 'text',
                'placeholder' => 'Soil Density',
            ]); 
        $this->crud->addField([
                'name' => 'avg_yield',
                'label' => 'Avg. Yield',
                'type' => 'text',
                'placeholder' => 'Avg. Yield',
            ]); 


          $this->crud->addField([
            'name' => 'order_status',
            'label' => 'Order Status',
            'type' => 'select2_from_array',
            'options' => ['pending' => 'Pending', 'done' => 'Done' , 'cancelled' => 'Cancel'],
            'hint' => '',
        ]);
        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function downloadSoilTest(Request $request)
    {
        $soil_test_id = $request->soil_test_id;
        $soilTest = \DB::table('soil_test_orders')->find($soil_test_id);

        $krishitantra_order_id = $soilTest->krishitantra_farmer_id;

        //dd($krishitantra_order_id);

        Setting::AssignSetting();

        /*$curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => SOILTEST_URL,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"query":"query Query($getExternalTestsByFarmerFarmer: ID!) {getExternalTestsByFarmer(farmer: $getExternalTestsByFarmerFarmer) {        id        test {            html            results        }        createdAt        updatedAt        status        expiresAt        latitude        area        cropType        soilType        soilDensity        surveyNo        sampleDate        longitude    }}","variables":{"getExternalTestsByFarmerFarmer":"'.$krishitantra_order_id.'"}}',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.SOILTEST_TOKEN,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, 1);

        if(isset($result['data']))
        {
            $soilResults = $result['data']['getExternalTestsByFarmer'];

            foreach($soilResults as $soil_result)
            {
                if($soil_result['status'] == "Completed")
                {
                    if($soil_result['test']['html'] != "")
                    {
                        // Create Directory
                        $root_path = public_path('/');

                        $this::add_directory("uploads/soil-test-orders/".$soil_test_id, $root_path);

                        $disk = Storage::disk('uploads')->put('soil-test-orders/'.$soil_test_id.'/soil-test.html', $soil_result['test']['html']);

                        \DB::table('soil_test_orders')->where('id', $soil_test_id)->update(['soil_test_html' => 'uploads/soil-test-orders/'.$soil_test_id.'/soil-test.html']);
                    }
                }
            }
        }*/

        $filename = $soilTest->soil_test_html;

        if (Storage::disk('public')->exists($filename)) {
            $html = Storage::disk('public')->get($filename);
            $html = str_replace("Soil Test Report", "मिट्टी परिक्षण रिपोर्ट (Soil Health Card)", $html);
            $html = str_replace("Powered By", "", $html);
            $html = str_replace('<style type="text/css">', '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><style type="text/css">', $html);
            $html = str_replace("data:image/png;base64,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", env('APP_URL')."/uploads/logo/512-png-short.png", $html);

            $ajaxcode = '
            <script>
            var html_table_data = "";  
            var bRowStarted = true;  
            $(".Table1 tbody>tr").each(function () {  
                $("td", this).each(function () {  
                    if (html_table_data.length == 0 || bRowStarted == true) {  
                        html_table_data += $(this).text();  
                        bRowStarted = false;  
                    }  
                    else  
                        html_table_data += " | " + $(this).text();  
                });  
                html_table_data += "\n";  
                bRowStarted = true;  
            });  

            alert(html_table_data);

            $.ajax({
                url: "saveSoilTest",
                type: "post",
                data: {soil_test_id : '.$soil_test_id.', famer_name : $("tr:nth-child(2) td:nth-child(2)").html(), famer_code : $("tr:nth-child(2) td:nth-child(4)").html(), crop_grown : $("tr:nth-child(2) td:nth-child(6)").html(), sample_number : $("tr:nth-child(2) td:nth-child(8)").html(), field_size : $("tr:nth-child(3) td:nth-child(2)").html(), sampling_date : $("tr:nth-child(3) td:nth-child(4)").html(), sample_testing_date : $("tr:nth-child(3) td:nth-child(6)").html(), region : $("tr:nth-child(3) td:nth-child(8)").html(), previous_season : $("tr:nth-child(4) td:nth-child(2)").html(), sample_collected_by : $("tr:nth-child(4) td:nth-child(4)").html(), yield_goal : $("tr:nth-child(4) td:nth-child(6)").html(), previous_crop : $("tr:nth-child(4) td:nth-child(8)").html()},
                success:function(response) {
                    console.log(response);
                }
            }); 

            </script>';

            $html = str_replace('</body>', $ajaxcode.'</body>', $html);
            echo $html; exit;
        }
    }

    public function saveSoilTest(Request $request)
    {
        echo strip_tags(trim($request->famer_name));
        dd($request->all());
    }    


    public function downloadSoilTestPartner(Request $request)
    {
        $soil_test_id = $request->soil_test_id;
        $soilTest = \DB::table('soil_test_orders')->find($soil_test_id);

        $krishitantra_order_id = $soilTest->krishitantra_order_id;

        //dd($krishitantra_order_id);

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
          CURLOPT_POSTFIELDS =>'{"query":"query Query($getExternalTestsByFarmerFarmer: ID!) {getExternalTestsByFarmer(farmer: $getExternalTestsByFarmerFarmer) { id test {html results} createdAt updatedAt status expiresAt latitude  area cropType soilType soilDensity  surveyNo sampleDate longitude    }}","variables":{"getExternalTestsByFarmerFarmer":"'.$krishitantra_order_id.'"}}',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.SOILTEST_TOKEN,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, 1);

        if(isset($result['data']))
        {
            $soilResults = $result['data']['getExternalTestsByFarmer'];

            foreach($soilResults as $soil_result)
            {
                if($soil_result['status'] == "Completed")
                {
                    echo $soil_result['test']['html']; exit;
                }
            }
        }
    }
}
