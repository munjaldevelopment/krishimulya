<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SoilTestOrderDataRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SoilTestOrderDataCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SoilTestOrderDataCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\SoilTestOrderData::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/soiltest-orderdata');
        CRUD::setEntityNameStrings('soil test order data', 'soil test order datas');

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
        //CRUD::removeColumn('soil_test_order_id');
        $this->crud->addColumn([
            'label'     => 'Soil Test Order',
            'type'      => 'select_html',
            'name'      => 'soil_test_order_id',
            'entity'    => 'soilTestOrder', //function name
            'attribute' => 'soil_test_row', //name of fields in models table like districts
            'model'     => "App\Models\SoilTestOrder", //name of Models

         ]);

        CRUD::setFromDb(); // columns

        $this->crud->disableResponsiveTable();

        

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }
}
