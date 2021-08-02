<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CustomerRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CustomerCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CustomerCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Customer::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/customer');
        CRUD::setEntityNameStrings('customer', 'customers');

        $this->crud->enableExportButtons();
        
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

        $this->crud->disableResponsiveTable();

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
        $this->crud->addColumn([
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'hint' => '',
        ]);

         /*$this->crud->addColumn([
            'name' => 'age',
            'label' => 'Birth of Date',
            'type' => 'date',
            'hint' => '',                                                                           
        ]);*/

        $this->crud->addColumn([
            'name' => 'telephone',
            'label' => 'Mobile',
            'type' => 'text',
            'hint' => '',                                                                           
        ]);

        /*$this->crud->addColumn([
            'name' => 'farmer_id',
            'label' => 'Farmer ID',
            'type' => 'text',
            'hint' => '',                                                                           
        ]);*/

        $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Date',
            'type' => 'datetime',
        ]);

        $this->crud->addColumn([
            'name' => 'city',
            'label' => 'City',
            'type' => 'text',
            'hint' => '',                                                                           
        ]);

        $this->crud->addColumn([
            'name' => 'pincode',
            'label' => 'Pincode',
            'type' => 'text',
            'hint' => '',                                                                           
        ]);

        $this->crud->addColumn([
                'label'     => 'State',
                'type'      => 'select',
                'name'      => 'state',
                'entity'    => 'allStates', //function name
                'attribute' => 'name', //name of fields in models table like districts
                'model'     => "App\Models\States", //name of Models

                ]);

        

        $this->crud->addColumn([
            'name' => 'status',
            'label' => 'Is Active',
            'type' => 'boolean',
            'hint' => '',                                                                           
        ]); 
        
        $this->crud->addFilter([ // select2 filter
                'name' => 'name',
                'type' => 'text',
                'label'=> 'Name',
            ], function () {
               
               
            }, function ($value) { // if the filter is active
                $this->crud->addClause('where', 'name', $value);
            });

        $this->crud->addFilter([ // select2 filter
                'name' => 'status',
                'type' => 'select2',
                'label'=> 'Is Active',
            ], function () {
                return ['1' => 'Yes', '0' => 'No'];
            }, function ($value) { // if the filter is active
                $this->crud->addClause('where', 'status', $value);
            });

       $this->crud->addFilter([ // select2 filter
                'name' => 'state',
                'type' => 'select2',
                'label'=> 'All State',
            ], function () {
                $all_states1 = array();
                $states1 = \DB::table('states')->orderBy('name')->get();
                if($states1)
                {
                    foreach($states1 as $row1)
                    {
                        $all_states1[$row1->id] = $row1->name;
                    }
                }
                return $all_states1;
            }, function ($value) { // if the filter is active
                $this->crud->addClause('where', 'state', $value);
        });

       $this->crud->addFilter([ // select2 filter
                'name' => 'created_at',
                'type' => 'date_range',
                'label'=> 'Date',
            ], 
            false
            , function ($value) { // if the filter is active
                $dates = json_decode($value);
                $this->crud->addClause('where', 'created_at', '>=', $dates->from);
                $this->crud->addClause('where', 'created_at', '<=', $dates->to . ' 23:59:59');
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
        CRUD::setValidation(CustomerRequest::class);

        //CRUD::setFromDb(); // fields
        /*$this->crud->addField([
            'name' => 'first_name',
            'label' => 'First Name',
            'type' => 'select2_from_array',
            'options' => ['--Select--', 'A' => 'A', 'B' => 'B'],
            'hint' => '',
        ]);*/

        $this->crud->addField([
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'age',
            'label' => 'Birth of Date',
            'type' => 'date',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'email',
            'label' => 'Email Address',
            'type' => 'email',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'telephone',
            'label' => 'Mobile',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'address1',
            'label' => 'Address',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'address2',
            'label' => 'Land mark',
            'type' => 'text',
            'hint' => '',
        ]);

         $this->crud->addField([
            'name' => 'city',
            'label' => 'City',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'pincode',
            'label' => 'Pincode',
            'type' => 'text',
            'hint' => '',
        ]);

        
        $all_states = array();
        
        $all_states[0] = 'Select';
        $states = \DB::table('states')->orderBy('name')->get();
        if($states)
        {
            foreach($states as $row)
            {
                $all_states[$row->id] = $row->name;
            }
        }

        $this->crud->addField([
                'label'     => 'state',
                'type'      => 'select2_from_array',
                'name'      => 'state',
                'options'   => $all_states
                
         ]);

         $this->crud->addField([
                'name' => 'image',
                'label' => 'Image',
                'type' => 'browse',
            ]);

        /*$this->crud->addField([
            'name' => 'farmer_id',
            'label' => 'Farmer ID',
            'type' => 'text',
            'hint' => '',
        ]);
         */
        $this->crud->addField([
            'name' => 'status',
            'label' => 'Is Active',
            'type' => 'select2_from_array',
            'options' => ['1' => 'Yes', '0' => 'No'],
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

   
}
