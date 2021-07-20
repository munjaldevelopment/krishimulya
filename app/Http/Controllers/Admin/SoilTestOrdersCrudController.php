<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SoilTestOrdersRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use App\Models\Setting;

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
          CURLOPT_POSTFIELDS =>'{"query":"query Query($getExternalTestsByFarmerFarmer: ID!) {getExternalTestsByFarmer(farmer: $getExternalTestsByFarmerFarmer) {        id        test {            html            results        }        createdAt        updatedAt        status        expiresAt        latitude        area        cropType        soilType        soilDensity        surveyNo        sampleDate        longitude    }}","variables":{"getExternalTestsByFarmerFarmer":"'.$krishitantra_order_id.'"}}',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.SOILTEST_TOKEN,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, 1);
        dd($result);

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
