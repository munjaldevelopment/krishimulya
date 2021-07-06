<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TractorSellEnquiryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TractorSellEnquiryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TractorSellEnquiryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\TractorSellEnquiry::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/tractor_sell_enquiry');
        CRUD::setEntityNameStrings('Tractor Sale Enquiry', 'Tractor Sale Enquiry');

        $this->crud->enableExportButtons();

        $this->crud->addClause("where", "user_type", "=", "customer");
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
       // CRUD::setFromDb(); // columns

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */

         $this->crud->addColumn([
            'label'     => 'Customer Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allCustomers', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Customer", //name of Models

         ]);
         $this->crud->addColumn('company_name');
         $this->crud->addColumn('model');
         $this->crud->addColumn('hourse_power');
         $this->crud->addColumn('exp_price'); 
         $this->crud->addColumn('location');
         $this->crud->addColumn('sale_type'); 
         $this->crud->addColumn('year_manufacturer'); 
         $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Date',
            'type' => 'datetime',
        ]);
         $this->crud->addColumn('location');
         $this->crud->addColumn('hrs');
         $this->crud->addColumn('exp_price');
         $this->crud->addColumn('contact_person_name');
        $this->crud->addColumn('contact_person_phone');
         $this->crud->addFilter([ // select2 filter
                'name' => 'sale_type',
                'type' => 'select2',
                'label'=> 'Sale Type',
            ], function () {
                return ['Tractor (ट्रैक्टर)' => 'Tractor (ट्रैक्टर)', 'Equipment (उपकरण)' => 'Equipment (उपकरण)'];
            }, function ($value) { // if the filter is active
                $this->crud->addClause('where', 'sale_type', $value);
            });

         $this->crud->addFilter([ // select2 filter
                'name' => 'customer_id',
                'type' => 'select2',
                'label'=> 'All Customer',
            ], function () {
                $all_customers1 = array();
                $customers1 = \DB::table('customers')->orderBy('name')->get();
                if($customers1)
                {
                    foreach($customers1 as $row1)
                    {
                        $all_customers1[$row1->id] = ($row1->name != '') ? $row1->name : $row1->telephone;
                    }
                }
                return $all_customers1;
            }, function ($value) { // if the filter is active
                $this->crud->addClause('where', 'customer_id', $value);
            }); 
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TractorSellEnquiryRequest::class);

       // CRUD::setFromDb(); // fields

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
        $cities = \DB::table('cities')->orderBy('id')->get();
        if($cities)
        {
            foreach($cities as $row)
            {
                $all_city[$row->name] = $row->name;
            }
        }

        $all_company = array();
        
        $all_company[0] = 'Select';
        $company = \DB::table('company')->orderBy('id')->get();
        if($company)
        {
            foreach($company as $row)
            {
                $all_company[$row->title] = $row->title;
            }
        }


        $all_hp = array();
        
        $all_hp[0] = 'Select';
        $hpower = \DB::table('hpower')->orderBy('id')->get();
        if($company)
        {
            foreach($hpower as $row)
            {
                $all_hp[$row->title] = $row->title;
            }
        }

        $this->crud->addField([
                'label'     => 'Customer',
                'type'      => 'select2_from_array',
                'name'      => 'customer_id',
                'options'   => $all_customers
                
         ]);

        $this->crud->addField([
            'name' => 'sale_type',
            'label' => 'Sale Type',
            'type' => 'select2_from_array',
            'options' => ['Tractor (ट्रैक्टर)' => 'Tractor (ट्रैक्टर)', 'Equipment (उपकरण)' => 'Equipment (उपकरण)'],
            'hint' => '',
        ]);

        $yearList       =   array();
        $year = date('Y');
        $year2 = date('Y')-10;
        for($y = $year; $y>$year2; $y--){
            $yearList[$y] = $y;
        }
        $this->crud->addField([
            'name' => 'year_manufacturer',
            'label' => 'Registration Year',
            'type' => 'select2_from_array',
            'options' => $yearList,
            'hint' => '',
        ]);

        $this->crud->addField([
                'name' => 'company_name',
                'label' => 'Company Name',
                'type' => 'select2_from_array',
                'options'   => $all_company
            ]);

         

         $this->crud->addField([
                'name' => 'model',
                'label' => 'Model',
                'type' => 'text',
                'placeholder' => 'Your model here',
            ]);

         
        $this->crud->addField([
                'name' => 'hourse_power',
                'label' => 'Horse Power (HP)',
                'type' => 'select2_from_array',
                'options'   => $all_hp
            ]);

        $this->crud->addField([
                'label'     => 'Location',
                'type'      => 'select2_from_array',
                'name'      => 'location',
                'options'   => $all_city
                
         ]);

          $this->crud->addField([
                'name' => 'hrs',
                'label' => 'HRS',
                'type' => 'text',
                'placeholder' => 'Your HRS here',
            ]);

         $this->crud->addField([
                'name' => 'exp_price',
                'label' => 'Expexted Price',
                'type' => 'text',
                'placeholder' => 'Your HRS here',
            ]);

          $this->crud->addField([
                'name' => 'image',
                'label' => 'Image',
                'type' => 'browse',
                'placeholder' => 'Your year here',
            ]);
         

         $this->crud->addField([
                'name' => 'comment',
                'label' => 'Comment',
                'type' => 'textarea',
                'placeholder' => 'Your comment here',
            ]);
         $this->crud->addField([
                'name' => 'isactive',
                'label' => 'Is Active',
                'type' => 'checkbox',
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

    protected function setupShowOperation()
    {
        $this->setupListOperation();

        $this->crud->addColumn([
            'label'     => 'Customer Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allCustomers', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Customer", //name of Models

         ]);  
    }
}
