<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CropMaterialsEnquiryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use URL;
use DB;
/**
 * Class CropMaterialsEnquiryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CropMaterialsEnquiryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitCropMaterialsEnquiryStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitCropMaterialsEnquiryUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\CropMaterialsEnquiry::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/cropmaterialsenquiry');
        CRUD::setEntityNameStrings('Crop Materials Enquiry', 'Crop Material Enquiries');

        $this->crud->addClause("where", "user_type", "=", "partner");
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
            'name'      => 'customer_id',
            'entity'    => 'allVendors', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Vendor", //name of Models

         ]);
        $this->crud->addColumn('crop_material');

        $this->crud->addColumn([
                'name' => 'image',
                'label' => 'Image',
                'type' => 'image',
            ]);
        $this->crud->addColumn([
            'name' => 'status',
            'label' => 'Is Approve',
            'type' => 'boolean',
            'hint' => '',                                                                           
        ]); 
        $this->crud->addColumn([
            'name' => 'created_at',
            'label' => 'Date',
            'type' => 'datetime',
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
        CRUD::setValidation(CropMaterialsEnquiryRequest::class);

        //CRUD::setFromDb(); // fields

        $all_customers = array();
        
        $all_customers[0] = 'Select';
        $customers = \DB::table('vendors')->orderBy('name')->get();
        if($customers)
        {
            foreach($customers as $row)
            {
                $all_customers[$row->id] = ($row->name != '') ? $row->name : $row->telephone;
            }
        }

        $this->crud->addField([
                'label'     => 'Customer',
                'type'      => 'select2_from_array',
                'name'      => 'customer_id',
                'options'   => $all_customers
                
         ]);

        $all_crop_materials = array();
        
        $all_crop_materials[0] = 'Select';
        $crop_materials = \DB::table('crop_materials')->orderBy('name')->get();
        if($crop_materials)
        {
            foreach($crop_materials as $row)
            {
                $all_crop_materials[$row->name] = $row->name;
            }
        }

        $this->crud->addField([
                'label'     => 'Crop Material',
                'type'      => 'select2_from_array',
                'name'      => 'crop_material',
                'options'   => $all_crop_materials
                
         ]);

        $this->crud->addField([
                'name' => 'image',
                'label' => 'Image',
                'type' => 'browse',
                'placeholder' => 'Your image here',
            ]);

        $this->crud->addField([
                'name' => 'description',
                'label' => 'Description',
                'type' => 'textarea',
                'placeholder' => 'Your description here',
            ]);
        $this->crud->addField([
                'name' => 'status',
                'label' => 'Is Approve',
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

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        //$this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        $result = $this->traitCropMaterialsEnquiryStore();
        $id = $this->crud->entry->id;

        $customers = DB::table('customers')->whereNotNull('fcmToken')->get();

        foreach($customers as $cust)
        {
            $title = $this->crud->getRequest()->title;
            $message1 = strip_tags($this->crud->getRequest()->content);
            $this->sendNotification($cust->id, $id, $title, $message1, '');
        }

        return $result;
    }    

    public function update()
    {
        //echo $this->crud->->title; exit;

        $this->crud->setRequest($this->crud->validateRequest());
        //$this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        $result = $this->traitCropMaterialsEnquiryUpdate();
        $id = $this->crud->getRequest()->id;

        $status = $this->crud->getRequest()->status;
        $partner_id = $this->crud->getRequest()->customer_id;
        $crop_material = $this->crud->getRequest()->crop_material;
        $description = $this->crud->getRequest()->description;
        $cropimage = $this->crud->getRequest()->image;
        $baseUrl = URL::to("/");
        
        $cropimageURL = "";
        if($cropimage){
            $cropimageURL  =  $baseUrl."/public/".$cropimage;
        }
       if($status == '1'){
             $vendors = DB::table('vendors')->where('id', $partner_id)->where('is_onboard', '=', '1')->first();
             if($vendors){ 
                $name = $vendors->name;
                $mobile = $vendors->phone; 
                
                /*$title = "Crop Material";
                $message1 = "Name: ".$name.", Phone:".$mobile.",Crop Material: ".$crop_material.",  Description:".$description;
                $this->sendNotification('266', $id, $title, $message1, $cropimageURL, $mobile);*/

                
                $customers = DB::table('customers')->whereNotNull('fcmToken')->get();
                if($customers){     
                    foreach($customers as $cust)
                    {
                        $title = "Crop Material";
                        $message1 = "Name: ".$name.", Phone:".$mobile.",Crop Material: ".$crop_material.",  Description:".$description;
                        $this->sendNotification($cust->id, $id, $title, $message1, $cropimageURL, $mobile);
                    }
                }
             }      
        }
        
        return $result;
    }

    public function sendNotification($customer_id, $lead_id, $title, $message, $image = '', $mobile)
    {
        $date = date('Y-m-d H:i:s');
        $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $customer_id, 'lead_id' => $lead_id, 'notification_title' => $title, 'notification_content' => $message, 'notification_type' => 'customer_notification', 'user_type' => 'customer', 'mobile' => $mobile, 'image' => $image,  'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);
    }
}
