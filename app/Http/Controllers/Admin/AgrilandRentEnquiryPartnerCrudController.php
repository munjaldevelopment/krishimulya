<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AgrilandRentEnquiryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AgrilandRentEnquiryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AgrilandRentEnquiryPartnerCrudController extends CrudController
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
        CRUD::setModel(\App\Models\AgrilandRentEnquiry::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/agrilandrentenquiry_partner');
        CRUD::setEntityNameStrings('Agri Land Rent Enquiry', 'Agri land Rent Enquiry');

        $this->crud->addClause("where", "user_type", "=", "partner");
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
            'label'     => 'Vendor Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allVendors', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Vendor", //name of Models

         ]);

         $this->crud->addColumn('location');
         $this->crud->addColumn('size_in_acore');
          $this->crud->addColumn('how_much_time');  
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
        CRUD::setValidation(AgrilandRentEnquiryRequest::class);

        //CRUD::setFromDb(); // fields

        $all_customers = array();
        
        $all_customers[0] = 'Select';
        $customers = \DB::table('vendors')->orderBy('name')->get();
        if($customers)
        {
            foreach($customers as $row)
            {
                $all_customers[$row->id] = ($row->name != '') ? $row->name : $row->phone;
            }
        }

        $all_city = array();
        
        $all_city[0] = 'Select';
        $cities = \DB::table('cities')->orderBy('id','asc')->get();
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

        $all_rent_time = array();

        $all_rent_time[0] = 'Select';
        $rent_time = \DB::table('rent_time')->orderBy('id')->get();
        if($rent_time)
        {
            foreach($rent_time as $row)
            {
                $all_rent_time[$row->title] = $row->title;
            }
        }

        $this->crud->addField([
                'label'     => 'Vendor',
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
                'label'     => 'Location',
                'type'      => 'select2_from_array',
                'name'      => 'location',
                'options'   => $all_city
            ]); 

        $this->crud->addField([
                'name' => 'size_in_acore',
                'label' => 'Size In Acre',
                'type'      => 'select2_from_array',
                'options'   => $all_landSize
            ]); 

         $this->crud->addField([
                'name' => 'how_much_time',
                'label' => 'How much time (Year) you want in Rent',
                'type'      => 'select2_from_array',
                'options'   => $all_rent_time
            ]); 

        $this->crud->addField([
                'name' => 'comment',
                'label' => 'Comment',
                'type' => 'textarea',
                'placeholder' => 'Your comment here',
            ]);

        $this->crud->addField([
                'name' => 'is_edit',
                'label' => 'Is Edit',
                'type' => 'checkbox',
            ]);

        $this->crud->addField([
                'name' => 'isactive',
                'label' => 'Is Active',
                'type' => 'checkbox',
            ]);

        $this->crud->addField([
                'label'     => 'Person Name',
                'type'      => 'text',
                'name'      => 'contact_person_name'
            ]); 

        $this->crud->addField([
                'label'     => 'Person Phone',
                'type'      => 'text',
                'name'      => 'contact_person_phone'
            ]); 

        $this->crud->addField([
                'label'     => 'Person OTP',
                'type'      => 'text',
                'name'      => 'contact_person_otp'
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
