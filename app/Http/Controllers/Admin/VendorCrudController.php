<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Auth;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\VendorRequest;
use App\Http\Requests\VendorUpdateRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class VendorCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class VendorCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitVendorStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitVendorUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation { destroy as traitDestroy; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    
    use \Backpack\ReviseOperation\ReviseOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Vendor::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vendor');
        CRUD::setEntityNameStrings('Vendor', 'Vendors');

        $this->crud->addColumn([
                'label'     => 'User',
                'type'      => 'select',
                'name'      => 'user_id',
                'entity'    => 'users', //function name
                'attribute' => 'name', //name of fields in models table like districts
                'model'     => "App\User", //name of Models

                ]);
        
        $this->crud->addColumn([
                                'name' => 'name',
                                'label' => 'Name',
                                'type' => 'text',
                            ]);

        $this->crud->addColumn([
                                'name' => 'email',
                                'label' => 'Email',
                                'type' => 'email',
                            ]);

        $this->crud->addColumn([
                                'name' => 'phone',
                                'label' => 'Phone',
                                'type' => 'tel',
                            ]);
                    
        // fields
        //$this->crud->enableAjaxTable();

        $this->crud->addFilter([
              'type' => 'text',
              'name' => 'name',
              'label'=> 'Name'
            ],
            false,
            function($value) {
                $this->crud->addClause('where', 'name', 'LIKE', "%$value%");
        });
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
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function addVendorFields()
    {
        $this->crud->addField([
                'label'     => 'User',
                'type'      => 'select',
                'name'      => 'user_id',
                'entity'    => 'users', //function name
                'attribute' => 'name', //name of fields in models table like districts
                'model'     => "App\User", //name of Models
                'wrapperAttributes' => [
                    //'style' => 'display:none;'
                ],
                'tab' => 'User'
                ]);
                
        $this->crud->addField([
                                'name' => 'name',
                                'label' => 'Name',
                                'type' => 'text',
                                'tab' => 'User'
                            ]);
                            
        $this->crud->addField([
                                'name' => 'email',
                                'label' => 'Email',
                                'type' => 'text',
                                'tab' => 'User'
                            ]);
                            
        $this->crud->addField([
                                'name' => 'phone',
                                'label' => 'Phone',
                                'type' => 'tel',
                                'tab' => 'User'
                            ]);
                            
        $this->crud->addField([
                                'name' => 'password',
                                'label' => 'Password',
                                'type' => 'password',
                                'tab' => 'User'
                            ]);
        
        $this->crud->addField([
                                'name' => 'is_onboard',
                                'label' => 'User On-board',
                                'type' => 'select2_from_array',
                                'options' => ['Approve' => 'Approve', 'Pending' => 'Pending', 'Onboarded' => 'Onboarded'],
                                'tab' => 'User'
                            ]);
                            
                    
        $this->crud->addField([
                                'name' => 'vendor_assign',
                                'label' => 'Vendor Assign',
                                'type' => 'vendor_assign',
                                //'allows_multiple' => 'true',
                                'tab'   => 'Vendor Assign Info',
                            ]);
                            
        
        
    }
    
    protected function updateVendorFields()
    {
        $this->crud->addField([
                'label'     => 'User',
                'type'      => 'select',
                'name'      => 'user_id',
                'entity'    => 'users', //function name
                'attribute' => 'name', //name of fields in models table like districts
                'model'     => "App\User", //name of Models
                'wrapperAttributes' => [
                    //'style' => 'display:none;'
                ],
                'tab' => 'User'
                ]);
                
        $this->crud->addField([
                                'name' => 'name',
                                'label' => 'Name',
                                'type' => 'text',
                                'tab' => 'User'
                            ]);
                            
        $this->crud->addField([
                                'name' => 'email',
                                'label' => 'Email',
                                'type' => 'text',
                                'tab' => 'User'
                            ]);
                            
        $this->crud->addField([
                                'name' => 'phone',
                                'label' => 'Phone',
                                'type' => 'tel',
                                'tab' => 'User'
                            ]);
                            
        $this->crud->addField([
                                'name' => 'password',
                                'label' => 'Password',
                                'type' => 'password',
                                'tab' => 'User'
                            ]);
        
        $this->crud->addField([
                                'name' => 'is_onboard',
                                'label' => 'User On-board',
                                'type' => 'select2_from_array',
                                'options' => ['Yet to onboarded' => 'Yet to onboarded', 'Onboarded' => 'Onboarded'],
                                'tab' => 'User'
                            ]);
                            
                            
        $this->crud->addField([
                                'name' => 'vendor_assign',
                                'label' => 'Vendor Assign',
                                'type' => 'vendor_assign_edit',
                                'allows_multiple' => 'true',
                                'tab'   => 'Vendor Assign Info',
                            ]);
                            
                            
        
    }

    protected function setupCreateOperation()
    {
        $this->addVendorFields();
        CRUD::setValidation(VendorRequest::class);

        CRUD::setFromDb(); // fields

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
        $this->updateVendorFields();
        CRUD::setValidation(VendorUpdateRequest::class);

        CRUD::setFromDb(); // fields
        //$this->setupCreateOperation();
    }
}
