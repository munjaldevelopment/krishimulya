<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Agri_type_enquiryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class Agri_type_enquiryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class Agri_type_enquiryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Agri_type_enquiry::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/agri_type_enquiry');
        CRUD::setEntityNameStrings('Agri Type Enquiry', 'Agri Type Enquiry');
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
            'entity'    => 'customers', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Customer", //name of Models

         ]);

        $this->crud->addColumn('agri_type');

        $this->crud->addColumn('city');

        

        $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Date',
            'type' => 'datetime',
        ]);
        
        $this->crud->addColumn('comment');

        $this->crud->addColumn([
            'name' => 'isactive',
            'label' => 'Is Active',
            'type' => 'boolean',
            'hint' => '',                                                                           
        ]); 

        $this->crud->addColumn('created_at');
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
        CRUD::setValidation(Agri_type_enquiryRequest::class);

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

        $all_agritype = array();
        
        $all_agritype[0] = 'Select';
        $agritype = \DB::table('agri_type')->orderBy('typename')->get();
        if($agritype)
        {
            foreach($agritype as $row)
            {
                $all_agritype[$row->typename] = $row->typename;
            }
        }

        $all_city = array();
        
        $all_city[0] = 'Select';
        $city = \DB::table('cities')->orderBy('name')->get();
        if($city)
        {
            foreach($city as $row)
            {
                $all_city[$row->name] = $row->name;
            }
        }

         $this->crud->addField([
                'label'     => 'Customer',
                'type'      => 'select2_from_array',
                'name'      => 'customer_id',
                'options'   => $all_customers
                
         ]);
         
        $this->crud->addField([
                'label'     => 'Agri Type',
                'type'      => 'select2_from_array',
                'name'      => 'agri_type',
                'options'   => $all_agritype
                
         ]);

         $this->crud->addField([
                'label'     => 'City',
                'type'      => 'select2_from_array',
                'name'      => 'city',
                'options'   => $all_city
                
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
