<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LabourEnquiryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class LabourEnquiryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LabourEnquiryPartnerCrudController extends CrudController
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
        CRUD::setModel(\App\Models\LabourEnquiry::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/labour_enquiry_partner');
        CRUD::setEntityNameStrings('Labour Enquiry', 'Labour Enquiry');
        $this->crud->enableExportButtons();

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

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */

        $this->crud->addColumn([
            'label'     => 'Partner Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allVendors', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Vendor", //name of Models

         ]);  
         $this->crud->addColumn('location');
         $this->crud->addColumn('purpose');
         $this->crud->addColumn('labour_no');

         $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Date',
            'type' => 'datetime',
        ]);

         $this->crud->addFilter([ // select2 filter
                'name' => 'customer_id',
                'type' => 'select2',
                'label'=> 'All Vendor',
            ], function () {
                $all_customers1 = array();
                $customers1 = \DB::table('vendors')->orderBy('name')->get();
                if($customers1)
                {
                    foreach($customers1 as $row1)
                    {
                        $all_customers1[$row1->id] = ($row1->name != '') ? $row1->name : $row1->phone;
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
        CRUD::setValidation(LabourEnquiryRequest::class);

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
        $cities = \DB::table('cities')->orderBy('name')->get();
        if($cities)
        {
            foreach($cities as $row)
            {
                $all_city[$row->name] = $row->name;
            }
        }

        $all_purposeType = array();
        
        $all_purposeType[0] = 'Select';
        $purposeType = \DB::table('purpose_type')->orderBy('title')->get();
        if($purposeType)
        {
            foreach($purposeType as $row)
            {
                $all_purposeType[$row->title] = $row->title;
            }
        }

        $this->crud->addField([
                'label'     => 'Customer',
                'type'      => 'select2_from_array',
                'name'      => 'customer_id',
                'options'   => $all_customers
                
         ]);

        $this->crud->addField([
                'name' => 'location',
                'label' => 'Location',
                'type' => 'select2_from_array',
                'options'   => $all_city
            ]);

        $this->crud->addField([
            'name' => 'purpose',
            'label' => 'Purpose',
            'type' => 'select2_from_array',
            'options'   => $all_purposeType
        ]);

        /* $this->crud->addField([
                'name' => 'purpose',
                'label' => 'Purpose',
                'type' => 'textarea',
                'placeholder' => 'Write here',
            ]);*/

         $this->crud->addField([
                'name' => 'labour_no',
                'label' => 'Labour Number',
                'type' => 'text',
                'placeholder' => 'Labour no.',
            ]);

         $this->crud->addField([
                'name' => 'comments',
                'label' => 'Comments',
                'type' => 'textarea',
                'placeholder' => 'Write here',
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
