<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\WalletPaymentRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class WalletPaymentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class WalletPaymentCrudController extends CrudController
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
        CRUD::setModel(\App\Models\WalletPayment::class);
        $this->crud->enableExportButtons(); CRUD::setRoute(config('backpack.base.route_prefix') . '/walletpayment');
        CRUD::setEntityNameStrings('Wallet Payment', 'Wallet Payments');
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
            'label'     => 'Partner Name',
            'type'      => 'select',
            'name'      => 'partner_id',
            'entity'    => 'allpartners', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Partners", //name of Models

         ]);

         $this->crud->addColumn('amount');
        // $this->crud->addColumn('comment');
        
        

         $this->crud->addColumn('payment_status');
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
        CRUD::setValidation(WalletPaymentRequest::class);

        //CRUD::setFromDb(); // fields

        $all_partners = array();
        
        $all_partners[0] = 'Select';
        $partners = \DB::table('partners')->orderBy('name')->get();
        if($partners)
        {
            foreach($partners as $row)
            {
                $all_partners[$row->id] = ($row->name != '') ? $row->name : $row->telephone;
            }
        }

        $this->crud->addField([
                'label'     => 'Partner',
                'type'      => 'select2_from_array',
                'name'      => 'partner_id',
                'options'   => $all_partners
                
         ]);
        $this->crud->addField([
                'name' => 'amount',
                'label' => 'Amount',
                'type' => 'text',
                'placeholder' => 'Amount',
            ]); 


        $this->crud->addField([
            'name' => 'payment_status',
            'label' => 'Payment Status',
            'type' => 'select2_from_array',
            'options' => ['pending' => 'Pending', 'done' => 'Done' , 'cancelled' => 'Cancel'],
            'hint' => '',
        ]);

        $this->crud->addField([
                'label'     => 'Comment',
                'type'      => 'textarea',
                'name'      => 'comment',
                'placeholder' => 'Your comment here',
                
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
