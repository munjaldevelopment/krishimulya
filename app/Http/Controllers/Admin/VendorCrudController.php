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
                                'name' => 'pcode',
                                'label' => 'Pcode',
                                'type' => 'text',
                                'tab' => 'User'
                            ]);

        $this->crud->addField([
                                'name' => 'age',
                                'label' => 'Age',
                                'type' => 'date',
                                'tab' => 'User'
                            ]);

        $this->crud->addField([
                                'name' => 'image',
                                'label' => 'Image',
                                'type' => 'browse',
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
                                'options' => ['0' => 'Inactive', '1' => 'Active'],
                                'tab' => 'User'
                            ]);


        // Address
        $this->crud->addField([
                                'name' => 'address',
                                'label' => 'Address',
                                'type' => 'textarea',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'city',
                                'label' => 'City',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'state',
                                'label' => 'State',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'pincode',
                                'label' => 'Pincode',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'device_id',
                                'label' => 'Device',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'fcmToken',
                                'label' => 'fcmToken',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'otp',
                                'label' => 'otp',
                                'type' => 'number',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'app_version',
                                'label' => 'App Version',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        

        $this->crud->addField([
                'label' => 'Tags',
                'type' => 'relationship',
                'name' => 'vendorService', // the method that defines the relationship in your Model
                'entity' => 'vendorService', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'pivot' => true, // on create&update, do you need to add/delete pivot table entries?
                'tab'   => 'Vendor Service',
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
                                'name' => 'pcode',
                                'label' => 'Pcode',
                                'type' => 'text',
                                'tab' => 'User'
                            ]);

        $this->crud->addField([
                                'name' => 'age',
                                'label' => 'Age',
                                'type' => 'date',
                                'tab' => 'User'
                            ]);

        $this->crud->addField([
                                'name' => 'image',
                                'label' => 'Image',
                                'type' => 'browse',
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
                                'options' => ['0' => 'Inactive', '1' => 'Active'],
                                'tab' => 'User'
                            ]);

        // Address
        $this->crud->addField([
                                'name' => 'address',
                                'label' => 'Address',
                                'type' => 'textarea',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'city',
                                'label' => 'City',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'state',
                                'label' => 'State',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'pincode',
                                'label' => 'Pincode',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'device_id',
                                'label' => 'Device',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'fcmToken',
                                'label' => 'fcmToken',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'otp',
                                'label' => 'otp',
                                'type' => 'number',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                                'name' => 'app_version',
                                'label' => 'App Version',
                                'type' => 'text',
                                'tab' => 'Address'
                            ]);

        $this->crud->addField([
                'label' => 'Tags',
                'type' => 'relationship',
                'name' => 'vendorService', // the method that defines the relationship in your Model
                'entity' => 'vendorService', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'pivot' => true, // on create&update, do you need to add/delete pivot table entries?
                'tab'   => 'Vendor Service',
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

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run
        
        $result = $this->traitVendorStore();
        
        // Save Data in user table
        $id = $this->crud->entry->id;

        $user_id = User::insertGetId([
            'name' => $this->crud->getRequest()->name,
            'email' => $this->crud->getRequest()->email,
            'phone' => $this->crud->getRequest()->phone,
            'password' => Hash::make($this->crud->getRequest()->password),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        Vendor::where('id', $id)->update(['user_id' => $user_id]);
        
        // create role entry-
        \DB::table('model_has_roles')->insert(['role_id' => '2', 'model_type' => 'App\User', 'model_id' => $user_id]);

        if($this->crud->getRequest()->vendor_assign)
        {
            foreach($this->crud->getRequest()->vendor_assign as $k => $vendor_assign)
            {
                if(!is_null($vendor_assign))
                {
                    \DB::table('vendor_service_assign')->insert(['vendor_id' => $id, 'vendor_service_id' => $vendor_assign, 'zip_code' => $this->crud->getRequest()->vendor_assign_zipcode[$k], 'price' => $this->crud->getRequest()->vendor_assign_price[$k]]);
                }
            }
        }

        return $result;
    }

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        //$this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        $result = $this->traitVendorUpdate();

        $user_id = $this->crud->getRequest()->user_id;

        if($this->crud->getRequest()->password == NULL)
        {
            User::where('id', $user_id)->update(['name' => $this->crud->getRequest()->name, 'phone' => $this->crud->getRequest()->phone, 'email' => $this->crud->getRequest()->email, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        else
        {
            User::where('id', $user_id)->update(['name' => $this->crud->getRequest()->name, 'phone' => $this->crud->getRequest()->phone, 'email' => $this->crud->getRequest()->email, 'password' => Hash::make($this->crud->getRequest()->password), 'updated_at' => date('Y-m-d H:i:s')]);
        }

        if($this->crud->getRequest()->vendor_assign)
        {
            \DB::table('vendor_service_assign')->where('vendor_id', $this->crud->getRequest()->id)->delete();

            foreach($this->crud->getRequest()->vendor_assign as $k => $vendor_assign)
            {
                if(!is_null($vendor_assign))
                {
                    \DB::table('vendor_service_assign')->insert(['vendor_id' => $this->crud->getRequest()->id, 'vendor_service_id' => $vendor_assign, 'zip_code' => $this->crud->getRequest()->vendor_assign_zipcode[$k], 'price' => $this->crud->getRequest()->vendor_assign_price[$k]]);
                }
            }
        }
        
        return $result;
    }

    protected function handlePasswordInput($request)
    {
        // Remove fields not present on the user.
        $this->crud->getRequest()->request->remove('password_confirmation');

        // Encrypt password if specified.
        if ($this->crud->getRequest()->input('password')) {
            $this->crud->getRequest()->request->set('password', Hash::make($this->crud->getRequest()->input('password')));
        } else {
            $this->crud->getRequest()->request->remove('password');
        }

        return $this->crud->getRequest();
    }

    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        \DB::table('vendor_service_assign')->where('vendor_id', $id)->delete();
        //\DB::table('lender_banking_details')->where('lender_id', $id)->delete();

        return $this->crud->delete($id);
    }
}
