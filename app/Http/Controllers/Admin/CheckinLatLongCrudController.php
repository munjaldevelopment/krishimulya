<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CheckinLatLongRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CheckinLatLongCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CheckinLatLongCrudController extends CrudController
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
        CRUD::setModel(\App\Models\CheckinLatLong::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/checkinlatlong');
        CRUD::setEntityNameStrings('checkin latlong', 'checkin lat longs');
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
            'label'     => 'Checkin',
            'type'      => 'datetime',
            'name'      => 'checkin_date',
         ]);
        $this->crud->addColumn([
            'label'     => 'Lat',
            'type'      => 'text',
            'name'      => 'user_lat',

         ]);
        $this->crud->addColumn([
            'label'     => 'Long',
            'type'      => 'text',
            'name'      => 'user_long',

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
        CRUD::setValidation(CheckinLatLongRequest::class);

        //CRUD::setFromDb(); // fields

        $this->crud->addField([
            'label'     => 'User',
            'type'      => 'select2',
            'name'      => 'user_id',
            'entity'    => 'users', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\User", //name of Models
         ]);

        $this->crud->addField([
            'label'     => 'Checkin',
            'type'      => 'datetime',
            'name'      => 'checkin_date',
         ]);
        $this->crud->addField([
            'label'     => 'Lat',
            'type'      => 'text',
            'name'      => 'user_lat',

         ]);
        $this->crud->addField([
            'label'     => 'Long',
            'type'      => 'text',
            'name'      => 'user_long',

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
