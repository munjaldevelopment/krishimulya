<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\NotificationRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class NotificationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class NotificationCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitNotificationStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitNotificationUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Notification::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/notification');
        CRUD::setEntityNameStrings('Notification', 'Notifications');

        $this->crud->addColumn([
                    'label'     => 'Customer',
                    'type'      => 'select',
                    'name'      => 'customer_id',
                    'entity'    => 'customer', //function name
                    'attribute' => 'name', //name of fields in models table like districts
                    'model'     => "App\Model\Customer", //name of Models
                    ]);

        $this->crud->addColumn([
                    'label'     => 'Notification Title',
                    'type'      => 'text',
                    'name'      => 'notification_title',
                    ]);

        $this->crud->addColumn([
                    'label'     => 'Notification Type',
                    'type'      => 'text',
                    'name'      => 'notification_type',
                    ]);

        $this->crud->addColumn([
                    'label'     => 'Type',
                    'type'      => 'text',
                    'name'      => 'user_type',
                    ]);


        // Fields
        $this->crud->addField([
                    'label'     => 'Customer',
                    'type'      => 'select2',
                    'name'      => 'customer_id',
                    'entity'    => 'customer', //function name
                    'attribute' => 'name', //name of fields in models table like districts
                    'model'     => "App\Model\Customer", //name of Models
                    ]);

        $this->crud->addField([
                    'label'     => 'Notification Title',
                    'type'      => 'text',
                    'name'      => 'notification_title',
                    ]);

        $this->crud->addField([
                    'label'     => 'Notification Content',
                    'type'      => 'textarea',
                    'name'      => 'notification_content',
                    ]);

        $this->crud->addField([
                    'label'     => 'Notification Type',
                    'type'      => 'text',
                    'name'      => 'notification_type',
                    ]);

        $this->crud->addField([
                    'label'     => 'Type',
                    'type'      => 'text',
                    'name'      => 'user_type',
                    ]);

        $this->crud->addField([
                    'label'     => 'Active',
                    'type'      => 'checkbox',
                    'name'      => 'isactive',
                    ]);

        $this->crud->addButtonFromModelFunction('top', 'send_notification', 'sendUserNotication', 'end');

        
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
    protected function setupCreateOperation()
    {
        CRUD::setValidation(NotificationRequest::class);

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

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        //$this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        $result = $this->traitNotificationStore();

        return $result;
    }    

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        //$this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        $result = $this->traitNotificationUpdate();

        return $result;
    }
}
