<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\InsuranceEnquiryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class InsuranceEnquiryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class InsuranceEnquiryPartnerCrudController extends CrudController
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
        CRUD::setModel(\App\Models\InsuranceEnquiry::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/insuranceenquiry_partner');
        CRUD::setEntityNameStrings('Insurance Enquiry', 'Insurance Enquiries');

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
            'label'     => 'Partner Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allVendors', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Vendor", //name of Models

         ]);

        /*$this->crud->addColumn([
            'label'     => 'Insurance Type',
            'type'      => 'select',
            'name'      => 'insurance_type',
            'entity'    => 'allInsuranceType', //function name
            'attribute' => 'title', //name of fields in models table like districts
            'model'     => "App\Models\InsuranceType", //name of Models

         ]);*/

         $this->crud->addColumn('insurance_type');
         $this->crud->addColumn('comments');
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
        CRUD::setValidation(InsuranceEnquiryRequest::class);

        //CRUD::setFromDb(); // fields

        $all_customers = array();
        
        $all_customers[0] = 'Select';
        $customers = \DB::table('vendors')->orderBy('name', 'asc')->get();
        if($customers)
        {
            foreach($customers as $row)
            {
                $all_customers[$row->id] = ($row->name != '') ? $row->name : $row->phone;
            }
        }


        $all_insurance = array();
        
        $all_insurance[0] = 'Select';
        $insurance = \DB::table('insurance_type')->orderBy('id', 'asc')->get();
        if($insurance)
        {
            foreach($insurance as $row)
            {
                $all_insurance[$row->title] = $row->title;
            }
        }

        $this->crud->addField([
                'label'     => 'Customer',
                'type'      => 'select2_from_array',
                'name'      => 'customer_id',
                'options'   => $all_customers
                
         ]);

        $this->crud->addField([
                'label'     => 'Insurance Type',
                'type'      => 'select2_from_array',
                'name'      => 'insurance_type',
                'options'   => $all_insurance
                
         ]);

         $this->crud->addField([
                'name' => 'comments',
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
