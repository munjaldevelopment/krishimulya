<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class SoilTestOrderCropData extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'soil_test_order_crop_data';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [
        'soil_test_order_id',
        'soil_test_order_data_id',
        'ph_value', 'ph_unit', 'ph_ideal_range', 'ph_rating', 'nutrient_1',
        'oc_value', 'oc_unit', 'oc_ideal_range', 'oc_rating', 'nutrient_2',
        'nitrogen_value', 'nitrogen_unit', 'nitrogen_ideal_range', 'nitrogen_rating', 'nutrient_3',
        'potassium_value', 'potassium_unit', 'potassium_ideal_range', 'potassium_rating', 'nutrient_4',
        'copper_value', 'copper_unit', 'copper_ideal_range', 'copper_rating', 'nutrient_5',
        'zinc_value', 'zinc_unit', 'zinc_ideal_range', 'zinc_rating', 'nutrient_6',
        'salinity_value', 'salinity_unit', 'salinity_idea_range', 'salinity_rating', 'nutrient_7',
        'organic_value', ' organic_unit', 'organic_idea_range', 'organic_rating', 'nutrient_8',
        'phosphorus_value', 'phosphorus_unit', 'phosphorus_idea_range', 'phosphorus_rating', 'nutrient_9',
        'sulphur_value', 'sulphur_unit', 'sulphur_idea_range', 'sulphur_rating', 'nutrient_10',
        'iron_rating', 'iron_value', 'iron_unit', 'iron_idea_range', 'nutrient_11',
        'boron_value', 'boron_unit', 'boron_idea_range', 'boron_rating', 'nutrient_12'
    ];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function soilTestOrder()
    {
        return $this->belongsTo('App\Models\SoilTestOrder', 'soil_test_order_id');
    }

    public function soilTestOrderData()
    {
        return $this->belongsTo('App\Models\SoilTestOrderData', 'soil_test_order_data_id');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
