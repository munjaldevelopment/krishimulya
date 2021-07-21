<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\HomeSliderRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class HomeSliderCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class HomeSliderCrudController extends CrudController
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
        CRUD::setModel(\App\Models\HomeSlider::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/homeslider');
        CRUD::setEntityNameStrings('Home Slider', 'Home Slider');
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
                'name' => 'language',
                'label' => 'Language',
                'type' => 'text',
            ]);

        $this->crud->addColumn([
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
            ]);

        $this->crud->addColumn([
                'name' => 'isactive',
                'label' => 'Is Active',
                'type' => 'boolean',
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
        CRUD::setValidation(HomeSliderRequest::class);

        //CRUD::setFromDb(); // fields

        $this->crud->addField([
                'name' => 'language',
                'label' => 'Language',
                'type' => 'select2_from_array',
                'options' => ['en' => 'English', 'hi' => 'Hindi'],
            ]);

        $this->crud->addField([
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
                'placeholder' => 'Your title here',
            ]);

        $this->crud->addField([
                'name' => 'image',
                'label' => 'Image',
                'type' => 'browse',
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
