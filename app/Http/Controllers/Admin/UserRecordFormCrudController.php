<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRecordFormRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class UserRecordFormCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserRecordFormCrudController extends CrudController
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
        CRUD::setModel(\App\Models\UserRecordForm::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/userrecord-form');
        CRUD::setEntityNameStrings('user record form', 'user record forms');

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
        $this->crud->disableResponsiveTable();
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
            'label'     => 'Date',
            'type'      => 'date',
            'name'      => 'survey_date',
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
            'label'     => 'Land Size',
            'type'      => 'text',
            'name'      => 'land_size',
         ]);

        $this->crud->addColumn([
            'label'     => 'Crop Type',
            'type'      => 'text',
            'name'      => 'crop_type',
         ]);

        $this->crud->addColumn([
            'label'     => 'Last Production',
            'type'      => 'text',
            'name'      => 'last_production',
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
        CRUD::setValidation(UserRecordFormRequest::class);

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
            'label'     => 'Date',
            'type'      => 'date',
            'name'      => 'survey_date',
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
            'label'     => 'Land Size',
            'type'      => 'text',
            'name'      => 'land_size',
         ]);

        $this->crud->addField([
            'label'     => 'Crop Type',
            'type'      => 'text',
            'name'      => 'crop_type',
         ]);

        $this->crud->addField([
            'label'     => 'Last Production',
            'type'      => 'text',
            'name'      => 'last_production',
         ]);

        $this->crud->addField([
            'label'     => 'Earning Sale',
            'type'      => 'text',
            'name'      => 'earning_sale',
         ]);

        $this->crud->addField([
            'label'     => 'Proposed Crop',
            'type'      => 'text',
            'name'      => 'proposed_crop',
         ]);

        $this->crud->addField([
            'label'     => 'Tractor Yes/ No',
            'type'      => 'select2_from_array',
            'options'   => ['Yes' => 'Yes', 'No' => 'No'],
            'name'      => 'tractor',
         ]);

        $this->crud->addField([
            'label'     => 'Tractor Make',
            'type'      => 'text',
            'name'      => 'tractor_make',
         ]);

        $this->crud->addField([
            'label'     => 'Tractor Model',
            'type'      => 'text',
            'name'      => 'tractor_model',
         ]);

        $this->crud->addField([
            'label'     => 'Tractor Finance or Free',
            'type'      => 'text',
            'name'      => 'tractor_finance_free',
         ]);

        $this->crud->addField([
            'label'     => 'Tractor Cultivation',
            'type'      => 'text',
            'name'      => 'tractor_cultivation',
         ]);

        $this->crud->addField([
            'label'     => 'Tractor Rental Price',
            'type'      => 'text',
            'name'      => 'rental_price',
         ]);

        $this->crud->addField([
            'label'     => 'Tractor Taken From',
            'type'      => 'text',
            'name'      => 'rent_taken_from',
         ]);

        $this->crud->addField([
            'label'     => 'Contact Number',
            'type'      => 'text',
            'name'      => 'contact_number',
         ]);

        $this->crud->addField([
            'label'     => 'Contact Details',
            'type'      => 'text',
            'name'      => 'contact_details',
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
