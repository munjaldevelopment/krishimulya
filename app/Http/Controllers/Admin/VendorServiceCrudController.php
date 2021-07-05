<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\VendorServiceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class VendorServiceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class VendorServiceCrudController extends CrudController
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
        CRUD::setModel(\App\Models\VendorService::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/vendorservice');
        CRUD::setEntityNameStrings('Vendor Service', 'Vendor Services');
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

        $this->crud->addColumn('service_code');
        $this->crud->addColumn([
                'name' => 'service_color',
                'label' => 'Color',
                'type' => 'color',
            ]);
        $this->crud->addColumn('name');

        $this->crud->addColumn([
                'name' => 'image',
                'label' => 'Image',
                'type' => 'image',
            ]);

        $this->crud->addColumn([
                'name' => 'status',
                'label' => 'Status',
                'type' => 'check',
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
        CRUD::setValidation(VendorServiceRequest::class);

        $this->crud->addField('service_code');
        $this->crud->addField([
                'name' => 'service_color',
                'label' => 'color',
                'type' => 'color',
            ]);
        $this->crud->addField('name');
        $this->crud->addField('table_name');

        $this->crud->addField([
                'name' => 'image',
                'label' => 'image',
                'type' => 'browse',
            ]);

        $this->crud->addField([
                'name' => 'status',
                'label' => 'Status',
                'type' => 'checkbox',
            ]);

        //CRUD::setFromDb(); // fields

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
