<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AgrilandsaleEnquiryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AgrilandsaleEnquiryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AgrilandsaleEnquiryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\AgrilandsaleEnquiry::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/agrilandsaleenquiry');
        CRUD::setEntityNameStrings('Agri Land Sale Enquiry', 'Agri Land Sale Enquiry');

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
        //CRUD::setFromDb(); // columns

         $this->crud->addColumn([
            'label'     => 'Customer Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allCustomers', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Customer", //name of Models

         ]);

         $this->crud->addColumn('location');
         $this->crud->addColumn('size_in_acre');
        //  $this->crud->addColumn('exp_price');  
         $this->crud->addColumn('land_type');
        
         $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Date',
            'type' => 'datetime',
        ]);
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
        CRUD::setValidation(AgrilandsaleEnquiryRequest::class);

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

        $all_landType = array();
        
        $all_landType[0] = 'Select';
        $landType = \DB::table('land_type')->orderBy('id')->get();
        if($landType)
        {
            foreach($landType as $row)
            {
                $all_landType[$row->title] = $row->title;
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

        $this->crud->addField([
                'label'     => 'Customer',
                'type'      => 'select2_from_array',
                'name'      => 'customer_id',
                'options'   => $all_customers
                
         ]);

        $this->crud->addField([
            'name' => 'land_type',
            'label' => 'Land Type',
            'type' => 'select2_from_array',
            'options' => $all_landType,
            'hint' => '',
        ]);

        $this->crud->addField([
                'name' => 'location',
                'label' => 'Location',
                'type' => 'select2_from_array',
                'options'   => $all_city
            ]); 

        $this->crud->addField([
                'name' => 'size_in_acre',
                'label' => 'Size In Acre',
                'type' => 'select2_from_array',
                'options' => $all_landSize,
                'hint' => '',
            ]); 

         $this->crud->addField([
                'name' => 'exp_price',
                'label' => 'Expected Price',
                'type' => 'text',
                'placeholder' => 'Expected Price',
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
}
