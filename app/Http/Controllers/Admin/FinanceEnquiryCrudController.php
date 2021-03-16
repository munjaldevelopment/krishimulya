<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\FinanceEnquiryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class Finance_enquiryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class FinanceEnquiryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\FinanceEnquiry::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/finance_enquiry');
        CRUD::setEntityNameStrings('Finance Enquiry', 'Finance Enquiries');
        $this->crud->enableExportButtons();
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */

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

    protected function setupListOperation()
    {
        //CRUD::setFromDb(); // columns
         //$this->crud->addColumn('customer_id'); 
         $this->crud->addColumn([
            'label'     => 'Customer Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allCustomers', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Customer", //name of Models

         ]);  
         $this->crud->addColumn('purpose');
         $this->crud->addColumn('usesof');
         $this->crud->addColumn('model');
         $this->crud->addColumn('ftype');

         $this->crud->addFilter([ // select2 filter
                'name' => 'ftype',
                'type' => 'select2',
                'label'=> 'Finance Type',
            ], function () {
                return ['new' => 'New', 'old' => 'Old'];
            }, function ($value) { // if the filter is active
                $this->crud->addClause('where', 'ftype', $value);
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
        /* $this->crud->addColumn([
            'name' => 'status',
            'label' => 'Is Active',
            'type' => 'boolean',
            'hint' => '',                                                                           
        ]); */
        
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
        CRUD::setValidation(FinanceEnquiryRequest::class);

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

        $this->crud->addField([
                'label'     => 'Customer',
                'type'      => 'select2_from_array',
                'name'      => 'customer_id',
                'options'   => $all_customers
                
         ]);

        $this->crud->addField([
                'name' => 'purpose',
                'label' => 'Purpose',
                'type' => 'textarea',
                'placeholder' => 'Your purpose here',
            ]);

         $this->crud->addField([
                'name' => 'usesof',
                'label' => 'Requirement',
                'type' => 'textarea',
                'placeholder' => 'Your Requirement here',
            ]);

         $this->crud->addField([
                'name' => 'brandname',
                'label' => 'Brand / Model',
                'type' => 'text',
                'placeholder' => 'Your brand / model here',
            ]);

         $this->crud->addField([
                'name' => 'year_of_manufacture',
                'label' => 'Manufacture Year',
                'type' => 'text',
                'placeholder' => 'Your year here',
            ]);

         $this->crud->addField([
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'placeholder' => 'Your name here',
            ]);

         $this->crud->addField([
                'name' => 'mobile',
                'label' => 'Mobile',
                'type' => 'text',
                'placeholder' => 'Your mobile here',
            ]);
         $this->crud->addField([
            'name' => 'ftype',
            'label' => 'Finance Type',
            'type' => 'select2_from_array',
            'options' => ['new' => 'New', 'old' => 'Old'],
            'hint' => '',
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
