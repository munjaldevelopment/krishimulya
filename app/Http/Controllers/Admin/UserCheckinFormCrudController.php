<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserCheckinFormRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class UserCheckinFormCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCheckinFormCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\UserCheckinForm::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/usercheckinform');
        CRUD::setEntityNameStrings('user checkin form', 'user checkin forms');

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

        $this->crud->addColumn([
            'label'     => 'User',
            'type'      => 'select',
            'name'      => 'user_id',
            'entity'    => 'users', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\User", //name of Models
         ]);

        $this->crud->addColumn([
            'label'     => 'Checkin-out',
            'type'      => 'select',
            'name'      => 'users_checkin_out_id',
            'entity'    => 'checkinOut', //function name
            'attribute' => 'checkin_time', //name of fields in models table like districts
            'model'     => "App\Models\UserCheckinOut", //name of Models
         ]);
        $this->crud->addColumn([
            'label'     => 'Name',
            'type'      => 'text',
            'name'      => 'customer_name',

         ]);
        $this->crud->addColumn([
            'label'     => 'Mobile',
            'type'      => 'text',
            'name'      => 'mobile_number',

         ]);
        $this->crud->addColumn([
            'label'     => 'Call Type',
            'type'      => 'select',
            'name'      => 'call_type_id',
            'entity'    => 'callType', //function name
            'attribute' => 'type_name', //name of fields in models table like districts
            'model'     => "App\Models\CallType", //name of Models
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
        CRUD::setValidation(UserCheckinFormRequest::class);

        //CRUD::setFromDb(); // fields

        $this->crud->addField([
            'label'     => 'User',
            'type'      => 'select2',
            'name'      => 'user_id',
            'entity'    => 'users', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\User", //name of Models
         ]);

        //', 'customer_name', 'mobile_number', 'call_type_id
        $this->crud->addField([
            'label'     => 'Checkin-out',
            'type'      => 'select2',
            'name'      => 'users_checkin_out_id',
            'entity'    => 'checkinOut', //function name
            'attribute' => 'checkin_time', //name of fields in models table like districts
            'model'     => "App\Models\UserCheckinOut", //name of Models
         ]);
        $this->crud->addField([
            'label'     => 'Name',
            'type'      => 'text',
            'name'      => 'customer_name',

         ]);
        $this->crud->addField([
            'label'     => 'Mobile',
            'type'      => 'tel',
            'name'      => 'mobile_number',

         ]);
        $this->crud->addField([
            'label'     => 'Call Type',
            'type'      => 'select2',
            'name'      => 'call_type_id',
            'entity'    => 'callType', //function name
            'attribute' => 'type_name', //name of fields in models table like districts
            'model'     => "App\Models\CallType", //name of Models
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
