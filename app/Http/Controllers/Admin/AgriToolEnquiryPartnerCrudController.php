<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Agri_tool_enquiryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class Agri_tool_enquiryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AgriToolEnquiryPartnerCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Agri_tool_enquiry::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/agri_tool_enquiry_partner');
        CRUD::setEntityNameStrings('Agri Tool Enquiry', 'Agri Tool Enquiry');

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
       // CRUD::setFromDb(); // columns

         $this->crud->addColumn([
            'label'     => 'Partner Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allVendors', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Vendor", //name of Models

         ]);

        $this->crud->addColumn('agri_tool');

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
        CRUD::setValidation(Agri_tool_enquiryRequest::class);

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

        $all_agritype = array();
        
        $all_agritool[0] = 'Select';
        $agritool = \DB::table('agri_tool_type')->orderBy('title')->get();
        if($agritool)
        {
            foreach($agritool as $row)
            {
                $all_agritool[$row->title] = $row->title;
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
                'label'     => 'Agri Tool',
                'type'      => 'select2_from_array',
                'name'      => 'agri_tool',
                'options'   => $all_agritool
                
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
