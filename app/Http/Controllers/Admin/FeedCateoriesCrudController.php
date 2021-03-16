<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\FeedCateoriesRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class FeedCateoriesCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class FeedCateoriesCrudController extends CrudController
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
        CRUD::setModel(\App\Models\FeedCateories::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/feedcateories');
        CRUD::setEntityNameStrings('Feed Cateories', 'Feed Cateories');
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
                'name' => 'lang',
                'label' => 'Language',
                'type' => 'text',
            ]);

        $this->crud->addColumn('name');

         $this->crud->addColumn('description');

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
        CRUD::setValidation(FeedCateoriesRequest::class);

        //CRUD::setFromDb(); // fields

         $this->crud->addField([
                'name' => 'lang',
                'label' => 'Language',
                'type' => 'select2_from_array',
                'options' => ['en' => 'English', 'hi' => 'Hindi'],
            ]);

         $this->crud->addField([
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'placeholder' => 'Your name here',
            ]);

         $this->crud->addField([
                'name' => 'description',
                'label' => 'Description',
                'type' => 'textarea',
                'placeholder' => 'Your description here',
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
