<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PartnersRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PartnersCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PartnersCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Partners::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/partners');
        CRUD::setEntityNameStrings('Partner', 'Partners');
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
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addColumn([
            'name' => 'pcode',
            'label' => 'Partner Code',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addColumn([
            'name' => 'email',
            'label' => 'Email',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addColumn([
            'name' => 'mobile',
            'label' => 'Mobile',
            'type' => 'text',
            'hint' => '',
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
        CRUD::setValidation(PartnersRequest::class);

        //CRUD::setFromDb(); // fields
        $maxpcode = \DB::table('partners')->select('id','pcode')->orderBy('id', 'DESC')->first();
        if(!empty($maxpcode) && $maxpcode->id != '') {
            $p_code = $maxpcode->pcode;
            $p_code = $p_code+1;
        }else{
            $p_code = 1;
        }
        $partner_code = str_pad($p_code, 5, "0", STR_PAD_LEFT);  

        $this->crud->addField([
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'hint' => '',
        ]);

         $this->crud->addField([
            'name' => 'pcode',
            'label' => 'Patner Code',
            'type' => 'text',
            'attributes' => ['readonly' => 'readonly'],
            'value' => $partner_code,
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'age',
            'label' => 'Birth of Year',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'email',
            'label' => 'Email Address',
            'type' => 'email',
            'hint' => '',
        ]);

         $this->crud->addField([
            'name' => 'password',
            'label' => 'Password',
            'type' => 'password',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'mobile',
            'label' => 'Mobile',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'address',
            'label' => 'Address',
            'type' => 'textarea',
            'hint' => '',
        ]);

    
        $this->crud->addField([
            'name' => 'city',
            'label' => 'City',
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
            'name' => 'pincode',
            'label' => 'Pincode',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addField([
                'name' => 'image',
                'label' => 'Image',
                'type' => 'browse',
            ]);

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
