<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\FeedsRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class FeedsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class FeedsCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitFeedStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitFeedUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Feeds::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/feeds');
        CRUD::setEntityNameStrings('feeds', 'feeds');

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
            'name' => 'language',
            'label' => 'Language',
            'type' => 'text',
        ]);
        
        $this->crud->addColumn([
            'label'     => 'Feed Category',
            'type'      => 'select',
            'name'      => 'category_id',
            'entity'    => 'allFeedCategories', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\FeedCategories", //name of Models

            ]);

        $this->crud->addColumn('title');


        
        $this->crud->addColumn([
            'name' => 'date',
            'label' => 'Date',
            'type' => 'date',
        ]);
        
        $this->crud->addColumn('status');

        $this->crud->addColumn([
            'name' => 'featured',
            'label' => 'Featured',
            'type' => 'check',
        ]);
        
        $this->crud->addFilter([ // select2 filter
            'name' => 'language',
            'type' => 'select2',
            'label'=> 'Language',
        ], function () {
            return ['en' => 'English', 'hi' => 'Hindi'];
        }, function ($value) { // if the filter is active
            $this->crud->addClause('where', 'language', $value);
        });
            
       

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
        //CRUD::setValidation(FeedsRequest::class);

        //CRUD::setFromDb(); // fields

        /*
        |--------------------------------------------------------------------------
        | CREATE & UPDATE OPERATIONS
        |--------------------------------------------------------------------------
        */
        //$this->crud->operation(['create', 'update'], function () {
        $this->crud->setValidation(FeedsRequest::class);

         $this->crud->addField([
            'name' => 'language',
            'label' => 'Language',
            'type' => 'select2_from_array',
            'options' => ['en' => 'English', 'hi' => 'Hindi'],
        ]);

        $all_categories = array();
    
        $all_categories[0] = 'Select';
        $states = \DB::table('feed_categories')->orderBy('name')->get();
        if($states)
        {
            foreach($states as $row)
            {
                $all_categories[$row->id] = $row->name;
            }
        }

        $this->crud->addField([
                'label'     => 'Feed Category',
                'type'      => 'select2_from_array',
                'name'      => 'category_id',
                'options'   => $all_categories
                
         ]);

        $this->crud->addField([
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
            'placeholder' => 'Your title here',
        ]);
        /*$this->crud->addField([
            'name' => 'slug',
            'label' => 'Slug (URL)',
            'type' => 'text',
            'hint' => 'Will be automatically generated from your title, if left empty.',
            // 'disabled' => 'disabled'
        ]);*/
        $this->crud->addField([
            'name' => 'date',
            'label' => 'Date',
            'type' => 'date',
            'default' => date('Y-m-d'),
        ]);

        $this->crud->addField([
            'name' => 'content',
            'label' => 'Content',
            'type' => 'ckeditor',
            'placeholder' => 'Your textarea text here',
        ]);

        /*$this->crud->addField([ // image
            'label' => "Image",
            'name' => "image",
            'type' => 'image',
            'upload' => true,
            'disk' => 'uploads/tractor_image'
        ],'both');*/

        $this->crud->addField([
            'name' => 'image',
            'label' => 'Image',
            'type' => 'browse',
        ]);

         $this->crud->addField([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'enum',
        ]);
            
        //});

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

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        //$this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        $result = $this->traitFeedStore();

        return $result;
    }    

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        //$this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        $result = $this->traitFeedUpdate();

        return $result;
    }
}
